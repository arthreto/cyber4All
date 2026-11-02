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
            log_message('debug', 'Theme selected: ' . $data['themeSelect']);
        } else {
            log_message('debug', 'Theme selected: ' . $data['themeSelect']);
        }

        return view('header')
            . view('notice')
            . view('admin/tracabilite/onglets', $data)
            . view('admin/tracabilite/adminQuestions', $data)
            . view('footer');
    }

    public function questionAjouter()
    {
        $form = $this->session->get('questionForm') ?? new QuestionForm();
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
                            'regex_match[/\.(png|jpg|jpeg|webp)$/i]',
                        ],
                        'errors' => [
                            'max_length' => 'Le chemin de l’image ne peut pas dépasser 255 caractères.',
                            'regex_match' => 'L’image doit être au format PNG, JPG, JPEG ou WEBP.',
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

                    if (empty($errors)) {
                        $newId = $this->question_model->creerQuestion($formData);
                        forEach ($form->quiz as $quizId) {
                            $this->quiz_question_model->creerLiaison($newId, $quizId);
                        }
                    }

                    $this->session->remove('questionForm');

                    return redirect()->to(base_url('floribondant/theme'))
                        ->with('success', 'Question créée avec succès !');
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

    public function quizSection()
    {
        $data = [
            'onglet' => 'quiz',
            'themeList' => $this->theme_model->getAllThemes(),
        ];

        return view('header', $data)
            . view('notice')
            . view('admin/tracabilite/onglets', $data)
            . view('admin/tracabilite/adminQuiz')
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