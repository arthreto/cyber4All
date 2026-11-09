<?php

namespace App\Models;

use App\Entities\Lecon;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;

/**
 * Ce model représente la gestion des leçons de la table lecon
 * @author Raphael BIERLAIRE
 * @since 1.0.0
 * @package Cyber4all
 */
class LeconModel extends Model
{
    protected $DBGroup          = 'tracabilite';
    protected $table            = 'lecon';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = Lecon::class;
    protected $allowedFields    = ['titre', 'contenu', 'id_theme', 'id_auteur'];

    // Remplissage automatique des dates d'ajout et de modification
    protected $useTimestamps = true;
    protected $createdField  = 'date_ajout';
    protected $updatedField  = 'date_modif';

    /**
     * Récupère une leçon complète à partir de son identifiant.
     *
     * Cette méthode effectue une requête sur la table `lecon` et retourne
     * la leçon correspondant à l'identifiant donné sous forme d'un objet `Lecon`,
     * avec toutes ses colonnes (titre, contenu, thème, auteur, dates).
     * Si la leçon n'existe pas, ou en cas d'erreur (ex. problème de connexion
     * ou de requête), un message d'erreur est logué et null est retourné.
     *
     * @param int $idLecon Identifiant de la leçon à récupérer.
     *
     * @return Lecon|null Objet `Lecon` correspondant, ou null si introuvable ou en cas d'erreur.
     *
     * @since 1.0.0
     * @author Raphael BIERLAIRE
     */
    public function getLeconById(int $idLecon): ?Lecon
    {
        try {
            return $this->find($idLecon);
        } catch (DatabaseException $e) {
            log_message('error', 'Erreur getLeconById : ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère toutes les leçons d'un thème, triées par ordre de création.
     *
     * Cette méthode effectue une requête sur la table `lecon` et retourne
     * les leçons du thème donné sous forme d'un tableau d'objets `Lecon`,
     * triées par le champ `id` en ordre croissant. Seuls les champs `id` et
     * `titre` sont récupérés : le contenu n'est pas chargé, car cette liste
     * sert uniquement au sommaire et à la navigation entre les leçons.
     * En cas d'erreur (ex. problème de connexion ou de requête), un message
     * d'erreur est logué et un tableau vide est retourné.
     *
     * @param int $idTheme Identifiant du thème dont on veut les leçons.
     *
     * @return Lecon[] Tableau d'objets `Lecon` (id et titre uniquement), vide si aucune leçon ou en cas d'erreur.
     *
     * @since 1.0.0
     * @author Raphael BIERLAIRE
     */
    public function getLeconsByTheme(int $idTheme): array
    {
        try {
            // Requête limitée à id et titre, ordonnée par le champ id en ASC
            return $this->select('id, titre')
                ->where('id_theme', $idTheme)
                ->orderBy('id', 'ASC')
                ->findAll();
        } catch (DatabaseException $e) {
            log_message('error', "Erreur getLeconsByTheme (thème {id}): {message}", [
                'id'      => $idTheme,
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }
}