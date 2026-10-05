<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Entities\Image;
use App\Entities\MotCle;
use App\Entities\Utilisateur;
use App\Models\CategorieModel;
use App\Models\ImageModel;
use App\Models\MotCle\MotCleCategorieModel;
use App\Models\MotCle\MotCleModel;
use App\Models\MotCle\MotCleValidationModel;
use App\Models\SignalementModel;
use CodeIgniter\Config\Services;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Session\Session;
use CodeIgniter\Validation\Validation;

/**
 * Ce controller permet la gestion de la page d'administration des mots
 * @since 1.0.0
 * @package Cyber4all
 * @author Benjamin Lorieul
 */
class AdminMotController extends BaseController
{
    protected MotCleModel $motCle_model;
    protected MotCleValidationModel $motCleValidation_model;
    protected Session $session;
    protected CategorieModel $categorie_model;
    protected ?Utilisateur $utilisateur = null;
    protected Validation $validation;
    protected SignalementModel $signalement_model;
    protected ImageModel $image_model;

    public function __construct()
    {
        $this->motCle_model = new MotCleModel();
        $this->motCleValidation_model = new MotCleValidationModel();
        $this->motCleCategorie_model = new MotCleCategorieModel();
        $this->session = session();
        $this->validation = Services::validation();
        $this->categorie_model = new CategorieModel();
        $this->image_model = new ImageModel();
        $this->signalement_model = new SignalementModel();

        // Vérification de la session et du rôle de l'utilisateur
        $utilisateur = $this->session->get('utilisateur');

        if ($utilisateur && $utilisateur->role === 'admin') {
            $this->utilisateur = $utilisateur;

        } else {
            throw PageNotFoundException::forPageNotFound('Accès refusé');
        }
    }

