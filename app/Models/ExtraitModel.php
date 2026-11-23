<?php

namespace App\Models;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;

/**
 * Ce model représente la gestion de la table extrait
 *
 * @author Arthur
 * @since 2.0.0
 * @package Cyber4all
 */
class ExtraitModel extends Model
{
    protected $DBGroup = 'tracabilite';
    protected $table = 'extrait';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['titre', 'type', 'contenu', 'actif'];

    /**
     * Types possibles d'un extrait avec leur libellé
     */
    public const TYPES = [
        'token'   => 'Token',
        'log'     => 'Log',
        'session' => 'Session',
    ];

    /**
     * Récupère les extraits actifs, triés par type puis par titre.
     * Un extrait supprimé (désactivé) n'est plus proposé, sauf celui qu'une question utilise déjà.
     *
     * @param int|null $idExtraitGarde Extrait à garder même s'il est inactif (extrait actuel de la question), null pour aucun.
     * @return array Liste des extraits, vide si cela ne fonctionne pas
     */
    public function getAllExtraits(?int $idExtraitGarde = null): array
    {
        try {
            $this->groupStart()->where('actif', 1);
            if ($idExtraitGarde !== null) {
                $this->orWhere('id', $idExtraitGarde);
            }
            $this->groupEnd();

            return $this->orderBy('type', 'ASC')
                ->orderBy('titre', 'ASC')
                ->findAll();

        } catch (DatabaseException $e) {
            log_message('error', "Erreur de récupération des extraits : {exception}", ['exception' => $e]);
            return [];
        }
    }

    /**
     * Récupère les extraits pour la page d'administration, avec le nombre de questions
     * qui les utilisent. Triés par type puis par titre.
     *
     * @param string|null $type Filtre sur un type, null pour tous les extraits.
     * @return array Liste des extraits, vide en cas d'erreur.
     *
     * @author Arthur
     */
    public function getExtraitsAdmin(?string $type = null): array
    {
        try {
            $builder = $this->db->table($this->table)
                ->select('extrait.*, COUNT(question.id) AS nb_questions')
                ->join('question', 'question.id_extrait = extrait.id', 'left')
                ->groupBy('extrait.id')
                ->orderBy('extrait.type', 'ASC')
                ->orderBy('extrait.titre', 'ASC');

            if ($type !== null) {
                $builder->where('extrait.type', $type);
            }

            return $builder->get()->getResultArray();

        } catch (DatabaseException $e) {
            log_message('error', "Erreur de récupération des extraits (admin) : {exception}", ['exception' => $e]);
            return [];
        }
    }

    /**
     * Récupère les questions qui utilisent un extrait
     *
     * @param int $idExtrait Identifiant de l'extrait.
     * @return array Liste des questions, vide en cas d'erreur.
     *
     * @author Arthur
     */
    public function getQuestionsDeExtrait(int $idExtrait): array
    {
        try {
            return $this->db->table('question')
                ->select('id, libelle, actif')
                ->where('id_extrait', $idExtrait)
                ->orderBy('libelle', 'ASC')
                ->get()
                ->getResultArray();

        } catch (DatabaseException $e) {
            log_message('error', "Erreur de récupération des questions d'un extrait : {exception}", ['exception' => $e]);
            return [];
        }
    }

    /**
     * Compte les questions qui utilisent un extrait, sauf éventuellement une question.
     *
     * @param int $idExtrait Identifiant de l'extrait.
     * @param int $idQuestionExclue Question à ne pas compter (celle en cours de modification), 0 pour aucune.
     * @return int Nombre de questions
     *
     * @author Arthur
     */
    public function compterQuestions(int $idExtrait, int $idQuestionExclue = 0): int
    {
        try {
            $builder = $this->db->table('question')->where('id_extrait', $idExtrait);

            if ($idQuestionExclue > 0) {
                $builder->where('id !=', $idQuestionExclue);
            }

            return $builder->countAllResults();

        } catch (DatabaseException $e) {
            log_message('error', "Erreur de comptage des questions d'un extrait : {exception}", ['exception' => $e]);
            return 0;
        }
    }

    /**
     * Crée un nouvel extrait.
     *
     * @param array $donnees Données de l'extrait
     * @return int|false Identifiant de l'extrait créé, false en cas d'erreur.
     */
    public function creerExtrait(array $donnees): int|false
    {
        try {
            $idExtrait = $this->insert($donnees);

            return $idExtrait === false ? false : (int) $idExtrait;

        } catch (DatabaseException $e) {
            log_message('error', "Erreur de création d'un extrait : {exception}", ['exception' => $e]);
            return false;
        }
    }

    /**
     * Modifie un extrait existant.
     *
     * @param int $idExtrait Identifiant de l'extrait.
     * @param array $donnees Nouvelles données
     * @return bool True si la modification a réussi, false sinon.
     */
    public function modifierExtrait(int $idExtrait, array $donnees): bool
    {
        try {
            return $this->update($idExtrait, $donnees);

        } catch (DatabaseException $e) {
            log_message('error', "Erreur de modification d'un extrait : {exception}", ['exception' => $e]);
            return false;
        }
    }

    /**
     * « Supprime » un extrait : il n'est pas effacé, il passe inactif (actif = 0),
     * et les questions actives qui l'utilisent passent inactives elles aussi.
     * Les questions gardent leur lien vers lui. L'extrait peut être réactivé,
     * mais ses questions ne sont pas réactivées automatiquement.
     *
     * @param int $idExtrait Identifiant de l'extrait.
     * @return array|false Identifiants des questions désactivées, false en cas d'erreur.
     */
    public function supprimerExtrait(int $idExtrait): array|false
    {
        try {
            $this->db->transBegin();

            $idsQuestions = array_column(
                $this->db->table('question')
                    ->select('id')
                    ->where('id_extrait', $idExtrait)
                    ->where('actif', 1)
                    ->get()
                    ->getResultArray(),
                'id'
            );

            if ($idsQuestions !== []) {
                $this->db->table('question')
                    ->whereIn('id', $idsQuestions)
                    ->update(['actif' => 0]);
            }

            $this->update($idExtrait, ['actif' => 0]);

            $this->db->transCommit();
            return array_map('intval', $idsQuestions);

        } catch (DatabaseException $e) {
            $this->db->transRollback();
            log_message('error', "Erreur de suppression d'un extrait : {exception}", ['exception' => $e]);
            return false;
        }
    }

    /**
     * Réactive un extrait supprimé (désactivé).
     *
     * @param int $idExtrait Identifiant de l'extrait.
     * @return bool True si la réactivation a réussi, false sinon.
     *
     * @author Raphael Bierlaire
     */
    public function reactiverExtrait(int $idExtrait): bool
    {
        try {
            return $this->update($idExtrait, ['actif' => 1]);

        } catch (DatabaseException $e) {
            log_message('error', "Erreur de réactivation d'un extrait : {exception}", ['exception' => $e]);
            return false;
        }
    }
}
