<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Classe ExtraitForm
 * Représente le formulaire d'un extrait (administration de l'Espace Traçabilité)
 *
 *
 * @author Arthur
 * @since 2.0.0
 * @package Cyber4all
 */
class ExtraitForm extends Entity
{
    protected $attributes = [
        'modifier' => null, // id de l'extrait en modification, null en ajout
        'titre'    => '',
        'type'     => null,
        'contenu'  => '',
    ];
}
