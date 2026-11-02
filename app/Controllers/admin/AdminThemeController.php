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
use App\Models\QuizModel;
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

    public function __construct()
    {
        $this->utilisateur_model = new UtilisateurModel();
        $this->session = session();

        $this->theme_model = new ThemeModel();
        $this->quiz_model = new QuizModel();

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
        $data = ['onglet' => 'questions'];

        return view('header')
            . view('notice')
            . view('admin/tracabilite/onglets', $data)
            . view('admin/tracabilite/adminQuestions')
            . view('footer');
    }

    public function questionAjouter()
    {
        $form = $this->session->get('questionForm') ?? new QuestionForm();

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
            }

            if ($this->request->getPost('retirer_quiz')) {
                $quiz = $form->quiz;
                $idQuiz = $this->request->getPost('retirer_quiz');

                $quiz = array_values(array_diff($quiz, [$idQuiz]));

                $form->quiz = $quiz;
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
        ];

        return view('header')
            . view('notice')
            . view('admin/tracabilite/formQuestion', $data)
            . view('footer');
    }

    public function quizSection()
    {
        $data = ['onglet' => 'quiz'];

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