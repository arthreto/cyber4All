<?php

namespace App\Models\Model_groupe_C;

use CodeIgniter\Model;

class ConceptCyberModel extends Model
{
    protected $table      = 'conceptcyber';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    // Nombre de termes publiés pour une colonne (DISPONIBILITE, INTEGRITE...)
    public function compterParPilier(string $colonne): int
    {
        // Liste blanche : on n'accepte que ces 4 colonnes
        $autorisees = ['DISPONIBILITE', 'INTEGRITE', 'CONFIDENTIALITE', 'TRACABILITE'];
        if (!in_array($colonne, $autorisees, true)) {
            return 0;
        }

        return $this->join('mot_cle', 'mot_cle.id = conceptcyber.id_mot_cle')
            ->where('mot_cle.statut', 1)
            ->where('conceptcyber.' . $colonne, 1)
            ->countAllResults();
    }
}