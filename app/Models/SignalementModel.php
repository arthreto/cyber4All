<?php

namespace App\Models;

use App\Entities\Signalement;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;

class SignalementModel extends Model
{
    protected $table = 'mot_cle_signalement';
    protected $primaryKey = 'id_signalement';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['id_mot_cle', 'motif', 'id_utilisateur'];
    protected $returnType = \App\Entities\Signalement::class;

    /**
     * Signale un mot-clé par un utilisateur.
     *
     * Insère un nouveau signalement dans la table `mot_cle_signalement`.
     * Si le signalement existe déjà (doublon), retourne -1.
     *
     * @param Signalement $signalement Objet contenant id_mot_cle, id_utilisateur et motif.
     * @return int|bool ID du nouveau signalement si succès, -1 si doublon, false si erreur.
     *
     * @since 1.0.0
     * @author Benjamin
     */
    public function signalerMotCle(Signalement $signalement): bool|int
    {
        try {
            return $this->insert([
                'id_mot_cle' => $signalement->id_mot_cle,
                'motif' => $signalement->motif,
                'id_utilisateur' => $signalement->id_utilisateur
            ]);
        } catch (DatabaseException $e) {
            if ($e->getCode() == 1062) {
                log_message('error', 'Signalement en doublon détecté : ' . $e->getMessage());
                return -1;
            }

            log_message('error', 'Erreur lors de signalerMotCle : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime un signalement par son identifiant.
     *
     * @param int $signalementId ID du signalement à supprimer.
     * @return bool|int true si suppression réussie, false sinon.
     *
     * @since 1.0.0
     * @author Benjamin
     */
    public function supprimerSignalement(int $signalementId): bool|int
    {
        try {
            return $this->delete($signalementId);
        } catch (DatabaseException $e) {
            log_message('error', 'Erreur lors de supprimerSignalement : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère tous les signalements d’un utilisateur.
     *
     * @param int $idUtilisateur ID de l’utilisateur.
     * @return Signalement[] Tableau de signalements, ou vide si aucun.
     *
     * @since 1.0.0
     * @author Benjamin
     */
    public function getSignalementUtilisateur(int $idUtilisateur): array
    {
        try {
            return $this->where('id_utilisateur', $idUtilisateur)->findAll();
        } catch (DatabaseException $e) {
            log_message('error', 'Erreur lors de getSignalementUtilisateur : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les IDs des mots-clés signalés par un utilisateur.
     *
     * @param int $idUtilisateur ID de l’utilisateur.
     * @return int[] Tableau des IDs des mots-clés signalés, ou vide si aucun.
     *
     * @since 1.0.0
     * @author Benjamin
     */
    public function getSignalementIdsUtilisateur(int $idUtilisateur): array
    {
        try {
            return $this->where('id_utilisateur', $idUtilisateur)->findColumn('id_mot_cle');
        } catch (DatabaseException $e) {
            log_message('error', 'Erreur lors de getSignalementIdsUtilisateur : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère tous les signalements.
     * Chaque signalement est enrichi avec le mot associé.
     *
     * @return array Tableau d’objets signalements avec le champ `mot` ajouté.
     *
     * @since 1.0.0
     * @author Benjamin
     */
    public function getSignalements(): array
    {
        try {
            $results = $this->findAll();
            $signalements = [];
            foreach ($results as $signalement) {
                $signalement->mot = $this->getMotSignalement($signalement->id_mot_cle);
                $signalements[] = $signalement;
            }
            return $signalements;
        } catch (DatabaseException $e) {
            log_message('error', 'Erreur lors de getSignalements : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère un signalement par son ID.
     *
     * @param int $idSignalement ID du signalement.
     * @return Signalement|null L’objet Signalement, ou null si non trouvé ou erreur.
     *
     * @since 1.0.0
     * @author Benjamin
     */
    public function getSignalementById(int $idSignalement): ?Signalement
    {
        try {
            return $this->find($idSignalement);
        } catch (DatabaseException $e) {
            log_message('error', 'Erreur lors de getSignalementById : ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère le nombre total de signalements.
     *
     * @return int Nombre total de signalements.
     *
     * @since 1.0.0
     * @author Benjamin
     */
    public function getNbSignalements(): int
    {
        try {
            return $this->countAllResults();
        } catch (DatabaseException $e) {
            log_message('error', 'Erreur lors de getNbSignalements : ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Récupère le mot associé à un signalement.
     *
     * @param int $idMotCle ID du mot-clé.
     * @return string Mot associé, ou chaîne vide si non trouvé ou erreur.
     *
     * @since 1.0.0
     * @author Benjamin
     */
    private function getMotSignalement(int $idMotCle): string
    {
        try {
            $result = $this->select('mot_cle.mot')
                ->join('mot_cle', 'mot_cle.id = mot_cle_signalement.id_mot_cle')
                ->where('mot_cle.id', $idMotCle)
                ->asArray()
                ->first();

            return $result['mot'] ?? '';
        } catch (DatabaseException $e) {
            log_message('error', 'Erreur lors de getMotSignalement : ' . $e->getMessage());
            return '';
        }
    }
}