<?php

namespace App\Models;

use App\Entities\MotCle;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;
use App\Entities\Categorie;

/**
 * Ce model représente la gestion des Themes de la table theme
 * @author Arthur
 * @since 1.0.0
 * @package Cyber4all
 */
class ThemeModel extends Model
{
    protected $DBGroup = 'tracabilite';
    protected $table = 'theme';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['id', 'nom'];
    protected $returnType = \App\Entities\Theme::class;

    /**
     * Récupère tout les thèmes de la base de données triées par ordre alphabétique.
     *
     * Cette méthode effectue une requête sur la table `theme` et retourne
     * tout les thèmes sous forme d'un tableau d'objets `Theme`, triées
     * par le champ `nomTheme` en ordre croissant. En cas d'erreur (ex. problème de
     * connexion ou de requête), un message d'erreur est logué et un tableau vide
     * est retourné.
     *
     * @return Theme[] Tableau d'objets `Theme` représentant tous les thèmes.
     *
     * @since 1.0.0
     * @author Arthur
     */
    public function getAllThemes(): array
    {
        log_message('error','contact');
        // Requête ordonnée par le champ mot en DESC
        try {
            log_message('error',count($this->findAll()));
            return $this->orderBy('nom', 'DESC')->findAll();
        } catch (DatabaseException $e) {
            log_message('error',"Erreur getAllCategories : " . $e->getMessage());
            return [];
        }
    }
}