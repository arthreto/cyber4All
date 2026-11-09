<?php

namespace App\Models;

use CodeIgniter\Model;

class ThemeModel extends Model
{
    function getThemes()
    {
        $result = false;
        $db = db_connect('default2');
        $query = $db->table('theme')
            ->select ('titre,libelle')
            ->get();
        if ($query->getNumRows() > 0) {
            $result = $query->getResult();
        }
        return $result;
    }
}