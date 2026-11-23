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
            log_message('error', "Erreur de récupération des liaisons quiz-question : {exception}", ['exception' => $e]);
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
            log_message('error', "Erreur de mise à jour des liaisons quiz-question : {exception}", ['exception' => $e]);
            return false;
        }
    }

    /**
     * Récupère les identifiants des questions rattachées à un quiz.
     *
     * @param int $idQuiz Identifiant du quiz.
     * @return array Liste des identifiants de questions (tableau vide en cas d'erreur).
     */
    public function getIdsQuestionsByQuiz(int $idQuiz): array
    {
        try {
            $liaisons = $this->db->table('quiz_question')
                ->select('id_question')
                ->where('id_quiz', $idQuiz)
                ->get()
                ->getResultArray();

            return array_map('strval', array_column($liaisons, 'id_question'));

        } catch (DatabaseException $e) {
            log_message('error', "Erreur de récupération des questions d'un quiz : {exception}", ['exception' => $e]);
            return [];
        }
    }

    /**
     * Met à jour les liaisons entre un quiz et ses questions.
     *
     * Ajoute les nouvelles associations et supprime celles qui
     * ne sont plus présentes dans la liste fournie.
     *
     * @param int $idQuiz Identifiant du quiz.
     * @param array $questions Liste des identifiants des questions souhaitées.
     * @return bool True si la mise à jour réussit, false sinon.
     */
    public function updateLiaisonsQuiz(int $idQuiz, array $questions): bool
    {
        try {
            $builder = $this->db->table('quiz_question');

            $idsExistants = array_map('intval', $this->getIdsQuestionsByQuiz($idQuiz));
            $questions = array_unique(array_map('intval', $questions));

            // Ajout des nouvelles liaisons
            foreach ($questions as $idQuestion) {
                if (!in_array($idQuestion, $idsExistants, true)) {
                    $builder->insert([
                        'id_question' => $idQuestion,
                        'id_quiz' => $idQuiz
                    ]);
                }
            }

            // Suppression des liaisons qui n'existent plus
            foreach ($idsExistants as $idQuestion) {
                if (!in_array($idQuestion, $questions, true)) {
                    $builder->where('id_quiz', $idQuiz)
                        ->where('id_question', $idQuestion)
                        ->delete();
                }
            }

            return true;

        } catch (DatabaseException $e) {
            log_message('error', "Erreur de mise à jour des liaisons quiz-question : {exception}", ['exception' => $e]);
            return false;
        }
    }

    /**
     * Quiz déjà complets parmi ceux où l'on veut rattacher une question.
     * Un quiz est complet quand il a autant de questions que son nombre prévu (quiz.nb_questions).
     * Les quiz où la question est déjà rattachée sont ignorés : elle y est déjà, rien n'est ajouté.
     *
     * @param int|null $idQuestion Question modifiée, null pour une nouvelle question.
     * @param array    $quiz       Identifiants des quiz cochés.
     * @return array|null Quiz complets (titre, nb_questions, nb_actuel), [] si aucun, null en cas d'erreur.
     */
    public function getQuizComplets(?int $idQuestion, array $quiz): ?array
    {
        $dejaRattaches = $idQuestion === null ? [] : array_map('intval', $this->getIdsQuizByQuestion($idQuestion));
        $nouveaux = array_values(array_diff(array_unique(array_map('intval', $quiz)), $dejaRattaches));

        if ($nouveaux === []) {
            return [];
        }

        try {
            $lignes = $this->db->table('quiz')
                ->select('quiz.titre, quiz.nb_questions, COUNT(quiz_question.id_question) AS nb_actuel')
                ->join('quiz_question', 'quiz_question.id_quiz = quiz.id', 'left')
                ->whereIn('quiz.id', $nouveaux)
                ->groupBy('quiz.id')
                ->get()
                ->getResultArray();

            return array_values(array_filter($lignes, fn (array $l): bool
                => (int) $l['nb_questions'] > 0 && (int) $l['nb_actuel'] >= (int) $l['nb_questions']));

        } catch (DatabaseException $e) {
            log_message('error', "Erreur de vérification des quiz complets : {exception}", ['exception' => $e]);
            return null;
        }
    }

    /**
     * Récupère les identifiants des quiz auxquels au moins une des questions est rattachée.
     *
     * @param array $idsQuestions Identifiants des questions.
     * @return array Identifiants des quiz (tableau vide si aucun ou en cas d'erreur).
     *
     * @author Raphael Bierlaire
     */
    public function getIdsQuizByQuestions(array $idsQuestions): array
    {
        $idsQuestions = array_values(array_unique(array_map('intval', $idsQuestions)));

        if ($idsQuestions === []) {
            return [];
        }

        try {
            $liaisons = $this->db->table('quiz_question')
                ->select('id_quiz')
                ->distinct()
                ->whereIn('id_question', $idsQuestions)
                ->get()
                ->getResultArray();

            return array_map('intval', array_column($liaisons, 'id_quiz'));

        } catch (DatabaseException $e) {
            log_message('error', "Erreur de récupération des quiz de plusieurs questions : {exception}", ['exception' => $e]);
            return [];
        }
    }

    /**
     * Désactive les quiz actifs qui n'ont plus assez de questions.
     * Seules les questions actives comptent : ce sont les seules proposées sur la page des quiz.
     * À appeler après la suppression (désactivation) d'une question ou la modification de ses quiz.
     *
     * @param array $idsQuiz Identifiants des quiz à vérifier.
     * @return array Titres des quiz désactivés ([] si aucun ou en cas d'erreur).
     *
     * @author Raphael Bierlaire
     */
    public function desactiverQuizIncomplets(array $idsQuiz): array
    {
        $idsQuiz = array_values(array_unique(array_map('intval', $idsQuiz)));

        if ($idsQuiz === []) {
            return [];
        }

        try {
            $lignes = $this->db->table('quiz')
                ->select('quiz.id, quiz.titre, quiz.nb_questions')
                ->select('SUM(CASE WHEN question.actif = 1 THEN 1 ELSE 0 END) AS nb_actives', false)
                ->join('quiz_question', 'quiz_question.id_quiz = quiz.id', 'left')
                ->join('question', 'question.id = quiz_question.id_question', 'left')
                ->whereIn('quiz.id', $idsQuiz)
                ->where('quiz.actif', 1)
                ->groupBy('quiz.id')
                ->get()
                ->getResultArray();

            $incomplets = array_values(array_filter($lignes, fn (array $l): bool
                => (int) $l['nb_actives'] < (int) $l['nb_questions']));

            if ($incomplets === []) {
                return [];
            }

            $this->db->table('quiz')
                ->whereIn('id', array_column($incomplets, 'id'))
                ->update(['actif' => 0]);

            return array_column($incomplets, 'titre');

        } catch (DatabaseException $e) {
            log_message('error', "Erreur de désactivation des quiz incomplets : {exception}", ['exception' => $e]);
            return [];
        }
    }
}
