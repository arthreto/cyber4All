<?php

namespace App\Models\Model_groupe_C;

use CodeIgniter\Model;

/**
 * MODÈLE : table conceptcyber (les cases D/I/C/T de chaque terme classé).
 * Fichier : app/Models/Model_groupe_C/ConceptCyberModel.php
 */
class ConceptCyberModel extends Model
{
    protected $table = 'conceptcyber';
    protected $primaryKey = 'id_mot_cle';

    protected $useAutoIncrement = false;
    protected $allowedFields = ['id_mot_cle', 'disponibilite','integrite','confidentialite','tracabilite'];

    /** Un terme est-il déjà classé (a-t-il une ligne dans conceptcyber) ? */
    public function estClasse(int $idMot): bool
    {
        return $this->where('id_mot_cle', $idMot)->countAllResults() > 0;
    }

    /** Les cases D/I/C/T d'un terme (null si le terme n'est pas classé). */
    public function getParMot(int $idMot): ?array
    {
        return $this->where('id_mot_cle', $idMot)->first();
    }

    /** Retire un terme du classement. Renvoie true si une ligne a bien été supprimée. */
    public function retirer(int $idMot): bool
    {
        $this->where('id_mot_cle', $idMot)->delete();

        return $this->db->affectedRows() > 0;
    }

    /**
     * Enregistre les cases D/I/C/T d'un terme, puis recalcule les bonnes réponses
     * de toutes les questions liées à ce terme. Renvoie true si tout a réussi.
     *
     * $valeurs = ['disponibilite' => 0|1, 'integrite' => 0|1, ...]
     */
    public function modifierCases(int $idMot, array $valeurs): bool
    {
        $this->db->transStart(); // transaction : tout ou rien

        // 1. On enregistre les nouvelles cases du terme (table conceptcyber)
        $this->update($idMot, $valeurs);

        // 2. On recalcule les bonnes réponses de toutes les questions liées à ce terme
        $reponses = new QuestionReponseModel();
        foreach ((new QuestionQuizModel())->getIdsParMot($idMot) as $idQuestion) {
            $reponses->synchroniser((int) $idQuestion, $idMot);
        }

        $this->db->transComplete();

        // transStatus() = true si TOUTES les requêtes de la transaction ont réussi
        return $this->db->transStatus();
    }
}