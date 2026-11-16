<?php

namespace App\Controllers\admin;

use App\Entities\QuestionForm;
use App\Models\ExtraitModel;
use App\Models\QuestionModel;
use App\Models\QuizModel;
use App\Models\QuizQuestionModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Ce controller gère les questions de l'Espace compréhension SIO (administration) :
 * liste, ajout / modification (avec l'extrait facultatif et les quiz) et suppression.
 *
 * Le contrôle de l'administrateur et les méthodes des listes sont dans AdminController.
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
        // Constructeur du parent : session, ThemeModel et vérification de l'administrateur
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

        // Colonnes du tableau. La colonne Quiz n'a pas de phrase : elle n'est pas triable.
        $colonnes = [
            'libelle'       => ['entete' => 'Énoncé', 'phrase' => 'par énoncé', 'asc' => 'de A à Z', 'desc' => 'de Z à A'],
            'quiz'          => ['entete' => 'Quiz'],
            'bonne_reponse' => ['entete' => 'Réponse', 'phrase' => 'par bonne réponse', 'asc' => 'Faux d\'abord', 'desc' => 'Vrai d\'abord'],
            'actif'         => ['entete' => 'Active'], // non triable : les inactives sont toujours à la fin
            'date_modif'    => ['entete' => 'Dernière modification', 'phrase' => 'par date de modification', 'asc' => 'de la plus ancienne à la plus récente', 'desc' => 'de la plus récente à la plus ancienne'],
        ];

        // 1. Paramètres de l'URL
        $themeSelect = (int) ($this->request->getGet('id_theme') ?? -1);

        $tri = $this->request->getGet('tri');
        if (! in_array($tri, ['libelle', 'bonne_reponse', 'date_modif'], true)) {
            $tri = null;
        }

        $ordre = 'asc';
        if ($this->request->getGet('ordre') === 'desc') {
            $ordre = 'desc';
        }

        // 2. Questions du thème choisi (colonne id_theme de la question), ou toutes les questions
        if ($themeSelect > 0) {
            $questions = $this->question_model->getQuestionsDuTheme($themeSelect);
        } else {
            $questions = $this->question_model->getAllQuestions();
        }

        // Tri en PHP. usort() compare les questions deux par deux avec la fonction donnée :
        // c'est une fonction anonyme (sans nom), écrite directement à l'endroit où elle sert.
        // use (...) lui donne accès aux variables $tri, $ordre et $collator de la méthode.
        // Le Collator compare les textes dans l'ordre alphabétique français (É rangé avec E).
        if ($tri !== null) {
            $collator = new \Collator('fr_FR');

            usort($questions, function (array $a, array $b) use ($tri, $ordre, $collator): int {
                $valeurA = (string) $a[$tri];
                $valeurB = (string) $b[$tri];

                // Pour la date, une question jamais modifiée est rangée selon sa date d'ajout
                if ($tri === 'date_modif') {
                    $valeurA = (string) ($a['date_modif'] ?? $a['date_ajout']);
                    $valeurB = (string) ($b['date_modif'] ?? $b['date_ajout']);
                }

                $resultat = $collator->compare($valeurA, $valeurB);

                // Ordre décroissant : on inverse le résultat
                if ($ordre === 'desc') {
                    $resultat = -$resultat;
                }

                return $resultat;
            });
        }

        // Question qui vient d'être ajoutée ou modifiée (envoyée par questionAjouter), en premier ;
        // les questions inactives (supprimées) à la fin
        $idNouveau = (int) ($this->session->getFlashdata('id_nouveau') ?? 0);
        $questions = $this->ordonnerListe($questions, $idNouveau);

        // 3. Pagination : 20 questions par page
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

        // 4. Liens pour la vue (méthodes d'AdminController)
        $parametresTri = [];
        if ($tri !== null) {
            $parametresTri = ['tri' => $tri, 'ordre' => $ordre];
        }
        $parametresTheme = [];
        if ($themeSelect > 0) {
            $parametresTheme = ['id_theme' => $themeSelect];
        }

        // Un formulaire de question resté en session ne sert plus quand on revient à la liste
        $this->session->remove('questionForm');

        $data = [
            'onglet'         => 'questions',
            'themeSelect'    => $themeSelect,
            'questionList'   => array_slice($questions, ($page - 1) * $parPage, $parPage),
            'idNouveau'      => $idNouveau,
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
     * « Suppression » d'une question : elle n'est pas effacée de la base, elle passe inactive (actif = 0).
     * Elle n'est plus proposée dans les quiz et s'affiche à la fin de la liste.
     * Pour la réactiver : la modifier et cocher « Question active ».
     *
     * @param int $idQuestion Identifiant de la question à désactiver
     * @return RedirectResponse Retour à la liste des questions, avec un message
     *
     * @author Arthur
     */
    public function questionSupprimer(int $idQuestion)
    {
        $question = $this->question_model->find($idQuestion);

        if ($question === null) {
            return redirect()->to(base_url('floribondant/theme'))
                ->with('error', 'Cette question n\'existe pas.');
        }

        // Les liaisons avec les quiz sont gardées : elles servent si la question est réactivée
        if (! $this->question_model->updateQuestion(['actif' => 0], $idQuestion)) {
            return redirect()->to(base_url('floribondant/theme'))
                ->with('error', "La question n'a pas pu être supprimée. Veuillez réessayer.");
        }
        
        helper('text');
        $debutLibelle = character_limiter($question['libelle'], 30, '…');

        return redirect()->to(base_url('floribondant/theme'))
            ->with('success', 'Question « ' . $debutLibelle . ' » supprimée : elle est désactivée et rangée à la fin de la liste.');
    }
    /**
     * Ajout ou modification d'une question, avec son thème, son extrait facultatif et ses quiz.
     * Le formulaire est gardé en session entre deux envois (actions sur un extrait).
     *
     * Règles liées au thème :
     * - les quiz cochés doivent être du thème de la question ;
     * - l'extrait doit être du thème de la question (son type est le nom du thème au singulier).
     *
     * @param int|null $idQuestionModif Identifiant de la question à modifier, null pour un ajout.
     * @return mixed
     *
     * @author Arthur
     */
    public function questionAjouter(?int $idQuestionModif = null)
    {
        $form = $this->session->get('questionForm');

        // Le mode (ajout ou modification) vient de l'URL : un formulaire en session
        // qui ne correspond pas à cette URL (autre question, ou ajout) est abandonné.
        if ($form !== null && (int) ($form->modifier ?? 0) !== (int) ($idQuestionModif ?? 0)) {
            $form = null;
        }

        if ($form === null) {
            $form = new QuestionForm();

            if ($idQuestionModif !== null) {
                $question = $this->question_model->find($idQuestionModif);

                if ($question !== null) {
                    // fill() remplit aussi id_theme, enregistré dans la question
                    $form->fill($question);
                    $form->modifier = $idQuestionModif;

                    // Extrait : la question en a-t-elle déjà un ?
                    if (! empty($question['id_extrait'])) {
                        $form->extrait_mode = 'existant';
                    } else {
                        $form->extrait_mode = 'aucun';
                    }

                    // Quiz de la question (cases cochées dans le formulaire)
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

            // Quiz cochés : aucune case cochée = rien n'est envoyé, d'où le ?? []
            $form->quiz = array_values((array) ($this->request->getPost('quiz') ?? []));

            $action = (string) $this->request->getPost('action');
            $actionsExtrait = ['modifier_extrait', 'enregistrer_extrait', 'supprimer_extrait', 'annuler_extrait'];

            // Toute autre action ferme la modification d'extrait en cours
            if (! in_array($action, $actionsExtrait, true)) {
                $form->extrait_action = null;
            }

            if (in_array($action, $actionsExtrait, true)) {
                $infoExtrait = $this->gererActionExtrait($action, $form, $errors);
                $extraitList = $this->extrait_model->getAllExtraits();
            } elseif ($action === 'enregistrer') {
                // Thème choisi, et type des extraits de ce thème (« Logs » donne « log »)
                $idTheme = (int) ($this->request->getPost('id_theme') ?? 0);
                $typeTheme = '';
                foreach ($themeList as $theme) {
                    if ((int) $theme->id === $idTheme) {
                        $typeTheme = $this->typeExtraitDuTheme($theme->nom);
                    }
                }

                // Identifiants des extraits du thème, pour vérifier l'extrait choisi
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

                // Règles de l'extrait selon l'option choisie
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

                // En cas d'erreur, on ne redirige pas : le formulaire est réaffiché
                // plus bas avec $errors et la saisie conservée en session.
                if (empty($errors)) {
                    $estModification = ! empty($form->modifier);

                    // Extrait, question et liaisons sont enregistrés ensemble ou pas du tout
                    $db = \Config\Database::connect('tracabilite');
                    $db->transBegin();

                    // Extrait : aucun, extrait existant, ou nouvel extrait créé maintenant
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

                        return redirect()->to(base_url('floribondant/theme'))
                            ->with('id_nouveau', $idQuestion) // affichée en premier dans la liste
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

        // ---------- Préparation de l'affichage : un bloc par thème, avec ses quiz et ses extraits ----------
        // La vue affiche seulement le bloc du thème coché (petit script sio-admin-theme-filtre.js).

        $themeChoisi = (int) ($form->id_theme ?? 0);
        $idExtraitChoisi = (string) ($form->id_extrait ?? '');

        // Quiz cochés, en texte pour les comparer strictement aux identifiants
        $quizCoches = [];
        foreach ((array) ($form->quiz ?? []) as $idQuiz) {
            $quizCoches[] = (string) $idQuiz;
        }

        // Extraits triés par titre. usort() compare les extraits deux par deux
        // avec une fonction anonyme (fonction sans nom, écrite à l'endroit où elle sert).
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

            // Quiz du thème. array_column() donne [id => titre], que les quiz soient des tableaux ou des objets.
            foreach (array_column($this->quiz_model->getAllQuizCategoryById((int) $theme->id), 'titre', 'id') as $idQuiz => $titre) {
                $bloc['quiz'][] = [
                    'id'    => (int) $idQuiz,
                    'titre' => $titre,
                    'coche' => in_array((string) $idQuiz, $quizCoches, true),
                ];
            }

            // Extraits du thème : ceux dont le type est le nom du thème au singulier
            $typeTheme = $this->typeExtraitDuTheme($theme->nom);

            foreach ($extraitList as $extrait) {
                if ($extrait['type'] !== $typeTheme) {
                    continue;
                }

                // Début du contenu, affiché sous le titre : retours à la ligne remplacés par des espaces,
                // puis coupé à 160 caractères
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
        ];

        return view('header')
            . view('notice')
            . view('admin/tracabilite/formQuestion', $data)
            . view('footer');
    }

    /**
     * Type des extraits d'un thème : le nom du thème en minuscules et au singulier.
     * Exemple : « Logs » donne « log », « Sessions » donne « session ».
     *
     * @param string $nomTheme Nom du thème
     * @return string
     *
     * @author Arthur
     */
    private function typeExtraitDuTheme(string $nomTheme): string
    {
        $type = mb_strtolower(trim($nomTheme));

        // On retire le « s » final du pluriel
        if (str_ends_with($type, 's')) {
            $type = mb_substr($type, 0, -1);
        }

        return $type;
    }

    /**
     * Modification / suppression de l'extrait existant choisi dans le formulaire de question.
     *
     * @param string $action Action demandée (modifier_extrait, enregistrer_extrait, supprimer_extrait,
     *                       annuler_extrait). La suppression est immédiate, sans confirmation.
     * @param QuestionForm $form Formulaire en cours (mis à jour).
     * @param array $errors Erreurs à afficher (complété).
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

        // Autres questions que celle en cours qui utilisent cet extrait
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
