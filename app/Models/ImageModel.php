<?php

namespace App\Models;

use App\Entities\Image;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;

class ImageModel extends Model
{
    protected $table = 'mot_image';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['mot_id', 'image', 'alt', 'legend'];
    protected $returnType = \App\Entities\Image::class;

    /**
     * Insert une image sans utiliser la clé primaire
     */
    // ImageModel.php
    public function insertImageMotCle(Image $image): int|bool
    {
        try {
            // ✅ Log les données avant insertion
            log_message('debug', '[ImageModel] Attempting to insert image: ' . json_encode([
                    'mot_id' => $image->mot_id,
                    'image' => $image->image,
                    'alt' => $image->alt,
                    'legend' => $image->legend
                ]));

            $result = $this->insert(
                [
                    'mot_id' => $image->mot_id,
                    'image' => $image->image,
                    'alt' => $image->alt,
                    'legend' => $image->legend
                ],
                false
            );

            //  Log les erreurs de validation du modèle
            if (!$result) {
                $errors = $this->errors();
                log_message('error', '[ImageModel] Insert failed. Validation errors: ' . json_encode($errors));

                // Log l'erreur de la base de données
                $db = \Config\Database::connect();
                log_message('error', '[ImageModel] DB Error: ' . $db->error()['message']);
            } else {
                log_message('debug', '[ImageModel] Image inserted with ID: ' . $result);
            }

            return $result;

        } catch (DatabaseException $e) {
            log_message('error', '[ImageModel] Exception: ' . $e->getMessage());
            log_message('error', '[ImageModel] SQL: ' . $this->db->getLastQuery());
            return false;
        }
    }

    /**
     * Récupère toutes les images d'un mot
     */
    public function getImagesByMotId(int $motId): array
    {
        return $this->where('mot_id', $motId)->findAll();
    }

    /**
     * Supprime les images d'un mot
     */
    public function deleteImagesByMotId(int $motId): bool
    {
        try {
            return $this->where('mot_id', $motId)->delete();
        } catch (DatabaseException $e) {
            log_message('error', '[ImageModel] Delete failed: ' . $e->getMessage());
            return false;
        }
    }
}