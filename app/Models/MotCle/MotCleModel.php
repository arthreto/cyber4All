<?php

namespace App\Models\MotCle;

use App\Entities\MotCle;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;

/**
 * Ce model représente la gestion des MotCle de la table mot_cle
 * @author Benjamin Lorieul
 * @since 1.0.0
 * @package Cyber4all
 */
class MotCleModel extends Model
{
    protected $table = 'mot_cle';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['id', 'mot', 'definition', 'definition_complete', 'id_auteur', 'statut'];
    protected $returnType = \App\Entities\MotCle::class;

    /**
     * deleteMotCleById permet de supprimer un MotCle par son id dans la table mot_cle.
     * @param int $id L'identifiant du mot
     * @return bool True si bien supprimer sinon false
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function deleteMotCleById(int $id): bool
    {
        try {
            return $this->delete($id);
        } catch (DatabaseException $e) {
            error_log("Erreur deleteMotCleById : " . $e->getMessage());
            return false;
        }
    }

    /**
     * updateMotCle permet de modifier un mot dans la table MotCle
     * @param MotCle $MotCle Nouveau MotCle
     * @return bool|int vraie si modification réussie -1 si le mot existe déjà étant écrit par l'auteur
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function updateMotCle(MotCle $MotCle): bool|int
    {
        try {
            $data = [
                'mot' => $MotCle->mot,
                'definition' => $MotCle->definition,
                'definition_complete' => $MotCle->definition_complete,

            ];
            return $this->update($MotCle->id, $data);
        } catch (DatabaseException $e) {
            error_log("Erreur updateMotCleByID : " . $e->getMessage());
            if ($e->getCode() === 1062) {
                // Contrainte UNIQUE violée
                return -1;
            }
            return false;
        }
    }

    /**
     * insertMotCle permet d'insérer un nouveau mot dans la table MotCle
     * @param MotCle $motCle nouveau mot à ajouter
     * @return int|false retourne l'id du mot ajouté si ajout réussi, -1 si l'auteur a déjà ajouté ce mot sinon false
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function insertMotCle(MotCle $motCle): int|false
    {
        try {
            $dataMot = [
                'mot' => $motCle->mot,
                'definition' => $motCle->definition,
                'definition_complete' => $motCle->definition_complete,
                'id_auteur' => $motCle->id_auteur,
                'statut' => $motCle->statut
            ];

            return $this->insert($dataMot);
        } catch (DatabaseException $e) {
            if ($e->getCode() === 1062) {
                // Contrainte UNIQUE violée
                return -1;
            }
            error_log("Erreur insertMotCle : " . $e->getMessage());
            return false;
        }
    }

    /**
     * getAllMotCle permet de récupérer tous les mots de la table MotCle
     * par ordre alphabétique
     * @return array Tableau d'objets (vide si aucun résultat)
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function getAllMotCle(): array
    {
        // Requête ordonnée par le champ mot en ASC
        try {
            return $this->orderBy('mot', 'ASC')->findAll();
        } catch (DatabaseException $e) {
            error_log("Erreur getAllMotCle : " . $e->getMessage());
            return [];
        }
    }

    public function getMotClePaginated(int $perPage = 10, ?int $statut = null): array
    {
        try {
            if ($statut !== null) {
                $this->where('statut', $statut);
            }
            return $this->orderBy('mot', 'ASC')->paginate($perPage);
        } catch (DatabaseException $e) {
            error_log("Erreur getMotClePaginated : " . $e->getMessage());
            return [];
        }
    }

    /**
     * getMotClePaginatedOrderBy permet de récupérer tous les mots dans l'ordre passé en paramètre
     * @param int $perPage
     * @param int|null $statut
     * @return array
     */
    public function getMotClePaginatedOrderBy(?string $ordre = "", ?string $recherche = "", int $perPage = 10, ?int $statut = null): array
    {
        try {
            // attribue à l'ordre codé l'ordre réel en bdd
            if (!empty($ordre)):
                $champEtOrdre = $this->setchampEtOrdre($ordre);
                $champ = $champEtOrdre[0];
                $ordre = $champEtOrdre[1];
                $champ = in_array($champ, $this->allowedFields) ? $champ : "date_ajout";
            else:
                $champ = 'date_ajout';
                $ordre = 'ASC';
            endif;

            $recherche = trim($recherche);

            // vérifie que le champ fait bien partie des champs accessibles sur la table
            $this
                ->where('mot_cle.statut !=', 2)
                ->groupStart()
                ->like('mot_cle.mot', $recherche, 'both', null, true)
                ->orLike('mot_cle.date_ajout', $recherche, 'both', null, true)
                ->orLike('mot_cle.date_modif', $recherche, 'both', null, true)
                ->orLike('mot_cle.id', $recherche, 'both', null, true)
                ->groupEnd();

            // récupère le filtre du statut
            if ($statut !== null) {
                $this->where('mot_cle.statut', $statut);
            }
            if ($champ === 'mot') {
                // trie avec les accents
                $this->orderBy('mot_cle.mot COLLATE utf8mb4_unicode_ci', $ordre, false);
            } else {
                $this->orderBy($champ, $ordre);
            }
            return $this
                ->paginate($perPage); // paginer par le nombre de mot à afficher

        } catch (DatabaseException $e) {
            error_log("Erreur getMotClePaginatedOrderBy : " . $e->getMessage());
            return [];
        }
    }

