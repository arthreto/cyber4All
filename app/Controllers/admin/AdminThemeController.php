<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Entities\Image;
use App\Entities\MotCle;
use App\Entities\QuestionForm;
use App\Entities\Utilisateur;
use App\Models\CategorieModel;
use App\Models\ImageModel;
use App\Models\MotCle\MotCleCategorieModel;
use App\Models\MotCle\MotCleModel;
use App\Models\MotCle\MotCleValidationModel;
use App\Models\QuestionModel;
use App\Models\QuizModel;
use App\Models\QuizQuestionModel;
use App\Models\SignalementModel;
use App\Models\ThemeModel;
use App\Models\UtilisateurModel;
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
class AdminThemeController extends BaseController
{

    protected UtilisateurModel $utilisateur_model;
    protected ?Utilisateur $utilisateur = null;
    protected Session $session;
    protected QuizModel $quiz_model;
    protected QuestionModel $question_model;
    protected QuizQuestionModel $quiz_question_model;

    public function __construct()
    {
        $this->utilisateur_model = new UtilisateurModel();
        $this->session = session();

        $this->theme_model = new ThemeModel();
        $this->quiz_model = new QuizModel();
        $this->question_model = new QuestionModel();
        $this->quiz_question_model = new QuizQuestionModel();

        // Vérification de la session et du rôle de l'utilisateur
        $utilisateur = $this->session->get('utilisateur');

        if ($utilisateur && $utilisateur->role === 'admin') {
            $this->utilisateur = $utilisateur;

        } else {
            throw PageNotFoundException::forPageNotFound('Accès refusé');
        }
    }

    /**
     * Récupère toutes les leçons, thèmes.
     * @return string Affichage principal de la page d'admin
     *
     * @since 1.0.0
     * @author Arthur
     */
    public function index()
    {
        $data = ['onglet' => 'questions',
            'themeList' => $this->theme_model->getAllThemes(),
            'themeSelect' => -1];

        if ($this->request->is('post')) {
            $data['themeSelect'] = $this->request->getPost('id_theme');
        }

        if($data['themeSelect'] == -1){
            $data['questionList'] = $this->question_model->getAllQuestions();
        } else {
            $data['questionList'] = $this->question_model->getQuestionsFromTheme($data['themeSelect']);
        }

        return view('header')
            . view('notice')
            . view('admin/tracabilite/onglets', $data)
            . view('admin/tracabilite/adminQuestions', $data)
            . view('footer');
    }

    /**
     * Suppresion d'une Question
     *
     * @param int|null $idQuestion Identifiant de la question à supprimer
     * @return boolean Suppresion reussi, Vrai = Oui, Faux = Non
     */
    public function questionSupprimer(int $idQuestion)
    {
        $this->quiz_question_model->delete($idQuestion);
        $this->question_model->delete($idQuestion);

        return $this->index();
    }


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
                    $form->fill($question);
                    $form->modifier = $idQuestionModif;

                    $form->quiz = $this->quiz_question_model->getIdsQuizByQuestion($idQuestionModif);

