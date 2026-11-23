<?php

namespace App\Models\Model_groupe_C;

use CodeIgniter\Model;

/**
 * MODÈLE : table question_quiz (les questions du quiz Concept).
 * Fichier : app/Models/Model_groupe_C/QuestionQuizModel.php
 *
 * Les 2 méthodes modifierQuestion() et supprimerQuestion() gèrent leur propre transaction
 * ("tout ou rien") : le contrôleur n'a plus besoin de toucher à la base.
 */
class QuestionQuizModel extends Model
{
    protected $table = 'question_quiz';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['id_mot_cle', 'question', 'explication'];

    /** Toutes les questions + le mot associé (colonne "mot"), les plus récentes en premier. */
    public function getAvecMot(): array
    {
        return $this->select('question_quiz.*, mot_cle.mot')
            ->join('mot_cle', 'mot_cle.id = question_quiz.id_mot_cle', 'left') // left = garde aussi les questions SANS terme
            ->orderBy('question_quiz.id', 'DESC')                              // les plus récentes en premier
            ->findAll();
    }

    /** Les ids des questions liées à un terme. */
    public function getIdsParMot(int $idMot): array
    {
        return array_column(
            $this->select('id')->where('id_mot_cle', $idMot)->findAll(),
            'id'
        );
    }

    /**
     * Enregistre une question et son terme associé, puis recalcule ses bonnes réponses.
     * Renvoie true si tout a réussi.
     */
    public function modifierQuestion(int $idQuestion, string $question, ?string $explication, ?int $idMot): bool
    {
        $this->db->transStart(); // transaction : tout ou rien

        $this->update($idQuestion, [
            'question'    => $question,
            'explication' => $explication,
            'id_mot_cle'  => $idMot, // le terme associé choisi dans la liste
        ]);

        // On recalcule question_reponse d'après les cases D/I/C/T (conceptcyber) du terme choisi
        (new QuestionReponseModel())->synchroniser($idQuestion, $idMot);

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Supprime une question et ses bonnes réponses. Renvoie true si tout a réussi.
     */
    public function supprimerQuestion(int $idQuestion): bool
    {
        $this->db->transStart();

        // 1. D'abord ses lignes dans question_reponse (sinon la clé étrangère peut bloquer)
        (new QuestionReponseModel())->supprimerParQuestion($idQuestion);
        // 2. Puis la question elle-même
        $this->delete($idQuestion);

        $this->db->transComplete();

        return $this->db->transStatus();
    }
}
