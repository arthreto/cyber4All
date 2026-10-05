<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\Utilisateur;
use App\Models\UtilisateurModel;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Model;
use CodeIgniter\Session\Session;
use Config\App;

/**
 * Ce controller correspond à la gestion de la page de connexion
 * @since 1.0.0
 * @package Cyber4all
 * @author Benjamin Lorieul
 */
class ConnexionController extends BaseController
{
    protected Session $session;

    protected array $rulesConnexion = [
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
    public function errorOnPage($page, $errorMessage) {
        log_message('warning', 'Erreurs de validation connexion d Id: ' . $errorMessage);
        return view('header', $data ?? [])
            . view('notice')
            . view($page, [
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

        if (!$this->validate($this->rulesConnexion)) {
            log_message('warning', 'Erreurs de validation connexion: ' . json_encode($this->validator->getErrors()));
            return view('header', $data ?? [])
                . view('notice')
                . view('connexion/connexion', [
                    'validation' => $this->validator
                ])
                . view('footer');
        }

        $this->utilisateurModel = new UtilisateurModel();

        $identifiant = $this->request->getPost('identifiant');
        $mdp = $this->request->getPost('mdp') ?? '';

        $utilisateur = $this->utilisateurModel->verifierUtilisateur($identifiant, $mdp);
        if(!isset($utilisateur)) {
            return $this->errorOnPage('connexion/connexion','Impossible de ce connecter au compte');
        }

        session()->set('utilisateur', $utilisateur);

        return view('header', $data ?? [])
            . view('notice')
            . view('compte/compte')
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
        if (!$this->validate($this->rulesConnexion)) {
            log_message('warning', 'Erreurs de validation inscription: ' . json_encode($this->validator->getErrors()));
            return view('header', $data ?? [])
                . view('notice')
                . view('connexion/inscription', [
                    'validation' => $this->validator
                ])
                . view('footer');
        }

        $this->utilisateurModel = new UtilisateurModel();

        $identifiant = $this->request->getPost('identifiant');
        $email = $this->request->getPost('email') ?? '';
        $mdp = $this->request->getPost('mdp') ?? '';

        if($this->utilisateurModel->adresseEmailExiste($email) || $this->utilisateurModel->getUtilisateurParPseudo($identifiant)) {
            return $this->errorOnPage('connexion/inscription','Utilisateur ou mail déjà utilisé');
        }

        $utilisateurEntity = new Utilisateur();
        $utilisateur = $utilisateurEntity::creer($identifiant, $mdp, 'contributeur', $this->request->getPost('email'));


        $idCreerUtilisateur = $this->utilisateurModel->insertUtilisateur($utilisateur);

        if (!$idCreerUtilisateur) {
            return $this->errorOnPage('connexion/inscription','Impossible de crée le compte, réessayez ultérieurement');
        }

        $dataUtilisateur = $this->utilisateurModel->getUtilisateurById($idCreerUtilisateur);

        session()->set('utilisateur', $dataUtilisateur);

        return view('header', $data ?? [])
            . view('notice')
            . view('compte/compte')
            . view('footer');
    }
}