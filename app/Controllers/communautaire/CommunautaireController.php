<?php

namespace App\Controllers\communautaire;

use App\Controllers\BaseController;
use App\Entities\Image;
use App\Entities\MotCle;
use App\Entities\Utilisateur;
use App\Models\CategorieModel;
use App\Models\ImageModel;
use App\Models\ThemeModel;
use App\Models\MotCle\MotCleCategorieModel;
use App\Models\MotCle\MotCleModel;
use App\Models\MotCle\MotCleValidationModel;
use App\Models\MotCle\MotCleVoteModel;
use App\Models\UtilisateurModel;
use App\Models\VoteModel;
use CodeIgniter\Config\Services;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Session\Session;
use CodeIgniter\Validation\Validation;

/**
 * Ce controller correspond à la gestion de la page communautaire
 * @since 1.0.0
 * @package Cyber4all
 * @author Benjamin Lorieul
 */
class CommunautaireController extends BaseController
{
    protected MotCleModel $motCle_model;
    protected MotCleValidationModel $motCleValidation_model;
    protected Session $session;
    protected CategorieModel $categorie_model;
    protected ?Utilisateur $utilisateur = null;
    protected Validation $validation;
    protected MotCleCategorieModel $motCleCategorie_model;
    protected MotCleVoteModel $motCleVote_model;
    protected ImageModel $image_model;
    protected ThemeModel $theme_model;
    protected VoteModel $vote_model;
    protected UtilisateurModel $utilisateur_model;

    public function __construct()
    {
        $this->motCle_model = new MotCleModel();
        $this->motCleValidation_model = new MotCleValidationModel();
        $this->motCleCategorie_model = new MotCleCategorieModel();
        $this->motCleVote_model = new MotCleVoteModel();
        $this->vote_model = new VoteModel();
        $this->session = session();
        $this->validation = Services::validation();
        $this->categorie_model = new CategorieModel();
        $this->image_model = new ImageModel();
        $this->utilisateur_model = new UtilisateurModel();
        $this->theme_model = new ThemeModel();
        // Vérification de la session et du rôle de l'utilisateur
        $utilisateur = $this->session->get('utilisateur');

        if ($utilisateur && in_array($utilisateur->role, ['contributeur'], true)) {
            $this->utilisateur = $utilisateur;
        } else {
            throw PageNotFoundException::forPageNotFound('Accès refusé');
        }
    }

