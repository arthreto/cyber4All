<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Classe QuestionForm
 * Représente le formulaire de Question
 *
 * Thème : id_theme, il choisit les quiz et les extraits proposés.
 * Extrait de la question, choisi par extrait_mode :
 *  - 'aucun'    : pas d'extrait
 *  - 'existant' : id_extrait d'un extrait déjà en base (du thème de la question)
 *  - 'nouveau'  : extrait créé à l'enregistrement avec extrait_titre et extrait_contenu
 *                 (son type est celui du thème de la question)
 * Un extrait existant peut être modifié (champs edit_*) ou supprimé directement depuis le formulaire.
 *
 * @author Arthur
 * @since 2.0.0
 * @package Cyber4all
 */
class QuestionForm extends Entity
{
    protected $attributes = [
        'modifier' => null,       // id de la question en modification, null en ajout
        'id_theme' => null,
        'quiz' => [],             // ids des quiz cochés
        'libelle' => '',
        'image' => '',
        'extrait_mode' => 'aucun',
        'id_extrait' => null,
        'extrait_titre' => '',
        'extrait_type' => '',
        'extrait_contenu' => '',
        // Action en cours sur l'extrait existant choisi : null ou 'modifier'
        'extrait_action' => null,
        'edit_titre' => '',
        'edit_type' => '',
        'edit_contenu' => '',
        'bonne_reponse' => null,  // rien de coché pour une nouvelle question
        'explication' => '',
        'actif' => '1',
    ];
}
