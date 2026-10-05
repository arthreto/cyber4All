<?php

namespace App\Models\MotCle;

use App\Entities\Utilisateur;
use App\Models\CategorieModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;
use App\Entities\MotCle;

class MotCleValidationModel extends Model
{
    protected $table = 'mot_cle_validation';
    protected $primaryKey = 'id_validation';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['id_mot', 'id_utilisateur', 'date_demande', 'decision', 'date_decision', 'id_admin'];

    /**
     *
     * @param int $idMotCle
     * @param int|null $decision valider ou refuser
     * @return bool
     * @throws \ReflectionException
     */
    public function prendreLaDecision(int $idMotCle, ?string $decision = 'refuser'): bool
    {
        try {
            // on s'assure que la demande existe
            $idDemande = $this->getIdDemande($idMotCle);
            if ($idDemande === null) {
                log_message('error', "[prendreLaDecision] Demande introuvable pour mot_id: {$idMotCle}");
                return false;
            }
            // on control la decision
            $decision = trim(strtolower($decision));

            // soit valider, soit refuser
            if (in_array($decision, ['valider', 'refuser'])) {
                return $this->update($idDemande, ['decision' => $decision,
                    'id_admin' => session()->get('utilisateur')->id ?? null
                ]);

            } else {
                return false;
            }
        } catch (DatabaseException $e) {
            log_message('error', $e->getMessage());
            return false;
        }
    }

    private function getIdDemande(int $idMotCle)
    {
        try {
            // si le statut vaut 1 alors, on le change à 0.
            //
            $row = $this->select('id_validation')
                ->where('id_mot', $idMotCle)
                ->first();
            return $row['id_validation'] ?? null;
        } catch (DatabaseException $e) {
            error_log("Erreur getIdDemande : " . $e->getMessage());
            return null;
        }
    }

    /**
     * getMotCleValidationContributeurPaginate permet de récupérer
     * toutes les suggestions d'ajout d'un utilisateur paginé
     * @param int $perPage Le nombre de mots à afficher par page
     * @param ?string $recherche Le terme recherché (optionnel)
     * @return array
     */
    public function getMotCleValidationContributeurPaginate(int $idContributeur, $perPage = 5, ?string $recherche = ''): array
    {
        try {
            // Récupère les mots
            $mots = $this->select('mot_cle.*') // Obligatoire pour gérer le conflit des ids
            ->join('mot_cle', 'mot_cle.id = mot_cle_validation.id_mot', 'inner')
                ->join('mot_cle_categorie', 'mot_cle_categorie.id_mot_cle = mot_cle.id', 'left')
                ->join('categories', 'categories.id = mot_cle_categorie.id_categorie', 'left')
                ->where('mot_cle_validation.id_utilisateur', $idContributeur)
                ->groupBy(['mot_cle.id', 'mot_cle.mot'])
                ->orderBy('mot_cle_validation.date_demande', 'DESC')
                ->asObject(\App\Entities\MotCle::class)
                ->paginate($perPage);

            if (empty($mots)) {
                return [];
            }

            // Récupères les IDs des mots
            $ids = array_map(fn($mot) => $mot->id, $mots);

            // Récupère les Catégories
            $catModel = new CategorieModel();
            $rows = $catModel->getCategoriesByMotIds($ids);

            // Indexation pour unir les catégories aux mots
            $catsByMot = [];

            foreach ($rows as $row) {
                $catsByMot[$row->id_mot_cle][] = $row; // pas besoin de créer une nouvelle Entity
            }

            // Hydratation
            foreach ($mots as $mot) {
                $mot->setCategories($catsByMot[$mot->id] ?? []);
            }

            return $mots;
        } catch (DatabaseException $e) {
            log_message('error', 'getMotCleValidationContributeurPaginate : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * getMotCleValidationContributeurAttentePaginate permet de récupérer
     * toutes les suggestions d'ajout en attente de validation d'un utilisateur paginé
     * @param int $perPage Le nombre de mots à afficher par page
     * @param ?string $recherche Le terme recherché (optionnel)
     * @return array
     */
    public function getMotCleValidationContributeurAttentePaginate(int $idContributeur, $perPage = 5, ?string $recherche = ''): array
    {
        try {
            // Récupère les mots
            $mots = $this->select('mot_cle.*') // Obligatoire pour gérer le conflit des ids
            ->join('mot_cle', 'mot_cle.id = mot_cle_validation.id_mot', 'inner')
                ->join('mot_cle_categorie', 'mot_cle_categorie.id_mot_cle = mot_cle.id', 'left')
                ->join('categories', 'categories.id = mot_cle_categorie.id_categorie', 'left')
                ->where('mot_cle_validation.id_utilisateur', $idContributeur)
                ->where('mot_cle.statut', 3)//est en attente
                ->groupBy(['mot_cle.id', 'mot_cle.mot'])
                ->orderBy('mot_cle_validation.date_demande', 'DESC')
                ->asObject(\App\Entities\MotCle::class)
                ->paginate($perPage);

            if (empty($mots)) {
                return [];
            }

            // Récupères les IDs des mots
            $ids = array_map(fn($mot) => $mot->id, $mots);

            // Récupère les Catégories
            $catModel = new CategorieModel();
            $rows = $catModel->getCategoriesByMotIds($ids);

            // Indexation pour unir les catégories aux mots
            $catsByMot = [];

            foreach ($rows as $row) {
                $catsByMot[$row->id_mot_cle][] = $row; // pas besoin de créer une nouvelle Entity
            }

            // Hydratation
            foreach ($mots as $mot) {
                $mot->setCategories($catsByMot[$mot->id] ?? []);
            }

            return $mots;
        } catch (DatabaseException $e) {
            log_message('error', 'getMotCleValidationContributeurAttentePaginate : ' . $e->getMessage());
            return [];
        }
    }
}