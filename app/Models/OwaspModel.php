<?php

namespace App\Models;

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
    public function getMenacesApi()
    {
        $db = db_connect('owasp');
        $query = $db->table('menace')->where('id_menace >', 10)->get()->getResult();
        return $query;
    }

    public function getMenacesWeb()
    {
        $db = db_connect('owasp');
        $query = $db->table('menace')->where('id_menace')->get()->getResult();
        return $query;
    }

    public function getTopById(int $top): ?object
    {
        try {
            // Requête limitée à id et titre, ordonnée par le champ id en ASC
            return $this->select('nom_top, edition_top, libelle_top')
                ->where('id_top', $top)
                ->get()
                ->getRow();
        } catch (DatabaseException $e) {
            log_message('error', "Erreur getTopById (Top numéro {id}): {message}", [
                'id' => $top,
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }
}