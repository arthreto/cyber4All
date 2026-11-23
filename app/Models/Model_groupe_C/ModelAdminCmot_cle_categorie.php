<?php

namespace App\Models\Model_groupe_C;

use CodeIgniter\Model;

/**
 * MODÈLE : table conceptcyber (les cases D/I/C/T de chaque terme classé).
 * Fichier : app/Models/Model_groupe_C/ModelAdminCmot_cle_categorie.php
 */
class ModelAdminCmot_cle_categorie extends Model
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
}