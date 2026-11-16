<?php

namespace App\Controllers\admin;

use App\Entities\ExtraitForm;
use App\Models\ExtraitModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Ce controller gère les extraits de l'Espace Traçabilité (administration)
 *
 * @since 2.0.0
 * @package Cyber4all
 * @author Arthur
 */
class AdminExtraitController extends AdminController
{
    protected ExtraitModel $extrait_model;

    public function __construct()
    {
        // Constructeur du parent : session, ThemeModel et vérification de l'administrateur
        parent::__construct();

        $this->extrait_model = new ExtraitModel();
    }

    /**
     * Liste des extraits filtre par type, tri et pagination.
     *
     * @return string
     *
     * @author Arthur
     */
    public function index()
    {
        $base = 'floribondant/theme/extrait';

        $colonnes = [
            'titre'        => ['entete' => 'Titre', 'phrase' => 'par titre', 'asc' => 'de A à Z', 'desc' => 'de Z à A'],
            'type'         => ['entete' => 'Type', 'phrase' => 'par type', 'asc' => 'de A à Z', 'desc' => 'de Z à A'],
            'contenu'      => ['entete' => 'Contenu'],
            'nb_questions' => ['entete' => 'Questions', 'phrase' => 'par nombre de questions', 'asc' => 'du plus petit au plus grand', 'desc' => 'du plus grand au plus petit'],
        ];

        $typeSelect = (string) ($this->request->getGet('type') ?? '');
        if (! array_key_exists($typeSelect, ExtraitModel::TYPES)) {
            $typeSelect = null;
        }

        $tri = $this->request->getGet('tri');
        if (! in_array($tri, ['titre', 'type', 'nb_questions'], true)) {
            $tri = null;
        }

        $ordre = 'asc';
        if ($this->request->getGet('ordre') === 'desc') {
            $ordre = 'desc';
        }

        $extraits = $this->extrait_model->getExtraitsAdmin($typeSelect);

        if ($tri != null) {

            usort($extraits, function ($a, $b) use ($tri, $ordre) {

                $valeurA = $a[$tri];
                $valeurB = $b[$tri];

                if ($tri == 'nb_questions') {

                    if ($valeurA < $valeurB) {
                        $resultat = -1;
                    } elseif ($valeurA > $valeurB) {
                        $resultat = 1;
                    } else {
                        $resultat = 0;
                    }

                } else {
                    $resultat = strcmp($valeurA, $valeurB);
                }

                // Si le tri est décroissant
                if ($ordre == 'desc') {
                    $resultat = -$resultat;
                }

                return $resultat;
            });
        }

        $idNouveau = (int) ($this->session->getFlashdata('id_nouveau') ?? 0);
        $extraits = $this->ordonnerListe($extraits, $idNouveau);

        foreach ($extraits as $numero => $extrait) {
            $debut = preg_replace('/\s+/u', ' ', (string) $extrait['contenu']);
            $extraits[$numero]['debut'] = mb_strimwidth(trim($debut), 0, 120, '…');
        }

        $parPage = 20;
        $totalExtraits = count($extraits);
        $nbPages = max(1, (int) ceil($totalExtraits / $parPage));

        $page = (int) ($this->request->getGet('page') ?? 1);
        if ($page < 1) {
            $page = 1;
        }
        if ($page > $nbPages) {
            $page = $nbPages;
        }

        $parametresTri = [];
        if ($tri !== null) {
            $parametresTri = ['tri' => $tri, 'ordre' => $ordre];
        }
        $parametresType = [];
        if ($typeSelect !== null) {
            $parametresType = ['type' => $typeSelect];
        }

        $data = [
            'onglet'        => 'extrait',
            'typeSelect'    => $typeSelect,
            'extraitList'   => array_slice($extraits, ($page - 1) * $parPage, $parPage),
            'idNouveau'     => $idNouveau,
            'totalExtraits' => $totalExtraits,
            'typesExtrait'  => ExtraitModel::TYPES,
            'filtreTypes'   => parent::filtreTypes($base, $typeSelect, $parametresTri),
            'colonnes'      => parent::colonnesTri($base, $colonnes, $tri, $ordre, $parametresType),
            'messageTri'    => parent::messageTri('Extraits triés', $colonnes, $tri, $ordre),
            'urlSansTri'    => parent::adresseListe($base, $parametresType),
            'pages'         => parent::pagesListe($base, $nbPages, $page, $parametresType + $parametresTri),
        ];

        return view('header')
            . view('notice')
            . view('admin/tracabilite/onglets', $data)
            . view('admin/tracabilite/adminExtraits', $data)
            . view('footer');
    }

