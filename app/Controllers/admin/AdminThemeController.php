<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Entities\Image;
use App\Entities\MotCle;
use App\Entities\QuestionForm;
use App\Entities\Utilisateur;
use App\Models\CategorieModel;
use App\Models\ExtraitModel;
use App\Models\ImageModel;
use App\Models\MotCle\MotCleCategorieModel;
use App\Models\MotCle\MotCleModel;
use App\Models\MotCle\MotCleValidationModel;
use App\Models\QuestionModel;
use App\Models\QuizModel;
use App\Models\QuizQuestionModel;
use App\Models\SignalementModel;
use App\Models\ThemeModel;
use App\Models\LeconModel;
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

        if ($this->request->is('post') && $this->request->getPost('id_theme') != null) {
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

        // Redirection vers la liste : l'URL redevient celle de la liste (la position de
        // défilement y est remise) et un rafraîchissement ne renvoie pas la suppression
        return redirect()->to(base_url('floribondant/theme'));
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

                    // Détail : la question a-t-elle déjà un extrait ?
                    $form->detail_mode = !empty($question['id_extrait']) ? 'existant' : 'aucun';

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
        $infoDetail = null;
        $extraitList = $this->extrait_model->getAllExtraits();

        if ($this->request->is('post')) {
            $post = $this->request->getPost();
            unset($post['quiz']);

            $form->fill($post);

            $action = (string) $this->request->getPost('action');
            $actionsExtrait = ['modifier_extrait', 'enregistrer_extrait', 'supprimer_extrait', 'annuler_extrait'];

            // Toute autre action (changement d'option, d'extrait, de thème…) ferme
            // la modification / suppression d'extrait en cours
            if (!in_array($action, $actionsExtrait, true)) {
                $form->extrait_action = null;
            }

            if (in_array($action, $actionsExtrait, true)) {
                $infoDetail = $this->gererActionExtrait($action, $form, $errors);
                $extraitList = $this->extrait_model->getAllExtraits();
            } elseif ($action === 'ajouter_quiz') {
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
                    'detail_mode'     => $form->detail_mode ?? 'aucun',
                    'id_extrait'      => $form->id_extrait ?? '',
                    'extrait_titre'   => $form->extrait_titre ?? '',
                    'extrait_type'    => $form->extrait_type ?? '',
                    'extrait_contenu' => $form->extrait_contenu ?? '',
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
                    'detail_mode' => [
                        'label' => 'Détail',
                        'rules' => 'required|in_list[aucun,existant,nouveau]',
                        'errors' => [
                            'required' => 'Veuillez choisir une option pour le détail.',
                            'in_list' => 'L’option choisie pour le détail est invalide.',
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

                // Règles du détail selon l'option choisie
                if ($formData['detail_mode'] === 'existant') {
                    $rules['id_extrait'] = [
                        'label' => 'Extrait',
                        'rules' => 'required|in_list[' . implode(',', array_column($extraitList, 'id')) . ']',
                        'errors' => [
                            'required' => 'Veuillez choisir un extrait existant.',
                            'in_list' => 'L’extrait choisi n’existe pas.',
                        ],
                    ];
                } elseif ($formData['detail_mode'] === 'nouveau') {
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

                    // En cas d'erreur, on ne redirige pas : le formulaire est réaffiché
                    // plus bas avec $errors et la saisie conservée en session.
                    if (empty($errors)) {
                        $estModification = !empty($form->modifier);

                        // Extrait, question et liaisons sont enregistrés ensemble ou pas du tout
                        $db = \Config\Database::connect('tracabilite');
                        $db->transBegin();

                        // Détail : aucun, extrait existant, ou nouvel extrait créé maintenant
                        $idExtrait = null;

                        if ($formData['detail_mode'] === 'existant') {
                            $idExtrait = (int) $formData['id_extrait'];
                        } elseif ($formData['detail_mode'] === 'nouveau') {
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
                                $idQuestion = $this->question_model->updateQuestion($formData, (int) $form->modifier)
                                    ? (int) $form->modifier
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
            'extraitList' => $extraitList,
            'typesExtrait' => ExtraitModel::TYPES,
            'infoDetail' => $infoDetail,
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
     * @param string $action Action demandée (modifier_extrait, enregistrer_extrait, supprimer_extrait,
     *                       annuler_extrait). La suppression est immédiate, sans confirmation.
     * @param QuestionForm $form Formulaire en cours (mis à jour).
     * @param array $errors Erreurs à afficher (complété).
     * @return string|null Message de réussite à afficher, ou null.
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
                $form->detail_mode = 'aucun';
                return 'Extrait supprimé.';
        }

        return null;
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


        $data = ['onglet' => 'lecons',
            'themeList' => $this->theme_model->getAllThemes(),
            'leconList' => $this->lecon_model->getAllLecons(),
            'themeSelect' => -1];

        if ($this->request->is('post')) {
            $data['themeSelect'] = $this->request->getPost('id_theme');
        }

        if($data['themeSelect'] == -1){
            $data['leconList'] = $this->lecon_model->getAllLecons();
        } else {
            $data['leconList'] = $this->lecon_model->getLeconsByTheme($data['themeSelect']);
        }


        return view('header', $data)
            . view('notice')
            . view('admin/tracabilite/onglets', $data)
            . view('admin/tracabilite/adminLecons')
            . view('footer');
    }
}