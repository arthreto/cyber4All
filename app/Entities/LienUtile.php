<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 *Cette classe représente la redirection vers un lien externe
 * sous forme de tuile
 *
 * @author Benjamin Lorieul
 * @since 1.0.0
 * @package Cyber4all
 */
class LienUtile extends Entity
{
    /**
    * Valeurs par défaut des attributs
    */
    protected $attributes = [
        'id'            => null, // Identifiant unique du lien
        'titre'         => null, // Titre du lien
        'description'   => null, // description du lien
        'lien'          => null // URL du lien
    ];

    /**
     * Les attributs par défaut de l'entité
     */
    protected $casts = [
        'id'            => 'integer',
        'titre'         => 'string',
        'description'   => 'string',
        'lien'          => 'string'
    ];

    /**
     * afficherLienUtile permet d'afficher la redirection sous forme de tuile
     * @param LienUtile $lienUtile La redirection
     * @return string code html de la tuile
     * @author Benjamin Lorieul 
     * @since 1.0.0
     */
    public function afficherLienUtile(LienUtile $lienUtile):string{
        $tuiles=("
        <div class='tuiles-wrapper'>
            <a href ='{$this->lien}' class='tuile-container'
            target='_blank' title='{$this->titre} - nouvelle fenêtre'>
                <div class='tuile-title'>
                    <h2>{$this->titre}</h2>
                    <p>{$this->description}</p>
                </div>
                <i class='fa-solid fa-up-right-from-square'></i>
            </a>
        </div>
        ");
        return $tuiles;
    }
}