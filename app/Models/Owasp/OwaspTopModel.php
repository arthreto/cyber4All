<?php

namespace App\Models\Owasp;

use CodeIgniter\Model;

class OwaspTopModel extends Model
{
    protected $DBGroup = 'owasp';
    protected $table = 'tops';
    protected $primaryKey = 'id_top';
    protected $allowedFields = ['nom_top', 'edition_top', 'libelle_top'];
    protected $returnType = 'object';

    public function getTopById(string $top): ?object
    {
        return $this->select('id_top, nom_top, edition_top, libelle_top')
            ->where('id_top', $top)
            ->get()
            ->getRow();
    }

    public function getAutresTops(string $top): array
    {
        return $this->select('id_top, nom_top, edition_top, libelle_top')
            ->where('id_top !=', $top)
            ->get()
            ->getResult();
    }
}