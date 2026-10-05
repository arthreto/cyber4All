<?php

namespace App\Models\MotCle;

use App\Entities\MotCle;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;
use DateTime;

/**
 * Ce model représente la gestion des VoteController de la table vote
 * @author Benjamin Lorieul
 * @since 1.0.0
 * @package Cyber4all
 */
class MotCleVoteModel extends Model
{
    protected $table = 'mot_cle';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['mot', 'definition', 'definition_complete', 'id_auteur', 'date_ajout'];
    protected $returnType = \App\Entities\MotCle::class;

    /**
     * Récupère tous les mots-clés votés par un utilisateur au cours de la semaine en cours.
     *
     * Cette méthode effectue une jointure entre les tables `mot_cle` et `vote` pour
     * retourner tous les mots-clés sur lesquels l'utilisateur identifié par `$id_utilisateur`
     * a voté depuis le lundi de la semaine en cours.
     *
     * @param int $id_utilisateur Identifiant de l'utilisateur.
     * @return MotCle[] Tableau d'objets `MotCle`, ou vide si aucun vote trouvé.
     *
     * @since 1.0.0
     */
    public function getAllMotCleVotesCetteSemaineByUtilisateurId(int $id_utilisateur): array
    {
        $debutSemaine = new \DateTime('monday this week');
        $debutSemaineStr = $debutSemaine->format('Y-m-d H:i:s');

        try {
            return $this->select('mot_cle.*')
                ->join('vote', 'vote.id_mot_cle = mot_cle.id', 'LEFT')
                ->where('vote.id_utilisateur', $id_utilisateur)
                ->where('DATE(vote.date_vote) >=', $debutSemaineStr)
                ->findAll();
        } catch (DatabaseException $e) {
            log_message('error', 'getAllMotCleVotesCetteSemaineByUtilisateurId : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère tous les mots-clés populaires de la semaine en cours.
     *
     * Les mots-clés sont triés d'abord par le nombre de votes décroissant, puis
     * par la date du dernier vote décroissante, afin de prioriser les mots les plus
     * populaires et récents.
     *
     * @return array Tableau d'objets `MotCle` avec informations de vote, ou vide si aucun résultat.
     *
     * @since 1.0.0
     */
    public function getAllMotClePopulairesDateDesc(): array
    {
        try {
            return $this->select('mot_cle.*, COUNT(vote.id_vote) AS nbVotes, MAX(vote.date_vote) AS lastVote')
                ->join('vote', 'vote.id_mot_cle = mot_cle.id', 'INNER')
                ->where('vote.date_vote >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)', null, false)
                ->groupBy('mot_cle.id')
                ->orderBy('nbVotes', 'DESC')
                ->orderBy('lastVote', 'DESC')
                ->findAll();
        } catch (DatabaseException $e) {
            log_message('error', 'getAllMotClePopulairesDateDesc : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les mots-clés les plus votés de la semaine précédente.
     *
     * La semaine précédente est définie du lundi au dimanche. La méthode retourne
     * les `$nbMots` mots les plus votés pendant cette période, triés par nombre de votes décroissant.
     *
     * @param int $nbMots Nombre maximum de mots à retourner.
     * @return array Tableau d'objets `MotCle` avec le nombre de votes, ou vide si aucun résultat.
     *
     * @since 1.0.0
     */
    public function getMotsPopulairesSemainePrecedente(int $nbMots): array
    {
        $debutSemaine = new DateTime('last monday');
        $debutSemaineStr = $debutSemaine->format('Y-m-d H:i:s');

        $finSemaine = clone $debutSemaine;
        $finSemaine->modify('+6 days');
        $finSemaineStr = $finSemaine->format('Y-m-d H:i:s');

        try {
            return $this->select('*, COUNT(*) AS nb_votes')
                ->join('vote', 'vote.id_mot_cle = mot_cle.id', 'INNER')
                ->where('DATE(date_vote) >=', $debutSemaineStr)
                ->where('DATE(date_vote) <=', $finSemaineStr)
                ->groupBy('id_mot_cle')
                ->orderBy('nb_votes', 'DESC')
                ->findAll($nbMots);
        } catch (DatabaseException $e) {
            log_message('error', 'getMotsPopulairesSemainePrecedente : ' . $e->getMessage());
            return [];
        }
    }
}