    /**
     * Récupère tous les mots paginés et triés par l'ordre transmit.
     * Récupère tous les signalements
     * Récupère le nombre total de signalements en cours
     * @return string Affichage principal de la page d'admin
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function index()
    {
        $ordre = $this->request->getVar('ordre');
        $recherche = trim($this->request->getPost('recherche'));

        //récupérer tous les mots pour les afficher dans un tableau d'administration paginer
        $data['mots'] = $this->motCle_model->getMotClePaginatedOrderBy($ordre, $recherche, 8); // permet de récupérer tous le mots paginé
        $data['pager'] = $this->motCle_model->pager; // Permet de récupérer les pages pour naviguer

        $data['actionValidation'] = base_url('floribondant/mots/validations');

        $data['placeholderAdmin'] = 'Rechercher un terme du glossaire'; // Placeholder de la recherche des mots
        $data['actionRecherche'] = 'floribondant/mots'; // url de traitements des mots recherché

        $data['signalements'] = $this->signalement_model->getSignalements();
        $data['totalSignalements'] = $this->signalement_model->getNbSignalements();

        return view('header', $data)
            . view('notice')
            . view('admin/adminMots')
            . view('footer');
    }

    /**
     * Permet de changer le statut de publication d'un mot.
     * Récupère l'id du mot en post pour éviter de pouvoir
     * switch le statut avec l'url uniquement
     * @return \CodeIgniter\HTTP\RedirectResponse|void
     * @throws \ReflectionException
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function switchStatutMotCle()
    {
        if ($this->request->getPost('statut')) {
            $this->validation->setRules([
                'id' => 'required',
                'statut' => 'required|in_list[Y,N]'
            ],
                //gestion des messages d'erreurs
                [
                    'id' => ['required' => "L'id du mot est obligatoire"],
                    'statut' => ['required' => 'Le statut est obligatoire']
                ]);

            //récupère les données
            $dataForm = [
                'id' => (int)$this->request->getPost('idMotCle'),
                'statut' => $this->request->getPost('statut'),
            ];

            //contrôle de validation des règles sur les données
            if ($this->validation->run($dataForm)) {
                $dataValide = $this->validation->getValidated();

                // Récupère le mot par son id
                $motCle = $this->motCle_model->getMotCleById($dataValide['id']);
                if (!$motCle) {
                    throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('mot clé introuvable');
                }

                // Faire le switch du statut
                $result = $this->motCle_model->switchStatut($motCle);

                // Gestion des résultats
                if ($result) {
                    $this->session->setFlashdata('notice', [
                        'type' => "success",
                        'titre' => "Statut de publication",
                        'details' => "Vous avez changer le statut de publication du mot {$motCle->mot}",
                    ]);

                } else {
                    $this->session->setFlashdata('notice', [
                        'type' => "error",
                        'titre' => "Statut de publication",
                        'details' => "Le statut de publication du mot {$motCle->mot} n'a pas été changé",
                    ]);
                }
                return redirect()->back();
            } else {

                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Données invalides');
            }
        }
    }

    /**
     * Permet d'ajouter un motCle directement en tant qu'administrateur.
     * Il n'a donc pas besoin de validation pour être publié
     * Traite et valide ou non les données du formulaire d'ajout du mot.
     * @return \CodeIgniter\HTTP\RedirectResponse|string Affichage du formulaire d'ajout du mot
     *
     */
    public function addMotCle()
    {
        $utilisateur = $this->session->get('utilisateur');

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
            'nouvLegend' => [
                'label' => 'Légende',
                'rules' => $hasImage ? 'required|min_length[5]' : 'permit_empty',
                'errors' => [
                    'required' => 'La légende est obligatoire si vous ajoutez une image',
                    'min_length' => '5 caractères minimum'
                ]
            ]
        ]);
        $data['categoriesOptions'] = $this->getCategoriesOptions();

        if ($this->request->getPost('actionMot') === 'Ajouter') {
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
                $nouvMot->id_auteur = $utilisateur->id;

                if (is_numeric($idMotCle = $this->motCle_model->insertMotCle($nouvMot))) {

                    // Gestion des catégories
                    $categoriesIdsToAdd = $this->request->getPost('nouvCategories') ?? [];

                    if (!empty($categoriesIdsToAdd)) {
                        $categoriesAdded = $this->motCleCategorie_model->insertCategoriesMotCle($categoriesIdsToAdd, $idMotCle);
                    }

                    // Gestion de l'image
                    $imageAdded = $hasImage ? $this->processImageUpload($idMotCle) : false;

                    // Résultat
                    if ((empty($categoriesAdded) || $categoriesAdded) && $imageAdded) {
                        $this->session->setFlashdata('notice', [
                            'type' => 'success',
                            'titre' => "Ajout du mot réussi",
//                            'details' => ($imageAdded ? "L'image a bien été ajoutée. " : "Aucune image associée au mot")
//                            . ($categoriesAdded ? "Les catégories ont bien été liés. " : "Aucune catégories associées au mot"),
                        ]);
                        return redirect()->to(base_url('floribondant/mots/update/' . (int)$idMotCle));

                    } else {
                        $this->session->setFlashdata('notice', [
                            'type' => (!$categoriesAdded || !$imageAdded) ? 'success' : 'ok',
                            'titre' => "Ajout du mot réussi",
//                            'details' => $imageAdded ? "L'image a bien été ajoutée. " : ("Aucune image associée au mot. "
//                            . $categoriesAdded ? "Les catégories ont bien été liés. " : "Aucune catégories associées au mot. "),
                        ]);
                    }
                } else {
                    $this->session->setFlashdata('notice', [
                        'type' => 'error',
                        'titre' => "Ajout du mot échoué",
                        'details' => $this->validation->getErrors(),
                    ]);
                    $data['motCle'] = $nouvMot;
                    $data['categoriesSelect'] = $this->request->getPost('nouvCategories');
                    $data['errors'] = $this->validation->getErrors();
                }
            } else {
                $this->session->setFlashdata('notice', [
                    'type' => 'error',
                    'titre' => "Ajout du mot échoué",
                    'details' => $this->validation->getErrors(),
                ]);
                $data['categoriesSelect'] = $this->request->getPost('nouvCategories');
            }
        }

        return view('header', $data)
            . view('notice', $data)
            . view('admin/formMotCle', $data)
            . view('footer');
    }

    /**
     * Permet de modifier un motCle directement en tant qu'administrateur.
     * Traite et valide ou non les données du formulaire de modification du mot.
     * @param int $idMotCle id du MotCle à modifier
     * @return string Affichage du formulaire de modification du mot
     * @since 1.0.0
     */
    public function updateMotCle(int $idMotCle)
    {
        $utilisateur = $this->session->get('utilisateur');

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
                $motUpdated = true;
                if ($hasChanges) {
                    $motUpdated = $this->motCle_model->updateMotCle($motCle);
                }

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
            . view('admin/formMotCle', $data)
            . view('footer');
    }

    /**
     * Permet de prendre une décision (Refuser ou Valider) concernant la demande
     * d'ajout d'un mot au glossaire par un contributeur.
     * Si valider redirige vers le formulaire de modification du mot.
     * Si refuser redirige vers la page admin des mots.
     * @param int $idMotCle id du MotCle à modifier
     * @return string|RedirectResponse affichage du formulaire de modification du mot
     * @throws \ReflectionException
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function decisionMotCle(int $idMotCle): string|RedirectResponse
    {
        $utilisateur = $this->session->get('utilisateur');
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
                    'min_length' => '25 caractères minimum'
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

        $idMotCle = $this->request->getPost('idMotCle') ?? $idMotCle;
        $motCle = $this->motCle_model->getMotCleById($idMotCle);
        $categories = $this->categorie_model->getCategoriesMotCle($motCle->id);

        $data['motCle'] = $motCle;
        $data['images'] = $this->image_model->getImagesByMotId($idMotCle);
        $data['categoriesOptions'] = $this->getCategoriesOptions();
        $data['categoriesSelect'] = array_column($categories, 'id');
        $data['actionAdmin'] = 'floribondant/mots/validation/decision/';
        $data['actionRecherche'] = 'floribondant/mots/validation';

        $actionMot = $this->request->getPost('actionMot');

        if (in_array($actionMot, ['valider', 'refuser'], true)) {

            if ($this->validation->withRequest($this->request)->run()) {

                $dataMot = [
                    'mot' => $this->request->getPost('nouvMot'),
                    'definition' => $this->request->getPost('nouvDefinition'),
                    'definition_complete' => $this->request->getPost('nouvDefinitionComplete')
                ];

                // Mise à jour du mot
                $motCle->mot = $dataMot['mot'];
                $motCle->definition = $dataMot['definition'];
                $motCle->definition_complete = $dataMot['definition_complete'];

                if ($this->motCle_model->updateMotCle($motCle)) {

                    // Gestion des catégories
                    $postedCategories = $this->request->getPost('nouvCategories') ?? [];
                    // catégories actuelles du mot
                    $existingCategories = array_column($categories, 'id');
                    // catégories à ajouter
                    $categoriesIdsToAdd = array_diff($postedCategories, $existingCategories);
                    // catégories a supprimé
                    $categoriesIdsToRemove = array_diff($existingCategories, $postedCategories);

                    $categoriesAdded = false;
                    $categoriesDeleted = false;

                    if (!empty($categoriesIdsToAdd)) { // si catégories à ajouter
                        $categoriesAdded = $this->motCleCategorie_model->insertCategoriesMotCle($categoriesIdsToAdd, $idMotCle);
                    }

                    if (!empty($categoriesIdsToRemove)) { // si catégories à supprimer
                        $categoriesDeleted = $this->motCleCategorie_model->deleteCategoriesMotCle($categoriesIdsToRemove, $idMotCle);
                    }

                    // Gestion de l'image
                    $imageAdded = $hasImage ? $this->processImageUpload($idMotCle) : false;
                    $details = '';
                    $details .= $imageAdded ? "L'image a été ajoutée. " : "Aucune image ajoutée. ";
                    $details .= $categoriesAdded ? "Les catégories ont été liées. " : "Aucune catégorie liée. ";
                    // Prise de décision (valider ou refuser)
                    $decision = $this->motCleValidation_model->prendreLaDecision($idMotCle, $actionMot);
                    var_dump($decision);
                    // Résultat
                    if ($decision && $categoriesAdded && $categoriesDeleted && $imageAdded) {
                        // mets a jour la view directement
                        $data['motCle'] = $this->motCle_model->getMotCleById($idMotCle);
                        $data['images'] = $this->image_model->getImagesByMotId($idMotCle);
                        $categories = $this->categorie_model->getCategoriesMotCle($motCle->id);
                        $data['categoriesSelect'] = array_column($categories, 'id');

                        $this->session->setFlashdata('notice', [
                            'type' => 'success',
                            'titre' => "Le mot à correctement été validé",
                            'details' => $details
                        ]);
                        return redirect()->back();

                    } else {

                        $this->session->setFlashdata('updateMotCle', 'warning');
                        $this->session->setFlashdata('updateCategories', ($categoriesAdded && $categoriesDeleted) ? 'ok' : 'error');
                        $this->session->setFlashdata('updateImage', $imageAdded ? 'ok' : 'error');
                        $this->session->setFlashdata('decision', $decision ? 'ok' : 'error');
                        $this->session->setFlashdata('notice', [
                            'type' => 'warning',
                            'titre' => "Le mot à correctement été validé, cependant",
                            'details' => $details,
                        ]);
                        return redirect()->to(base_url('floribondant/mots/validations'));
                    }

                } else {
                    $this->session->setFlashdata('notice', [
                        'type' => 'error',
                        'titre' => "Décision du mot échouée",
                        'details' => "Le mot n'a pas été modifié",
                    ]);
                    return redirect()->to(base_url('floribondant/mots/validations'));
                }
            } else {
                $this->session->setFlashdata('notice', [
                    'type' => 'error',
                    'titre' => "Décision du mot échouée",
                    'details' => $this->validation->getErrors(),
                ]);
                return redirect()->to(base_url('floribondant/mots/validations'));
            }
        }

        return view('header', $data)
            . view('notice', $data)
            . view('admin/formMotCle', $data)
            . view('footer');
    }


    /**
     * Permet de supprimer un MotCle
     * @return \CodeIgniter\HTTP\RedirectResponse page précédente
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function suppMotCle()
    {
        $this->validation->setRules([
            'id' => 'required'
        ],
            //gestion des messages d'erreurs
            [
                'id' => ['required' => "L'id du mot est obligatoire"]
            ]);
        //récupère les données
        $dataForm = [
            'id' => (int)$this->request->getPost('idMotCle')
        ];
        //contrôle de validation des règles sur les données
        if ($this->validation->run($dataForm)) {
            $dataValide = $this->validation->getValidated();
            // Récupère le mot par son id
            $motCle = $this->motCle_model->getMotCleById($dataValide['id']);
            if (!$motCle) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('mot clé introuvable');
            }
            // Faire le switch du statut
            $result = $this->motCle_model->deleteMotCleById($motCle->id);

            // Gestion des résultats
            if ($result) {
                $this->session->setFlashdata('notice', [
                    'type' => 'success',
                    'titre' => "Suppression du mot réussie",
                    'details' => '',
                ]);
            } else {
                $this->session->setFlashdata('notice', [
                    'type' => 'error',
                    'titre' => "Suppression du mot échouée",
                    'details' => "Le mot ne s'est pas correctement supprimer",
                ]);
            }
            return redirect()->back();

        } else {
            $this->session->setFlashdata('notice', [
                'type' => 'error',
                'titre' => "Suppression du mot échouée",
                'details' => $this->validation->getErrors(),
            ]);
        }
    }

    /**
     * Permet d'afficher toutes les demandes de validations en cours trié par l'ordre transmis en url
     * @return string
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function afficherValidations()
    {
        $ordre = $this->request->getVar('ordre');
        $recherche = trim($this->request->getPost('recherche'));
        //récupérer tous les mots pour les afficher dans un tableau d'administration paginer
        $data['mots'] = $this->motCle_model->getMotCleValidationPaginatedOrderBy($ordre, $recherche); // permet de récupérer tous le mots paginé
        $data['actionAdmin'] = 'floribondant/mots/validation/decision/';
        $data['placeholderAdmin'] = 'Rechercher un terme';
        $data['pager'] = $this->motCle_model->pager; // Permet de récupérer les pages pour naviguer
        $data['indication'] = "Sont afficher: seulement les demandes qui n'ont pas été traitées";
        return view('header', $data)
            . view('notice', $data)
            . view('recherche')
            . view('admin/tabMots')
            . view('footer');
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

    /**
     * Génère un nom de fichier unique avec helper filesystem
     * @param string $basename
     * @param string $extension
     * @return string
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    private function generateUniqueFilename(string $basename, string $extension): string
    {
        $uploadPath = FCPATH . IMAGES_PATH;
        $filename = $basename . '.' . $extension;
        $counter = 1;

        // Utiliser get_filenames() du helper filesystem si besoin
        while (file_exists($uploadPath . $filename)) {
            $filename = $basename . '_' . $counter . '.' . $extension;
            $counter++;
        }

        return $filename;
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

    private function setRenderView(string $view, array $data)
    {
        $data['mots'] = $this->motCle_model->getMotClePaginated(); // permet de récupérer tous le smots paginé
        $data['pager'] = $this->motCle_model->pager; // Permet de récupérer les pages pour naviguer
        $data['categoriesOptions'] = $this->getCategoriesOptions();
        return view('header', $data)
            . view($view)
            . view('admin/formMotCle')
            . view('admin/tabMots')
            . view('footer');
    }
}