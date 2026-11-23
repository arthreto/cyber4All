<?php

namespace App\Controllers\admin;

use App\Entities\QuizForm;
use App\Models\QuestionModel;
use App\Models\QuizModel;
use App\Models\QuizQuestionModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Ce controller gère les quiz de l'Espace compréhension SIO (administration)
 *
 * @since 2.0.0
 * @package Cyber4all
 * @author Arthur
 */
class AdminQuizController extends AdminController
{
    protected QuizModel $quiz_model;
    protected QuestionModel $question_model;
    protected QuizQuestionModel $quiz_question_model;

    public function __construct()
    {
        parent::__construct();

        $this->quiz_model = new QuizModel();
        $this->question_model = new QuestionModel();
        $this->quiz_question_model = new QuizQuestionModel();
    }

    /**
     * Liste des quiz filtre par thème, tri et pagination.
     *
     * @return string
     * @author Arthur
     */
    public function index()
    {
        $base = 'floribondant/theme/quiz';

        $colonnes = [
            'titre'        => ['entete' => 'Titre', 'phrase' => 'par titre', 'asc' => 'de A à Z', 'desc' => 'de Z à A'],
            'theme_nom'    => ['entete' => 'Thème', 'phrase' => 'par thème', 'asc' => 'de A à Z', 'desc' => 'de Z à A'],
            'nb_questions' => ['entete' => 'Questions', 'phrase' => 'par nombre de questions', 'asc' => 'du plus petit au plus grand', 'desc' => 'du plus grand au plus petit'],
            'actif'        => ['entete' => 'Actif'], // non triable : les inactifs sont toujours à la fin
        ];

        $themeSelect = (int) ($this->request->getGet('id_theme') ?? -1);

        $tri = $this->request->getGet('tri');
        if (! in_array($tri, ['titre', 'theme_nom', 'nb_questions'], true)) {
            $tri = null;
        }

        $ordre = 'asc';
        if ($this->request->getGet('ordre') === 'desc') {
            $ordre = 'desc';
        }

        // 2. Quiz du thème choisi, -1 pour tous les thèmes
        $quizList = $this->quiz_model->getQuizAdmin($themeSelect);

        if ($tri !== null) {
            // Permet de trié l'alphabet et de comparé / trié des chaines
            $collator = new \Collator('fr_FR');

            usort($quizList, function ($a, $b) use ($tri, $ordre, $collator) {

                if ($tri == 'nb_questions' || $tri == 'actif') {
                    $valeurA = (int) $a[$tri];
                    $valeurB = (int) $b[$tri];

                    if ($valeurA < $valeurB) {
                        $resultat = -1;
                    } elseif ($valeurA > $valeurB) {
                        $resultat = 1;
                    } else {
                        $resultat = 0;
                    }

                } else {
                    $resultat = $collator->compare($a[$tri], $b[$tri]);
                }

                if ($ordre == 'desc') {
                    $resultat = -$resultat;
                }

                return $resultat;
            });
        }

        // Quiz qui vient d'être ajouté ou modifié, en premier ; les quiz inactifs à la fin
        $idNouveau = (int) ($this->session->getFlashdata('id_nouveau') ?? 0);
        $quizList = $this->ordonnerListe($quizList, $idNouveau);
        $parPage = 20;
        $totalQuiz = count($quizList);
        $nbPages = max(1, (int) ceil($totalQuiz / $parPage));

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
        $parametresTheme = [];
        if ($themeSelect > 0) {
            $parametresTheme = ['id_theme' => $themeSelect];
        }

        $quizPage = array_slice($quizList, ($page - 1) * $parPage, $parPage);

        // Quiz inactifs de la page : raison qui empêche la réactivation (null = réactivable)
        $raisonsReactivation = [];
        foreach ($quizPage as $quiz) {
            if ((int) ($quiz['actif'] ?? 1) !== 1) {
                $raisonsReactivation[(int) $quiz['id']] = $this->raisonNonReactivable((int) $quiz['id']);
            }
        }

        $data = [
            'onglet'       => 'quiz',
            'themeSelect'  => $themeSelect,
            'quizList'     => $quizPage,
            'raisonsReactivation' => $raisonsReactivation,
            'idNouveau'    => $idNouveau,
            'totalQuiz'    => $totalQuiz,
            'filtreThemes' => $this->filtreThemes($base, $this->theme_model->getAllThemes(), $themeSelect, $parametresTri),
            'colonnes'     => $this->colonnesTri($base, $colonnes, $tri, $ordre, $parametresTheme),
            'messageTri'   => $this->messageTri('Quiz triés', $colonnes, $tri, $ordre),
            'urlSansTri'   => $this->adresseListe($base, $parametresTheme),
            'pages'        => $this->pagesListe($base, $nbPages, $page, $parametresTheme + $parametresTri),
        ];

        return view('header')
            . view('notice')
            . view('admin/tracabilite/onglets', $data)
            . view('admin/tracabilite/adminQuiz', $data)
            . view('footer');
    }

