<?php

namespace App\Models\Model_groupe_C;

use CodeIgniter\Model;

class ModelAdminCmot_cle extends Model
{
    protected $table = 'mot_cle';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['id','mot', 'definition','definition_complete'];

}