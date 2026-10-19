<?php

namespace App\Models;

use CodeIgniter\Model;

class OwaspApiMenaceModel extends Model
{

    protected $DBGroup = 'usersDB';
    protected $table = 'menace';
    protected $primaryKey = 'id_menace';
    protected $allowedFields = [
        'id_menace',
        'desc_menace',
        'nom_menace',
        'prog_menace'
    ];
    public function getMenace()
    {
        $db = db_connect('usersDB');
        $query = $db->select()->where('id_menace >', 10)->getResult();
        return $query;
    }

}