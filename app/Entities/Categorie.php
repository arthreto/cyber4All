<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Classe Categorie
 * Représente une catégorie avec un nom et un thème
 *
 * @author Benjamin
 * @since 2.0.0
 * @package Cyber4all
 */
class Categorie extends Entity
{
    /**
     * Définition des types des attributs
     */
    protected $casts = [
        'id'            => 'integer', // ID de la catégorie
        'nom'           => 'string',  // Nom de la catégorie
        'theme'         => 'string',  // Thème associé à la catégorie
    ];

    /**
     * Valeurs par défaut des attributs
     */
    protected $attributes = [
        'id'            => null,      // Valeur initiale nulle
        'nom'           => null,
        'theme'         => null,
    ];

    /**
     * Modifie le nom de la catégorie actuelle
     *
     * @param string $nom Nom de la catégorie
     * @return $this
     */
    public function setNom(string $nom): self
    {
        $this->attributes['nom'] = $nom;
        return $this;
    }

    /**
     * Modifie le thème de la catégorie actuelle
     *
     * @param string $theme Thème de la catégorie
     * @return $this
     */
    public function setTheme(string $theme): self
    {
        $this->attributes['theme'] = $theme;
        return $this;
    }
}
