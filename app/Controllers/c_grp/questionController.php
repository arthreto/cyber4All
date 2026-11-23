<?php

namespace App\Controllers\c_grp;

use App\Controllers\BaseController;
use App\Models\Model_groupe_C\ModelQuiz;


class questionController extends BaseController
{
    private const CONCEPTS = [1, 2, 3, 4];

    private const NOMS = [
        1 => 'Tisponibilité',
        2 => 'Intégrité',
        3 => 'Confidentialité',
        4 => 'Traçabilité',
    ];

    public function Demarrer()
    {
        $model = new \App\Models\Model_groupe_C\ModelQuiz();
        $total = $model->countAllResults();

        $duree = $this->request->getPost('duree');
        $nb    = ($duree === 'tout') ? $total : (int) $duree;
        $nb    = max(1, min($nb, $total));

        $ids = array_column($model->select('id')->orderBy('RAND()')->findAll($nb), 'id');

        session()->set('quiz', ['ids' => $ids, 'current' => 0, 'score' => 0]);

        return redirect()->to(base_url('quiz/question'));
    }

    // Les 4 id_concept possibles : 1 dispo, 2 inte, 3 conf, 4 Traca



    public function AfficherQuestion()
    {

        $quiz = session()->get('quiz');
        if (!$quiz) {
            return redirect()->to(base_url('quiz'));
        }

        // Quiz terminé : résultat
        if ($quiz['current'] >= count($quiz['ids'])) {
            session()->remove(['quiz', 'correction']);
            return view('header')
                . view('/groupe_c/dict/resultatQuiz', [
                    'score' => $quiz['score'],
                    'total' => count($quiz['ids']),
                ])
                . view('footer');
        }

        // 1. On crée les variables
        $model    = new ModelQuiz();
        $question = $model->questionAvecMot($quiz['ids'][$quiz['current']]);

        $total  = count($quiz['ids']);        // créé ici
        $numero = $quiz['current'] + 1;       // créé ici

        // 2. Puis on les utilise
        return view('header')
            . view('/groupe_c/dict/questionQuiz', [
                'question'  => $question,
                'numero'    => $numero,
                'total'     => $total,
                'restantes' => $total - $numero,
                'score'     => $quiz['score'],
            ])
            . view('footer');
    }

    public function Repondre()
    {


        $quiz = session()->get('quiz');
        if (!$quiz || $quiz['current'] >= count($quiz['ids'])) {
            return redirect()->to(base_url('quiz'));
        }

        $model    = new ModelQuiz();
        $idQ      = $quiz['ids'][$quiz['current']];
        $question = $model->questionAvecMot($idQ);
        $cc       = $model->conceptPourQuestion($idQ);

        $post = (array) $this->request->getPost('reponses');


// Aucune case cochée : on refuse et on revient à la question
        if (empty($post)) {
            return redirect()->to(base_url('quiz/question'));
        }
        $dispo = in_array('1', $post) ? 1 : 0;
        $inte = in_array('2', $post) ? 1 : 0;
        $conf = in_array('3', $post) ? 1 : 0;
        $traca = in_array('4', $post) ? 1 : 0;

        $juste = $cc
            && $dispo === (int) $cc['disponibilite']
            && $inte === (int) $cc['integrite']
            && $conf === (int) $cc['confidentialite']
            && $traca === (int) $cc['tracabilite'];

        //dd($post, $cc, $d, $i, $c, $t);
        if ($juste) {
            $quiz['score']++;
        }

        // Mémorise la correction pour la page suivante
        $cochesIds = array_map('intval', $post);

        $bonnesIds = [];
        if ($cc) {
            if ((int) $cc['disponibilite'])   $bonnesIds[] = 1;
            if ((int) $cc['integrite'])       $bonnesIds[] = 2;
            if ((int) $cc['confidentialite']) $bonnesIds[] = 3;
            if ((int) $cc['tracabilite'])     $bonnesIds[] = 4;
        }

        session()->set('correction', [
            'juste'       => $juste,
            'mot'         => $question['mot'],
            'question'    => $question['question'],
            'explication' => $question['explication'],
            'coches'      => $cochesIds,
            'bonnes'      => $bonnesIds,
        ]);

        $quiz['current']++;
        session()->set('quiz', $quiz);

        return redirect()->to(base_url('quiz/correction'));
    }


    public function AfficherCorrection()
    {
        $quiz       = session()->get('quiz');          // crée $quiz
        $correction = session()->get('correction');    // crée $correction

        // Rien à corriger : retour à la question
        if (!$quiz || !$correction) {
            return redirect()->to(base_url('quiz/question'));
        }

        $total = count($quiz['ids']);
        $fait  = $quiz['current'];   // déjà incrémenté dans Repondre()

        return view('header')
            . view('/groupe_c/dict/correctionQuiz', [
                'correction' => $correction,
                'numero'     => $fait,
                'total'      => $total,
                'restantes'  => $total - $fait,
                'score'      => $quiz['score'],
                'fini'       => $fait >= $total,
            ])
            . view('footer');
    }
}