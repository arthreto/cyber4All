<?php

namespace App\Controllers\admin;

use App\Entities\QuestionForm;
use App\Models\ExtraitModel;
use App\Models\QuestionModel;
use App\Models\QuizModel;
use App\Models\QuizQuestionModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Ce controller gère les questions de l'Espace compréhension SIO (administration)
 *
 * @since 2.0.0
 * @package Cyber4all
 * @author Arthur
 */
class AdminQuestionController extends AdminController
{
    protected QuizModel $quiz_model;
    protected QuestionModel $question_model;
    protected QuizQuestionModel $quiz_question_model;
    protected ExtraitModel $extrait_model;

    public function __construct()
    {
        parent::__construct();

        $this->quiz_model = new QuizModel();
        $this->question_model = new QuestionModel();
        $this->quiz_question_model = new QuizQuestionModel();
        $this->extrait_model = new ExtraitModel();
    }

    /**
     * Liste des questions : filtre par thème, tri et pagination.
     *
     * @return string
     *
     * @author Arthur
     */
    public function index()
    {
        $base = 'floribondant/theme';

        $colonnes = [
            'libelle'       => ['entete' => 'Énoncé', 'phrase' => 'par énoncé', 'asc' => 'de A à Z', 'desc' => 'de Z à A'],
            'quiz'          => ['entete' => 'Quiz'],
            'bonne_reponse' => ['entete' => 'Réponse', 'phrase' => 'par bonne réponse', 'asc' => 'Faux d\'abord', 'desc' => 'Vrai d\'abord'],
            'actif'         => ['entete' => 'Active', 'phrase' => 'par visibilité', 'asc' => 'inactives d\'abord', 'desc' => 'actives d\'abord'],
            'date_modif'    => ['entete' => 'Dernière modification', 'phrase' => 'par date de modification', 'asc' => 'de la plus ancienne à la plus récente', 'desc' => 'de la plus récente à la plus ancienne'],
        ];

        $themeSelect = (int) ($this->request->getGet('id_theme') ?? -1);

        $tri = $this->request->getGet('tri');
        if (! in_array($tri, ['libelle', 'bonne_reponse', 'actif', 'date_modif'], true)) {
            $tri = null;
        }

        $ordre = 'asc';
        if ($this->request->getGet('ordre') === 'desc') {
            $ordre = 'desc';
        }

        if ($themeSelect > 0) {
            $questions = $this->question_model->getQuestionsDuTheme($themeSelect);
        } else {
            $questions = $this->question_model->getAllQuestions();
        }


        if ($tri !== null) {
            $collator = new \Collator('fr_FR');

            usort($questions, function (array $a, array $b) use ($tri, $ordre, $collator): int {
                $valeurA = (string) $a[$tri];
                $valeurB = (string) $b[$tri];


                if ($tri === 'date_modif') {
                    $valeurA = (string) ($a['date_modif'] ?? $a['date_ajout']);
                    $valeurB = (string) ($b['date_modif'] ?? $b['date_ajout']);
                }

                $resultat = $collator->compare($valeurA, $valeurB);

                // Ordre décroissant donc on inverse le résultat
                if ($ordre === 'desc') {
                    $resultat = -$resultat;
                }

                return $resultat;
            });
        }

        $parPage = 20;
        $totalQuestions = count($questions);
        $nbPages = max(1, (int) ceil($totalQuestions / $parPage));

        $page = (int) ($this->request->getGet('page') ?? 1);
        if ($page < 1) {
            $page = 1;
        }
        if ($page > $nbPages) {
            $page = $nbPages;
        }

        // Liens pour la vue
        $parametresTri = [];
        if ($tri !== null) {
            $parametresTri = ['tri' => $tri, 'ordre' => $ordre];
        }
        $parametresTheme = [];
        if ($themeSelect > 0) {
            $parametresTheme = ['id_theme' => $themeSelect];
        }

        $this->session->remove('questionForm');

        $data = [
            'onglet'         => 'questions',
            'themeSelect'    => $themeSelect,
            'questionList'   => array_slice($questions, ($page - 1) * $parPage, $parPage),
            'totalQuestions' => $totalQuestions,
            'filtreThemes'   => $this->filtreThemes($base, $this->theme_model->getAllThemes(), $themeSelect, $parametresTri),
            'colonnes'       => $this->colonnesTri($base, $colonnes, $tri, $ordre, $parametresTheme),
            'messageTri'     => $this->messageTri('Questions triées', $colonnes, $tri, $ordre),
            'urlSansTri'     => $this->adresseListe($base, $parametresTheme),
            'pages'          => $this->pagesListe($base, $nbPages, $page, $parametresTheme + $parametresTri),
        ];

        return view('header')
            . view('notice')
            . view('admin/tracabilite/onglets', $data)
            . view('admin/tracabilite/adminQuestions', $data)
            . view('footer');
    }

