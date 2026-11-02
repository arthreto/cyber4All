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
        'id_theme' => null,
        'id_quiz_ajout' => null,
        'quiz' => [],
        'enonce' => '',
        'image' => '',
        'detail' => '',
        'bonne_reponse' => '0',
        'explication' => '',
        'actif' => '1',
    ];
}
