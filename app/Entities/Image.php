<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Classe Image
 * Représente une image associée à un MotCle.
 * Un MotCle peut avoir plusieurs images.
 *
 * @author Benjamin
 * @since 1.0.0
 * @package Cyber4all
 */
class Image extends Entity
{
    /**
     * Définition des types des attributs
     */
    protected $casts = [
        'id'            => 'integer',  // ID auto-incrémenté
        'mot_id'        => 'integer',  // ID du mot-clé associé
        'image'         => 'string',   // Nom ou chemin du fichier
        'alt'           => 'string',   // Texte alternatif (nullable)
        'legend'        => 'string',   // Légende de l'image
    ];

    /**
     * Valeurs par défaut des attributs
     */
    protected $attributes = [
        'id'            => null,
        'mot_id'        => null,
        'image'         => null,
        'alt'           => null,
        'legend'        => null,
    ];
}
