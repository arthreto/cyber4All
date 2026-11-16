<?php

namespace App\Models\Owasp;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;

class OwaspMenaceModel extends Model
{

    protected $DBGroup = 'owasp';
    protected $table = 'menace';
    protected $primaryKey = 'id_menace';
    protected $allowedFields = [
        'id_menace',
        'desc_menace',
        'nom_menace',
        'prog_menace',
        'tops_id'
    ];
    public function getMenaces(string $id): ?array
    {
        return $this->select('id_menace, nom_menace')->
        where('tops_id', $id)->
        get()->getResult();
    }
    public function getMenace(string $top)
    {
        return $this->select('desc_menace, nom_menace, prog_menace, desc_cm, prog_cm')->
        join('contre_mesure', 'menace.id_menace = contre_mesure.menace_id')->
        where('tops_id ', $top)->
        get()->getResult();
    }
}