    /**
     * Suppression d'un quiz.

     * @param int $idQuiz Identifiant du quiz à supprimer
     * @return \CodeIgniter\HTTP\RedirectResponse Retour à la liste des quiz, avec un message
     *
     * @author Arthur
     */
    public function quizSupprimer(int $idQuiz)
    {
        // Suppression uniquement par le formulaire (POST + jeton CSRF) : un simple lien ne doit rien supprimer
        if (! $this->request->is('post')) {
            return redirect()->to(base_url('floribondant/theme/quiz'));
        }

        $quiz = $this->quiz_model->find($idQuiz);

        if ($quiz === null) {
            return redirect()->to(base_url('floribondant/theme/quiz'))
                ->with('error', 'Ce quiz n\'existe pas.');
        }

        // Les questions du quiz sont gardées : elles servent si le quiz est réactivé
        if (! $this->quiz_model->updateQuiz(['actif' => 0], $idQuiz)) {
            return redirect()->to(base_url('floribondant/theme/quiz'))
                ->with('error', "Le quiz n'a pas pu être supprimé. Veuillez réessayer.");
        }

        return redirect()->to(base_url('floribondant/theme/quiz'))
            ->with('success', 'Quiz « ' . $quiz->titre . ' » supprimé : il est désactivé et rangé à la fin de la liste.');
    }

    /**
     * Réactivation d'un quiz supprimé (désactivé), seulement s'il est valide et complet.
     *
     * @param int $idQuiz Identifiant du quiz à réactiver
     * @return \CodeIgniter\HTTP\RedirectResponse Retour à la liste des quiz, avec un message
     *
     * @author Raphael Bierlaire
     */
    public function quizReactiver(int $idQuiz)
    {
        $raison = $this->raisonNonReactivable($idQuiz);

        if ($raison !== null) {
            return redirect()->to(base_url('floribondant/theme/quiz'))
                ->with('error', 'Le quiz ne peut pas être réactivé : ' . $raison);
        }

        if (! $this->quiz_model->updateQuiz(['actif' => 1], $idQuiz)) {
            return redirect()->to(base_url('floribondant/theme/quiz'))
                ->with('error', "Le quiz n'a pas pu être réactivé. Veuillez réessayer.");
        }

        return redirect()->to(base_url('floribondant/theme/quiz'))
            ->with('id_nouveau', $idQuiz) // affiché en premier dans la liste
            ->with('success', 'Quiz réactivé : il est de nouveau proposé sur la page des quiz.');
    }

