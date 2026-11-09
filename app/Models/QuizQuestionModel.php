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
     * Récupère les identifiants des quiz auxquels une question est rattachée.
     *
     * @param int $idQuestion Identifiant de la question.
     * @return array Liste des identifiants de quiz (tableau vide en cas d'erreur).
     */
    public function getIdsQuizByQuestion(int $idQuestion): array
    {
        try {
            $liaisons = $this->db->table('quiz_question')
                ->select('id_quiz')
                ->where('id_question', $idQuestion)
                ->get()
                ->getResultArray();

            return array_map('strval', array_column($liaisons, 'id_quiz'));

        } catch (DatabaseException $e) {
            log_message('error', "Erreur de récupération des liaisons quiz-question : " . $e->getMessage());
            return [];
        }
    }


    /**
    * Met à jour les liaisons entre une question et ses quiz.
    *
    * Ajoute les nouvelles associations et supprime celles qui
    * ne sont plus présentes dans la liste fournie.
    *
    * @param int $idQuestion Identifiant de la question.
    * @param array $quiz Liste des identifiants des quiz souhaités.
    * @return bool True si la mise à jour réussit, false sinon.
    */
    public function updateLiaisons(int $idQuestion, array $quiz): bool
    {
        try {
            $builder = $this->db->table('quiz_question');

            // Récupération des quiz actuellement associés
            $existants = $builder
                ->select('id_quiz')
                ->where('id_question', $idQuestion)
                ->get()
                ->getResultArray();

            $idsExistants = array_column($existants, 'id_quiz');

            // Mettre identifiant en INT + supprime les doublons
            $quiz = array_unique(array_map('intval', $quiz));

            // Ajout des nouvelles liaisons
            foreach ($quiz as $idQuiz) {
                if (!in_array($idQuiz, $idsExistants)) {
                    $builder->insert([
                        'id_question' => $idQuestion,
                        'id_quiz' => $idQuiz
                    ]);
                }
            }

            // Suppression des liaisons qui n'existe plus
            foreach ($idsExistants as $idQuiz) {
                if (!in_array((int) $idQuiz, $quiz)) {
                    $builder->where('id_question', $idQuestion)
                        ->where('id_quiz', $idQuiz)
                        ->delete();
                }
            }

            return true;

        } catch (DatabaseException $e) {
            log_message('error', "Erreur de mise à jour des liaisons quiz-question : " . $e->getMessage());
            return false;
        }
    }
}