<?php

namespace App\Models\m_grp;

use CodeIgniter\Model;

class QuizModel extends Model
{
    public function Compter(){
        $db = \Config\Database::connect();
        $nbD = $db->table('conceptcyber')
            ->where('categorie', 'DISPONIBILITE')
            ->countAllResults();

    }
}