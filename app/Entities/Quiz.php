<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Classe Quiz
 * Représente un quiz avec des questions
 *
 * @author Arthur
 * @since 2.0.0
 * @package Cyber4all
 */
class Quiz extends Entity
{
    /**
     * Définition des types des attributs
     */
    protected $casts = [
        'id'            => 'integer', // ID du thème
        'titre'           => 'string',  // Titre du thème
    ];

    /**
     * Valeurs par défaut des attributs
     */
    protected $attributes = [
        'id'            => null,      // Valeur initiale nulle
        'titre'           => null,
    ];
}
