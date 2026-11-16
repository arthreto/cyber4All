<?php

namespace App\Models;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;

class OwaspModel extends Model
{

    protected $DBGroup = 'owasp';
    protected $table = [
        'menace',
        'tops'
    ];
    protected $primaryKey = [
        'id_menace',
        'id_top'
    ];
    protected $allowedFields = [
        'id_menace',
        'desc_menace',
        'nom_menace',
        'prog_menace',
        'id_top',
        'nom_top',
        'edition_top',
        'libelle_top'
    ];
    public function getMenaces(string $id): ?array
    {
        $db = db_connect('owasp');
        $query = $db->table('menace')->
        select('id_menace, nom_menace')->
        where('tops_id', $id)->
        get()->
        getResult();
        return $query;
    }
    public function getMenace(string $top)
    {
        $db = db_connect('owasp');
        $query = $db->table('menace')->
        select('desc_menace, nom_menace, prog_menace, desc_cm, prog_cm')->
        join('contre_mesure', 'menace.id_menace = contre_mesure.menace_id')->
        where('tops_id =', $top)->
        get()->
        getResult();
        return $query;
    }

    public function getTopById(string $top): ?object
    {
        try {
            return $this->select('nom_top, edition_top, libelle_top')
                ->where('id_top', $top)
                ->get()
                ->getRow();
        } catch (DatabaseException $e) {
            log_message('error', "Erreur getTopById (Top numéro {id}): {message}", [
                'id' => $top,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }
}