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
    protected $allowedFields = ['titre', 'type', 'contenu'];

    /**
     * Types possibles d'un extrait avec leur libellé
     */
    public const TYPES = [
        'token'   => 'Token',
        'log'     => 'Log',
        'session' => 'Session',
    ];

    /**
     * Récupère tous les extraits, triés par type puis par titre.
     *
     * @return array Liste des extraits, vide si cela ne fonctionne pas
     */
    public function getAllExtraits(): array
    {
        try {
            return $this->orderBy('type', 'ASC')
                ->orderBy('titre', 'ASC')
                ->findAll();

        } catch (DatabaseException $e) {
            log_message('error', "Erreur de récupération des extraits : " . $e->getMessage());
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
            log_message('error', "Erreur de récupération des extraits (admin) : " . $e->getMessage());
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
            log_message('error', "Erreur de récupération des questions d'un extrait : " . $e->getMessage());
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
            log_message('error', "Erreur de comptage des questions d'un extrait : " . $e->getMessage());
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
            log_message('error', "Erreur de création d'un extrait : " . $e->getMessage());
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
            log_message('error', "Erreur de modification d'un extrait : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime un extrait
     *
     * @param int $idExtrait Identifiant de l'extrait.
     * @return bool True si la suppression a réussi, false sinon.
     */
    public function supprimerExtrait(int $idExtrait): bool
    {
        try {
            $this->db->transBegin();

            $this->db->table('question')
                ->where('id_extrait', $idExtrait)
                ->update(['id_extrait' => null]);

            $this->delete($idExtrait);

            $this->db->transCommit();
            return true;

        } catch (DatabaseException $e) {
            $this->db->transRollback();
            log_message('error', "Erreur de suppression d'un extrait : " . $e->getMessage());
            return false;
        }
    }
}
