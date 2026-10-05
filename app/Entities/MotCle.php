<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use App\Entities\Categorie;

/**
 * Classe MotCle
 * Représente un mot-clé avec ses propriétés et relations.
 *
 * @author Benjamin Lorieul
 * @since 1.0.0
 * @package Cyber4all
 * /
 */
class MotCle extends Entity
{
    /**
     * Les champs qui doivent être traités comme des dates
     */
    protected $dates = ['date_ajout', 'date_modif'];

    /**
     * Les attributs par défaut de l'entité
     */
    protected $attributes = [
        'id'                  => null,   // Identifiant unique du mot-clé
        'mot'                 => null,   // Le mot lui-même
        'definition'          => null,   // Définition courte
        'definition_complete' => null,   // Définition complète
        'id_auteur'           => null,   // ID de l'auteur du mot
        'date_ajout'          => null,   // Date de création
        'date_modif'          => null,   // Date de dernière modification
        'statut'              => 0,      // Statut (0 par défaut)

        // Relations objets
        'categories'          => [],     // Liste des catégories associées
        'decision'            => 0,      // Champ décisionnel
    ];

    /**
     * Types de données pour chaque attribut
     */
    protected $casts = [
        'id'                  => 'integer',
        'mot'                 => 'string',
        'definition'          => 'string',
        'definition_complete' => 'string',
        'id_auteur'           => 'integer',
        'date_ajout'          => '?datetime', // null car géré par la bdd
        'date_modif'          => '?datetime', // null car géré par la bdd
        'statut'              => 'integer',
    ];

    /**
     * Setter pour la relation avec les catégories
     *
     * @param array $categories Liste des IDs des catégories
     * @return self
     */
    public function setCategories(array $categories): self
    {
        $this->attributes['categories'] = $categories;
        return $this;
    }

    /**
     * Getter pour récupérer les catégories liées au mot-clé
     *
     * @return array Liste des IDs des catégories
     */
    public function getCategories(): array
    {
        return $this->attributes['categories'] ?? [];
    }
}
