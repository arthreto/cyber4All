<?php

namespace App\Models\MotCle;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;

class MotCleCategorieModel extends Model
{
    protected $table = 'mot_cle';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['mot', 'definition', 'definition_complete', 'id_auteur', 'date_ajout'];

    /**
     * Associe plusieurs catégories à un mot-clé donné.
     *
     * Cette méthode insère des enregistrements dans la table pivot `mot_cle_categorie`
     * pour relier chaque catégorie (identifiée par son ID) au mot-clé spécifié.
     * Si toutes les insertions réussissent, la méthode retourne `true`.
     * En cas d'erreur (ex. contrainte de clé étrangère ou problème de base),
     * un message d'erreur est logué et la méthode retourne `false`.
     *
     * @param int[] $idCategories Tableau contenant les identifiants des catégories à associer.
     * @param int $idMotCle Identifiant du mot-clé auquel les catégories seront associées.
     * @return bool `true` si toutes les associations ont été insérées avec succès, `false` sinon.
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function insertCategoriesMotCle(array $idCategories, int $idMotCle): bool
    {
        try {
            foreach ($idCategories as $idCategorie) {
                $this->db->table('mot_cle_categorie')
                    ->insert([
                        'id_mot_cle' => $idMotCle,
                        'id_categorie' => $idCategorie
                    ]);
            }
            return true;
        } catch (DatabaseException $e) {
            log_message('error', 'insertCategoriesMotCle : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime les associations entre un mot-clé et plusieurs catégories.
     *
     * Cette méthode parcourt le tableau `$idCategories` et supprime pour chaque
     * catégorie l'enregistrement correspondant dans la table pivot `mot_cle_categorie`
     * pour le mot-clé identifié par `$idMotCle`. Si toutes les suppressions réussissent,
     * la méthode retourne `true`. En cas d'erreur, un message est logué et la méthode
     * retourne `false`.
     *
     * @param int[] $idCategories Tableau contenant les identifiants des catégories à dissocier.
     * @param int $idMotCle Identifiant du mot-clé dont les associations seront supprimées.
     * @return bool `true` si toutes les suppressions ont réussi, `false` sinon.
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function deleteCategoriesMotCle(array $idCategories, int $idMotCle): bool
    {
        try {
            foreach ($idCategories as $idCategorie) {
                $this->db->table('mot_cle_categorie')
                    ->where('id_categorie', $idCategorie)
                    ->where('id_mot_cle', $idMotCle)
                    ->delete();
            }
            return true;
        } catch (DatabaseException $e) {
            log_message('error', 'deleteCategoriesMotCle : ' . $e->getMessage());
            return false;
        }
    }
}