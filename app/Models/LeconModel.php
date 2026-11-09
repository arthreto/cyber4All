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
    protected $allowedFields    = ['titre', 'contenu', 'id_theme', 'id_auteur', 'id_auteur_modif'];

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

    /**
     * Récupère toutes les leçons, triées par thème puis par ordre de création.
     *
     * Cette méthode effectue une requête sur la table `lecon` et retourne
     * toutes les leçons sous forme d'un tableau d'objets `Lecon`, triées
     * par le champ `id_theme` puis par le champ `id` en ordre croissant.
     * En cas d'erreur (ex. problème de connexion ou de requête), un message
     * d'erreur est logué et un tableau vide est retourné.
     *
     * @return Lecon[] Tableau d'objets `Lecon`, vide en cas d'erreur.
     *
     * @since 1.0.0
     * @author Raphael BIERLAIRE
     */
    public function getAllLecons(): array
    {
        try {
            // Requête ordonnée par thème, puis par le champ id en ASC
            $lecons = $this->select('lecon.*, theme.nom AS nom_theme')
                ->join('theme', 'theme.id = lecon.id_theme')
                ->orderBy('lecon.id_theme', 'ASC')
                ->orderBy('lecon.id', 'ASC')
                ->findAll();
        } catch (DatabaseException $e) {
            log_message('error', 'Erreur getAllLecons : ' . $e->getMessage());
            return [];
        }

        $utilisateurModel = new UtilisateurModel();

        // Pseudos déjà trouvés (auteurs et modificateurs), sous la forme [id => pseudo]
        $pseudos = [];

        foreach ($lecons as $lecon) {
            log_message('error', 'id_auteur: ' . $lecon->id_auteur);
            $lecon->user_auteur       = $utilisateurModel->getUtilisateurById($lecon->id_auteur);
            $lecon->user_modificateur = isset($lecon->id_auteur_modif) ? $utilisateurModel->getUtilisateurById($lecon->id_auteur_modif) : null;
        }

        return $lecons;
    }

    /**
     * Crée une nouvelle leçon.
     *
     * Cette méthode insère une ligne dans la table `lecon` et retourne
     * l'identifiant de la leçon créée. Les dates d'ajout et de modification
     * sont remplies automatiquement. En cas d'erreur (ex. problème de connexion
     * ou de requête), un message d'erreur est logué et false est retourné.
     *
     * @param array $data Données de la leçon (id_theme, titre, contenu, id_auteur).
     *
     * @return int|false Identifiant de la leçon créée, ou false en cas d'erreur.
     *
     * @since 1.0.0
     * @author Raphael BIERLAIRE
     */
    public function creerLecon(array $data): int|false
    {
        try {
            $idLecon = $this->insert($data);

            return $idLecon === false ? false : (int) $idLecon;
        } catch (DatabaseException $e) {
            log_message('error', 'Erreur creerLecon : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Modifie une leçon existante.
     *
     * Cette méthode met à jour la ligne de la table `lecon` correspondant
     * à l'identifiant donné. La date de modification est remplie automatiquement.
     * En cas d'erreur (ex. problème de connexion ou de requête), un message
     * d'erreur est logué et false est retourné.
     *
     * @param array $data    Données à modifier (id_theme, titre, contenu, id_auteur_modif).
     * @param int   $idLecon Identifiant de la leçon à modifier.
     *
     * @return bool Vrai si la modification a réussi, faux sinon.
     *
     * @since 1.0.0
     * @author Raphael BIERLAIRE
     */
    public function updateLecon(array $data, int $idLecon): bool
    {
        try {
            return $this->update($idLecon, $data);
        } catch (DatabaseException $e) {
            log_message('error', 'Erreur updateLecon (leçon {id}) : {message}', [
                'id'      => $idLecon,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Supprime une leçon.
     *
     * Cette méthode supprime la ligne de la table `lecon` correspondant
     * à l'identifiant donné. En cas d'erreur (ex. problème de connexion ou de
     * requête), ou si aucune ligne n'a été supprimée, false est retourné.
     *
     * @param int $idLecon Identifiant de la leçon à supprimer.
     *
     * @return bool Vrai si la leçon a été supprimée, faux sinon.
     *
     * @since 1.0.0
     * @author Raphael BIERLAIRE
     */
    public function supprimerLecon(int $idLecon): bool
    {
        try {
            $this->delete($idLecon);

            // Vérifie qu'une ligne a réellement été supprimée
            return $this->db->affectedRows() > 0;
        } catch (DatabaseException $e) {
            log_message('error', 'Erreur supprimerLecon (leçon {id}) : {message}', [
                'id'      => $idLecon,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}