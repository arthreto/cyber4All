<?php

namespace App\Models;

use App\Entities\MotCle;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;
use App\Entities\Categorie;

/**
 * Ce model représente la gestion de la table de liaison quiz_question
 * @author Arthur Vidal
 * @since 1.0.0
 * @package Cyber4all
 */
class QuizQuestionModel extends Model
{
    protected $DBGroup = 'tracabilite';
    protected $table = 'quiz_question';
    protected $primaryKey = 'id_question';
    protected $useAutoIncrement = false;
    protected $allowedFields = ['id_question', 'id_quiz'];
    //protected $returnType = \App\Entities\Quiz::class;

    /**
     * Crée une liaison entre une question et un quiz.
     *
     * Cette méthode insère une nouvelle association dans la table
     * `quiz_question` à partir de l'identifiant de la question
     * et de l'identifiant du quiz.
     *
     * En cas de succès, elle retourne true.
     * En cas d'échec, la méthode retourne false.
     *
     * @param int $idQuestion Identifiant de la question.
     * @param int $idQuiz Identifiant du quiz.
     * @return bool True si la liaison est créée, false sinon.
     *
     * @since 1.0.0
     * @author Arthur
     */
    public function creerLiaison(int $idQuestion, int $idQuiz): bool
    {
        try {
            return $this->db->table($this->table)->insert([
                'id_question' => $idQuestion,
                'id_quiz' => $idQuiz,
            ]);

        } catch (DatabaseException $e) {
            log_message('error', "Erreur de création d'une liaison quiz-question : " . $e->getMessage());
            return false;
        }
    }
}