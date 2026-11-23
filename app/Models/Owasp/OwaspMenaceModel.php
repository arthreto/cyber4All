<?php

namespace App\Models\Owasp;

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
    protected $returnType = 'object';

    public function getMenaces(string $id): array
    {
        return $this->select('id_menace, nom_menace')
            ->where('tops_id', $id)
            ->orderBy('id_menace')
            ->findAll();
    }
    public function getMenaceContreMesures(int $id): array
    {
        return $this->db->table('contre_mesure')
            ->select('id_cm, desc_cm, prog_cm')
            ->where('menace_id', $id)
            ->get()
            ->getResult();
    }

    public function getMenace(string $top, int $id): ?object
    {
        return $this->select('desc_menace, nom_menace, prog_menace')
            ->where('tops_id', $top)
            ->where('id_menace', $id)
            ->first();
    }
    public function updateMenace(int $id, array $data): bool
    {
        return $this->db->table('menace')
            ->update([
                'desc_menace' => $data['desc_menace'],
                'nom_menace' => $data['nom_menace'],
                'prog_menace' => $data['prog_menace']
            ]);
    }
}