    /**
     * getMotClePaginatedOrderBy permet de récupérer tous les mots dans l'ordre passé en paramètre
     * @param int $perPage
     * @param int|null $statut
     * @return array
     */
    public function getMotCleValidationPaginatedOrderBy(?string $ordre = "", ?string $recherche = "", int $perPage = 10, ?int $statut = null): array
    {
        try {
            // attribue à l'ordre codé l'ordre réel en bdd
            if (!empty($ordre)):
                $champEtOrdre = $this->setchampEtOrdre($ordre);
                $champ = $champEtOrdre[0];
                $ordre = $champEtOrdre[1];
                $champ = in_array($champ, $this->allowedFields) ? $champ : "date_ajout";
            else:
                $champ = 'date_ajout';
                $ordre = 'ASC';
            endif;
            $recherche = trim($recherche);

            // vérifie que le champ fait bien partie des champs accessibles sur la table
            $champ = in_array($champ, $this->allowedFields) ? $champ : "date_ajout";
            $this->join('mot_cle_validation', 'mot_cle.id = mot_cle_validation.id_mot', 'inner')
                ->where('mot_cle_validation.decision', null)
                ->groupStart()
                ->like('mot_cle.mot', $recherche)
                ->orLike('mot_cle.date_ajout', $recherche)
                ->orLike('mot_cle.date_modif', $recherche)
                ->orLike('mot_cle.id', $recherche)
                ->groupEnd();

            // récupère le filtre du statut
            if ($statut !== null) {
                $this->where('mot_cle.statut', $statut);
            }
            if ($champ === 'mot') {
                // trie avec les accents
                $this->orderBy('mot_cle.mot COLLATE utf8mb4_unicode_ci', $ordre, false);
            } else {
                $this->orderBy($champ, $ordre);
            }
            return $this
                // trie avec les accents
                ->paginate($perPage); // paginer par le nombre de mot à afficher

        } catch (DatabaseException $e) {
            error_log("Erreur getMotClePaginatedOrderBy : " . $e->getMessage());
            return [];
        }
    }

    /**
     * getMotCleById permet de récupérer un mot par son id dans la table MotCle
     * @param int $id L'identifiant du mot-cléb
     * @return object|null Null si aucun résultat. Objet si résultat.
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function getMotCleById(int $id): ?MotCle
    {
        // Requête par ID.
        // Find permet de récupérer un enregistrement dont la clé primaire correspond à la valeur passée en paramètre
        try {
            return $this->find($id);
        } catch (DatabaseException $e) {
            error_log("Erreur getMotCleById : " . $e->getMessage());
            return null;
        }
    }

    /**
     * updateStatut permet de changer le statut de publication d'un mot par son id
     * @param MotCle $motCle motCle a publier ou dépublier
     * @return bool
     * @throws \ReflectionException
     */
    public function switchStatut(MotCle $motCle): bool
    {
        try {
            // si le statut vaut 1 alors, on le change à 0.
            return $this->update($motCle->id, ['statut' => !$motCle->statut]);
        } catch (DatabaseException $e) {
            error_log("Erreur switchStatut : " . $e->getMessage());
            return false;
        }
    }

    /**
     * @return int Le nombre total de termes publié
     */
    public function getNbTotalGlossaire(): int
    {
        try {
            return (int)$this->where('statut', 1)
                ->countAllResults();
        } catch (DatabaseException $e) {
            error_log('Erreur lors de getNbTotalGlossaire : ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * getNbSignalementMot permet de récupérer le nombre de signalements d'un mot
     * @param int $idMotCle
     * @return int
     */
    public function getNbSignalementsMot(int $idMotCle): int
    {
        try {
            return (int)$this->db->table('mot_cle_signalement')
                ->where('id_mot_cle', $idMotCle)
                ->countAllResults();
        } catch (DatabaseException $e) {
            error_log('Erreur lors de getNbSignalementsMot : ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Permet d'attribuer le bon champ avec le bon ordre avec l'abreviation passer en parametre
     * @param string $ordre
     * @return string[] [ordre, champ]
     */
    private function setChampEtOrdre(string $ordre): array
    {
        // attribue à l'ordre codé l'ordre réel en bdd
        switch ($ordre) {
            case 'ida':
                $champ = 'id';
                $ordre = 'ASC';
                break;
            case 'idd':
                $champ = 'id';
                $ordre = 'DESC';
                break;
            case 'pua':
                $champ = 'statut';
                $ordre = 'ASC';
                break;
            case 'pud':
                $champ = 'statut';
                $ordre = 'DESC';
                break;
            case 'moa':
                $champ = 'mot';
                $ordre = 'ASC';
                break;
            case 'mod':
                $champ = 'mot';
                $ordre = 'DESC';
                break;
            case 'dpa':
                $champ = 'date_ajout';
                $ordre = 'ASC';
                break;
            case 'dpd':
                $champ = 'date_ajout';
                $ordre = 'DESC';
                break;
            case 'dma':
                $champ = 'date_modification';
                $ordre = 'ASC';
                break;
            case 'dmd':
                $champ = 'date_modification';
                $ordre = 'DESC';
                break;
            case 'iaa':
                $champ = 'id_auteur';
                $ordre = 'ASC';
                break;
            case 'iad':
                $champ = 'id_auteur';
                $ordre = 'DESC';
                break;
            default:
                $champ = 'date_ajout';
                $ordre = 'DESC';
        }
        // vérifie que le champ fait bien partie des champs accessibles sur la table
        $champ = in_array($champ, $this->allowedFields) ? $champ : "date_ajout";
        return [$champ, $ordre];
    }


}