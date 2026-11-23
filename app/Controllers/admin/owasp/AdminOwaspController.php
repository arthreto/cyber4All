<?php

namespace App\Controllers\admin\owasp;

use App\Controllers\BaseController;
use App\Entities\Utilisateur;
use App\Models\Owasp\OwaspMenaceModel;
use App\Models\Owasp\OwaspQuizModel;
use App\Models\Owasp\OwaspTopModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Session\Session;

class AdminOwaspController extends BaseController
{
    protected OwaspMenaceModel $owaspMenace_model;
    protected OwaspQuizModel $owaspQuiz_model;
    protected OwaspTopModel $owaspTop_model;
    protected ?Utilisateur $utilisateur = null;
    protected Session $session;

    public function __construct()
    {
        $this->owaspMenace_model = new OwaspMenaceModel();
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
        $data['api'] = $this->owaspMenace_model->getMenaces('API');
        $data['web'] = $this->owaspMenace_model->getMenaces('WEB');
        return view('header', $data) .
            view('admin/owasp/adminOwasp', $data) .
            view('footer', $data);
    }
}