    /**
     * Ajout ou modification d'un extrait.
     *
     * @param int|null $idExtraitModif Identifiant de l'extrait à modifier, null pour un ajout.
     * @return mixed
     *
     * @author Arthur
     */
    public function extraitAjouter(?int $idExtraitModif = null)
    {
        $form = new ExtraitForm();

        if ($idExtraitModif !== null) {
            $extrait = $this->extrait_model->find($idExtraitModif);

            if ($extrait === null) {
                throw PageNotFoundException::forPageNotFound('Extrait introuvable');
            }

            $form->fill($extrait);
            $form->modifier = $idExtraitModif;
        }

        $errors = [];

        if ($this->request->is('post') && $this->request->getPost('action') === 'enregistrer') {
            $formData = [
                'titre'   => trim((string) ($this->request->getPost('titre') ?? '')),
                'type'    => (string) ($this->request->getPost('type') ?? ''),
                'contenu' => (string) ($this->request->getPost('contenu') ?? ''),
            ];

            // Saisie gardée pour réafficher le formulaire en cas d'erreur
            $form->fill($formData);

            $rules = [
                'titre' => [
                    'label'  => 'Titre',
                    'rules'  => 'required|max_length[100]',
                    'errors' => [
                        'required'   => 'Veuillez renseigner le titre de l’extrait.',
                        'max_length' => 'Le titre de l’extrait ne peut pas dépasser 100 caractères.',
                    ],
                ],
                'type' => [
                    'label'  => 'Type',
                    'rules'  => 'required|in_list[' . implode(',', array_keys(ExtraitModel::TYPES)) . ']',
                    'errors' => [
                        'required' => 'Veuillez choisir le type de l’extrait.',
                        'in_list'  => 'Le type de l’extrait est invalide.',
                    ],
                ],
                'contenu' => [
                    'label'  => 'Contenu',
                    'rules'  => 'required|max_length[5000]',
                    'errors' => [
                        'required'   => 'Veuillez renseigner le contenu de l’extrait.',
                        'max_length' => 'Le contenu de l’extrait ne peut pas dépasser 5000 caractères.',
                    ],
                ],
            ];

            if (! $this->validateData($formData, $rules)) {
                $errors = $this->validator->getErrors();
            }

            if (empty($errors)) {
                $estModification = ! empty($form->modifier);

                if ($estModification) {
                    $idExtrait = $this->extrait_model->modifierExtrait((int) $form->modifier, $formData)
                        ? (int) $form->modifier
                        : false;
                } else {
                    $idExtrait = $this->extrait_model->creerExtrait($formData);
                }

                if ($idExtrait !== false) {
                    return redirect()->to(base_url('floribondant/theme/extrait'))
                        ->with('id_nouveau', $idExtrait) // affiché en premier dans la liste
                        ->with('success', $estModification
                            ? 'Extrait modifié avec succès !'
                            : 'Extrait créé avec succès !');
                }

                $errors['enregistrement'] = "L'extrait n'a pas pu être enregistré. Veuillez réessayer.";
            }
        }

        $questions = [];
        if (! empty($form->modifier)) {
            $questions = $this->extrait_model->getQuestionsDeExtrait((int) $form->modifier);
        }

        $data = [
            'form'         => $form,
            'typesExtrait' => ExtraitModel::TYPES,
            'questions'    => $questions,
            'errors'       => $errors,
        ];

        return view('header')
            . view('notice')
            . view('admin/tracabilite/formExtrait', $data)
            . view('footer');
    }

    /**
     * Suppression d'un extrait. Les questions qui l'utilisaient sont conservées, sans extrait
     *
     * @param int $idExtrait Identifiant de l'extrait à supprimer
     * @return RedirectResponse Retour à la liste des extraits, avec un message
     *
     * @author Arthur
     */
    public function extraitSupprimer(int $idExtrait)
    {
        if (! $this->request->is('post')) {
            return redirect()->to(base_url('floribondant/theme/extrait'));
        }

        $extrait = $this->extrait_model->find($idExtrait);

        if ($extrait === null) {
            return redirect()->to(base_url('floribondant/theme/extrait'))
                ->with('error', 'Cet extrait n\'existe pas ou a déjà été supprimé.');
        }

        $nbQuestions = $this->extrait_model->compterQuestions($idExtrait);

        if (! $this->extrait_model->supprimerExtrait($idExtrait)) {
            return redirect()->to(base_url('floribondant/theme/extrait'))
                ->with('error', "L'extrait n'a pas pu être supprimé. Veuillez réessayer.");
        }

        $message = 'Extrait « ' . $extrait['titre'] . ' » supprimé.';
        if ($nbQuestions > 0) {
            $message .= ' ' . $nbQuestions . ' question(s) n\'ont plus d\'extrait.';
        }

        return redirect()->to(base_url('floribondant/theme/extrait'))
            ->with('success', $message);
    }
}
