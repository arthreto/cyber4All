<?php

namespace App\Models;

use App\Entities\Lecon;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;

class LeconModel extends Model
{
    protected $DBGroup          = 'tracabilite';
    protected $table            = 'lecon';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = Lecon::class;
    protected $allowedFields    = ['titre', 'contenu', 'id_theme', 'id_auteur'];

    protected $useTimestamps = true;
    protected $createdField  = 'date_ajout';
    protected $updatedField  = 'date_modif';

    public function getLeconById(int $idLecon): ?Lecon
    {
        try {
            return $this->find($idLecon);
        } catch (DatabaseException $e) {
            log_message('error', 'Erreur getLeconById : ' . $e->getMessage());
            return null;
        }
    }

    public function getLeconsByTheme(int $idTheme): array
    {
        try {
            return $this->select('id, titre')
                ->where('id_theme', $idTheme)
                ->orderBy('id', 'ASC')
                ->findAll();
        } catch (DatabaseException $e) {
            log_message('error', "Erreur getLeconsByTheme (thème {id}): {message}", [
                'id' => $idTheme,
                'message' => $e->getMessage()
            ]);

            return [];
        }
    }
}