    /**
     * Index correspond à la page par défaut de communautaire
     * @return string|RedirectResponse;
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function index(): string|RedirectResponse
    {
        // récupère tous les mots votés de la semaine
        $mots = $this->motCleVote_model->getAllMotCleVotesCetteSemaineByUtilisateurId(
            $this->session->get('utilisateur')->id);

        $data = [
            'DataCommunautaire' => $this->setDataCommunautaire($mots),
            'themeList' => $this->theme_model->getAllThemes(),
        ];

        return view('header', $data)
            . view('notice')
            . view('communautaire/communautaire')
            . view('footer');
    }


    public function ajouterDemandeMot(): string|RedirectResponse
    {
        $file = $this->request->getFile('nouvImage');
        $hasImage = $file && $file->isValid();

        $data['categoriesOptions'] = $this->getCategoriesOptions();

        // Validation des champs
        $this->validation->setRules([
            'nouvMot' => [
                'label' => 'Mot',
                'rules' => 'required|min_length[2]',
                'errors' => [
                    'required' => 'Le mot est obligatoire',
                    'min_length' => '2 caractères minimum'
                ]
            ],
            'nouvLegend' => [
                'label' => 'Légende',
                'rules' => $hasImage ? 'required|min_length[5]' : 'permit_empty',
                'errors' => [
                    'required' => 'La légende est obligatoire si vous ajoutez une image',
                    'min_length' => '5 caractères minimum'
                ]
            ]
        ]);

        if ($this->request->getPost('actionMot') === 'Soumettre') {
            $dataMot = [
                'mot' => $this->request->getPost('nouvMot'),
                'definition' => $this->request->getPost('nouvDefinition'),
                'definition_complete' => $this->request->getPost('nouvDefinitionComplete')
            ];

            if ($this->validation->withRequest($this->request)->run()) {
                $nouvMot = new MotCle($dataMot);
                // Mise à jour du mot
                $nouvMot->mot = $dataMot['mot'];
                $nouvMot->definition = $dataMot['definition'];
                $nouvMot->definition_complete = $dataMot['definition_complete'];
                $nouvMot->id_auteur = $this->utilisateur->id;

                if (is_numeric($idMotCle = $this->motCle_model->insertMotCle($nouvMot))) {
                    if ($idMotCle == -1) {
                        $this->session->setFlashdata('notice', [
                            'type' => 'error',
                            'titre' => "Erreur de modification",
                            'details' => "Vous avez déjà suggérer un mot portant le même nom. "
                        ]);

                        return redirect()->to(base_url('communautaire'));

                    }

                    // Gestion des catégories
                    $categoriesIdsToAdd = $this->request->getPost('nouvCategories') ?? [];
                    $categoriesAdded = false;

                    if (!empty($categoriesIdsToAdd)) {
                        $categoriesAdded = $this->motCleCategorie_model->insertCategoriesMotCle($categoriesIdsToAdd, $idMotCle);
                    }

                    // Gestion de l'image
                    $imageAdded = $hasImage ? $this->processImageUpload($idMotCle) : false;

                    $details = '';
                    $details .= $imageAdded ? "L'image a été ajoutée. " : "Aucune image ajoutée. ";
                    $details .= $categoriesAdded ? "Les catégories ont été liées. " : "Aucune catégorie liée. ";
                    // Résultat
                    if ((empty($categoriesAdded) || $categoriesAdded) && $imageAdded) {
                        $this->session->setFlashdata('notice', [
                            'type' => 'success',
                            'titre' => "Demande d'ajout du mot réussie, un administrateur validera ou non votre suggestion d'ajout au glossaire !",
                            'details' => $details,
                        ]);
//                        return redirect()->to(base_url('admin/mots/update/'.(int)$idMotCle));
                        return redirect()->to(base_url('communautaire'));

                    } else {
                        $this->session->setFlashdata('notice', [
                            'type' => 'success',
                            'titre' => "Demande d'ajout du mot réussie, un administrateur validera ou non votre suggestion d'ajout au glossaire !",
                            'details' => '',
                        ]);
                        return redirect()->to(base_url('communautaire'));
                    }

                } else {
                    $this->session->setFlashdata('notice', [
                        'type' => 'error',
                        'titre' => "Demande d'ajout du mot échouée",
                        'details' => $this->validation->getErrors(),
                    ]);

                    $data['motCle'] = $nouvMot;
                    $data['categoriesSelect'] = $this->request->getPost('nouvCategories');
                }
            } else {
                $this->session->setFlashdata('notice', [
                    'type' => 'error',
                    'titre' => "Demande d'ajout du mot échoué",
                    'details' => $this->validation->getErrors(),
                ]);
                $data['categoriesSelect'] = $this->request->getPost('nouvCategories');
            }
        }

        return view('header', $data)
            . view('notice')
            . view('communautaire/formMotCle')
            . view('footer');
    }

    /**
     * Permet de modifier un motCle directement en tant qu'administrateur.
     * Traite et valide ou non les données du formulaire de modification du mot.
     * @param int $idMotCle id du MotCle à modifier
     * @return string|RedirectResponse Affichage du formulaire de modification du mot
     * @since 1.0.0
     */
    public function updateMot(int $idMotCle): string|RedirectResponse
    {

        $file = $this->request->getFile('nouvImage');
        $hasImage = $file && $file->isValid();

        // Validation des champs
        $this->validation->setRules([
            'nouvMot' => [
                'label' => 'Mot',
                'rules' => 'required|min_length[2]',
                'errors' => [
                    'required' => 'Le mot est obligatoire',
                    'min_length' => '2 caractères minimum'
                ]
            ],
            'nouvDefinition' => [
                'label' => 'Définition',
                'rules' => 'required|min_length[25]',
                'errors' => [
                    'required' => 'La définition du mot est obligatoire',
                    'min_length' => '50 caractères minimum'
                ]
            ],
            'nouvLegend' => [
                'label' => 'Légende',
                'rules' => $hasImage ? 'required|min_length[5]' : 'permit_empty',
                'errors' => [
                    'required' => 'La légende est obligatoire si vous ajoutez une image',
                    'min_length' => '5 caractères minimum'
                ]
            ]
        ]);

        $motCle = $this->motCle_model->getMotCleById($idMotCle);
        $data['motCle'] = $motCle;
        $data['images'] = $this->image_model->getImagesByMotId($idMotCle);
        $data['categoriesOptions'] = $this->getCategoriesOptions();
        $categories = $this->categorie_model->getCategoriesMotCle($motCle->id);
        $data['nbSignalements'] = $this->motCle_model->getNbSignalementsMot($idMotCle);


        // Pré-cochage des catégories déjà liées
        $data['categoriesSelect'] = array_column($categories, 'id');
        $data['actionContributeur'] = 'communautaire/mots/update/';
        if ($this->request->getPost('actionMot') === 'Modifier') {

            $nouvMot = $this->request->getPost('nouvMot');
            $nouvDefinition = $this->request->getPost('nouvDefinition');
            $nouvDefinitionComplete = $this->request->getPost('nouvDefinitionComplete');


            if ($this->validation->withRequest($this->request)->run()) {

                $dataMot = [
                    'mot' => $nouvMot,
                    'definition' => $nouvDefinition,
                    'definition_complete' => $nouvDefinitionComplete
                ];

                // Vérification des changements
                $hasChanges = false;

                if ($motCle->mot !== $dataMot['mot'] ||
                    $motCle->definition !== $dataMot['definition'] ||
                    $motCle->definition_complete !== $dataMot['definition_complete']) {

                    $hasChanges = true;
                    $motCle->mot = $dataMot['mot'];
                    $motCle->definition = $dataMot['definition'];
                    $motCle->definition_complete = $dataMot['definition_complete'];
                }

                // Mise à jour du mot uniquement si modifié
                $motUpdated = $this->motCle_model->updateMotCle($motCle);
//                if ($motUpdated === -1) {
//                    $this->session->setFlashdata('notice', [
//                        'type' => 'error',
//                        'titre' => "Erreur de modification",
//                        'details' => "Vous avez déjà un mot identique enregistré."
//                    ]);
//                    return redirect()->back()->withInput();
//                }

                // Gestion des catégories
                $postedCategories = $this->request->getPost('nouvCategories') ?? [];
                $existingCategories = array_column($categories, 'id');

                $categoriesIdsToAdd = array_diff($postedCategories, $existingCategories);
                $categoriesIdsToRemove = array_diff($existingCategories, $postedCategories);

                $categoriesAdded = true;
                $categoriesDeleted = true;

                if (!empty($categoriesIdsToAdd)) {
                    $categoriesAdded = $this->motCleCategorie_model->insertCategoriesMotCle($categoriesIdsToAdd, $idMotCle);
                    $hasChanges = true;
                }

                if (!empty($categoriesIdsToRemove)) {
                    $categoriesDeleted = $this->motCleCategorie_model->deleteCategoriesMotCle($categoriesIdsToRemove, $idMotCle);
                    $hasChanges = true;
                }

                // Gestion de l'image
                $imageAdded = true;
                if ($hasImage) {
                    $imageAdded = $this->processImageUpload($idMotCle);
                    $hasChanges = true;
                }

                // Résultat
                if ($hasChanges) {
                    $data['motCle'] = $this->motCle_model->getMotCleById($idMotCle);
                    $data['images'] = $this->image_model->getImagesByMotId($idMotCle);
                    $categories = $this->categorie_model->getCategoriesMotCle($motCle->id);
                    $data['categoriesSelect'] = array_column($categories, 'id');

                    $this->session->setFlashdata('notice', [
                        'type' => 'success',
                        'titre' => "Le mot '{$motCle->mot}' a été modifié",
//                        'details' => ($imageAdded ? "L'image a été ajoutée. " : "Aucune image modifiée. ")
//                            . ($categoriesAdded ? "Les catégories ont été mises à jour. " : "Aucune modification de catégorie. ")
//                            . ($hasChanges && $motUpdated ? "Le mot a été mis à jour." : "")
                    ]);
                } else {
                    $this->session->setFlashdata('notice', [
                        'type' => 'info',
                        'titre' => "Aucun changement détecté",
                        'details' => "Vous n'avez apporté aucune modification au mot."
                    ]);
                }
            } else {
                $this->session->setFlashdata('notice', [
                    'type' => 'error',
                    'titre' => "Modification du mot échouée",
                    'details' => $this->validation->getErrors(),
                ]);
            }
        }

        return view('header', $data)
            . view('notice', $data)
            . view('communautaire/formMotCle')
            . view('footer');
    }

