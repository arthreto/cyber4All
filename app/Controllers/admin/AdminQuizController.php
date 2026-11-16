<?php

namespace App\Controllers\admin;

use App\Entities\QuizForm;
use App\Models\QuestionModel;
use App\Models\QuizModel;
use App\Models\QuizQuestionModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Ce controller gère les quiz de l'Espace compréhension SIO (administration) :
 * liste, ajout / modification avec leurs questions, et suppression.
 *
 * Le contrôle de l'administrateur et les méthodes des listes sont dans AdminController.
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
        // Constructeur du parent : session, ThemeModel et vérification de l'administrateur
        parent::__construct();

        $this->quiz_model = new QuizModel();
        $this->question_model = new QuestionModel();
        $this->quiz_question_model = new QuizQuestionModel();
    }

    /**
     * Liste des quiz : filtre par thème, tri et pagination.
     *
     * @return string
     * @author Arthur
     */
    public function index()
    {
        $base = 'floribondant/theme/quiz';

        // Colonnes du tableau
        $colonnes = [
            'titre'        => ['entete' => 'Titre', 'phrase' => 'par titre', 'asc' => 'de A à Z', 'desc' => 'de Z à A'],
            'theme_nom'    => ['entete' => 'Thème', 'phrase' => 'par thème', 'asc' => 'de A à Z', 'desc' => 'de Z à A'],
            'nb_questions' => ['entete' => 'Questions', 'phrase' => 'par nombre de questions', 'asc' => 'du plus petit au plus grand', 'desc' => 'du plus grand au plus petit'],
            'actif'        => ['entete' => 'Actif'], // non triable : les inactifs sont toujours à la fin
        ];

        // 1. Paramètres de l'URL
        $themeSelect = (int) ($this->request->getGet('id_theme') ?? -1);

        $tri = $this->request->getGet('tri');
        if (! in_array($tri, ['titre', 'theme_nom', 'nb_questions'], true)) {
            $tri = null;
        }

        $ordre = 'asc';
        if ($this->request->getGet('ordre') === 'desc') {
            $ordre = 'desc';
        }

        // 2. Quiz du thème choisi (-1 = tous les thèmes)
        $quizList = $this->quiz_model->getQuizAdmin($themeSelect);

        // Tri en PHP avec une fonction anonyme (voir l'explication dans index())
        if ($tri !== null) {
            $collator = new \Collator('fr_FR');

            usort($quizList, function (array $a, array $b) use ($tri, $ordre, $collator): int {
                if ($tri === 'nb_questions' || $tri === 'actif') {
                    // Nombres : comparés comme des nombres (en texte, "10" serait avant "9").
                    // <=> renvoie -1 si a < b, 0 si a = b, 1 si a > b
                    $resultat = (int) ($a[$tri] ?? 1) <=> (int) ($b[$tri] ?? 1);
                } else {
                    // Textes : ordre alphabétique français
                    $resultat = $collator->compare((string) $a[$tri], (string) $b[$tri]);
                }

                if ($ordre === 'desc') {
                    $resultat = -$resultat;
                }

                return $resultat;
            });
        }

        // Quiz qui vient d'être ajouté ou modifié (envoyé par quizAjouter), en premier ;
        // les quiz inactifs (supprimés) à la fin
        $idNouveau = (int) ($this->session->getFlashdata('id_nouveau') ?? 0);
        $quizList = $this->ordonnerListe($quizList, $idNouveau);

        // 3. Pagination : 20 quiz par page
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

        // 4. Liens pour la vue
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
     * « Suppression » d'un quiz : il n'est pas effacé de la base, il passe inactif (actif = 0).
     * Il n'est plus proposé sur la page des quiz et s'affiche à la fin de la liste.
     * Pour le réactiver : le modifier et cocher « Quiz actif ».
     *
     * @param int $idQuiz Identifiant du quiz à désactiver
     * @return \CodeIgniter\HTTP\RedirectResponse Retour à la liste des quiz, avec un message
     *
     * @author Arthur
     */
    public function quizSupprimer(int $idQuiz)
    {
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
     * Les questions sont des cases à cocher : tout est enregistré en une seule fois.
     *
     * Règles :
     * - un quiz n'a qu'un thème, et seulement des questions de ce thème ;
     * - nb_questions (entre 5 et 20) est le nombre exact de questions du quiz :
     *   on refuse l'enregistrement s'il n'y a pas exactement ce nombre de questions cochées.
     *
     * @param int|null $idQuizModif Identifiant du quiz à modifier, null pour un ajout.
     * @return mixed
     *
     * @author Arthur
     */
    public function quizAjouter(?int $idQuizModif = null)
    {
        $form = new QuizForm();

        // Modification : le formulaire est rempli avec le quiz et ses questions
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

            // Questions cochées : aucune case cochée = rien n'est envoyé, d'où le ?? [].
            // On garde les nombres uniquement, sans doublon (une question peut apparaître dans plusieurs thèmes).
            // On remplit un tableau simple, puis on le donne à $form : on ne peut pas faire
            // $form->questions[] = ... sur une entité (la modification serait perdue).
            $questionsCochees = [];
            foreach ((array) ($this->request->getPost('questions') ?? []) as $idQuestion) {
                $idQuestion = (string) $idQuestion;

                // ctype_digit() : uniquement des chiffres. in_array() : pas de doublon.
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

            // Exactement le nombre de questions prévu : ni plus, ni moins
            // (vérifié seulement si le nombre de questions est valide : sinon son erreur suffit)
            $nbPrevu = (int) $formData['nb_questions'];
            $nbCochees = count($form->questions);
            if (! isset($errors['nb_questions']) && $nbCochees !== $nbPrevu) {
                $errors['questions'] = 'Ce quiz doit contenir exactement ' . $nbPrevu . ' questions : '
                    . ($nbCochees < $nbPrevu
                        ? 'il en manque ' . ($nbPrevu - $nbCochees) . '.'
                        : 'il y en a ' . ($nbCochees - $nbPrevu) . ' de trop.')
                    . ' Vous en avez coché ' . $nbCochees . '.';
            }

            // Toutes les questions cochées doivent être du thème du quiz
            $idTheme = (int) $formData['id_theme'];
            if ($idTheme > 0 && $form->questions !== []) {
                $themesQuestions = $this->question_model
                    ->select('id, id_theme')
                    ->whereIn('id', $form->questions)
                    ->asArray()
                    ->findAll();

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

                if ($estModification) {
                    $idQuiz = $this->quiz_model->updateQuiz($formData, (int) $form->modifier)
                        ? (int) $form->modifier
                        : false;
                } else {
                    $idQuiz = $this->quiz_model->creerQuiz($formData);
                }

                if ($idQuiz !== false
                    && $this->quiz_question_model->updateLiaisonsQuiz($idQuiz, $form->questions)) {
                    return redirect()->to(base_url('floribondant/theme/quiz'))
                        ->with('id_nouveau', $idQuiz) // affiché en premier dans la liste
                        ->with('success', $estModification
                            ? 'Quiz modifié avec succès !'
                            : 'Quiz créé avec succès !');
                }

                $errors['enregistrement'] = "Le quiz n'a pas pu être enregistré. Veuillez réessayer.";
            }
        }

        $themeList = $this->theme_model->getAllThemes();

        // Questions cochées, en texte pour les comparer strictement aux identifiants
        $idsQuiz = [];
        foreach ($form->questions ?? [] as $idQuestion) {
            $idsQuiz[] = (string) $idQuestion;
        }

        // Questions du quiz (tous thèmes confondus).
        // whereIn() avec un tableau vide provoquerait une erreur SQL : on ne fait la requête que s'il y a des questions.
        $questionsQuiz = [];
        if ($idsQuiz !== []) {
            $questionsQuiz = $this->question_model
                ->select('id, libelle, bonne_reponse, id_theme')
                ->whereIn('id', $idsQuiz)
                ->orderBy('libelle', 'ASC')
                ->asArray()
                ->findAll();
        }

        // Questions du quiz qui ne sont pas du thème du quiz (quiz créé avant la règle « un seul thème ») :
        // la vue les signale, il faut les décocher pour pouvoir enregistrer
        $themeQuiz = (int) ($form->id_theme ?? 0);
        foreach ($questionsQuiz as $numero => $question) {
            $questionsQuiz[$numero]['autre_theme'] = $themeQuiz > 0 && (int) $question['id_theme'] !== $themeQuiz;
        }

        // Questions à ajouter : un bloc par thème, avec les questions de ce thème qui ne sont pas déjà dans le quiz.
        // La vue affiche seulement le bloc du thème coché (petit script sio-admin-theme-filtre.js).
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