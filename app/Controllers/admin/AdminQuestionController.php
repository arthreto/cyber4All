<?php

namespace App\Controllers\admin;

use App\Entities\QuestionForm;
use App\Models\ExtraitModel;
use App\Models\QuestionModel;
use App\Models\QuizModel;
use App\Models\QuizQuestionModel;
use CodeIgniter\Exceptions\PageNotFoundException;
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
            'actif'         => ['entete' => 'Active'], // non triable : les inactives sont toujours à la fin
            'date_modif'    => ['entete' => 'Dernière modification', 'phrase' => 'par date de modification', 'asc' => 'de la plus ancienne à la plus récente', 'desc' => 'de la plus récente à la plus ancienne'],
        ];

        $themeSelect = (int) ($this->request->getGet('id_theme') ?? -1);

        $tri = $this->request->getGet('tri');
        if (! in_array($tri, ['libelle', 'bonne_reponse', 'date_modif'], true)) {
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

        // Question qui vient d'être ajoutée ou modifiée, en premier ; les questions inactives à la fin
        $idNouveau = (int) ($this->session->getFlashdata('id_nouveau') ?? 0);
        $questions = $this->ordonnerListe($questions, $idNouveau);
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

        $questionList = array_slice($questions, ($page - 1) * $parPage, $parPage);

        // Questions inactives de la page : raison qui empêche la réactivation (null = réactivable)
        $raisonsReactivation = [];
        foreach ($questionList as $question) {
            if ((int) ($question['actif'] ?? 1) !== 1) {
                $raisonsReactivation[(int) $question['id']] = $this->raisonNonReactivable((int) $question['id']);
            }
        }

        $data = [
            'onglet'         => 'questions',
            'themeSelect'    => $themeSelect,
            'questionList'   => $questionList,
            'raisonsReactivation' => $raisonsReactivation,
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
     * Suppression d'une question et de ses liaisons avec les quiz.
     *
     * @param int $idQuestion Identifiant de la question à supprimer
     * @return RedirectResponse Retour à la liste des questions, avec un message
     *
     * @author Arthur
     */
    public function questionSupprimer(int $idQuestion)
    {
        // Suppression uniquement par le formulaire (POST + jeton CSRF) : un simple lien ne doit rien supprimer
        if (! $this->request->is('post')) {
            return redirect()->to(base_url('floribondant/theme'));
        }

        $question = $this->question_model->find($idQuestion);

        if ($question === null) {
            return redirect()->to(base_url('floribondant/theme'))
                ->with('error', 'Cette question n\'existe pas.');
        }

        // Les liaisons avec les quiz sont gardées pour une réactivation
        if (! $this->question_model->updateQuestion(['actif' => 0], $idQuestion)) {
            return redirect()->to(base_url('floribondant/theme'))
                ->with('error', "La question n'a pas pu être supprimée. Veuillez réessayer.");
        }

        // Les quiz de cette question qui n'ont plus assez de questions actives passent inactifs
        $quizDesactives = $this->quiz_question_model->desactiverQuizIncomplets(
            $this->quiz_question_model->getIdsQuizByQuestion($idQuestion)
        );

        helper('text');
        $debutLibelle = character_limiter($question['libelle'], 30, '…');
        $message = 'Question « ' . $debutLibelle . ' » supprimée : elle est désactivée et rangée à la fin de la liste.';

        // Quiz actifs qui contenaient cette question : ils n'ont plus leur nombre de questions
        $titresQuiz = [];
        foreach ($this->quiz_question_model->getIdsQuizByQuestion($idQuestion) as $idQuiz) {
            $quiz = $this->quiz_model->find((int) $idQuiz);
            if ($quiz !== null && (int) ($quiz->actif ?? 1) === 1) {
                $titresQuiz[] = '« ' . $quiz->titre . ' »';
            }
        }
        if ($titresQuiz !== []) {
            $message .= (count($titresQuiz) > 1 ? ' Quiz maintenant incomplets : ' : ' Quiz maintenant incomplet : ')
                . implode(', ', $titresQuiz) . '. Ajoutez-y une autre question.';
        }

        return redirect()->to(base_url('floribondant/theme'))
<<<<<<< Updated upstream
            ->with('success', $message);
=======
            ->with('success', 'Question « ' . $debutLibelle . ' » supprimée : elle est désactivée et rangée à la fin de la liste.'
                . $this->messageQuizDesactives($quizDesactives));
    }

    /**
     * Réactivation d'une question supprimée (désactivée), seulement si elle est valide.
     * Ses quiz ne sont pas réactivés automatiquement : ils se réactivent depuis la liste des quiz.
     *
     * @param int $idQuestion Identifiant de la question à réactiver
     * @return RedirectResponse Retour à la liste des questions, avec un message
     *
     * @author Raphael Bierlaire
     */
    public function questionReactiver(int $idQuestion)
    {
        $raison = $this->raisonNonReactivable($idQuestion);

        if ($raison !== null) {
            return redirect()->to(base_url('floribondant/theme'))
                ->with('error', 'La question ne peut pas être réactivée : ' . $raison);
        }

        if (! $this->question_model->updateQuestion(['actif' => 1], $idQuestion)) {
            return redirect()->to(base_url('floribondant/theme'))
                ->with('error', "La question n'a pas pu être réactivée. Veuillez réessayer.");
        }

        return redirect()->to(base_url('floribondant/theme'))
            ->with('id_nouveau', $idQuestion) // affichée en premier dans la liste
            ->with('success', 'Question réactivée : elle est de nouveau proposée dans ses quiz actifs.');
    }

    /**
     * Vérifie qu'une question inactive peut être réactivée (mêmes règles que le formulaire).
     *
     * @param int  $idQuestion              Identifiant de la question
     * @param bool $ignorerExtraitInactif   true : un extrait supprimé n'est pas une raison
     *                                      (formulaire de la question, où il est déjà signalé par un badge « Inactif »)
     * @return string|null Raison du refus, ou null si la question peut être réactivée.
     *
     * @author Raphael Bierlaire
     */
    private function raisonNonReactivable(int $idQuestion, bool $ignorerExtraitInactif = false): ?string
    {
        $question = $this->question_model->find($idQuestion);

        if ($question === null) {
            return "cette question n'existe pas.";
        }
        if ((int) ($question['actif'] ?? 1) === 1) {
            return 'elle est déjà active.';
        }
        if ($this->theme_model->find((int) $question['id_theme']) === null) {
            return "son thème n'existe plus : modifiez-la pour en choisir un autre.";
        }

        $longueurLibelle = mb_strlen((string) $question['libelle']);
        if ($longueurLibelle < 3 || $longueurLibelle > 1000) {
            return 'son énoncé doit contenir entre 3 et 1000 caractères : modifiez-la.';
        }

        $longueurExplication = mb_strlen((string) $question['explication']);
        if ($longueurExplication < 3 || $longueurExplication > 5000) {
            return 'son explication doit contenir entre 3 et 5000 caractères : modifiez-la.';
        }

        if (! in_array((string) $question['bonne_reponse'], ['0', '1'], true)) {
            return "sa bonne réponse n'est ni Vrai ni Faux : modifiez-la.";
        }

        if (! empty($question['id_extrait'])) {
            $extrait = $this->extrait_model->find((int) $question['id_extrait']);

            if ($extrait === null) {
                return "son extrait n'existe plus : modifiez-la.";
            }
            if (! $ignorerExtraitInactif && (int) ($extrait['actif'] ?? 1) !== 1) {
                return "son extrait « " . $extrait['titre'] . " » est supprimé : réactivez l'extrait, ou modifiez la question pour en changer.";
            }
        }

        return null;
    }

    /**
     * Phrase à ajouter au message de succès quand des quiz ont été désactivés.
     *
     * @param array $titres Titres des quiz désactivés.
     * @return string Phrase (vide si aucun quiz désactivé).
     *
     * @author Raphael Bierlaire
     */
    private function messageQuizDesactives(array $titres): string
    {
        if ($titres === []) {
            return '';
        }

        $noms = implode(', ', array_map(fn (string $titre): string => '« ' . $titre . ' »', $titres));

        return count($titres) > 1
            ? ' Ces quiz n\'ont plus assez de questions et sont désactivés : ' . $noms . '.'
            : ' Ce quiz n\'a plus assez de questions et est désactivé : ' . $noms . '.';
>>>>>>> Stashed changes
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

        // Modification d'une question qui n'existe pas : page introuvable (comme les leçons, quiz et extraits)
        if ($idQuestionModif !== null && $this->question_model->find($idQuestionModif) === null) {
            throw PageNotFoundException::forPageNotFound('Question introuvable');
        }

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

                // Le quiz n'est pas pré-coché : la question revient cochée dans le formulaire du quiz,
                // et c'est l'enregistrement du quiz qui la rattache (il vérifie le nombre exact de questions)
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
        $themeList = $this->theme_model->getAllThemes();
        // Extraits proposés : les actifs, plus l'extrait actuel de la question même s'il est supprimé (désactivé)
        $idExtraitActuel = null;
        if (! empty($form->modifier)) {
            $questionActuelle = $this->question_model->find((int) $form->modifier);
            $idExtraitActuel = empty($questionActuelle['id_extrait']) ? null : (int) $questionActuelle['id_extrait'];
        }
        $extraitList = $this->extrait_model->getAllExtraits($idExtraitActuel);

        if ($this->request->is('post')) {
            $post = $this->request->getPost();

            unset($post['quiz'], $post['image'], $post['image_envoyee']);

            $form->fill($post);

            $form->quiz = array_values((array) ($this->request->getPost('quiz') ?? []));

            $erreurImage = $this->gererImage($form);
            if ($erreurImage !== null) {
                $errors['image'] = $erreurImage;
            }

            $action = (string) $this->request->getPost('action');

            // Les extraits se modifient et se suppriment dans l'onglet « Extrait » (AdminExtraitController)
            if ($action === 'enregistrer') {
                $idTheme = (int) ($this->request->getPost('id_theme') ?? 0);
                $typeTheme = '';
                foreach ($themeList as $theme) {
                    if ((int) $theme->id === $idTheme) {
                        $typeTheme = $this->typeExtraitDuTheme($theme->nom);
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
                    'image'           => (string) ($form->image ?? ''),
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
                    // in_list : seulement un thème qui existe (une valeur modifiée à la main est refusée)
                    'id_theme' => [
                        'label' => 'Thème',
                        'rules' => 'required|in_list[' . implode(',', array_map(fn ($theme) => (int) $theme->id, $themeList)) . ']',
                        'errors' => [
                            'required' => 'Veuillez choisir un thème.',
                            'in_list' => "Le thème choisi n'existe pas.",
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

                // array_merge : on garde l'erreur éventuelle de l'image
                if (! $this->validateData($formData, $rules)) {
                    $errors = array_merge($errors, $this->validator->getErrors());
                }

                // Question active avec un extrait supprimé (désactivé) : refusée,
                // une question qui utilise un extrait supprimé doit rester inactive
                if ((string) $formData['actif'] === '1'
                    && $formData['extrait_mode'] === 'existant'
                    && ! isset($errors['id_extrait'])) {
                    foreach ($extraitList as $extrait) {
                        if ((int) $extrait['id'] === (int) $formData['id_extrait'] && (int) ($extrait['actif'] ?? 1) !== 1) {
                            $errors['id_extrait'] = 'L’extrait choisi est supprimé : choisissez-en un autre, réactivez-le, ou décochez « Question active ».';
                        }
                    }
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

                // Quiz complet : on refuse d'y ajouter la question (un quiz a exactement nb_questions questions)
                if (! isset($errors['quiz'])) {
                    $quizComplets = $this->quiz_question_model->getQuizComplets(
                        empty($form->modifier) ? null : (int) $form->modifier,
                        $form->quiz
                    );

                    if ($quizComplets === null) {
                        $errors['quiz'] = 'Impossible de vérifier le nombre de questions des quiz. Veuillez réessayer.';
                    } elseif ($quizComplets !== []) {
                        $noms = array_map(fn (array $q): string
                            => '« ' . $q['titre'] . ' » (' . $q['nb_actuel'] . ' / ' . $q['nb_questions'] . ')', $quizComplets);
                        $errors['quiz'] = (count($noms) > 1 ? 'Ces quiz sont complets' : 'Ce quiz est complet')
                            . ' : ' . implode(', ', $noms) . '. Retirez d’abord une question depuis le formulaire du quiz.';
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

                    // Quiz de la question avant l'enregistrement : ceux qu'elle quitte peuvent devenir incomplets
                    $quizAvant = $estModification
                        ? $this->quiz_question_model->getIdsQuizByQuestion((int) $form->modifier)
                        : [];

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
                        $this->session->remove('questionForm'); // l'image envoyée est maintenant celle de la question

                        // Question retirée d'un quiz ou rendue inactive : ses quiz incomplets passent inactifs
                        $quizDesactives = $this->quiz_question_model->desactiverQuizIncomplets(
                            array_merge($quizAvant, $form->quiz)
                        );


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
                            ->with('id_nouveau', $idQuestion) // affichée en premier dans la liste
                            ->with('success', ($estModification
                                ? 'Question modifiée avec succès !'
                                : 'Question créée avec succès !')
                                . $this->messageQuizDesactives($quizDesactives));
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


            foreach ($this->quiz_model->getAllQuizCategoryById((int) $theme->id) as $quiz) {
                $bloc['quiz'][] = [
                    'id'    => (int) $quiz->id,
                    'titre' => $quiz->titre,
                    'coche' => in_array((string) $quiz->id, $quizCoches, true),
                    'actif' => (int) ($quiz->actif ?? 1) === 1, // badge « Inactif » sinon
                ];
            }

            $typeTheme = $this->typeExtraitDuTheme($theme->nom);

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
                    'actif'   => (int) ($extrait['actif'] ?? 1) === 1, // badge « Inactif » sinon
                ];
            }

            $blocsThemes[] = $bloc;
        }

        // Question inactive en base : ce qui l'empêche d'être activée (hors extrait supprimé,
        // signalé par un badge « Inactif » sur l'extrait)
        $raisonActivation = null;
        if (! empty($form->modifier)) {
            $questionEnBase = $this->question_model->find((int) $form->modifier);
            if ($questionEnBase !== null && (int) ($questionEnBase['actif'] ?? 1) !== 1) {
                $raisonActivation = $this->raisonNonReactivable((int) $form->modifier, true);
            }
        }

        $data = [
            'form'         => $form,
            'raisonActivation' => $raisonActivation,
            'blocsThemes'  => $blocsThemes,
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
     * Image de la question : case « Retirer l'image », ou fichier choisi (champ fichier_image).
     * Le fichier est vérifié avec les règles de fichier de CodeIgniter, puis déplacé
     * dans public/images/questions/ sous un nom aléatoire (deux fichiers du même nom ne s'écrasent pas).
     * La question ne garde que ce nom (colonne image).
     *
     * Une image envoyée pendant la saisie puis remplacée ou retirée avant l'enregistrement
     * est supprimée du dossier. L'image déjà enregistrée avec la question n'est jamais supprimée ici.
     *
     * @param QuestionForm $form Formulaire en cours (image et image_envoyee mis à jour).
     * @return string|null Message d'erreur à afficher, ou null.
     *
     * @author Arthur
     */
    private function gererImage(QuestionForm $form): ?string
    {
        $dossier = FCPATH . 'images/questions/';

        // Retire l'image envoyée pendant cette saisie (pas celle déjà enregistrée avec la question)
        $oublierImageEnvoyee = function () use ($form, $dossier): void {
            $envoyee = (string) ($form->image_envoyee ?? '');
            if ($envoyee !== '' && is_file($dossier . $envoyee)) {
                unlink($dossier . $envoyee);
            }
            $form->image_envoyee = null;
        };

        if ($this->request->getPost('retirer_image') === '1') {
            $oublierImageEnvoyee();
            $form->image = '';
        }

        // getFile() renvoie null si le champ n'existe pas. UPLOAD_ERR_NO_FILE : aucun fichier choisi.
        $fichier = $this->request->getFile('fichier_image');
        if ($fichier === null || $fichier->getError() === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        // Règles de fichier de CodeIgniter. Instance non partagée (false) : le validateur
        // du contrôleur ($this->validator) reste libre pour les autres champs.
        $validation = service('validation', null, false);
        $validation->setRules([
            'fichier_image' => [
                'label'  => 'Image',
                'rules'  => [
                    'uploaded[fichier_image]',
                    'is_image[fichier_image]',
                    'mime_in[fichier_image,image/png,image/jpeg,image/webp]',
                    'ext_in[fichier_image,png,jpg,jpeg,webp]',
                    'max_size[fichier_image,2048]',
                ],
                'errors' => [
                    'uploaded' => 'L’image n’a pas pu être envoyée (2 Mo au maximum).',
                    'is_image' => 'Le fichier choisi n’est pas une image.',
                    'mime_in'  => 'L’image doit être au format PNG, JPG ou WEBP.',
                    'ext_in'   => 'L’image doit être au format PNG, JPG ou WEBP.',
                    'max_size' => 'L’image ne peut pas dépasser 2 Mo.',
                ],
            ],
        ]);

        if (! $validation->withRequest($this->request)->run()) {
            return $validation->getError('fichier_image');
        }

        // Nom aléatoire avec la bonne extension (getRandomName), puis déplacement
        $nom = $fichier->getRandomName();
        if (! $fichier->move($dossier, $nom) || ! $fichier->hasMoved()) {
            return 'L’image n’a pas pu être enregistrée. Veuillez réessayer.';
        }

        $oublierImageEnvoyee();
        $form->image = $nom;
        $form->image_envoyee = $nom;

        return null;
    }

    /**
     * Type des extraits d'un thème : le nom du thème en minuscules et au singulier.
     *
     * @param string $nomTheme Nom du thème
     * @return string
     *
     * @author Arthur
     */
    private function typeExtraitDuTheme(string $nomTheme): string
    {
        $type = mb_strtolower(trim($nomTheme));

        if (str_ends_with($type, 's')) {
            $type = mb_substr($type, 0, -1);
        }

        return $type;
    }
}
