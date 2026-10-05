<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MotCle\MotCleValidationModel;
use App\Models\UtilisateurModel;
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
    protected Session $session;

    public function __construct()
    {
        $this->utilisateur_model = new UtilisateurModel();
        $this->session = Services::session();
        // Vérification de la session et du rôle de l'utilisateur
        $utilisateur = $this->session->get('utilisateur');
        $this->motCleValidation_model = new MotCleValidationModel();

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
        $utilisateur = $this->session->get('utilisateur');
        $nouvPseudo = $this->request->getPost('nouvPseudo');
        $nouvEmail = $this->request->getPost('nouvEmail');
        $suppEmail = $this->request->getPost('suppEmail');

        //traitement du formulaire et des données
        if (!empty($nouvPseudo) && isset($nouvEmail)) {
            if (($nouvEmail === $utilisateur->email
                    && $nouvPseudo === $utilisateur->pseudo
                    && !isset($suppEmail))
                || ($nouvPseudo === $utilisateur->pseudo
                    && empty($nouvEmail))) {

                //aucune modification
                $this->session->setFlashdata('notice', [
                    'type' => 'info',
                    'titre' => "Modification de compte",
                    'details' => "Aucune modification n'a été faites sur le compte, le pseudo et l'email(si existante) n'ont pas changés",
                ]);
            }
            if (!empty($nouvEmail) && filter_var($nouvEmail, FILTER_VALIDATE_EMAIL) === false) {
                $this->session->setFlashdata('notice', [
                    'type' => 'error',
                    'titre' => "Échec de la modification de compte",
                    'details' => "L'adresse email: {$nouvEmail}, n'est pas valide",
                ]);
            }

            if (!empty($suppEmail) && $suppEmail === "supprimer") {
                $nouvEmail = null;
            }
            // Vérification si l'adresse email existe sur un autre compte.

            if (!empty($nouvEmail) && $nouvEmail !== $utilisateur->email) {
                if ($this->utilisateur_model->adresseEmailExiste($nouvEmail)) {
                    $data['nouvPseudo'] = $nouvPseudo;
                    $this->session->setFlashdata('notice', [
                        'type' => 'error',
                        'titre' => "Échec de la modification de compte",
                        'details' => "L'adresse email: {$nouvEmail}, est déjà utilisée",
                    ]);
                }
            }

            // verifier si le pseudo existe que s'il ne s'agit pas du sien
            if ($nouvPseudo !== $utilisateur->pseudo) {

                if ($this->utilisateur_model->pseudoExiste($nouvPseudo)) {
                    if (!empty($nouvEmail)) {
                        $data['nouvEmail'] = $nouvEmail;
                    }

                    $this->session->setFlashdata('notice', [
                        'type' => 'error',
                        'titre' => "Échec de la modification de compte",
                        'details' => "Pseudo: {$nouvPseudo}, déjà existant. Veuillez en choisir un autre",
                    ]);
                }

            }

            if ($this->utilisateur_model->updateUtilisateurByID(
                ['pseudo' => $nouvPseudo, 'email' => $nouvEmail], $utilisateur)) {

                $utilisateur = $this->utilisateur_model->getUtilisateurById($utilisateur->id);

                if ($utilisateur === null) {
                    // Session corrompue, déconnexion
                    session_destroy();

                    $this->session->setFlashdata('notice', [
                        'type' => 'error',
                        'titre' => "Échec de la modification de compte",
                        'details' => "Les modifications ne se sont pas passez comme prévu, vous avez étés déconnecté",
                    ]);

                    return redirect()->to(base_url());

                } else {
                    $this->session->set('utilisateur', $utilisateur);
                    $this->session->setFlashdata('notice', [
                        'type' => 'success',
                        'titre' => "Modification de compte réussie",
                        'details' => "Les modifications apportés sur votre compte ont été prises en compte avec succés",
                    ]);
                }

            }

        }

        $data = [
            'motsContributeur' => $this->motCleValidation_model
                ->getMotCleValidationContributeurPaginate($this->utilisateur->id),
            'motsContributeurAttente' => $this->motCleValidation_model
                ->getMotCleValidationContributeurAttentePaginate($this->utilisateur->id),
            'pager' => $this->motCleValidation_model->pager,
        ];
//        TODO créer une vue pour afficher les mots du contributeur
        return view('header', $data)
            . view('notice')
            . view('compte/compte')
            . view('compte/demandeMots')
            . view('footer');
    }

}