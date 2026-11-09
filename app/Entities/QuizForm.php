<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * Classe QuizForm
 * Représente le formulaire d'ajout / de modification d'un quiz
 *
 * @author Arthur
 * @since 2.0.0
 * @package Cyber4all
 */
class QuizForm extends Entity
{
    protected $attributes = [
        'modifier'          => 0,   // id du quiz en modification, 0 en ajout
        'id_theme'          => null,
        'titre'             => '',
        'description'       => '',
        'actif'             => '1',
        'id_theme_questions' => null, // thème utilisé pour filtrer les questions à ajouter
        'id_question_ajout' => null,
        'questions'         => [],  // ids des questions rattachées au quiz
    ];
}
