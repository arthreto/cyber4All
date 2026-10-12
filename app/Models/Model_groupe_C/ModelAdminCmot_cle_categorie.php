<?php

namespace App\Models\Model_groupe_C;

use CodeIgniter\Model;

class ModelAdminCmot_cle_categorie extends Model
{
    protected $table = 'conceptcyber';
    protected $useAutoIncrement = false;
    protected $allowedFields = ['id_mot_cle', 'DISPONIBILITE','INTEGRITE','CONFIDENTIALITE','TRACABILITE'];
}