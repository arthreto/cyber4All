<?php

namespace App\Models\Owasp;

use CodeIgniter\Model;

class OwaspContreMesureModel extends Model
{
    protected $DBGroup = 'owasp';
    protected $table = 'contre_mesure';
    protected $primaryKey = 'id_cm';
    protected $allowedFields = [
        'id_cm',
        'desc_cm',
        'prog_cm',
        'menace_id'
    ];
    public function ajouterContreMesure(int $menaceId, array $data): bool
    {
        return $this->db->table('contre_mesure')->insert([
            'menace_id' => $menaceId,
            'desc_cm' => $data['desc_cm'],
            'prog_cm' => $data['prog_cm'],
        ]);
    }

    public function updateContreMesure(int $menaceId, int $id, array $data): bool
    {
        return $this->db->table('contre_mesure')
            ->where('menace_id', $menaceId)
            ->where('id_cm', $id)
            ->update([
                'desc_cm' => $data['desc_cm'],
                'prog_cm' => $data['prog_cm']
            ]);
    }

    public function supprimerContreMesure(int $menaceId, int $id): bool
    {
       return $supprime = $this->db->table('contre_mesure')
            ->where('menace_id', $menaceId)
            ->where('id_cm', $id)
            ->delete();
    }

}