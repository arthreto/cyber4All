<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Cette entity représente les données du formulaire d'ajout / modification d'une leçon,
 * conservées en session entre deux envois du formulaire.
 *
 * @since 2.0.0
 * @package Cyber4all
 * @author Raphael BIERLAIRE
 */
class LeconForm extends Entity
{
    protected $attributes = [
        'modifier'     => null, // Identifiant de la leçon modifiée, null pour un ajout
        'id_theme'     => null,
        'titre'        => '',
        'contenu'      => '',

        // Informations en lecture seule, remplies par le contrôleur en modification
        'auteur'       => null, // Pseudo de l'auteur
        'auteur_modif' => null, // Pseudo du dernier modificateur
        'date_ajout'   => null, // Date formatée jj/mm/aaaa hh:mm
        'date_modif'   => null, // Date formatée jj/mm/aaaa hh:mm
    ];
}