<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Cette classe représente un vote
 *
 * @author Benjamin Lorieul
 * @since 1.0.0
 * @package Cyber4all
 */
class Vote extends Entity
{
    /**
     * Les champs qui doivent être traités comme des dates
     */
    protected $dates = ['date_creation'];

    /**
     * Les attributs par défaut de l'entité
     */
    protected $attributes = [
        'id_vote'           => null,
        'id_mot_cle'        => null,
        'id_utilisateur'    => null,
        'date_creation'     => null,
    ];

    /**
     * Types de données pour chaque attribut
     */
    protected $casts = [
        'id_vote'           => 'integer',
        'id_mot_cle'        => 'integer',
        'id_utilisateur'    => 'integer',
        'date_creation'     => 'datetime',
    ];
}