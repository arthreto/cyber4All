<?php

namespace App\Models\Owasp;

use CodeIgniter\Model;

class OwaspTopModel extends Model
{

    protected $DBGroup = 'owasp';
    protected $table = 'tops';
    protected $primaryKey= 'id_top';
    protected $allowedFields = ['nom_top','edition_top', 'libelle_top'];

    public function getTopById(string $top)
    {
        return $this->select('nom_top, edition_top, libelle_top')->where('id_top', $top)->get()->getRow();
    }
}