    /**
     * setDataMotsGlossaire permet de définir les variables de data.
     * Utiliser pour afficher le glossaire complet ou le lexique d'une catégorie avec une navbar.
     * @param array $mots Tableau de mots
     * @return array Tableau contenant les variables de data
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    private function setDataCommunautaire(array $mots): array
    {
        // Vérification de la session et du role de l'utilisateur
        if ($this->session->has('utilisateur')
            && in_array($this->session->get('utilisateur')->role,
                ['contributeur'], true)) {
            $nbVotes = $this->vote_model->getNbVotesSemaineActuelleByUtilisateurId($this->utilisateur->id);

            // récupère le nombre de votes effectué durant la semaine
            $data = [
                'nb_votes_total' => $this->vote_model->getNbVotesTotalByUtilisateurId($this->utilisateur->id),
                'nb_votes_restants' => NB_VOTES_MAX - $nbVotes,
                'nb_votes' => $nbVotes,
                // récupérer les mots votés par le contributeur
                'motsVotes' => $this->motCleVote_model->getAllMotCleVotesCetteSemaineByUtilisateurId($this->utilisateur->id),
                // Gestion de la redirection après vote ou suppression de vote
                'from' => base_url('communautaire'),
                // Récupère tous les id des mots votés depuis lundi
                'tab_id_mots' => $this->vote_model->getIdMotCleVotesSemaineActuelleByUtilisateurId($this->utilisateur->id),
            ];
        }

        $data = array_merge($data,
            [
                'categories' => $this->categorie_model->getAllCategories(),
                // récupérer les mots populaires votés par la communauté
                'motsPopulaires' => $this->motCleVote_model->getAllMotClePopulairesDateDesc(),
                // récupérer le classement
                'classements' => $this->utilisateur_model->getClassementVotes(8),
                'mots' => $mots,
            ]
        );

        if (!empty($mots)) {
            $data['categoriesParMot'] = $mots;
        }

        return $data;
    }

    /**
     * processImageUpload permet de traiter l'upload d'une image
     * @param int $idMotcle id du MotCle associé à l'image
     * @return bool
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    private function processImageUpload(int $idMotcle): bool
    {
        $file = $this->request->getFile('nouvImage');

        if (!$file) {
            log_message('debug', 'No file received');
            return true; // Pas d'image = pas d'erreur
        }

        if (!$file->isValid()) {
            log_message('error', 'File is not valid: ' . $file->getErrorString());
            return false; // ❌ Fichier invalide = erreur
        }

        log_message('debug', 'File is valid: ' . $file->getName());

        try {
            $uploadPath = FCPATH . IMAGES_PATH;

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
                log_message('debug', 'Created upload directory: ' . $uploadPath);
            }
            $newName = $file->getName();
            if (!$file->move($uploadPath, $newName)) {
                log_message('error', 'Failed to move file: ' . $file->getErrorString());
                return false;
            }

            log_message('debug', 'File moved successfully: ' . $newName);

            $imageEntity = new Image([
                'mot_id' => $idMotcle,
                'image' => $newName,
                'alt' => $this->request->getPost('nouvAlt') ?? '',
                'legend' => $this->request->getPost('nouvLegend') ?? ''
            ]);

            $result = $this->image_model->insertImageMotCle($imageEntity);

            log_message('debug', 'Image insert result: ' . ($result ? 'success' : 'failed'));

            return (bool)$result;

        } catch (\Exception $e) {
            log_message('error', '[Upload] ' . $e->getMessage());
            return false;
        }
    }

    private function getCategoriesOptions()
    {
        $categories = $this->categorie_model->getAllCategories();
        $categoriesOptions = [];
        foreach ($categories as $category) {
            $categoriesOptions[$category->id] = $category->nom;
        }
        return $categoriesOptions;
    }
}