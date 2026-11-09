<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Entities\QuizForm;
use App\Entities\Utilisateur;
use App\Models\QuestionModel;
use App\Models\QuizModel;
use App\Models\QuizQuestionModel;
use App\Models\ThemeModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Session\Session;

/**
 * Ce controller permet l'ajout et la modification des quiz
 * de l'Espace compréhension SIO (administration).
 *
 * @since 2.0.0
 * @package Cyber4all
 * @author Arthur
 */
class AdminQuizController extends BaseController
{
    protected ?Utilisateur $utilisateur = null;
    protected Session $session;
    protected ThemeModel $theme_model;
    protected QuizModel $quiz_model;
    protected QuestionModel $question_model;
    protected QuizQuestionModel $quiz_question_model;

    public function __construct()
    {
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
     * Suppresion d'un Quiz
     *
     * @param int|null $idQuiz Identifiant du quiz à supprimer
     * @return boolean Suppresion reussi, Vrai = Oui, Faux = Non
     */
    public function quizSupprimer(int $idQuiz)
    {
        $this->quiz_question_model->delete($idQuiz);
        $this->quiz_model->delete($idQuiz);

        return redirect()->route('quizList');
    }

    /**
     * Ajout ou modification d'un quiz,
     * avec le rattachement de ses questions.
     *
     * @param int|null $idQuizModif Identifiant du quiz à modifier, null pour un ajout.
     * @return mixed
     */
    public function quizAjouter(?int $idQuizModif = null)
    {
        $form = $this->session->get('quizForm');

        if ($form !== null && (int) ($form->modifier ?? 0) !== (int) ($idQuizModif ?? 0)) {
            $form = null;
        }

        if ($form === null) {
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
        }

        $errors = [];

        if ($this->request->is('post')) {
            $post = $this->request->getPost();
            unset($post['questions'], $post['modifier']);

            $form->fill($post);

            $action = $this->request->getPost('action');

            if ($action === 'ajouter_question') {
                $questions = $form->questions;
                $idQuestion = $this->request->getPost('id_question_ajout');

                if ($idQuestion && !in_array($idQuestion, $questions)) {
                    $questions[] = $idQuestion;
                }

                $form->questions = $questions;

            } elseif ($this->request->getPost('retirer_question')) {
                $idQuestion = $this->request->getPost('retirer_question');

                $form->questions = array_values(array_diff($form->questions, [$idQuestion]));

            } elseif ($action === 'enregistrer') {
                $formData = [
                    'id_theme'    => $this->request->getPost('id_theme') ?? '',
                    'titre'       => trim($this->request->getPost('titre') ?? ''),
                    'description' => trim($this->request->getPost('description') ?? ''),
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
                        'rules' => 'required|min_length[3]|max_length[150]',
                        'errors' => [
                            'required' => 'Veuillez renseigner un titre.',
                            'min_length' => 'Le titre doit contenir au moins 3 caractères.',
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
                    'actif' => [
                        'label' => 'Statut',
                        'rules' => 'required|in_list[0,1]',
                        'errors' => [
                            'required' => 'Veuillez définir le statut du quiz.',
                            'in_list' => 'Le statut du quiz est invalide.',
                        ],
                    ],
                ];

                if (!$this->validateData($formData, $rules)) {
                    $errors = $this->validator->getErrors();
                }

                if (empty($errors)) {
                    $estModification = !empty($form->modifier);

                    if ($estModification) {
                        $idQuiz = $this->quiz_model->updateQuiz($formData, (int) $form->modifier)
                            ? (int) $form->modifier
                            : false;
                    } else {
                        $idQuiz = $this->quiz_model->creerQuiz($formData);
                    }

                    if ($idQuiz !== false
                        && $this->quiz_question_model->updateLiaisonsQuiz($idQuiz, $form->questions ?? [])) {
                        $this->session->remove('quizForm');

                        return redirect()->to(base_url('floribondant/theme/quiz'))
                            ->with('success', $estModification
                                ? 'Quiz modifié avec succès !'
                                : 'Quiz créé avec succès !');
                    }

                    $errors['enregistrement'] = "Le quiz n'a pas pu être enregistré. Veuillez réessayer.";
                }
            }

            $this->session->set('quizForm', $form);
        }

        // Thème qui filtre les questions à ajouter : par défaut celui du quiz
        $themeList = $this->theme_model->getAllThemes();

        if (empty($form->id_theme_questions)) {
            $form->id_theme_questions = $form->id_theme ?: ($themeList[0]->id ?? null);
        }

        $idsAjoutes = array_map('strval', $form->questions ?? []);

        // Questions déjà dans le quiz (tous thèmes confondus)
        $questionsAjoutees = empty($idsAjoutes) ? [] : $this->question_model
            ->select('id, libelle, bonne_reponse')
            ->whereIn('id', $idsAjoutes)
            ->orderBy('libelle', 'ASC')
            ->findAll();

        // Questions du thème choisi qui ne sont pas encore dans le quiz
        $questionsDisponibles = array_values(array_filter(
            $this->question_model->getQuestionsFromTheme((int) $form->id_theme_questions),
            static fn ($question) => !in_array((string) $question['id'], $idsAjoutes, true)
        ));

        $data = [
            'form' => $form,
            'themeList' => $themeList,
            'questionsAjoutees' => $questionsAjoutees,
            'questionsDisponibles' => $questionsDisponibles,
            'errors' => $errors,
        ];

        return view('header')
            . view('notice')
            . view('admin/tracabilite/formQuiz', $data)
            . view('footer');
    }
}
