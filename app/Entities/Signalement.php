<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Classe Signalement
 * Représente un signalement avec ses propriétés et relations.
 *
 * @author Benjamin Lorieul
 * @since 1.0.0
 * @package Cyber4all
 */
class Signalement extends Entity
{
    /**
     * Les champs qui doivent être traités comme des dates
     */
    protected $dates = ['date_signalement'];

    /**
     * Les attributs par défaut de l'entité
     */
    protected $attributes = [
        'id_signalement'      => null,
        'id_mot_cle'          => null,
        'motif'               => null,
        'date_signalement'    => null,
        'id_utilisateur'      => null,
        'mot'                 => null, // relationnel, mot associé au signalement
    ];

    /**
     * Types de données pour chaque attribut
     */
    protected $casts = [
        'id_signalement'      => 'integer',
        'id_mot_cle'          => 'integer',
        'motif'               => 'string',
        'date_signalement'    => 'datetime',
        'id_utilisateur'      => 'integer',
    ];

    /**
     * Modifie le mot associé au signalement actuel
     * @return self
     * @author Benjamin Lorieul 
     * @since 1.0.0
     */
    public function setMot(string $mot): self
    {
        $this->attributes['mot'] = $mot;
        return $this;
    }

    /**
     * Obtient le mot associé au signalement actuel
     * @return string Le mot
     * @author Benjamin Lorieul 
     * @since 1.0.0
     */
    public function getMot(): string
    {
        return $this->attributes['mot'] ?? 'null';
    }

}