    /**
     * Suppression d'une question et de ses liaisons avec les quiz.
     *
     * @param int $idQuestion Identifiant de la question à supprimer
     * @return RedirectResponse Retour à la liste des questions, avec un message
     *
     * @author Arthur
     */
    public function questionSupprimer(int $idQuestion)
    {
        if ($this->question_model->find($idQuestion) === null) {
            return redirect()->to(base_url('floribondant/theme'))
                ->with('error', 'Cette question n\'existe pas ou a déjà été supprimée.');
        }


        $this->quiz_question_model->where('id_question', $idQuestion)->delete();
        $this->question_model->delete($idQuestion);

        return redirect()->to(base_url('floribondant/theme'))
            ->with('success', 'Question supprimée.');
    }

    /**
     * Ajout ou modification d'une question, avec son thème, son extrait facultatif et ses quiz.
     * @param int|null $idQuestionModif Identifiant de la question à modifier, null pour un ajout.
     * @return mixed
     *
     * @author Arthur
     */
    public function questionAjouter(?int $idQuestionModif = null)
    {
        // Par modal ?
        $modal = $this->request->getGet('modal') === '1';

        $form = $this->session->get('questionForm');

        // Verifié ajout ou modification
        if ($form !== null && (int) ($form->modifier ?? 0) !== (int) ($idQuestionModif ?? 0)) {
            $form = null;
        }

        // Ouvrir le modal
        if ($modal && ! $this->request->is('post')) {
            $form = null;
        }

        if ($form === null) {
            $form = new QuestionForm();

            // Modal
            if ($modal && $idQuestionModif === null) {
                $idThemeQuiz = (int) ($this->request->getGet('id_theme') ?? 0);
                if ($idThemeQuiz > 0) {
                    $form->id_theme = $idThemeQuiz;
                }

                $idQuiz = (int) ($this->request->getGet('quiz') ?? 0);
                if ($idQuiz > 0) {
                    $form->quiz = [(string) $idQuiz];
                }
            }

            if ($idQuestionModif !== null) {
                $question = $this->question_model->find($idQuestionModif);

                if ($question !== null) {
                    $form->fill($question);
                    $form->modifier = $idQuestionModif;

                    if (! empty($question['id_extrait'])) {
                        $form->extrait_mode = 'existant';
                    } else {
                        $form->extrait_mode = 'aucun';
                    }

                    $form->quiz = $this->quiz_question_model->getIdsQuizByQuestion($idQuestionModif);
                }
            }
        }

        $errors = [];
        $infoExtrait = null;
        $themeList = $this->theme_model->getAllThemes();
        $extraitList = $this->extrait_model->getAllExtraits();

        if ($this->request->is('post')) {
            $post = $this->request->getPost();
            unset($post['quiz']);

            $form->fill($post);

            $form->quiz = array_values((array) ($this->request->getPost('quiz') ?? []));

            $action = (string) $this->request->getPost('action');
            $actionsExtrait = ['modifier_extrait', 'enregistrer_extrait', 'supprimer_extrait', 'annuler_extrait'];

            if (! in_array($action, $actionsExtrait, true)) {
                $form->extrait_action = null;
            }

            if (in_array($action, $actionsExtrait, true)) {
                $infoExtrait = $this->gererActionExtrait($action, $form, $errors);
                $extraitList = $this->extrait_model->getAllExtraits();
            } elseif ($action === 'enregistrer') {
                $idTheme = (int) ($this->request->getPost('id_theme') ?? 0);
                $typeTheme = '';
                foreach ($themeList as $theme) {
                    if ((int) $theme->id === $idTheme) {
                        $typeTheme = mb_strtolower(trim($theme->nom));
                    }
                }

                $idsExtraitsTheme = [];
                foreach ($extraitList as $extrait) {
                    if ($extrait['type'] === $typeTheme) {
                        $idsExtraitsTheme[] = $extrait['id'];
                    }
                }

                $formData = [
                    'id_theme'        => $this->request->getPost('id_theme') ?? '',
                    'libelle'         => $this->request->getPost('libelle') ?? '',
                    'image'           => $this->request->getPost('image') ?? '',
                    'extrait_mode'    => $form->extrait_mode ?? 'aucun',
                    'id_extrait'      => $form->id_extrait ?? '',
                    'extrait_titre'   => $form->extrait_titre ?? '',
                    'extrait_type'    => $typeTheme, // un nouvel extrait prend le type du thème
                    'extrait_contenu' => $form->extrait_contenu ?? '',
                    'bonne_reponse'   => $this->request->getPost('bonne_reponse'),
                    'explication'     => $this->request->getPost('explication') ?? '',
                    'actif'           => $this->request->getPost('actif') ?? '0',
                ];

                $rules = [
                    'id_theme' => [
                        'label' => 'Thème',
                        'rules' => 'required|is_natural_no_zero',
                        'errors' => [
                            'required' => 'Veuillez choisir un thème.',
                            'is_natural_no_zero' => 'Le thème choisi est invalide.',
                        ],
                    ],
                    'libelle' => [
                        'label' => 'Énoncé',
                        'rules' => 'required|min_length[3]|max_length[1000]',
                        'errors' => [
                            'required' => 'Veuillez renseigner un énoncé.',
                            'min_length' => 'L’énoncé doit contenir au moins 3 caractères.',
                            'max_length' => 'L’énoncé ne peut pas dépasser 1000 caractères.',
                        ],
                    ],
                    'image' => [
                        'label' => 'Image',
                        'rules' => [
                            'permit_empty',
                            'max_length[255]',
                            'regex_match[/\.(png|jpg|jpeg|webp|avif)$/i]',
                        ],
                        'errors' => [
                            'max_length' => 'Le chemin de l’image ne peut pas dépasser 255 caractères.',
                            'regex_match' => 'L’image doit être au format PNG, AVIF, JPG, JPEG ou WEBP.',
                        ],
                    ],
                    'extrait_mode' => [
                        'label' => 'Extrait',
                        'rules' => 'required|in_list[aucun,existant,nouveau]',
                        'errors' => [
                            'required' => 'Veuillez choisir une option pour l’extrait.',
                            'in_list' => 'L’option choisie pour l’extrait est invalide.',
                        ],
                    ],
                    'bonne_reponse' => [
                        'label' => 'Bonne réponse',
                        'rules' => 'required|in_list[0,1]',
                        'errors' => [
                            'required' => 'Veuillez sélectionner une bonne réponse.',
                            'in_list' => 'La réponse doit être Vrai ou Faux.',
                        ],
                    ],
                    'explication' => [
                        'label' => 'Explication',
                        'rules' => 'required|min_length[3]|max_length[5000]',
                        'errors' => [
                            'required' => 'Veuillez renseigner une explication.',
                            'min_length' => 'L’explication doit contenir au moins 3 caractères.',
                            'max_length' => 'L’explication ne peut pas dépasser 5000 caractères.',
                        ],
                    ],
                    'actif' => [
                        'label' => 'Statut',
                        'rules' => 'required|in_list[0,1]',
                        'errors' => [
                            'required' => 'Veuillez définir le statut de la question.',
                            'in_list' => 'Le statut de la question est invalide.',
                        ],
                    ],
                ];

                if ($formData['extrait_mode'] === 'existant') {
                    $rules['id_extrait'] = [
                        'label' => 'Extrait',
                        'rules' => 'required|in_list[' . implode(',', $idsExtraitsTheme) . ']',
                        'errors' => [
                            'required' => 'Veuillez choisir un extrait existant.',
                            'in_list' => 'L’extrait choisi n’appartient pas au thème de la question.',
                        ],
                    ];
                } elseif ($formData['extrait_mode'] === 'nouveau') {
                    $rules['extrait_titre'] = [
                        'label' => 'Titre de l’extrait',
                        'rules' => 'required|max_length[100]',
                        'errors' => [
                            'required' => 'Veuillez renseigner le titre de l’extrait.',
                            'max_length' => 'Le titre de l’extrait ne peut pas dépasser 100 caractères.',
                        ],
                    ];
                    $rules['extrait_type'] = [
                        'label' => 'Type de l’extrait',
                        'rules' => 'required|in_list[' . implode(',', array_keys(ExtraitModel::TYPES)) . ']',
                        'errors' => [
                            'required' => 'Veuillez d’abord choisir le thème de la question.',
                            'in_list' => 'Aucun type d’extrait ne correspond au thème choisi.',
                        ],
                    ];
                    $rules['extrait_contenu'] = [
                        'label' => 'Contenu de l’extrait',
                        'rules' => 'required|max_length[5000]',
                        'errors' => [
                            'required' => 'Veuillez renseigner le contenu de l’extrait.',
                            'max_length' => 'Le contenu de l’extrait ne peut pas dépasser 5000 caractères.',
                        ],
                    ];
                }

                if (! $this->validateData($formData, $rules)) {
                    $errors = $this->validator->getErrors();
                }

                // Les quiz cochés doivent tous être du thème de la question
                if ($idTheme > 0) {
                    $idsQuizTheme = [];
                    foreach (array_column($this->quiz_model->getAllQuizCategoryById($idTheme), 'id') as $idQuiz) {
                        $idsQuizTheme[] = (string) $idQuiz;
                    }

                    foreach ($form->quiz as $idQuiz) {
                        if (! in_array((string) $idQuiz, $idsQuizTheme, true)) {
                            $errors['quiz'] = 'Un des quiz cochés n’appartient pas au thème de la question.';
                        }
                    }
                }

                if (empty($errors)) {
                    $estModification = ! empty($form->modifier);


                    $db = \Config\Database::connect('tracabilite');
                    $db->transBegin();

                    $idExtrait = null;

                    if ($formData['extrait_mode'] === 'existant') {
                        $idExtrait = (int) $formData['id_extrait'];
                    } elseif ($formData['extrait_mode'] === 'nouveau') {
                        $idExtrait = $this->extrait_model->creerExtrait([
                            'titre'   => $formData['extrait_titre'],
                            'type'    => $formData['extrait_type'],
                            'contenu' => $formData['extrait_contenu'],
                        ]);
                    }

                    $idQuestion = false;

                    if ($idExtrait !== false) {
                        $formData['id_extrait'] = $idExtrait;

                        if ($estModification) {
                            if ($this->question_model->updateQuestion($formData, (int) $form->modifier)) {
                                $idQuestion = (int) $form->modifier;
                            }
                        } else {
                            $idQuestion = $this->question_model->creerQuestion($formData);
                        }
                    }

                    if ($idQuestion !== false
                        && $this->quiz_question_model->updateLiaisons($idQuestion, $form->quiz)) {
                        $db->transCommit();
                        $this->session->remove('questionForm');


                        if ($modal) {
                            return view('admin/tracabilite/modalDebut')
                                . view('admin/tracabilite/questionEnregistree', [
                                    'question' => [
                                        'id'            => $idQuestion,
                                        'libelle'       => $formData['libelle'],
                                        'bonne_reponse' => (string) $formData['bonne_reponse'],
                                        'id_theme'      => (int) $formData['id_theme'],
                                        'quiz'          => array_map('intval', $form->quiz),
                                    ],
                                ])
                                . view('admin/tracabilite/modalFin');
                        }

                        return redirect()->to(base_url('floribondant/theme'))
                            ->with('success', $estModification
                                ? 'Question modifiée avec succès !'
                                : 'Question créée avec succès !');
                    }

                    $db->transRollback();
                    $errors['enregistrement'] = "La question n'a pas pu être enregistrée. Veuillez réessayer.";
                }
            }

            $this->session->set('questionForm', $form);
        }


        $themeChoisi = (int) ($form->id_theme ?? 0);
        $idExtraitChoisi = (string) ($form->id_extrait ?? '');

        $quizCoches = [];
        foreach ((array) ($form->quiz ?? []) as $idQuiz) {
            $quizCoches[] = (string) $idQuiz;
        }

        // Extraits triés par titre
        usort($extraitList, function (array $a, array $b): int {
            return strcasecmp($a['titre'], $b['titre']);
        });

        $blocsThemes = [];

        foreach ($themeList as $theme) {
            $bloc = [
                'id'       => (int) $theme->id,
                'nom'      => $theme->nom,
                'choisi'   => (int) $theme->id === $themeChoisi,
                'quiz'     => [],
                'extraits' => [],
            ];


            foreach (array_column($this->quiz_model->getAllQuizCategoryById((int) $theme->id), 'titre', 'id') as $idQuiz => $titre) {
                $bloc['quiz'][] = [
                    'id'    => (int) $idQuiz,
                    'titre' => $titre,
                    'coche' => in_array((string) $idQuiz, $quizCoches, true),
                ];
            }

            $typeTheme = mb_strtolower(trim($theme->nom));

            foreach ($extraitList as $extrait) {
                if ($extrait['type'] !== $typeTheme) {
                    continue;
                }

                $debut = preg_replace('/\s+/u', ' ', (string) $extrait['contenu']);
                $debut = mb_strimwidth(trim($debut), 0, 160, '…');

                $bloc['extraits'][] = [
                    'id'      => (int) $extrait['id'],
                    'titre'   => $extrait['titre'],
                    'contenu' => $extrait['contenu'],
                    'debut'   => $debut,
                    'choisi'  => (string) $extrait['id'] === $idExtraitChoisi,
                ];
            }

            $blocsThemes[] = $bloc;
        }

        $data = [
            'form'         => $form,
            'blocsThemes'  => $blocsThemes,
            'typesExtrait' => ExtraitModel::TYPES,
            'infoExtrait'  => $infoExtrait,
            'errors'       => $errors,
            'modal'        => $modal,
        ];

        if ($modal) {
            return view('admin/tracabilite/modalDebut')
                . view('admin/tracabilite/formQuestion', $data)
                . view('admin/tracabilite/modalFin');
        }

        return view('header')
            . view('notice')
            . view('admin/tracabilite/formQuestion', $data)
            . view('footer');
    }

