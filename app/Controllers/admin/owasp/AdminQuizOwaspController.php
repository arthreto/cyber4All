<?php

namespace App\Controllers\admin\owasp;

use App\Controllers\BaseController;
use App\Entities\Utilisateur;
use App\Models\Owasp\OwaspMenaceModel;
use App\Models\Owasp\OwaspPropositionModel;
use App\Models\Owasp\OwaspQuizModel;
use App\Models\Owasp\OwaspTopModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Session\Session;

class AdminQuizOwaspController extends BaseController
{
    protected OwaspMenaceModel $owaspMenace_model;
    protected OwaspPropositionModel $owaspProposition_model;
    protected OwaspQuizModel $owaspQuiz_model;
    protected OwaspTopModel $owaspTop_model;
    protected ?Utilisateur $utilisateur = null;
    protected Session $session;

    public function __construct()
    {
        $this->owaspMenace_model = new OwaspMenaceModel();
        $this->owaspProposition_model = new OwaspPropositionModel();
        $this->owaspQuiz_model = new OwaspQuizModel();
        $this->owaspTop_model = new OwaspTopModel();
        $this->session = session();

        // Vérification de la session et du rôle de l'utilisateur
        $utilisateur = $this->session->get('utilisateur');

        if ($utilisateur && $utilisateur->role === 'admin') {
            $this->utilisateur = $utilisateur;

        } else {
            throw PageNotFoundException::forPageNotFound('Accès refusé');
        }
    }

    public function index()
    {
        $recherche = trim((string) ($this->request->getGet('recherche') ?? $this->request->getPost('recherche') ?? ''));
        $ordre = (string) ($this->request->getGet('ordre') ?? 'id_asc');
        if (! in_array($ordre, ['id_asc', 'id_desc', 'question_asc', 'question_desc'], true)) {
            $ordre = 'id_asc';
        }

        $data = [
            'questions' => $this->owaspQuiz_model->questionsAdmin($recherche, $ordre),
            'pager' => $this->owaspQuiz_model->pager,
            'propositions' => $this->owaspProposition_model->orderBy('id_prop')->paginate(8, 'reponses'),
            'pagerReponses' => $this->owaspProposition_model->pager,
            'recherche' => $recherche,
            'ordre' => $ordre,
        ];

        return view('header') . view('admin/owasp/choixQuiz', $data) . view('footer');
    }

    public function question(int $id)
    {
        $question = $this->owaspQuiz_model->find($id);
        if ($question === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('header') . view('admin/owasp/modifQuiz', [
            'question' => $question,
            'reponses' => $this->owaspQuiz_model->bonnesReponses($id),
        ]) . view('footer');
    }

    public function reponse(int $id)
    {
        $proposition = $this->owaspQuiz_model->proposition($id);
        if ($proposition === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('header') . view('admin/owasp/modifQuiz', [
            'proposition' => $proposition,
        ]) . view('footer');
    }

    public function ajouter()
    {
        $erreurs = [];
        if ($this->request->is('post')) {
            $libelle = trim((string) $this->request->getPost('libelle_ques'));
            $menaceId = $this->request->getPost('menace_id');
            $propositionId = $this->request->getPost('proposition_id');
            if (! $this->validateData(['libelle_ques' => $libelle, 'menace_id' => $menaceId, 'proposition_id' => $propositionId], [
                'libelle_ques' => 'required|max_length[50]',
                'menace_id' => 'required|is_natural_no_zero',
                'proposition_id' => 'required|is_natural_no_zero',
            ])) {
                $erreurs = $this->validator->getErrors();
            } elseif ($this->owaspMenace_model->find((int) $menaceId) === null) {
                $erreurs[] = 'La menace sélectionnée est introuvable.';
            } elseif ($this->owaspQuiz_model->proposition((int) $propositionId) === null) {
                $erreurs[] = 'La réponse sélectionnée est introuvable.';
            } elseif (! $this->owaspQuiz_model->ajouterQuestion($libelle, (int) $menaceId, (int) $propositionId)) {
                $erreurs[] = 'La question n’a pas pu être enregistrée.';
            } else {
                return redirect()->to(site_url('floribondant/owasp/quiz'));
            }
        }

        return view('header') . view('admin/owasp/ajoutQuestion', [
            'menaces' => $this->owaspMenace_model->orderBy('nom_menace')->findAll(),
            'propositions' => $this->owaspQuiz_model->propositions(),
            'erreurs' => $erreurs,
        ]) . view('footer');
    }
}