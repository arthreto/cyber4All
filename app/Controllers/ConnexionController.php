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
     * Permet d'affiché des erreurs sur la page
     * @since 1.0.0
     * @package Cyber4all
     *
     */
    public function errorOnPage($errorMessage) {
        log_message('warning', 'Erreurs de validation connexion d Id: ' . $errorMessage);
        return view('header', $data ?? [])
            . view('notice')
            . view('connexion/connexion', [
                'erreur' => $errorMessage,
            ])
            . view('footer');
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

        $rulesConnexion = [
            'identifiant' => [
                'rules' => 'required|min_length[3]|max_length[13]',
                'errors' => [
                    'required' => 'Login obligatoire',
                    'min_length' => '3 caractères minimum',
                    'max_length' => '13 caractères maximum'
                ]
            ],
            'mdp' => [
                'rules' => 'required|min_length[13]',
                'errors' => [
                    'required' => 'Mot de passe obligatoire',
                    'min_length' => '13 caractères minimum'
                ]
            ]
        ];

        if (!$this->validate($rulesConnexion)) {
            log_message('warning', 'Erreurs de validation connexion: ' . json_encode($this->validator->getErrors()));
            return view('header', $data ?? [])
                . view('notice')
                . view('connexion/connexion', [
                    'validation' => $this->validator
                ])
                . view('footer');
        }

        return $this->errorOnPage('Utilisateur ou mail déjà utilisé');

//        return view('header', $data ?? [])
//            . view('notice')
//            . view('connexion/connexion')
//            . view('footer');
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