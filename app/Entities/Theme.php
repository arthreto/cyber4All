<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Classe Theme
 * Représente un thème avec un nom
 *
 * @author Arthur
 * @since 2.0.0
 * @package Cyber4all
 */
class Theme extends Entity
{
    /**
     * Définition des types des attributs
     */
    protected $casts = [
        'idTheme'            => 'integer', // ID du thème
        'nomTheme'           => 'string',  // Nom du thème
    ];

    /**
     * Valeurs par défaut des attributs
     */
    protected $attributes = [
        'idTheme'            => null,      // Valeur initiale nulle
        'nomTheme'           => null,
    ];

    /**
     * Modifie le nom du thème actuelle
     *
     * @param string $nom Nom du thème
     * @return $this
     */
    public function setNom(string $nom): self
    {
        $this->attributes['nomTheme'] = $nom;
        return $this;
    }

    /**
     * Récupère le nom du thème actuelle
     *
     * @return $this
     */
    public function getNom(): ?string
    {
        return $this->attributes['nomTheme'];
    }
}
