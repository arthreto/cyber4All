<?php

namespace App\Controllers;

use App\Entities\Utilisateur;
use App\Models\VoteModel;
use App\Entities\Vote;
use CodeIgniter\Config\Services;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Session\Session;
use CodeIgniter\Validation\Validation;

/**
 * Ce controller permet la gestion des votes
 * @since 1.0.0
 * @package Cyber4all
 * @author Benjamin Lorieul
 */
class VoteController extends BaseController
{
    protected VoteModel $vote_model;
    protected Session $session;
    protected Validation $validation;
    protected ?Utilisateur $utilisateur = null; // nullable puisque pas obligatoire

    public function __construct()
    {
        $this->session = session();
        $this->validation = Services::validation();
        $this->vote_model = new VoteModel();
        // Vérification de la session et du rôle de l'utilisateur
        $utilisateur = $this->session->get('utilisateur');

        if ($utilisateur && in_array($utilisateur->role, ['contributeur'], true)) {
            $this->utilisateur = $utilisateur;

        } else {
            throw PageNotFoundException::forPageNotFound('Accès refusé');
        }
    }


    /**
     * Permet la gestion des votes
     * @return \CodeIgniter\HTTP\RedirectResponse
     * @throws \ReflectionException
     */
    public function index(): \CodeIgniter\HTTP\RedirectResponse
    {
        $from = $this->request->getPost('from');

        //récupère l'id si suppréssion de vote demandé
        $suppVote = $this->request->getPost('suppVote');  // Le bouton
        $id_suppVote = (int)$this->request->getPost('id_vote');  // L'ID du vote

        //récupère si une demande de vote a été faite et l'id du mot
        $voter = $this->request->getPost('voter');
        $id_mot_vote = (int)$this->request->getPost('id_mot_vote');

        // Traitement des actions du vote
        if ($this->utilisateur !== null) {
            $nb_votes = $this->vote_model->getNbVotesSemaineActuelleByUtilisateurId($this->utilisateur->id);

            // Gère la suppression d'un vote
            if (!empty($suppVote)) {
                $result = $this->vote_model->deleteVoteById($id_suppVote);
                session()->setFlashdata('suppVote', $result ? 'ok' : 'error');
                $this->session->setFlashdata('notice', [
                    'type' => $result ? 'success' : 'error',
                    'titre' => $result ? "Suppression de vote réussie" : "Échec de la suppression du vote",
                    'details' => $result ? "La suppression à bien été prise en compte" : "Une erreur est survenue",
                ]);

                // Gère l'ajout d'un vote
            } elseif (!empty($voter)) {
                $dataVote = [
                    'id_mot_cle' => $id_mot_vote,
                    'id_utilisateur' => $this->utilisateur->id
                ];
                $vote = new Vote($dataVote);
                $result = false;
                if ($nb_votes < NB_VOTES_MAX) {
                    $result = $this->vote_model->insertVote($vote);
                }
                $this->session->setFlashdata('notice', [
                    'type' => $result ? 'success' : 'error',
                    'titre' => $result ? "Vote réussi" : "Échec du vote",
                    'details' => $result ? "Le vote à bien été pris en compte" : "Une erreur est survenue",
                ]);
            }

            return redirect()->to($from);

        } else {
            throw PageNotFoundException::forPageNotFound('Accès refusé');
        }
    }

}