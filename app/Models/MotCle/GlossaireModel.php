<?php

namespace App\Models\MotCle;

use App\Models\CategorieModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;

class GlossaireModel extends Model
{
    protected $table = 'mot_cle';
    protected $primaryKey = 'id';
    protected $allowedFields = ['mot', 'definition', 'definition_complete', 'statut'];
    protected $returnType = \App\Entities\MotCle::class;

    /**
     * Permet de récupérer le glossaire complet
     * @param string $recherche La recherche optionnelle
     * @return array tableau contenant tous les mots
     */
    public function getGlossaireComplet(string $recherche = ''): array
    {
        try {
            // Récupère les mots
            $builder = $this->select('mot_cle.*')
                ->join('mot_cle_categorie', 'mot_cle_categorie.id_mot_cle = mot_cle.id', 'left')
                ->join('categories', 'categories.id = mot_cle_categorie.id_categorie', 'left')
                ->where('mot_cle.statut', 1);  // Important : préfixer avec mot_cle.

            // Grouper les recherches si un terme est fourni
            if (!empty($recherche)) {
                $builder->groupStart()
                    ->like('mot_cle.mot', $recherche, 'both', null, true) // insensible à la casse
                    ->orLike('mot_cle.definition', $recherche, 'both', null, true)
                    ->orLike('mot_cle.definition_complete', $recherche, 'both', null, true)
                    ->orLike('categories.nom', $recherche, 'both', null, true)
                    ->groupEnd();
            }

            $mots = $builder->groupBy(['mot_cle.id', 'mot_cle.mot'])
                ->orderBy('mot_cle.mot COLLATE utf8mb4_unicode_ci', '', false)
                ->findAll();

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
            error_log('getGlossaireCompletObj : ' . $e->getMessage());
            return [];
        }
    }

    public function getGlossaireCategorie(int $idCat): array
    {
        try {
            // Récupère les mots
            $mots = $this
                ->select('mot_cle.*') // Obligatoire pour gérer le conflit des ids
                ->join('mot_cle_categorie', 'mot_cle_categorie.id_mot_cle = mot_cle.id', 'left')
                ->join('categories', 'categories.id = mot_cle_categorie.id_categorie', 'left')
                ->where('statut', 1)
                ->where('categories.id', $idCat)
                ->groupBy(['mot_cle.id', 'mot_cle.mot'])
                ->orderBy('mot_cle.mot COLLATE utf8mb4_unicode_ci', '', false) // trie avec les accents
                ->findAll();

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
            log_message('error', 'getGlossaireCompletObj : ' . $e->getMessage());
            return [];
        }
    }

    public function getGlossaireMot(int $idMot): array
    {
        try {
            // Récupère le mot avec son id
            $mots = $this
                ->select('mot_cle.*') // Obligatoire pour gérer le conflit des ids
                ->join('mot_cle_categorie', 'mot_cle_categorie.id_mot_cle = mot_cle.id', 'left')
                ->join('categories', 'categories.id = mot_cle_categorie.id_categorie', 'left')
                ->where('statut', 1)
                ->where('mot_cle.id', $idMot)
                ->findAll();

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
            error_log('getGlossaireCompletObj : ' . $e->getMessage());
            return [];
        }
    }

    public function getGlossairePaginate(int $perPage = 20, string $recherche = ''): array
    {
        try {
            // Récupère les mots
            $mots = $this->select('mot_cle.*') // Obligatoire pour gérer le conflit des ids
            ->join('mot_cle_categorie', 'mot_cle_categorie.id_mot_cle = mot_cle.id', 'left')
                ->join('categories', 'categories.id = mot_cle_categorie.id_categorie', 'left')
                ->where('statut', 1)
                ->groupStart()
                ->like('mot_cle.mot', $recherche, 'both', null, true) // insensible à la casse
                ->orLike('mot_cle.definition', $recherche, 'both', null, true)
                ->orLike('mot_cle.definition_complete', $recherche, 'both', null, true)
                ->orLike('categories.nom', $recherche, 'both', null, true)
                ->groupEnd()
                ->groupBy(['mot_cle.id', 'mot_cle.mot'])
                ->orderBy('mot_cle.mot COLLATE utf8mb4_unicode_ci', '', false) // trie avec les accents
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
            error_log('getGlossaireCompletObj : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * @param int $nbMots
     * @return array
     */
    public function getGlossaireRand(int $nbMots): array
    {
        try {
            // Récupère les mots
            $mots = $this->select('mot_cle.*') // Obligatoire pour gérer le conflit des ids
            ->join('mot_cle_categorie', 'mot_cle_categorie.id_mot_cle = mot_cle.id', 'left')
                ->join('categories', 'categories.id = mot_cle_categorie.id_categorie', 'left')
                ->where('statut', 1)
                ->groupBy('mot_cle.id')
                ->orderBy('RAND()')
                ->findAll($nbMots);

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
            error_log('getGlossaireRand : ' . $e->getMessage());
            return [];
        }
    }
}