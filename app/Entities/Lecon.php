<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Classe Lecon
 * Représente une leçon rattachée à un thème
 *
 * @author Raphael
 * @since 2.0.0
 * @package Cyber4all
 */
class Lecon extends Entity
{
    /**
     * Définition des types des attributs
     */
    protected $casts = [
        'id'         => 'integer',    // ID de la leçon
        'titre'      => 'string',     // Titre de la leçon
        'contenu'    => 'string',     // Contenu de la leçon
        'id_theme'   => 'integer',    // ID du thème associé
        'date_ajout' => '?datetime',  // Date de création
        'date_modif' => '?datetime',  // Date de dernière modification
    ];

    /**
     * Modifie le titre de la leçon
     *
     * @param string $titre Titre de la leçon
     * @return $this
     */
    public function setTitre(string $titre): self
    {
        $this->attributes['titre'] = $titre;
        return $this;
    }
}