<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\Utilisateur;
use App\Models\ThemeModel;
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
    protected ThemeModel $theme_model;
    protected UtilisateurModel $utilisateurModel;

    protected array $rulesConnexion = [
        'identifiant' => [
            'rules' => 'required',
            'errors' => [
                'required' => 'Saisissez votre identifiant',
            ]
        ],
        'mdp' => [
            'rules' => 'required',
            'errors' => [
                'required' => 'Saisissez votre mot de passe',
            ]
        ]
    ];


    protected array $rulesInscription = [
        'identifiant' => [
            'rules' => 'required|min_length[3]|max_length[13]',
            'errors' => [
                'required' => 'Saisissez un identifiant',
                'min_length' => 'L\'identifiant doit faire au moins 3 caractères',
                'max_length' => 'L\'identifiant doit faire au plus 13 caractères'
            ]
        ],
        'email' => [
            'rules' => 'permit_empty|valid_email|max_length[255]',
            'errors' => [
                'valid_email' => 'Adresse email invalide',
                'max_length' => 'Adresse email trop longue'
            ]
        ],
        'mdp' => [
            'rules' => 'required',
            'errors' => [
                'required' => 'Saisissez un mot de passe'
            ]
        ],
        'mdp_validation' => [
            'rules' => 'required|matches[mdp]',
            'errors' => [
                'required' => 'Confirmez le mot de passe',
                'matches' => 'Les mots de passe ne correspondent pas'
            ]
        ]
    ];

    public function __construct()
    {
        $this->session = Services::session();
        $this->theme_model = new ThemeModel();
    }



    /**
     * Permet d'affiché des erreurs sur la page
     * @since 1.0.0
     * @package Cyber4all
     *
     */
    public function errorOnPage($page, $errorMessage, array $valeurs = []) {
        log_message('warning', 'Erreurs de validation connexion d Id: ' . $errorMessage);

        $data = [
            'themeList' => $this->theme_model->getAllThemes(),
        ];

        return view('header', $data ?? [])
            . view('notice')
            . view($page, [
                'erreur' => $errorMessage,
            ] + $valeurs)
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
        // Premier affichage : formulaire vide, sans erreurs
        if (! $this->request->is('post')) {
            return $this->afficherFormulaire('connexion/connexion');
        }

        if (!$this->validate($this->rulesConnexion)) {
            log_message('warning', 'Erreurs de validation connexion: ' . json_encode($this->validator->getErrors()));

            return $this->afficherFormulaire('connexion/connexion', [
                'erreurs'     => $this->validator->getErrors(),
                'identifiant' => (string) $this->request->getPost('identifiant'),
            ]);
        }

        $this->utilisateurModel = new UtilisateurModel();

        $identifiant = $this->request->getPost('identifiant');
        $mdp = $this->request->getPost('mdp') ?? '';

        $utilisateur = $this->utilisateurModel->verifierUtilisateur($identifiant, $mdp);
        if(!isset($utilisateur)) {
            return $this->errorOnPage('connexion/connexion', 'Identifiant ou mot de passe incorrect', [
                'identifiant' => (string) $identifiant,
            ]);
        }

        session()->set('utilisateur', $utilisateur);

        // Admin : page Traçabilité (liste des questions)
        if ($utilisateur->role === 'admin') {
            return redirect()->to(base_url('floribondant/theme'));
        }

        $data = [
            'themeList' => $this->theme_model->getAllThemes(),
        ];

        return view('header', $data)
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
        // Premier affichage : formulaire vide, sans erreurs
        if (! $this->request->is('post')) {
            return $this->afficherFormulaire('connexion/inscription');
        }

        $identifiant = trim((string) $this->request->getPost('identifiant'));
        $mdp = (string) $this->request->getPost('mdp');

        // Email facultatif : champ vide => NULL en base (et non une chaîne vide)
        $email = trim((string) $this->request->getPost('email'));
        $email = $email === '' ? null : $email;

        $valeurs = [
            'pseudo' => $identifiant,
            'email'  => $email,
        ];

        $erreurs = $this->validate($this->rulesInscription) ? [] : $this->validator->getErrors();

        // Règles du mot de passe : un message précis pour la première règle non respectée
        if (! isset($erreurs['mdp']) && $mdp !== '') {
            $erreurMdp = $this->erreurMotDePasse($mdp, $identifiant);
            if ($erreurMdp !== null) {
                $erreurs['mdp'] = $erreurMdp;
            }
        }

        if ($erreurs !== []) {
            log_message('warning', 'Erreurs de validation inscription: ' . json_encode($erreurs));

            return $this->afficherFormulaire('connexion/inscription', ['erreurs' => $erreurs] + $valeurs);
        }

        $this->utilisateurModel = new UtilisateurModel();

        if ($this->utilisateurModel->getUtilisateurParPseudo($identifiant)) {
            return $this->afficherFormulaire('connexion/inscription', [
                'erreurs' => ['identifiant' => 'Cet identifiant est déjà utilisé'],
            ] + $valeurs);
        }

        if ($email !== null && $this->utilisateurModel->adresseEmailExiste($email)) {
            return $this->afficherFormulaire('connexion/inscription', [
                'erreurs' => ['email' => 'Cette adresse email est déjà utilisée'],
            ] + $valeurs);
        }

        $data = [
            'themeList' => $this->theme_model->getAllThemes(),
        ];
        $utilisateur = Utilisateur::creer($identifiant, $mdp, 'contributeur', $email);

        if ($utilisateur === null) {
            return $this->errorOnPage('connexion/inscription', 'Mot de passe invalide', $valeurs);
        }

        $idCreerUtilisateur = $this->utilisateurModel->insertUtilisateur($utilisateur);

        if (!$idCreerUtilisateur) {
            return $this->errorOnPage('connexion/inscription', 'Impossible de créer le compte, réessayez ultérieurement', $valeurs);
        }

        $dataUtilisateur = $this->utilisateurModel->getUtilisateurById($idCreerUtilisateur);

        session()->set('utilisateur', $dataUtilisateur);

        return view('header', $data ?? [])
            . view('notice')
            . view('compte/compte')
            . view('footer');
    }

    /**
     * Affiche la page de connexion ou d'inscription.
     *
     * @param string $page Vue à afficher
     * @param array $donnees Données de la vue (erreurs par champ, valeurs saisies)
     * @return string
     */
    private function afficherFormulaire(string $page, array $donnees = []): string
    {
        $data = [
            'themeList' => $this->theme_model->getAllThemes(),
        ];

        return view('header', $data)
            . view('notice')
            . view($page, $donnees)
            . view('footer');
    }

    /**
     * Vérifie les règles du mot de passe
     *
     * @param string $mdp Mot de passe saisi
     * @param string $pseudo Identifiant saisi
     * @return string|null Message de la première règle non respectée, null si tout est bon
     */
    private function erreurMotDePasse(string $mdp, string $pseudo): ?string
    {
        if (mb_strlen($mdp) < 13) {
            return 'Le mot de passe doit faire au moins 13 caractères (' . mb_strlen($mdp) . ' actuellement)';
        }
        if (strlen($mdp) > 255) {
            return 'Le mot de passe est trop long';
        }
        if (! preg_match('/[a-z]/', $mdp)) {
            return 'Le mot de passe doit contenir au moins une minuscule';
        }
        if (! preg_match('/[A-Z]/', $mdp)) {
            return 'Le mot de passe doit contenir au moins une majuscule';
        }
        if (! preg_match('/[0-9]/', $mdp)) {
            return 'Le mot de passe doit contenir au moins un chiffre';
        }
        if (! preg_match('/[.*?!%@=$£\[\]+]/u', $mdp)) {
            return 'Le mot de passe doit contenir au moins un symbole parmi . * ? ! % @ = $ £ [ ] +';
        }
        if ($pseudo !== '' && stripos($mdp, $pseudo) !== false) {
            return 'Le mot de passe ne doit pas contenir l\'identifiant';
        }

        return null;
    }
}
