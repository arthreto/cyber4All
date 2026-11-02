<?php

namespace App\Models;

use App\Entities\MotCle;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;
use App\Entities\Categorie;

/**
 * Ce model représente la gestion des Themes de la table theme
 * @author Benjamin Lorieul
 * @since 1.0.0
 * @package Cyber4all
 */
class QuizModel extends Model
{
    protected $DBGroup = 'tracabilite';
    protected $table = 'quiz';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['id', 'titre','id_theme'];
    protected $returnType = \App\Entities\Quiz::class;

    /**
     * Récupère tout les quiz de la base de données triées par ordre alphabétique.
     *
     * Cette méthode effectue une requête sur la table `quiz` et retourne
     * tout les thèmes sous forme d'un tableau d'objets `Quiz`, triées
     * par le champ `titre` en ordre croissant. En cas d'erreur (ex. problème de
     * connexion ou de requête), un message d'erreur est logué et un tableau vide
     * est retourné.
     *
     * @return Categorie[] Tableau d'objets `Quiz` représentant toutes les catégories.
     *
     * @since 1.0.0
     * @author Arthur
     */
    public function getAllQuizCategory(): array
    {
        // Requête ordonnée par le champ mot en ASC
        try {
            log_message('error',count($this->findAll()));
            return $this->orderBy('titre', 'ASC')->findAll();
        } catch (DatabaseException $e) {
            log_message('error',"Erreur getAllQuiz : " . $e->getMessage());
            return [];
        }
    }



    public function getAllQuizCategoryById(int $id): array
    {
        // Requête ordonnée par le champ mot en ASC
        try {
            log_message('error',count($this->findAll()));

            return $this->where('id_theme', $id)->findAll();
        } catch (DatabaseException $e) {
            log_message('error',"Erreur getAllQuiz : " . $e->getMessage());
            return [];
        }
    }
}