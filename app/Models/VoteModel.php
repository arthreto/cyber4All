<?php

namespace App\Models;

use App\Entities\Vote;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;
use DateTime;

/**
 * Ce model représente la gestion des VoteController de la table vote
 * @author Benjamin Lorieul
 * @since 1.0.0
 * @package Cyber4all
 */
class VoteModel extends Model
{
    protected $table = 'vote';
    protected $primaryKey = 'id_vote';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['id_mot_cle', 'id_utilisateur'];
    protected $returnType = Vote::class;

    /**
     * Insère un nouveau vote dans la table `vote`.
     *
     * Cette méthode ajoute un enregistrement correspondant à l'objet `Vote`
     * fourni dans la table `vote`. Si l'insertion réussit, elle retourne
     * l'identifiant du vote créé. En cas d'échec, elle retourne `false`.
     *
     * @param Vote $vote Objet Vote à insérer.
     * @return int|false ID du vote inséré si succès, false sinon.
     *
     * @throws \ReflectionException Peut être levée si l'entité Vote ne peut pas être insérée correctement.
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function insertVote(Vote $vote): bool
    {
        try {
            if ($this->getNbVotesSemaineActuelleByUtilisateurId($vote->id_utilisateur) < NB_VOTES_MAX) {
                return $this->insert($vote, false);

            } else {
                return false;
            }
        } catch (DatabaseException $e) {
            log_message('error', "Erreur insertVote : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime un vote par son identifiant.
     *
     * @param int $id L'identifiant du vote à supprimer.
     * @return bool `true` si la suppression a réussi, `false` sinon.
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function deleteVoteById(int $id): bool
    {
        try {
            return $this->delete($id);
        } catch (DatabaseException $e) {
            log_message('error', "Erreur deleteVoteById : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère le nombre total de votes d'un utilisateur.
     *
     * @param int $id_utilisateur Identifiant de l'utilisateur.
     * @return int Nombre total de votes.
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function getNbVotesTotalByUtilisateurId(int $id_utilisateur): int
    {
        try {
            return $this->where('id_utilisateur', $id_utilisateur)
                ->countAllResults();
        } catch (DatabaseException $e) {
            log_message('error', "Erreur getNbVotesTotalByUtilisateurId : " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Récupère le nombre de votes d'un utilisateur depuis le lundi de la semaine en cours.
     *
     * @param int $id_utilisateur Identifiant de l'utilisateur.
     * @return int Nombre de votes depuis le début de la semaine.
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function getNbVotesSemaineActuelleByUtilisateurId(int $id_utilisateur): int
    {
        $debutSemaine = new DateTime('monday this week');
        $debutSemaineStr = $debutSemaine->format('Y-m-d H:i:s');

        try {
            return $this->where('id_utilisateur', $id_utilisateur)
                ->where('DATE(date_vote) >=', $debutSemaineStr)
                ->countAllResults();
        } catch (DatabaseException $e) {
            log_message('error', "Erreur getNbVotesSemaineActuelleByUtilisateurId : " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Récupère un vote d'un utilisateur pour un mot-clé spécifique depuis le lundi de la semaine en cours.
     *
     * @param int $id_utilisateur Identifiant de l'utilisateur.
     * @param int $id_motcle Identifiant du mot-clé.
     * @return Vote|null Objet Vote si trouvé, sinon `null`.
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function getUnVoteSemaineEnCours(int $id_utilisateur, int $id_motcle): ?Vote
    {
        $debutSemaine = new DateTime('monday this week');
        $debutSemaineStr = $debutSemaine->format('Y-m-d H:i:s');

        try {
            $result = $this->where('id_utilisateur', $id_utilisateur)
                ->where('id_mot_cle', $id_motcle)
                ->where('DATE(date_vote) >=', $debutSemaineStr)
                ->first();

            return $result instanceof Vote ? $result : null;
        } catch (DatabaseException $e) {
            log_message('error', "Erreur getUnVoteSemaineEnCours : " . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère le nombre total de votes dans la table.
     *
     * @return int Nombre total de votes.
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function recupNbAllVotes(): int
    {
        try {
            return $this->countAllResults();
        } catch (DatabaseException $e) {
            log_message('error', "Erreur recupNbAllVotes : " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Récupère les identifiants des mots-clés déjà votés par un utilisateur
     * depuis le lundi de la semaine en cours.
     *
     * @param int $id_utilisateur Identifiant de l'utilisateur.
     * @return int[] Tableau des identifiants des mots-clés votés. Vide si aucun.
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function getIdMotCleVotesSemaineActuelleByUtilisateurId(int $id_utilisateur): array
    {
        $debutSemaine = new DateTime('monday this week');
        $debutSemaineStr = $debutSemaine->format('Y-m-d H:i:s');

        try {
            $results = $this->select('id_mot_cle')
                ->where('id_utilisateur', $id_utilisateur)
                ->where('DATE(date_vote) >=', $debutSemaineStr)
                ->asObject()
                ->findAll();

            $listeIdMots = [];
            foreach ($results as $result) {
                $listeIdMots[] = $result->id_mot_cle;
            }

            return $listeIdMots;
        } catch (DatabaseException $e) {
            log_message('error', "Erreur getIdMotCleVotesSemaineActuelleByUtilisateurId : " . $e->getMessage());
            return [];
        }
    }

}