<?php

namespace App\Models\Model_groupe_C;

use CodeIgniter\Model;

/**
 * MODÈLE : table question_reponse (les bonnes réponses D/I/C/T de chaque question).
 * Fichier : app/Models/Model_groupe_C/QuestionReponseModel.php
 *
 * Cette table n'a pas d'id propre : 1 ligne = 1 couple (question, concept).
 * On ne passe donc jamais par find($id) : uniquement des where() + delete() / insert().
 *
 * Ce modèle contient aussi la logique qui était avant dans le contrôleur parent
 * (synchroniserReponses) : elle lit et écrit des tables, donc elle a sa place ici.
 */
class QuestionReponseModel extends Model
{
    protected $table = 'question_reponse';
    protected $primaryKey = 'id_question';

    protected $useAutoIncrement = false;
    protected $allowedFields = ['id_question', 'id_concept'];

    /**
     * Correspondance entre le nom de la colonne (table conceptcyber) et l'id du concept en base.
     * Utilisée par synchroniser().
     */
    private const ID_CONCEPTS = [
        'disponibilite'   => 1,
        'integrite'       => 2,
        'confidentialite' => 3,
        'tracabilite'     => 4,
    ];

    /**
     * Met à jour les "bonnes réponses" d'une question (table question_reponse)
     * en fonction des cases D/I/C/T cochées sur le terme associé.
     *
     * Appelée par :
     *  - ConceptCyberModel::modifierCases()       (quand on change les cases d'un terme)
     *  - QuestionQuizModel::modifierQuestion()    (quand on change le terme d'une question)
     *
     * $idQuestion = la question à mettre à jour
     * $idMot      = le terme associé (ou null si aucun)
     */
    public function synchroniser(int $idQuestion, ?int $idMot): void
    {
        // 1. On efface d'abord toutes les anciennes bonnes réponses de cette question
        $this->supprimerParQuestion($idQuestion);

        // 2. Pas de terme associé = pas de bonnes réponses, on s'arrête là
        if (! $idMot) {
            return;
        }

        // 3. On lit les cases cochées du terme (table conceptcyber)
        $concept = (new ConceptCyberModel())->getParMot($idMot);
        if (! $concept) {
            return; // le terme n'est pas encore classé : rien à faire
        }

        // 4. Pour chaque case cochée (valeur 1), on crée une bonne réponse
        foreach (self::ID_CONCEPTS as $colonne => $idConcept) {
            if ((int) $concept[$colonne] === 1) {
                $this->insert([
                    'id_question' => $idQuestion,
                    'id_concept'  => $idConcept,
                ]);
            }
        }
    }

    /** Supprime toutes les bonnes réponses d'une question. */
    public function supprimerParQuestion(int $idQuestion): void
    {
        $this->where('id_question', $idQuestion)->delete();
    }
}
