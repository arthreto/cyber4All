<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Entities\Image;
use App\Entities\MotCle;
use App\Entities\Utilisateur;
use App\Models\CategorieModel;
use App\Models\ImageModel;
use App\Models\MotCle\MotCleCategorieModel;
use App\Models\MotCle\MotCleModel;
use App\Models\MotCle\MotCleValidationModel;
use App\Models\SignalementModel;
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
    public function __construct()
    {
        $this->utilisateur_model = new UtilisateurModel();
        $this->session = session();

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

        return view('header')
            . view('notice')
            . view('admin/tracabilite/formQuestion')
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