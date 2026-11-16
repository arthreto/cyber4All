<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Entities\QuestionForm;
use App\Entities\Utilisateur;
use App\Models\ExtraitModel;
use App\Models\LeconModel;
use App\Models\QuestionModel;
use App\Models\QuizModel;
use App\Models\QuizQuestionModel;
use App\Models\ThemeModel;
use App\Models\UtilisateurModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Session\Session;

/**
 * Ce controller gère l'administration de l'Espace compréhension SIO
 *
 * @since 1.0.0
 * @package Cyber4all
 * @author Arthur
 * @author Raphael Bierlaire (leçons)
 */
class AdminThemeController extends AdminController
{

    protected UtilisateurModel $utilisateur_model;
    protected ?Utilisateur $utilisateur = null;
    protected Session $session;
    protected ThemeModel $theme_model;
    protected QuizModel $quiz_model;
    protected QuestionModel $question_model;
    protected LeconModel $lecon_model;
    protected QuizQuestionModel $quiz_question_model;
    protected ExtraitModel $extrait_model;

    public function __construct()
    {
        $this->utilisateur_model = new UtilisateurModel();
        $this->session = session();
        $this->lecon_model = new LeconModel();
        $this->theme_model = new ThemeModel();
        $this->quiz_model = new QuizModel();
        $this->question_model = new QuestionModel();
        $this->quiz_question_model = new QuizQuestionModel();
        $this->extrait_model = new ExtraitModel();

        // Vérification de la session et du rôle de l'utilisateur
        $utilisateur = $this->session->get('utilisateur');

        if ($utilisateur && $utilisateur->role === 'admin') {
            $this->utilisateur = $utilisateur;

        } else {
            throw PageNotFoundException::forPageNotFound('Accès refusé');
        }
    }

    /**
     * Liste des questions : filtre par thème, tri et pagination.
     *
     * @return string
     * @author Arthur
     */
    public function index()
    {
        $base = 'floribondant/theme';

        $colonnes = [
            'libelle' => ['entete' => 'Énoncé', 'phrase' => 'par énoncé', 'asc' => 'de A à Z', 'desc' => 'de Z à A'],
            'quiz' => ['entete' => 'Quiz'],
            'bonne_reponse' => ['entete' => 'Réponse', 'phrase' => 'par bonne réponse', 'asc' => 'Faux d\'abord', 'desc' => 'Vrai d\'abord'],
            'actif' => ['entete' => 'Active', 'phrase' => 'par visibilité', 'asc' => 'inactives d\'abord', 'desc' => 'actives d\'abord'],
            'date_modif' => ['entete' => 'Dernière modification', 'phrase' => 'par date de modification', 'asc' => 'de la plus ancienne à la plus récente', 'desc' => 'de la plus récente à la plus ancienne'],
        ];

        $themeSelect = (int)($this->request->getGet('id_theme') ?? -1);

        $tri = $this->request->getGet('tri');
        if (!in_array($tri, ['libelle', 'bonne_reponse', 'actif', 'date_modif'], true)) {
            $tri = null;
        }

        $ordre = 'asc';
        if ($this->request->getGet('ordre') === 'desc') {
            $ordre = 'desc';
        }

        if ($themeSelect > 0) {
            $questions = $this->question_model->getQuestionsFromTheme($themeSelect);
        } else {
            $questions = $this->question_model->getAllQuestions();
        }


        if ($tri !== null) {
            $collator = new \Collator('fr_FR');

            usort($questions, function (array $a, array $b) use ($tri, $ordre, $collator): int {
                $valeurA = (string)$a[$tri];
                $valeurB = (string)$b[$tri];

                if ($tri === 'date_modif') {
                    $valeurA = (string)($a['date_modif'] ?? $a['date_ajout']);
                    $valeurB = (string)($b['date_modif'] ?? $b['date_ajout']);
                }

                $resultat = $collator->compare($valeurA, $valeurB);

                if ($ordre === 'desc') {
                    $resultat = -$resultat;
                }

                return $resultat;
            });
        }

        $parPage = 20;
        $totalQuestions = count($questions);
        $nbPages = max(1, (int)ceil($totalQuestions / $parPage));

        $page = (int)($this->request->getGet('page') ?? 1);
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
        $parametresTheme = [];
        if ($themeSelect > 0) {
            $parametresTheme = ['id_theme' => $themeSelect];
        }


        session()->remove('questionForm');

        $data = [
            'onglet' => 'questions',
            'themeSelect' => $themeSelect,
            'questionList' => array_slice($questions, ($page - 1) * $parPage, $parPage),
            'totalQuestions' => $totalQuestions,
            'filtreThemes'   => parent::filtreThemes($base, $this->theme_model->getAllThemes(), $themeSelect, $parametresTri),
            'colonnes'       => parent::colonnesTri($base, $colonnes, $tri, $ordre, $parametresTheme),
            'messageTri'     => $this->messageTri('Questions triées', $colonnes, $tri, $ordre),
            'urlSansTri'     => parent::adresseListe($base, $parametresTheme),
            'pages'          => parent::pagesListe($base, $nbPages, $page, $parametresTheme + $parametresTri),
        ];

        return view('header')
            . view('notice')
            . view('admin/tracabilite/onglets', $data)
            . view('admin/tracabilite/adminQuestions', $data)
            . view('footer');
    }

