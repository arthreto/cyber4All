<?php

namespace App\Models;

use App\Entities\MotCle;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;
use App\Entities\Categorie;

/**
 * Ce model représente la gestion des Categorie de la table categories
 * @author Benjamin Lorieul
 * @since 1.0.0
 * @package Cyber4all
 */
class CategorieModel extends Model
{
    protected $table = 'categories';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['nom', 'theme'];
    protected $returnType = \App\Entities\Categorie::class;

    /**
     * insertCategorie permet de créer une nouvelle catégorie dans la table categorie
     * @param Categorie $categorie
     * @return int|false retourne l'id de la catégorie ajoutée si ajout réussi, sinon false
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function insertCategorie(Categorie $categorie): int|false
    {
        try {
            $data = [
                'nom' => $categorie->nom,
                'theme' => $categorie->theme
            ];
            return $this->insert($data);
        } catch (DatabaseException $e) {
            log_message('error', "Erreur insertCategorie : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime une catégorie de la base de données en fonction de son identifiant.
     *
     * Cette méthode tente de supprimer l'enregistrement correspondant à l'ID fourni
     * dans la table des catégories. En cas d'erreur lors de l'opération (ex. contrainte
     * de clé étrangère ou problème de connexion à la base), l'exception est capturée
     * et un message d'erreur est logué.
     *
     * @param int $id Identifiant unique de la catégorie à supprimer.
     * @return bool Retourne true si la suppression a réussi, false sinon.
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function deleteCategorieById(int $id): bool
    {
        try {
            return $this->delete($id);
        } catch (DatabaseException $e) {
            log_message('error', 'deleteCategorieById : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour les informations d'une catégorie dans la base de données.
     *
     * Cette méthode reçoit une instance de la classe Categorie contenant les nouvelles
     * valeurs pour les champs `nom` et `theme`. Elle tente de mettre à jour l'enregistrement
     * correspondant à l'ID de la catégorie dans la table `categorie`. En cas d'erreur
     * lors de l'opération (ex. problème de connexion ou contrainte de la base), l'exception
     * est capturée et un message d'erreur est logué.
     *
     * @param Categorie $categorie Objet contenant l'ID et les nouvelles valeurs de la catégorie.
     * @return bool Retourne true si la mise à jour a réussi, false sinon.
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function updateCategorieByID(Categorie $categorie): bool
    {
        try {
            $data = [
                'nom' => $categorie->nom,
                'theme' => $categorie->theme
            ];
            return $this->update($categorie->id, $data);
        } catch (DatabaseException $e) {
            log_message('error', 'updateCategorieByID : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère toutes les catégories de la base de données triées par ordre alphabétique.
     *
     * Cette méthode effectue une requête sur la table `categorie` et retourne
     * toutes les catégories sous forme d'un tableau d'objets `Categorie`, triées
     * par le champ `nom` en ordre croissant. En cas d'erreur (ex. problème de
     * connexion ou de requête), un message d'erreur est logué et un tableau vide
     * est retourné.
     *
     * @return Categorie[] Tableau d'objets `Categorie` représentant toutes les catégories.
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function getAllCategories(): array
    {
        // Requête ordonnée par le champ mot en ASC
        try {
            return $this->orderBy('nom', 'ASC')->findAll();
        } catch (DatabaseException $e) {
            error_log("Erreur getAllCategories : " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère une catégorie à partir de son identifiant unique.
     *
     * Cette méthode effectue une requête sur la table `categorie` et retourne
     * l'objet `Categorie` correspondant à l'ID fourni. Si aucun enregistrement
     * n'est trouvé ou si une erreur survient (ex. problème de connexion à la base),
     * la méthode retourne `null`.
     *
     * @param int $id Identifiant unique de la catégorie à récupérer.
     * @return Categorie|null L'objet `Categorie` correspondant à l'ID, ou `null` si inexistant ou en cas d'erreur.
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function getCategorieById(int $id): ?object
    {
        // Requête par ID.
        // Find permet de récupérer un enregistrement dont la clé primaire correspond à la valeur passée en paramètre
        try {
            return $this->find($id);
        } catch (DatabaseException $e) {
            error_log("Erreur getMotcleById : " . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère toutes les catégories associées à un mot-clé donné.
     *
     * Cette méthode effectue une jointure entre la table `categories` et la table
     * pivot `mot_cle_categorie` pour récupérer toutes les catégories liées au mot
     * identifié par l'ID fourni. Les résultats sont triés par le nom de la catégorie
     * en ordre alphabétique. En cas d'erreur lors de la requête, un message d'erreur
     * est logué et un tableau vide est retourné.
     *
     * @param int $id Identifiant du mot-clé dont on souhaite récupérer les catégories.
     * @return Categorie[] Tableau d'objets `Categorie` correspondant aux catégories liées au mot.
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function getCategoriesMotcle(int $id): array
    {
        try {
            $results = $this
                ->join('mot_cle_categorie', 'mot_cle_categorie.id_categorie = categories.id', 'INNER')
                ->where('mot_cle_categorie.id_mot_cle', $id)
                ->orderBy('categories.nom', 'ASC')
                ->findALl();
            $categories = [];
            if ($results) {


                foreach ($results as $result):
                    $categories[] = $result;
                endforeach;
            }
            return $categories;
        } catch (DatabaseException $e) {
            error_log("Erreur getCategoriesMotcle : " . $e->getMessage());
            return [];
        }
    }


    /**
     * Récupère les catégories associées à plusieurs mots-clés.
     *
     * Cette méthode effectue une jointure entre la table `categories` et la table
     * pivot `mot_cle_categorie` pour obtenir toutes les catégories liées aux mots-clés
     * dont les identifiants sont fournis dans le tableau `$motIds`. Les résultats
     * sont triés par le nom de la catégorie. En cas d'erreur lors de la requête,
     * un message d'erreur est logué et un tableau vide est retourné.
     *
     * @param int[] $motIds Tableau d'identifiants des mots-clés.
     * @return array Tableau d'objets contenant `id_mot_cle`, `id` et `nom` des catégories.
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function getCategoriesByMotIds(array $motIds): array
    {
        try {
            return $this->select('
                mot_cle_categorie.id_mot_cle,
                categories.id,
                categories.nom
            ')
                ->join('mot_cle_categorie', 'categories.id = mot_cle_categorie.id_categorie', 'inner')
                ->whereIn('mot_cle_categorie.id_mot_cle', $motIds)
                ->orderBy('categories.nom')
                ->findAll();
        } catch (DatabaseException $e) {
            error_log("Erreur getAllCategoriesByMotIds : " . $e->getMessage());
            return [];
        }
    }

}