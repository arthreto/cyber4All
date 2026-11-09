<?php

namespace App\Models\Model_groupe_C;

use CodeIgniter\Model;

class ModelQuiz extends Model
{
    protected $table      = 'question_quiz';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    // Nombre de questions utilisables (mot publié uniquement)
    public function compterQuestions(): int
    {
        return $this->join('mot_cle', 'mot_cle.id = question_quiz.id_mot_cle')
            ->where('mot_cle.statut', 1)
            ->countAllResults();
    }

    // Tire $nb id de questions au hasard
    public function tirerIds(int $nb): array
    {
        $lignes = $this->select('question_quiz.id')
            ->join('mot_cle', 'mot_cle.id = question_quiz.id_mot_cle')
            ->where('mot_cle.statut', 1)
            ->orderBy('RAND()')
            ->findAll($nb);

        return array_column($lignes, 'id');
    }

    // Une question avec son mot
    public function questionAvecMot(int $id): ?array
    {
        return $this->select('question_quiz.id, question_quiz.question, question_quiz.explication, mot_cle.mot')
            ->join('mot_cle', 'mot_cle.id = question_quiz.id_mot_cle')
            ->where('question_quiz.id', $id)
            ->first();
    }

    // Les id_concept corrects d'une question
    // Les concepts corrects d'une question, lus dans conceptcyber
    public function bonnesReponses(int $idQuestion): array
    {
        $ligne = $this->db->table('question_quiz')
            ->select('conceptcyber.DISPONIBILITE AS d,
                  conceptcyber.INTEGRITE AS i,
                  conceptcyber.CONFIDENTIALITE AS c,
                  conceptcyber.TRACABILITE AS t')
            ->join('conceptcyber', 'conceptcyber.id_mot_cle = question_quiz.id_mot_cle')
            ->where('question_quiz.id', $idQuestion)
            ->get()
            ->getRowArray();

        if (!$ligne) {
            return [];
        }

        $bonnes = [];
        if ((int) $ligne['d'] === 1) $bonnes[] = 1;
        if ((int) $ligne['i'] === 1) $bonnes[] = 2;
        if ((int) $ligne['c'] === 1) $bonnes[] = 3;
        if ((int) $ligne['t'] === 1) $bonnes[] = 4;

        return $bonnes;
    }
    public function conceptPourQuestion(int $idQuestion): ?array
    {
        return $this->db->table('question_quiz')
            ->select('conceptcyber.*')
            ->join('conceptcyber', 'conceptcyber.id_mot_cle = question_quiz.id_mot_cle')
            ->where('question_quiz.id', $idQuestion)
            ->get()
            ->getRowArray();
    }
}