    /**
     * Modification / suppression de l'extrait existant choisi dans le formulaire
     *
     * @param string $action Action demandée
     * @param QuestionForm $form Formulaire en cours
     * @param array $errors Erreurs à afficher
     * @return string|null Message de réussite à afficher, ou null.
     *
     * @author Arthur
     */
    private function gererActionExtrait(string $action, QuestionForm $form, array &$errors): ?string
    {
        if ($action === 'annuler_extrait') {
            $form->extrait_action = null;
            return null;
        }

        $idExtrait = (int) ($form->id_extrait ?? 0);
        $extrait = $idExtrait > 0 ? $this->extrait_model->find($idExtrait) : null;

        if ($extrait === null) {
            $form->extrait_action = null;
            $errors['id_extrait'] = 'Veuillez choisir un extrait existant.';
            return null;
        }

        $autresQuestions = $this->extrait_model->compterQuestions($idExtrait, (int) ($form->modifier ?? 0));

        switch ($action) {
            case 'modifier_extrait':
                $form->extrait_action = 'modifier';
                $form->edit_titre = $extrait['titre'];
                $form->edit_type = $extrait['type'];
                $form->edit_contenu = $extrait['contenu'];
                return null;

            case 'enregistrer_extrait':
                $donnees = [
                    'edit_titre'   => $form->edit_titre ?? '',
                    'edit_type'    => $form->edit_type ?? '',
                    'edit_contenu' => $form->edit_contenu ?? '',
                ];

                $rules = [
                    'edit_titre' => [
                        'label' => 'Titre de l’extrait',
                        'rules' => 'required|max_length[100]',
                        'errors' => [
                            'required' => 'Veuillez renseigner le titre de l’extrait.',
                            'max_length' => 'Le titre de l’extrait ne peut pas dépasser 100 caractères.',
                        ],
                    ],
                    'edit_type' => [
                        'label' => 'Type de l’extrait',
                        'rules' => 'required|in_list[' . implode(',', array_keys(ExtraitModel::TYPES)) . ']',
                        'errors' => [
                            'required' => 'Veuillez choisir le type de l’extrait.',
                            'in_list' => 'Le type de l’extrait est invalide.',
                        ],
                    ],
                    'edit_contenu' => [
                        'label' => 'Contenu de l’extrait',
                        'rules' => 'required|max_length[5000]',
                        'errors' => [
                            'required' => 'Veuillez renseigner le contenu de l’extrait.',
                            'max_length' => 'Le contenu de l’extrait ne peut pas dépasser 5000 caractères.',
                        ],
                    ],
                ];

                if (!$this->validateData($donnees, $rules)) {
                    $errors = array_merge($errors, $this->validator->getErrors());
                    return null;
                }

                $ok = $this->extrait_model->modifierExtrait($idExtrait, [
                    'titre'   => $donnees['edit_titre'],
                    'type'    => $donnees['edit_type'],
                    'contenu' => $donnees['edit_contenu'],
                ]);

                if (!$ok) {
                    $errors['extrait'] = "L'extrait n'a pas pu être modifié. Veuillez réessayer.";
                    return null;
                }

                $form->extrait_action = null;
                return 'Extrait modifié.';

            case 'supprimer_extrait':
                if ($autresQuestions > 0) {
                    $form->extrait_action = null;
                    $errors['extrait'] = "Cet extrait est utilisé par {$autresQuestions} autre(s) question(s) : il ne peut pas être supprimé.";
                    return null;
                }

                if (!$this->extrait_model->supprimerExtrait($idExtrait)) {
                    $errors['extrait'] = "L'extrait n'a pas pu être supprimé. Veuillez réessayer.";
                    return null;
                }

                $form->extrait_action = null;
                $form->id_extrait = null;
                $form->extrait_mode = 'aucun';
                return 'Extrait supprimé.';
        }

        return null;
    }
}
