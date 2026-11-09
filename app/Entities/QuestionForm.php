<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Classe QuestionForm
 * Représente le formulaire de Question
 *
 * Détail (extrait) de la question, choisi par detail_mode :
 *  - 'aucun'    : pas d'extrait
 *  - 'existant' : id_extrait d'un extrait déjà en base
 *  - 'nouveau'  : extrait créé à l'enregistrement avec extrait_titre, extrait_type, extrait_contenu
 * Un extrait existant peut être modifié (champs edit_*) ou supprimé directement depuis le formulaire.
 *
 * @author Arthur
 * @since 2.0.0
 * @package Cyber4all
 */
class QuestionForm extends Entity
{
    protected $attributes = [
        'id_theme' => null,
        'id_quiz_ajout' => null,
        'quiz' => [],
        'libelle' => '',
        'image' => '',
        'detail_mode' => 'aucun',
        'id_extrait' => null,
        'extrait_titre' => '',
        'extrait_type' => '',
        'extrait_contenu' => '',
        // Action en cours sur l'extrait existant choisi : null ou 'modifier'
        'extrait_action' => null,
        'edit_titre' => '',
        'edit_type' => '',
        'edit_contenu' => '',
        'bonne_reponse' => '0',
        'explication' => '',
        'actif' => '1',
    ];
}
