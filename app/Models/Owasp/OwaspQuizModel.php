<?php

namespace App\Models\Owasp;

use CodeIgniter\Model;

class OwaspQuizModel extends Model
{
    protected $DBGroup = 'owasp';
    protected $table      = 'question';
    protected $primaryKey = 'id_ques';

    /** Le top existe-t-il ? (id_top, ex. "web" ou "api" : adapte à tes données) */
    public function top(string $idTop): ?array
    {
        return $this->db->table('tops')->where('id_top', $idTop)->get()->getRowArray();
    }

    /** N questions au hasard, uniquement celles du top demandé (question -> menace -> tops). */
    public function questionsAleatoires(string $idTop, int $nb): array
    {
        return $this->select('question.id_ques, question.libelle_ques')
            ->join('menace', 'menace.id_menace = question.menace_id')
            ->where('menace.tops_id', $idTop)
            ->orderBy('question.id_ques', 'RANDOM')
            ->limit($nb)
            ->findAll();
    }

    /** Bonnes réponses d'une question (choisies automatiquement via la table bonne_reponse). */
    public function bonnesReponses(int $idQues): array
    {
        return $this->db->table('bonne_reponse')
            ->select('proposition.id_prop, proposition.libelle_prop')
            ->join('proposition', 'proposition.id_prop = bonne_reponse.proposition_id')
            ->where('bonne_reponse.question_id', $idQues)
            ->get()->getResultArray();
    }

    /** N mauvaises réponses au hasard : toute proposition qui n'est PAS une bonne réponse de cette question. */
    public function mauvaisesReponses(int $idQues, int $nb): array
    {
        if ($nb <= 0) {
            return [];
        }

        return $this->db->table('proposition')
            ->select('id_prop, libelle_prop')
            ->whereNotIn('id_prop', static function (BaseBuilder $b) use ($idQues) {
                return $b->select('proposition_id')->from('bonne_reponse')->where('question_id', $idQues);
            })
            ->orderBy('id_prop', 'RANDOM')
            ->limit($nb)
            ->get()->getResultArray();
    }

    /** Ids des bonnes réponses (pour la correction côté serveur). */
    public function idsBonnesReponses(int $idQues): array
    {
        if ($idQues === []) {
            return [];
        }

        return $this->db->table('proposition')
            ->select('id_prop, libelle_prop')
            ->whereIn('id_prop', $idQues)
            ->get()->getResultArray();

    }
}
