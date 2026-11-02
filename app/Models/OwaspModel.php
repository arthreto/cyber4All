<?php

namespace App\Models;

use CodeIgniter\Model;

class OwaspModel extends Model
{

    protected $DBGroup = 'owasp';
    protected $table = 'menace';
    protected $primaryKey = 'id_menace';
    protected $allowedFields = [
        'id_menace',
        'desc_menace',
        'nom_menace',
        'prog_menace'
    ];
    public function getMenaces()
    {
        $db = db_connect('owasp');
        $query = $db->table('menace')->where('id_menace >', 10)->get()->getResult();
        return $query;
    }

}