<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\Utilisateur;
use App\Models\UtilisateurModel;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Session\Session;

/**
 * Ce controller correspond à la gestion de la page de connexion
 * @since 1.0.0
 * @package Cyber4all
 * @author Benjamin Lorieul
 */
class ConnexionController extends BaseController
{
    protected Session $session;

    public function __construct()
    {
        $this->session = Services::session();
    }

    /**
     * Permet de connecter l'utilisateur à son compte s'il est identifié
     * @since 1.0.0
     * @package Cyber4all
     *
     */
    public function connexion()
    {
        // TO_DO
        return view('header', $data ?? [])
            . view('notice')
            . view('connexion/connexion')
            . view('footer');
    }

    /**
     * Permet de déconnecter l'utilisateur de la session
     * @return \CodeIgniter\HTTP\RedirectResponse
     * @since 1.0.0
     * @package Cyber4all
     * @author Benjamin Lorieul
     */
    public function deconnexion()
    {
        $session = Services::session();
        $session->destroy();
        return redirect()->to(base_url());
    }

    /**
     * Permet de connecter l'utilisateur à son compte s'il est identifié
     * @return string
     * @since 1.0.0
     * @package Cyber4all
     *
     */
    public function inscription(): string|RedirectResponse
    {
        // TO_DO

        return view('header', $data ?? [])
            . view('notice')
            . view('connexion/inscription')
            . view('footer');
    }
}