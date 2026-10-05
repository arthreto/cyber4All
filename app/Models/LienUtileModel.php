<?php

namespace App\Models;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;
use App\Entities\LienUtile;

/**
 * Ce model représente la gestion des LienUtile de la table lien_utile
 * @author Benjamin Lorieul
 * @since 1.0.0
 * @package Cyber4all
 */
class LienUtileModel extends Model
{
    protected $table = 'lien_utile';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['titre', 'description', 'lien'];

    protected $returnType = \App\Entities\LienUtile::class;

    /**
     * Récupère tous les liens utiles enregistrés dans la base de données.
     *
     * Cette méthode effectue une requête sur la table `lien_utile` et retourne
     * un tableau d'objets `LienUtile` représentant tous les enregistrements.
     * Si aucun lien n'est présent ou si une erreur survient (ex. problème de
     * connexion à la base), un message d'erreur est logué et un tableau vide est retourné.
     *
     * @return LienUtile[] Tableau d'objets `LienUtile`, ou vide si aucun enregistrement.
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function getAllLiensUtiles(): array
    {
        try {
            return $this->findAll();
        } catch (DatabaseException $e) {
            log_message('error', 'getAllLiensUtiles : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Met à jour un lien utile existant dans la base de données.
     *
     * Cette méthode reçoit un objet `LienUtile` contenant les nouvelles valeurs
     * et tente de mettre à jour l'enregistrement correspondant à son identifiant (`id`)
     * dans la table `lien_utile`. En cas de succès, elle retourne `true`.
     * Si une erreur survient (ex. problème de réflexion ou de base de données),
     * un message d'erreur est logué et la méthode retourne `false`.
     *
     * @param LienUtile $lienUtile Objet représentant le lien utile avec les nouvelles données.
     * @return bool `true` si la mise à jour a réussi, `false` en cas d'échec.
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function modifierLienUtile(LienUtile $lienUtile): bool
    {
        try {
            return $this->update($lienUtile->id, $lienUtile);
        } catch (\ReflectionException $e) {
            log_message('error', 'modifierLienUtile : ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Insère un nouveau lien utile dans la base de données.
     *
     * Cette méthode reçoit un objet `LienUtile` et tente de l'insérer dans la table
     * `lien_utile`. En cas de succès, elle retourne l'identifiant généré pour le nouvel
     * enregistrement. Si une erreur survient (ex. problème de connexion ou contrainte
     * de la base), un message d'erreur est logué et la méthode retourne `false`.
     *
     * @param LienUtile $lienUtile Objet contenant les informations du lien utile à insérer.
     * @return int|bool Retourne l'ID du lien inséré si succès, `false` sinon.
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function ajoutLienUtile(LienUtile $lienUtile): int|bool
    {
        try {
            // requête
            return $this->insert($lienUtile);
        } catch (\ReflectionException $e) {
            log_message('error', 'ajoutLienUtile : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime un lien utile de la base de données.
     *
     * Cette méthode reçoit un objet `LienUtile` et tente de supprimer l'enregistrement
     * correspondant à son identifiant (`id`) dans la table `lien_utile`.
     * En cas de succès, elle retourne l'identifiant ou un indicateur de succès.
     * Si une erreur survient (ex. problème de connexion ou contrainte de clé étrangère),
     * un message d'erreur est logué et la méthode retourne `false`.
     *
     * @param LienUtile $lienUtile Objet représentant le lien utile à supprimer.
     * @return int|bool L'ID du lien supprimé si succès, ou `false` en cas d'échec.
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function supprimerLienUtile(LienUtile $lienUtile): int|bool
    {
        try {
            // requête
            return $this->delete($lienUtile->id);
        } catch (DatabaseException $e) {
            log_message('error', 'supprimerLienUtile : ' . $e->getMessage());
            return false;
        }
    }
}