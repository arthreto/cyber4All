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

        $data = [
            'onglet'       => 'quiz',
            'themeSelect'  => $themeSelect,
            'quizList'     => array_slice($quizList, ($page - 1) * $parPage, $parPage),
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

            // Le thème doit exister (une valeur modifiée à la main est refusée)
            $idTheme = (int) $formData['id_theme'];
            if (! isset($errors['id_theme']) && $this->theme_model->find($idTheme) === null) {
                $errors['id_theme'] = "Le thème choisi n'existe pas.";
            }

            // Exactement le nombre de questions prévu
            $nbPrevu = (int) $formData['nb_questions'];
            $nbCochees = count($form->questions);
            if (! isset($errors['nb_questions']) && $nbCochees !== $nbPrevu) {
                $errors['questions'] = 'Ce quiz doit contenir exactement ' . $nbPrevu . ' questions. ';
            }

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

                $nbAutreTheme = 0;
                foreach ($themesQuestions as $question) {
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
                    && $this->quiz_question_model->updateLiaisonsQuiz($idQuiz, $form->questions)
                    && $db->transStatus()) {
                    $db->transCommit();

                    return redirect()->to(base_url('floribondant/theme/quiz'))
                        ->with('id_nouveau', $idQuiz) // affiché en premier dans la liste
                        ->with('success', $estModification
                            ? 'Quiz modifié avec succès !'
                            : 'Quiz créé avec succès !');
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
                ->select('id, libelle, bonne_reponse, id_theme, actif')
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

        $data = [
            'form' => $form,
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