    /**
     * Vérifie qu'un quiz inactif peut être réactivé : mêmes règles que le formulaire,
     * et exactement son nombre de questions prévu en questions actives, toutes de son thème.
     *
     * @param int  $idQuiz            Identifiant du quiz
     * @param bool $ignorerInactives  true : les questions inactives comptent comme les autres
     *                                (formulaire du quiz, où elles sont déjà signalées par un badge « Inactive »)
     * @return string|null Raison du refus, ou null si le quiz peut être réactivé.
     *
     * @author Raphael Bierlaire
     */
    private function raisonNonReactivable(int $idQuiz, bool $ignorerInactives = false): ?string
    {
        $quiz = $this->quiz_model->find($idQuiz);

        if ($quiz === null) {
            return "ce quiz n'existe pas.";
        }
        if ((int) ($quiz->actif ?? 1) === 1) {
            return 'il est déjà actif.';
        }

        $idTheme = (int) $quiz->id_theme;
        if ($this->theme_model->find($idTheme) === null) {
            return "son thème n'existe plus : modifiez-le pour en choisir un autre.";
        }

        $longueurTitre = mb_strlen(trim((string) $quiz->titre));
        if ($longueurTitre < 14 || $longueurTitre > 150) {
            return 'son titre doit contenir entre 14 et 150 caractères : modifiez-le.';
        }

        $nbPrevu = (int) $quiz->nb_questions;
        if ($nbPrevu < 5 || $nbPrevu > 20) {
            return 'son nombre de questions doit être entre 5 et 20 : modifiez-le.';
        }

        $idsQuestions = $this->quiz_question_model->getIdsQuestionsByQuiz($idQuiz);
        $questions = [];
        if ($idsQuestions !== []) {
            $questions = $this->question_model
                ->select('id, id_theme, actif')
                ->whereIn('id', $idsQuestions)
                ->asArray()
                ->findAll();
        }

        if (count($questions) > $nbPrevu) {
            return 'il contient ' . count($questions) . ' questions pour ' . $nbPrevu . ' prévues : modifiez-le.';
        }

        foreach ($questions as $question) {
            if ((int) $question['id_theme'] !== $idTheme) {
                return "il contient une question d'un autre thème : modifiez-le.";
            }
        }

        if ($ignorerInactives) {
            if (count($questions) < $nbPrevu) {
                return 'il n\'a que ' . count($questions) . ' question(s) sur ' . $nbPrevu . ' : complétez-le.';
            }

            return null;
        }

        $nbActives = count(array_filter($questions, fn (array $q): bool => (int) $q['actif'] === 1));
        if ($nbActives < $nbPrevu) {
            return 'il n\'a que ' . $nbActives . ' question(s) active(s) sur ' . $nbPrevu . ' : complétez-le.';
        }

        return null;
    }

