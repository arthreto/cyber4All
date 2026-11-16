<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Classe QuestionForm
 * Représente le formulaire de Question
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
        'image_envoyee' => null,
        'extrait_mode' => 'aucun',
        'id_extrait' => null,
        'extrait_titre' => '',
        'extrait_type' => '',
        'extrait_contenu' => '',
        'bonne_reponse' => null,
        'explication' => '',
        'actif' => '1',
    ];
}
