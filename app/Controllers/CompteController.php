<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MotCle\MotCleValidationModel;
use App\Models\UtilisateurModel;
use App\Models\ThemeModel;
use CodeIgniter\Config\Services;
use App\Entities\Utilisateur;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Session\Session;

/**
 * Ce controller permet la gestion de la page d'informations du compte utilisateur
 * @since 1.0.0
 * @package Cyber4all
 * @author Benjamin Lorieul
 */
class CompteController extends BaseController
{
    protected UtilisateurModel $utilisateur_model;
    protected ?Utilisateur $utilisateur = null;
    protected MotCleValidationModel $motCleValidation_model;
    protected ThemeModel $theme_model;
    protected Session $session;

    public function __construct()
    {
        $this->utilisateur_model = new UtilisateurModel();
        $this->session = Services::session();
        // Vérification de la session et du rôle de l'utilisateur
        $utilisateur = $this->session->get('utilisateur');
        $this->motCleValidation_model = new MotCleValidationModel();
        $this->theme_model = new ThemeModel();

        if ($utilisateur && in_array($utilisateur->role, ['contributeur', 'admin'], true)) {
            $this->utilisateur = $utilisateur;

        } else {
            throw PageNotFoundException::forPageNotFound('Accès refusé');
        }
    }

    /**
     * @return string|RedirectResponse
     */
    public function index(): string|RedirectResponse
    {
        // Formulaire « Modifier mes informations » : traitement puis redirection (Post/Redirect/Get),
        // le message est affiché sur la page rechargée
        if ($this->request->is('post')) {
            $this->modifierInformations();

            return redirect()->to(base_url('compte'));
        }

        $data = [
            'motsContributeur' => $this->motCleValidation_model
                ->getMotCleValidationContributeurPaginate($this->utilisateur->id),
            'motsContributeurAttente' => $this->motCleValidation_model
                ->getMotCleValidationContributeurAttentePaginate($this->utilisateur->id),
            'pager' => $this->motCleValidation_model->pager,
            'themeList' => $this->theme_model->getAllThemes(),
        ];
//        TODO créer une vue pour afficher les mots du contributeur
        return view('header', $data)
            . view('notice')
            . view('compte/compte')
            . view('compte/demandeMots')
            . view('footer');
    }


    /**
     * Modifie le pseudo et l'email de l'utilisateur connecté.
     * À la première erreur, rien n'est enregistré et un message d'erreur est préparé.
     *
     * @return void Le résultat est un message flash « notice »
     */
    private function modifierInformations(): void
    {
        $utilisateur = $this->session->get('utilisateur');

        $nouvPseudo = trim((string) $this->request->getPost('nouvPseudo'));
        $nouvEmail = trim((string) $this->request->getPost('nouvEmail'));

        // Email facultatif : champ vidé ou case « Supprimer mon email » => NULL en base
        if ($nouvEmail === '' || $this->request->getPost('suppEmail') === 'supprimer') {
            $nouvEmail = null;
        }

        // Les messages sont affichés tels quels par notice.php : les valeurs saisies y sont échappées avec esc()
        $erreur = null;
        $longueurPseudo = mb_strlen($nouvPseudo);

        if ($longueurPseudo < 3 || $longueurPseudo > 13) {
            $erreur = 'Le pseudo doit contenir entre 3 et 13 caractères.';
        } elseif ($nouvEmail !== null && (filter_var($nouvEmail, FILTER_VALIDATE_EMAIL) === false || mb_strlen($nouvEmail) > 255)) {
            $erreur = "L'adresse email « " . esc($nouvEmail) . " » n'est pas valide.";
        } elseif ($nouvPseudo !== $utilisateur->pseudo && $this->utilisateur_model->pseudoExiste($nouvPseudo)) {
            $erreur = 'Le pseudo « ' . esc($nouvPseudo) . ' » est déjà utilisé. Veuillez en choisir un autre.';
        } elseif ($nouvEmail !== null && $nouvEmail !== $utilisateur->email && $this->utilisateur_model->adresseEmailExiste($nouvEmail)) {
            $erreur = "L'adresse email « " . esc($nouvEmail) . ' » est déjà utilisée.';
        }

        if ($erreur !== null) {
            $this->session->setFlashdata('notice', [
                'type'    => 'error',
                'titre'   => 'Échec de la modification de compte',
                'details' => $erreur,
            ]);
            return;
        }

        if ($nouvPseudo === $utilisateur->pseudo && $nouvEmail === ($utilisateur->email ?: null)) {
            $this->session->setFlashdata('notice', [
                'type'    => 'info',
                'titre'   => 'Modification de compte',
                'details' => "Aucune modification : le pseudo et l'email sont les mêmes.",
            ]);
            return;
        }

        if (! $this->utilisateur_model->updateUtilisateurByID(['pseudo' => $nouvPseudo, 'email' => $nouvEmail], $utilisateur)) {
            $this->session->setFlashdata('notice', [
                'type'    => 'error',
                'titre'   => 'Échec de la modification de compte',
                'details' => "Les modifications n'ont pas pu être enregistrées. Veuillez réessayer.",
            ]);
            return;
        }

        $utilisateurAJour = $this->utilisateur_model->getUtilisateurById($utilisateur->id);
        if ($utilisateurAJour !== null) {
            $this->session->set('utilisateur', $utilisateurAJour);
        }

        $this->session->setFlashdata('notice', [
            'type'    => 'success',
            'titre'   => 'Modification de compte réussie',
            'details' => 'Les modifications ont été enregistrées.',
        ]);
    }
}
