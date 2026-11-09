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
    public function getMenaces(int $num)
    {
        $numero = $num + 10;
        $db = db_connect('owasp');
        $query = $db->table('menace')->select('id_menace, nom_menace')->where('id_menace >', $num)->groupStart()->where('id_menace <', $numero)->get()->getResult();
        return $query;
    }
    public function test1 (int $num)
    {
        $db = db_connect('owasp');
        $query = $db->table('menace')->select('desc_menace, nom_menace, prog_menace, desc_cm, prog_cm')->join('contre_mesure', 'menace.id_menace = contre_mesure.menace_id')->where('id_menace =', $num)->get()->getResult();
        return $query;
    }

}