    /**
     * Ajout ou modification d'un quiz, avec ses questions.
     * @param int|null $idQuizModif Identifiant du quiz à modifier, null pour un ajout.
     * @return mixed
     *
     * @author Arthur
     */
    public function quizAjouter(?int $idQuizModif = null)
    {
        $form = new QuizForm();

        if ($idQuizModif !== null) {
            $quiz = $this->quiz_model->find($idQuizModif);

            if ($quiz === null) {
                throw PageNotFoundException::forPageNotFound('Quiz introuvable');
            }

            $form->fill($quiz->toArray());
            $form->modifier = $idQuizModif;
            $form->questions = $this->quiz_question_model->getIdsQuestionsByQuiz($idQuizModif);
        }

        $errors = [];

        if ($this->request->is('post') && $this->request->getPost('action') === 'enregistrer') {
            $post = $this->request->getPost();
            unset($post['questions'], $post['modifier']);
            $form->fill($post);


            $questionsCochees = [];
            foreach ((array) ($this->request->getPost('questions') ?? []) as $idQuestion) {
                $idQuestion = (string) $idQuestion;

                // ctype_digit() : uniquement des chiffres
                if (ctype_digit($idQuestion) && ! in_array($idQuestion, $questionsCochees, true)) {
                    $questionsCochees[] = $idQuestion;
                }
            }
            $form->questions = $questionsCochees;

            $formData = [
                'id_theme'    => $this->request->getPost('id_theme') ?? '',
                'titre'       => trim($this->request->getPost('titre') ?? ''),
                'description' => trim($this->request->getPost('description') ?? ''),
                'nb_questions' => $this->request->getPost('nb_questions') ?? '',
                'actif'       => $this->request->getPost('actif') ?? '0',
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
                'titre' => [
                    'label' => 'Titre',
                    'rules' => 'required|min_length[14]|max_length[150]',
                    'errors' => [
                        'required' => 'Veuillez renseigner un titre.',
                        'min_length' => 'Le titre doit contenir au moins 14 caractères.',
                        'max_length' => 'Le titre ne peut pas dépasser 150 caractères.',
                    ],
                ],
                'description' => [
                    'label' => 'Description',
                    'rules' => 'permit_empty|max_length[1000]',
                    'errors' => [
                        'max_length' => 'La description ne peut pas dépasser 1000 caractères.',
                    ],
                ],
                'nb_questions' => [
                    'label' => 'Nombre de questions',
                    'rules' => 'required|is_natural_no_zero|greater_than_equal_to[5]|less_than_equal_to[20]',
                    'errors' => [
                        'required' => 'Veuillez indiquer le nombre de questions du quiz.',
                        'is_natural_no_zero' => 'Le nombre de questions doit être un nombre entier.',
                        'greater_than_equal_to' => 'Le nombre de questions doit être d\'au moins 5.',
                        'less_than_equal_to' => 'Le nombre de questions ne peut pas dépasser 20.',
                    ],
                ],
                'actif' => [
                    'label' => 'Statut',
                    'rules' => 'required|in_list[0,1]',
                    'errors' => [
                        'required' => 'Veuillez définir le statut du quiz.',
                        'in_list' => 'Le statut du quiz est invalide.',
                    ],
                ],
            ];

            if (! $this->validateData($formData, $rules)) {
                $errors = $this->validator->getErrors();
            }

<<<<<<< Updated upstream
            // Le thème doit exister (une valeur modifiée à la main est refusée)
            $idTheme = (int) $formData['id_theme'];
            if (! isset($errors['id_theme']) && $this->theme_model->find($idTheme) === null) {
                $errors['id_theme'] = "Le thème choisi n'existe pas.";
            }

            // Exactement le nombre de questions prévu
=======
            // Thème et statut des questions cochées
            $questionsCocheesInfos = [];
            if ($form->questions !== []) {
                $questionsCocheesInfos = $this->question_model
                    ->select('id, id_theme, actif')
                    ->whereIn('id', $form->questions)
                    ->asArray()
                    ->findAll();
            }

            // Nombre de questions prévu : jamais dépassé.
            // En dessous (même 0 question), le quiz est enregistré mais forcé inactif.
            // Seules les questions actives comptent pour qu'il soit complet : ce sont les seules jouées.
>>>>>>> Stashed changes
            $nbPrevu = (int) $formData['nb_questions'];
            $nbCochees = count($form->questions);
            $nbActives = count(array_filter($questionsCocheesInfos, fn (array $q): bool => (int) $q['actif'] === 1));
            $quizIncomplet = false;
            if (! isset($errors['nb_questions'])) {
                if ($nbCochees > $nbPrevu) {
                    $errors['questions'] = 'Ce quiz ne peut pas contenir plus de ' . $nbPrevu . ' questions : décochez-en ' . ($nbCochees - $nbPrevu) . '.';
                } elseif ($nbActives < $nbPrevu) {
                    $quizIncomplet = true;
                    $formData['actif'] = '0';
                }
            }

<<<<<<< Updated upstream
            // Toutes les questions cochées doivent exister, être actives et être du thème du quiz
            if ($idTheme > 0 && $form->questions !== []) {
                $themesQuestions = $this->question_model
                    ->select('id, id_theme')
                    ->whereIn('id', $form->questions)
                    ->where('actif', 1)
                    ->asArray()
                    ->findAll();

                $nbIntrouvables = count($form->questions) - count($themesQuestions);
                if ($nbIntrouvables > 0) {
                    $errors['questions_introuvables'] = $nbIntrouvables . " question(s) cochée(s) n'existent pas ou ont été supprimées : décochez-les.";
                }

=======
            // Toutes les questions cochées doivent être du thème du quiz
            $idTheme = (int) $formData['id_theme'];
            if ($idTheme > 0) {
>>>>>>> Stashed changes
                $nbAutreTheme = 0;
                foreach ($questionsCocheesInfos as $question) {
                    if ((int) $question['id_theme'] !== $idTheme) {
                        $nbAutreTheme++;
                    }
                }

                if ($nbAutreTheme > 0) {
                    $errors['questions_theme'] = $nbAutreTheme . ' question(s) cochée(s) ne sont pas du thème du quiz : décochez-les.';
                }
            }

            if (empty($errors)) {
                $estModification = ! empty($form->modifier);

                // Quiz et liaisons enregistrés ensemble : en cas d'erreur, rien n'est gardé (pas de quiz à moitié créé)
                $db = \Config\Database::connect();
                $db->transBegin();

                if ($estModification) {
                    $idQuiz = $this->quiz_model->updateQuiz($formData, (int) $form->modifier)
                        ? (int) $form->modifier
                        : false;
                } else {
                    $idQuiz = $this->quiz_model->creerQuiz($formData);
                }

                if ($idQuiz !== false
<<<<<<< Updated upstream
                    && $this->quiz_question_model->updateLiaisonsQuiz($idQuiz, $form->questions)
                    && $db->transStatus()) {
                    $db->transCommit();
=======
                    && $this->quiz_question_model->updateLiaisonsQuiz($idQuiz, $form->questions)) {
                    $message = $estModification ? 'Quiz modifié avec succès !' : 'Quiz créé avec succès !';

                    if ($quizIncomplet) {
                        $message .= ' Il contient ' . $nbActives . ' question(s) active(s) sur ' . $nbPrevu
                            . ' : il reste inactif tant qu\'il n\'est pas complet.';
                    }
>>>>>>> Stashed changes

                    return redirect()->to(base_url('floribondant/theme/quiz'))
                        ->with('id_nouveau', $idQuiz) // affiché en premier dans la liste
                        ->with('success', $message);
                }

                $db->transRollback();
                $errors['enregistrement'] = "Le quiz n'a pas pu être enregistré. Veuillez réessayer.";
            }
        }

        $themeList = $this->theme_model->getAllThemes();

        $idsQuiz = [];
        foreach ($form->questions ?? [] as $idQuestion) {
            $idsQuiz[] = (string) $idQuestion;
        }


        $questionsQuiz = [];
        if ($idsQuiz !== []) {
            $questionsQuiz = $this->question_model
<<<<<<< Updated upstream
                ->select('id, libelle, bonne_reponse, id_theme, actif')
=======
                ->select('id, libelle, bonne_reponse, id_theme, actif') // actif : badge « Inactive » dans le formulaire
>>>>>>> Stashed changes
                ->whereIn('id', $idsQuiz)
                ->orderBy('libelle', 'ASC')
                ->asArray()
                ->findAll();
        }

        $themeQuiz = (int) ($form->id_theme ?? 0);
        foreach ($questionsQuiz as $numero => $question) {
            $questionsQuiz[$numero]['autre_theme'] = $themeQuiz > 0 && (int) $question['id_theme'] !== $themeQuiz;
        }


        $blocsThemes = [];
        foreach ($themeList as $theme) {
            $questionsAjout = [];
            foreach ($this->question_model->getQuestionsAdminParTheme((int) $theme->id) as $question) {
                if (! in_array((string) $question['id'], $idsQuiz, true)) {
                    $questionsAjout[] = $question;
                }
            }

            $blocsThemes[] = [
                'id'        => (int) $theme->id,
                'nom'       => $theme->nom,
                'choisi'    => (int) $theme->id === $themeQuiz,
                'questions' => $questionsAjout,
            ];
        }

        // Quiz inactif en base : ce qui l'empêche d'être activé (hors questions inactives,
        // signalées par un badge « Inactive » dans la liste de ses questions)
        $raisonActivation = null;
        if ($idQuizModif !== null && (int) ($quiz->actif ?? 1) !== 1) {
            $raisonActivation = $this->raisonNonReactivable($idQuizModif, true);
        }

        $data = [
            'form' => $form,
            'raisonActivation' => $raisonActivation,
            'themeList' => $themeList,
            'questionsQuiz' => $questionsQuiz,
            'blocsThemes' => $blocsThemes,
            'errors' => $errors,
        ];

        return view('header')
            . view('notice')
            . view('admin/tracabilite/formQuiz', $data)
            . view('footer');
    }
}