                    // Preselectionne le premier quiz
                    if (!empty($form->quiz)) {
                        $premierQuiz = $this->quiz_model->find($form->quiz[0]);

                        if ($premierQuiz !== null) {
                            $form->id_theme = $premierQuiz->id_theme;
                        }
                    }
                }
            }
        }

        $errors = [];

        if ($this->request->is('post')) {
            $post = $this->request->getPost();
            unset($post['quiz']);

            $form->fill($post);

            if ($this->request->getPost('action') === 'ajouter_quiz') {
                $quiz = $form->quiz;
                $idQuiz = $this->request->getPost('id_quiz_ajout');

                if ($idQuiz && !in_array($idQuiz, $quiz)) {
                    $quiz[] = $idQuiz;
                }

                $form->quiz = $quiz;
            } elseif ($this->request->getPost('retirer_quiz')) {
                $quiz = $form->quiz;
                $idQuiz = $this->request->getPost('retirer_quiz');

                $quiz = array_values(array_diff($quiz, [$idQuiz]));

                $form->quiz = $quiz;
            } else {
                $formData = [
                    'id_theme'      => $this->request->getPost('id_theme') ?? '',
                    'id_quiz_ajout' => $this->request->getPost('id_quiz_ajout') ?? '',
                    'libelle'        => $this->request->getPost('libelle') ?? '',
                    'image'         => $this->request->getPost('image') ?? '',
                    'detail'        => $this->request->getPost('detail') ?? '',
                    'bonne_reponse' => $this->request->getPost('bonne_reponse'),
                    'explication'   => $this->request->getPost('explication') ?? '',
                    'actif'         => $this->request->getPost('actif') ?? '0',
                    'modifier'      => $this->request->getPost('modifier') ?? '0',
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
                    'detail' => [
                        'label' => 'Détail',
                        'rules' => 'permit_empty|max_length[5000]',
                        'errors' => [
                            'max_length' => 'Le détail ne peut pas dépasser 5000 caractères.',
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


                if ($this->request->getPost('action') === 'enregistrer') {

                    if (!$this->validateData($formData, $rules)) {
                        $errors = $this->validator->getErrors();
                    }

                    // En cas d'erreur, on ne redirige pas : le formulaire est réaffiché
                    // plus bas avec $errors et la saisie conservée en session.
                    if (empty($errors)) {
                        $estModification = !empty($form->modifier);

                        if ($estModification) {
                            $idQuestion = $this->question_model->updateQuestion($formData, (int) $form->modifier)
                                ? (int) $form->modifier
                                : false;
                        } else {
                            $idQuestion = $this->question_model->creerQuestion($formData);
                        }

                        if ($idQuestion !== false
                            && $this->quiz_question_model->updateLiaisons($idQuestion, $form->quiz ?? [])) {
                            $this->session->remove('questionForm');

                            return redirect()->to(base_url('floribondant/theme'))
                                ->with('success', $estModification
                                    ? 'Question modifiée avec succès !'
                                    : 'Question créée avec succès !');
                        }

                        $errors['enregistrement'] = "La question n'a pas pu être enregistrée. Veuillez réessayer.";
                    }
                }
            }

            $this->session->set('questionForm', $form);
        }

        $quizList = $this->quiz_model->getAllQuizCategoryById($form->id_theme ?? 1);

        $quizAdded = [];

        foreach ($form->quiz ?? [] as $idQuiz) {
            $quiz = $this->quiz_model->find($idQuiz);

            if ($quiz) {
                $quizAdded[$idQuiz] = $quiz->titre;
            }
        }

        $data = [
            'form' => $form,
            'themeList' => $this->theme_model->getAllThemes(),
            'quizList' => $quizList,
            'quizAdded' => $quizAdded,
            'errors' => $errors,
        ];

        return view('header')
            . view('notice')
            . view('admin/tracabilite/formQuestion', $data)
            . view('footer');
    }

    /**
     * Onglet Quiz : liste des quiz, filtrable par thème.
     * Même fonctionnement que index() pour les questions.
     */
    public function quizSection()
    {
        $data = [
            'onglet' => 'quiz',
            'themeList' => $this->theme_model->getAllThemes(),
            'themeSelect' => -1,
        ];

        if ($this->request->is('post')) {
            $data['themeSelect'] = (int) $this->request->getPost('id_theme');
        }

        $data['quizList'] = $this->quiz_model->getQuizAdmin($data['themeSelect']);

        return view('header')
            . view('notice')
            . view('admin/tracabilite/onglets', $data)
            . view('admin/tracabilite/adminQuiz', $data)
            . view('footer');
    }

    public function leconsSection()
    {
        $data = ['onglet' => 'lecons'];

        return view('header', $data)
            . view('notice')
            . view('admin/tracabilite/onglets', $data)
            . view('admin/tracabilite/adminLecons')
            . view('footer');
    }
}