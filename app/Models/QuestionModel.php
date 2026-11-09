<?php

namespace App\Models;

use App\Entities\MotCle;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;
use App\Entities\Categorie;

/**
 * Ce model représente la gestion des Questions des Quiz
 * @author Arthur Vidal
 * @since 1.0.0
 * @package Cyber4all
 */
class QuestionModel extends Model
{
    protected $DBGroup = 'tracabilite';
    protected $table = 'question';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['id', 'libelle','id_extrait','image','bonne_reponse','explication','actif','date_ajout','date_modif'];
    //protected $returnType = \App\Entities\Quiz::class;

    /**
     * Crée une nouvelle question dans la base de données.
     *
     * Cette méthode insère les informations d'une question dans la
     * table `question` et renseigne automatiquement les dates
     * de création et de modification.
     *
     * En cas de succès, elle retourne l'identifiant de la question créée.
     * En cas d'échec, elle retourne false.
     *
     * @param array $donnees Données de la question à enregistrer.
     * @return int|false Identifiant de la question créée ou false en cas d'échec.
     *
     * @since 1.0.0
     * @author Arthur
     */
    public function creerQuestion(array $donnees): int|false
    {
        try {
            // Insertion de la question
            $idQuestion = $this->insert($donnees);

            // Retourne l'identifiant de la question créée
            return $idQuestion === false ? false : (int) $idQuestion;

        } catch (DatabaseException $e) {
            log_message('error', "Erreur de création d'une question : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Modifie une question existante dans la base de données.
     *
     * @param array $formData Données de la question à modifier.
     * @param int $idQuestion Identifiant de la question à modifier.
     * @return bool True si la modification a réussi, false sinon.
     */
    public function updateQuestion(array $formData, int $idQuestion): bool
    {
        try {
            return $this->update($idQuestion, $formData);

        } catch (DatabaseException $e) {
            log_message('error', "Erreur de modification d'une question : " . $e->getMessage());
            return false;
        }
    }


    /**
     * Récupère toutes les questions de la base de données.
     *
     * Cette méthode retourne l'ensemble des questions
     * présentes dans la table `question`.
     *
     * En cas de succès, elle retourne un tableau de questions.
     * En cas d'échec, elle retourne un tableau vide.
     *
     * @return array Liste des questions ou tableau vide en cas d'échec.
     *
     * @since 1.0.0
     * @author Arthur
     */
    public function getAllQuestions(): array
    {
        try {
            $questions = $this->findAll();

                foreach ($questions as &$question) {
                $question['quiz'] = $this->db->table('quiz')
                    ->select('quiz.id, quiz.titre')
                    ->join('quiz_question', 'quiz_question.id_quiz = quiz.id')
                    ->where('quiz_question.id_question', $question['id'])
                    ->get()
                    ->getResultArray();
            }
            unset($question);

            return $questions;

        } catch (DatabaseException $e) {
            log_message('error', "Erreur de récupération des questions : " . $e->getMessage());
            return [];
        }
    }


    /**
     * Récupère les questions associées à un thème.
     *
     * Cette méthode recherche les questions appartenant
     * aux quiz liés à un thème spécifique.
     *
     * En cas de succès, elle retourne un tableau de questions.
     * En cas d'échec, elle retourne un tableau vide.
     *
     * @param int $idTheme Identifiant du thème recherché.
     * @return array Liste des questions du thème ou tableau vide.
     *
     * @since 1.0.0
     * @author Arthur
     */
    public function getQuestionsFromTheme(int $idTheme): array
    {
        try {
            $questions = $this->select('question.*')
                ->join('quiz_question', 'quiz_question.id_question = question.id')
                ->join('quiz', 'quiz.id = quiz_question.id_quiz')
                ->where('quiz.id_theme', $idTheme)
                ->distinct()
                ->findAll();

            foreach ($questions as &$question) {
                $question['quiz'] = $this->db->table('quiz')
                    ->select('quiz.id, quiz.titre')
                    ->join('quiz_question', 'quiz_question.id_quiz = quiz.id')
                    ->where('quiz_question.id_question', $question['id'])
                    ->where('quiz.id_theme', $idTheme)
                    ->get()
                    ->getResultArray();
            }
            unset($question);

            return $questions;

        } catch (DatabaseException $e) {
            log_message('error', "Erreur de récupération des questions du thème : " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les questions associées à un Quiz.
     *
     * Cette méthode recherche les questions appartenant
     * aux quiz.
     *
     * En cas de succès, elle retourne un tableau de questions.
     * En cas d'échec, elle retourne un tableau vide.
     *
     * @param int $idTheme Identifiant du quiz recherché.
     * @return array Liste des questions du quiz ou tableau vide.
     *
     * @since 1.0.0
     * @author Arthur
     */
    public function getQuestionFromQuiz(int $idQuiz): array
    {
        try {
            $questions = $this->select('question.*')
                ->join('quiz_question', 'quiz_question.id_question = question.id')
                ->join('quiz', 'quiz.id = quiz_question.id_quiz')
                ->where('quiz_question.id_quiz', $idQuiz)
                ->distinct()
                ->findAll();

            foreach ($questions as &$question) {
                $question['quiz'] = $this->db->table('quiz')
                    ->select('quiz.id, quiz.titre')
                    ->join('quiz_question', 'quiz_question.id_quiz = quiz.id')
                    ->where('quiz_question.id_question', $question['id'])
                    ->where('quiz.id_theme', $idQuiz)
                    ->get()
                    ->getResultArray();
            }
            unset($question);

            return $questions;

        } catch (DatabaseException $e) {
            log_message('error', "Erreur de récupération des questions du thème : " . $e->getMessage());
            return [];
        }
    }
}