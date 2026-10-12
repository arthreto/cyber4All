<?php

namespace App\Controllers\admin;

use App\Controllers\BaseController;
use App\Models\UtilisateurModel;
use App\Entities\Utilisateur;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Session\Session;

class AdminUtilisateurController extends BaseController
{
    protected UtilisateurModel $utilisateur_model;
    protected ?Utilisateur $utilisateur = null;
    protected Session $session;

    /**
     * Constructeur personnalisé du controller,
     * gère le droit d'accès et l'hydratation des variables locales
     */
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
     * Récupère tous les utilisateurs paginés et triés par l'ordre transmit.
     * @return string Affichage principal de la page d'admin des utilisateurs
     *
     * @since 1.0.0
     *
     */
    public function index()
    {
        $ordre = $this->request->getVar('ordre');
        $recherche = trim($this->request->getPost('recherche'));

        $this->utilisateurModel = new UtilisateurModel();
        $data = $this->utilisateurModel->getUtilisateursPaginatedOrderBy($ordre, $recherche);


        // TO_DO
        return view('header', $data)
            . view('notice')
            . view('admin/adminUtilisateurs')
            . view('footer');
    }
}