    /**
     * Suppression d'une question et de ses liaisons avec les quiz
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
     * Ajout ou modification d'une question, avec son extrait facultatif et ses quiz.
     *
     * @param int|null $idQuestionModif Identifiant de la question à modifier, null pour un ajout.
     * @return mixed
     *
     * @author Arthur
     */
    public function questionAjouter(?int $idQuestionModif = null)
    {
        $form = $this->session->get('questionForm');

        if ($form !== null && (int)($form->modifier ?? 0) !== (int)($idQuestionModif ?? 0)) {
            $form = null;
        }

        if ($form === null) {
            $form = new QuestionForm();

            if ($idQuestionModif !== null) {
                $question = $this->question_model->find($idQuestionModif);

                if ($question !== null) {
                    $form->fill($question);
                    $form->modifier = $idQuestionModif;

                    if (!empty($question['id_extrait'])) {
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
        $extraitList = $this->extrait_model->getAllExtraits();

        if ($this->request->is('post')) {
            $post = $this->request->getPost();
            unset($post['quiz']);

            $form->fill($post);

            $form->quiz = array_values((array)($this->request->getPost('quiz') ?? []));

            $action = (string)$this->request->getPost('action');
            $actionsExtrait = ['modifier_extrait', 'enregistrer_extrait', 'supprimer_extrait', 'annuler_extrait'];

            if (!in_array($action, $actionsExtrait, true)) {
                $form->extrait_action = null;
            }

            if (in_array($action, $actionsExtrait, true)) {
                $infoExtrait = $this->gererActionExtrait($action, $form, $errors);
                $extraitList = $this->extrait_model->getAllExtraits();
            } else {
                $formData = [
                    'libelle' => $this->request->getPost('libelle') ?? '',
                    'image' => $this->request->getPost('image') ?? '',
                    'extrait_mode' => $form->extrait_mode ?? 'aucun',
                    'id_extrait' => $form->id_extrait ?? '',
                    'extrait_titre' => $form->extrait_titre ?? '',
                    'extrait_type' => $form->extrait_type ?? '',
                    'extrait_contenu' => $form->extrait_contenu ?? '',
                    'bonne_reponse' => $this->request->getPost('bonne_reponse'),
                    'explication' => $this->request->getPost('explication') ?? '',
                    'actif' => $this->request->getPost('actif') ?? '0',
                    'modifier' => $this->request->getPost('modifier') ?? '0',
                ];


                $rules = [
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
                        'rules' => 'required|in_list[' . implode(',', array_column($extraitList, 'id')) . ']',
                        'errors' => [
                            'required' => 'Veuillez choisir un extrait existant.',
                            'in_list' => 'L’extrait choisi n’existe pas.',
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
                            'required' => 'Veuillez choisir le type de l’extrait.',
                            'in_list' => 'Le type de l’extrait est invalide.',
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

                if ($this->request->getPost('action') === 'enregistrer') {

                    if (!$this->validateData($formData, $rules)) {
                        $errors = $this->validator->getErrors();
                    }

                    if (empty($errors)) {
                        $estModification = !empty($form->modifier);


                        $db = \Config\Database::connect('tracabilite');
                        $db->transBegin();

                        $idExtrait = null;

                        if ($formData['extrait_mode'] === 'existant') {
                            $idExtrait = (int)$formData['id_extrait'];
                        } elseif ($formData['extrait_mode'] === 'nouveau') {
                            $idExtrait = $this->extrait_model->creerExtrait([
                                'titre' => $formData['extrait_titre'],
                                'type' => $formData['extrait_type'],
                                'contenu' => $formData['extrait_contenu'],
                            ]);
                        }

                        $idQuestion = false;

                        if ($idExtrait !== false) {
                            $formData['id_extrait'] = $idExtrait;

                            if ($estModification) {
                                $idQuestion = $this->question_model->updateQuestion($formData, (int)$form->modifier)
                                    ? (int)$form->modifier
                                    : false;
                            } else {
                                $idQuestion = $this->question_model->creerQuestion($formData);
                            }
                        }

                        if ($idQuestion !== false
                            && $this->quiz_question_model->updateLiaisons($idQuestion, $form->quiz ?? [])) {
                            $db->transCommit();
                            $this->session->remove('questionForm');

                            return redirect()->to(base_url('floribondant/theme'))
                                ->with('success', $estModification
                                    ? 'Question modifiée avec succès !'
                                    : 'Question créée avec succès !');
                        }

                        $db->transRollback();
                        $errors['enregistrement'] = "La question n'a pas pu être enregistrée. Veuillez réessayer.";
                    }
                }
            }

            $this->session->set('questionForm', $form);
        }

        $typesExtrait = ExtraitModel::TYPES;
        $quizParTheme = [];
        foreach ($this->theme_model->getAllThemes() as $theme) {
            $quizParTheme[$theme->nom] = $this->quiz_model->getAllQuizCategoryById((int)$theme->id);
        }

        $quizCoches = [];
        foreach ((array)($form->quiz ?? []) as $idQuiz) {
            $quizCoches[] = (string)$idQuiz;
        }

        $groupesQuiz = [];
        foreach ($quizParTheme as $nomTheme => $quizDuTheme) {
            if (empty($quizDuTheme)) {
                continue;
            }

            $groupe = ['theme' => $nomTheme, 'ouvert' => false, 'quiz' => []];


            foreach (array_column($quizDuTheme, 'titre', 'id') as $idQuiz => $titre) {
                $coche = in_array((string)$idQuiz, $quizCoches, true);

                $groupe['quiz'][] = ['id' => (int)$idQuiz, 'titre' => $titre, 'coche' => $coche];

                if ($coche) {
                    $groupe['ouvert'] = true;
                }
            }

            $groupesQuiz[] = $groupe;
        }

        usort($extraitList, function (array $a, array $b): int {
            return strcasecmp($a['titre'], $b['titre']);
        });


        $idExtraitChoisi = (string)($form->id_extrait ?? '');
        $groupesExtraits = [];

        foreach ($extraitList as $extrait) {

            $type = $typesExtrait[$extrait['type']] ?? $extrait['type'];

            if (!isset($groupesExtraits[$type])) {
                $groupesExtraits[$type] = ['type' => $type, 'ouvert' => false, 'extraits' => []];
            }

            $choisi = (string)$extrait['id'] === $idExtraitChoisi;

            $debut = preg_replace('/\s+/u', ' ', (string)$extrait['contenu']);
            $debut = mb_strimwidth(trim($debut), 0, 160, '…');

            $groupesExtraits[$type]['extraits'][] = [
                'id' => (int)$extrait['id'],
                'titre' => $extrait['titre'],
                'contenu' => $extrait['contenu'],
                'debut' => $debut,
                'choisi' => $choisi,
            ];

            if ($choisi) {
                $groupesExtraits[$type]['ouvert'] = true;
            }
        }

        // Trie selon les clés du tableau dans l'ordre alphabetique
        ksort($groupesExtraits);

        $data = [
            'form' => $form,
            'groupesQuiz' => $groupesQuiz,
            'groupesExtraits' => $groupesExtraits,
            'typesExtrait' => $typesExtrait,
            'infoExtrait' => $infoExtrait,
            'errors' => $errors,
        ];

        return view('header')
            . view('notice')
            . view('admin/tracabilite/formQuestion', $data)
            . view('footer');
    }

    /**
     * Modification / suppression de l'extrait existant choisi dans le formulaire de question.
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

        $idExtrait = (int)($form->id_extrait ?? 0);
        $extrait = $idExtrait > 0 ? $this->extrait_model->find($idExtrait) : null;

        if ($extrait === null) {
            $form->extrait_action = null;
            $errors['id_extrait'] = 'Veuillez choisir un extrait existant.';
            return null;
        }

        $autresQuestions = $this->extrait_model->compterQuestions($idExtrait, (int)($form->modifier ?? 0));

        switch ($action) {
            case 'modifier_extrait':
                $form->extrait_action = 'modifier';
                $form->edit_titre = $extrait['titre'];
                $form->edit_type = $extrait['type'];
                $form->edit_contenu = $extrait['contenu'];
                return null;

            case 'enregistrer_extrait':
                $donnees = [
                    'edit_titre' => $form->edit_titre ?? '',
                    'edit_type' => $form->edit_type ?? '',
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
                    'titre' => $donnees['edit_titre'],
                    'type' => $donnees['edit_type'],
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

    /**
     * Liste des quiz : filtre par thème, tri et pagination.
     *
     * @return string
     * @author Arthur
     */
    public function quizSection()
    {
        $base = 'floribondant/theme/quiz';

        $colonnes = [
            'titre' => ['entete' => 'Titre', 'phrase' => 'par titre', 'asc' => 'de A à Z', 'desc' => 'de Z à A'],
            'theme_nom' => ['entete' => 'Thème', 'phrase' => 'par thème', 'asc' => 'de A à Z', 'desc' => 'de Z à A'],
            'nb_questions' => ['entete' => 'Questions', 'phrase' => 'par nombre de questions', 'asc' => 'du plus petit au plus grand', 'desc' => 'du plus grand au plus petit'],
            'actif' => ['entete' => 'Actif', 'phrase' => 'par visibilité', 'asc' => 'inactifs d\'abord', 'desc' => 'actifs d\'abord'],
        ];

        $themeSelect = (int)($this->request->getGet('id_theme') ?? -1);

        $tri = $this->request->getGet('tri');
        if (!in_array($tri, array_keys($colonnes), true)) {
            $tri = null;
        }

        $ordre = 'asc';
        if ($this->request->getGet('ordre') === 'desc') {
            $ordre = 'desc';
        }

        // 2. Quiz du thème choisi, -1 = tous les thèmes
        $quizList = $this->quiz_model->getQuizAdmin($themeSelect);

        if ($tri !== null) {
            $collator = new \Collator('fr_FR');

            usort($quizList, function (array $a, array $b) use ($tri, $ordre, $collator): int {
                if ($tri === 'nb_questions' || $tri === 'actif') {
                    $resultat = (int)($a[$tri] ?? 1) <=> (int)($b[$tri] ?? 1);
                } else {
                    $resultat = $collator->compare((string)$a[$tri], (string)$b[$tri]);
                }

                if ($ordre === 'desc') {
                    $resultat = -$resultat;
                }

                return $resultat;
            });
        }

        $parPage = 20;
        $totalQuiz = count($quizList);
        $nbPages = max(1, (int)ceil($totalQuiz / $parPage));

        $page = (int)($this->request->getGet('page') ?? 1);
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
        $parametresTheme = [];
        if ($themeSelect > 0) {
            $parametresTheme = ['id_theme' => $themeSelect];
        }

        $data = [
            'onglet'       => 'quiz',
            'themeSelect'  => $themeSelect,
            'quizList'     => array_slice($quizList, ($page - 1) * $parPage, $parPage),
            'totalQuiz'    => $totalQuiz,
            'filtreThemes' => parent::filtreThemes($base, $this->theme_model->getAllThemes(), $themeSelect, $parametresTri),
            'colonnes'     => parent::colonnesTri($base, $colonnes, $tri, $ordre, $parametresTheme),
            'messageTri'   => parent::messageTri('Quiz triés', $colonnes, $tri, $ordre),
            'urlSansTri'   => parent::adresseListe($base, $parametresTheme),
            'pages'        => $this->pagesListe($base, $nbPages, $page, $parametresTheme + $parametresTri),
        ];

        return view('header')
            . view('notice')
            . view('admin/tracabilite/onglets', $data)
            . view('admin/tracabilite/adminQuiz', $data)
            . view('footer');
    }

    /**
     * Liste des leçons : filtre par thème, tri et pagination.
     *
     * @return string
     * @author Raphael Bierlaire
     */
    public function leconsSection()
    {
        $base = 'floribondant/theme/lecons';


        $colonnes = [
            'titre' => ['entete' => 'Titre', 'phrase' => 'par titre', 'asc' => 'de A à Z', 'desc' => 'de Z à A'],
            'theme' => ['entete' => 'Thème', 'phrase' => 'par thème', 'asc' => 'de A à Z', 'desc' => 'de Z à A'],
            'auteur' => ['entete' => 'Auteur', 'phrase' => 'par auteur', 'asc' => 'de A à Z', 'desc' => 'de Z à A'],
            'date_ajout' => ['entete' => 'Ajoutée le', 'phrase' => 'par date d\'ajout', 'asc' => 'de la plus ancienne à la plus récente', 'desc' => 'de la plus récente à la plus ancienne'],
            'date_modif' => ['entete' => 'Dernière modification', 'phrase' => 'par date de modification', 'asc' => 'de la plus ancienne à la plus récente', 'desc' => 'de la plus récente à la plus ancienne'],
        ];

        $themeSelect = (int)($this->request->getGet('id_theme') ?? -1);

        $tri = $this->request->getGet('tri');
        if (!in_array($tri, array_keys($colonnes), true)) {
            $tri = null;
        }

        $ordre = 'asc';
        if ($this->request->getGet('ordre') === 'desc') {
            $ordre = 'desc';
        }

        $idTheme = null;
        if ($themeSelect > 0) {
            $idTheme = $themeSelect;
        }
        $lecons = $this->lecon_model->getAllLecons($idTheme, $tri, $ordre);

        $parPage = 20;
        $totalLecons = count($lecons);
        $nbPages = max(1, (int)ceil($totalLecons / $parPage));

        $page = (int)($this->request->getGet('page') ?? 1);
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
        $parametresTheme = [];
        if ($themeSelect > 0) {
            $parametresTheme = ['id_theme' => $themeSelect];
        }

        session()->remove('leconForm');

        $data = [
            'onglet'       => 'lecons',
            'themeSelect'  => $themeSelect,
            'leconList'    => array_slice($lecons, ($page - 1) * $parPage, $parPage),
            'totalLecons'  => $totalLecons,
            'filtreThemes' => parent::filtreThemes($base, $this->theme_model->getAllThemes(), $themeSelect, $parametresTri),
            'colonnes'     => parent::colonnesTri($base, $colonnes, $tri, $ordre, $parametresTheme),
            'messageTri'   => parent::messageTri('Leçons triées', $colonnes, $tri, $ordre),
            'urlSansTri'   => parent::adresseListe($base, $parametresTheme),
            'pages'        => $this->pagesListe($base, $nbPages, $page, $parametresTheme + $parametresTri),
        ];

        return view('header')
            . view('notice')
            . view('admin/tracabilite/onglets', $data)
            . view('admin/tracabilite/adminLecons', $data)
            . view('footer');
    }
}