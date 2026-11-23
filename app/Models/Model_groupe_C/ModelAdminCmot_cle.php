<?php

namespace App\Models\Model_groupe_C;

use CodeIgniter\Model;

/**
 * MODÈLE : table mot_cle (le glossaire : un mot + sa définition).
 * Fichier : app/Models/Model_groupe_C/ModelAdminCmot_cle.php
 *
 * Tes propriétés d'origine sont inchangées. J'ai seulement ajouté 2 méthodes
 * (listes de termes pour les menus déroulants), avant écrites dans les contrôleurs.
 */
class ModelAdminCmot_cle extends Model
{
    protected $table = 'mot_cle';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['id','mot', 'definition','definition_complete'];

    /**
     * Termes du glossaire PAS encore classés (liste "Terme à ajouter"), en ordre alphabétique.
     * Utilisée par DICT_Concept_Admin_groupeC::GroupeC_Admin() (mode ajout).
     */
    public function getMotsNonClasses(): array
    {
        return $this->db->table('mot_cle')
            ->select('mot_cle.id, mot_cle.mot')
            ->join('conceptcyber', 'conceptcyber.id_mot_cle = mot_cle.id', 'left') // left = garde aussi les mots sans ligne dans conceptcyber
            ->where('conceptcyber.id_mot_cle IS NULL')                              // ...et on ne garde que ceux-là
            ->orderBy('mot_cle.mot', 'ASC')
            ->get()->getResultArray();
    }

    /**
     * Termes déjà classés (liste "Terme associé" de la page des questions), en ordre alphabétique.
     * Utilisée par DICT_Question_Admin_GroupeC::GroupeC_Questions().
     */
    public function getMotsClasses(): array
    {
        return $this->db->table('mot_cle')
            ->select('mot_cle.id, mot_cle.mot')
            ->join('conceptcyber', 'conceptcyber.id_mot_cle = mot_cle.id') // seulement les termes classés
            ->orderBy('mot_cle.mot', 'ASC')
            ->get()->getResultArray();
    }
}