<?php

namespace App\Controllers\c_grp;

use App\Controllers\BaseController;
use App\Models\Model_groupe_C\ModelQuiz;
use App\Models\Model_groupe_C\ConceptCyberModel;

class QuizController extends BaseController
{



        public function AfficherQuiz(): string
    {
        $model = new \App\Models\Model_groupe_C\ConceptCyberModel();

        $data = [
            'nbD' => $model->compterParPilier('DISPONIBILITE'),
            'nbI' => $model->compterParPilier('INTEGRITE'),
            'nbC' => $model->compterParPilier('CONFIDENTIALITE'),
            'nbT' => $model->compterParPilier('TRACABILITE'),
        ];

        return view('header')
            . view('v_grp/quiz', $data)   // garde le nom de ta vue actuelle
            . view('footer');

    }

    public function Demarrer()
    {

        $model = new \App\Models\Model_groupe_C\ModelQuiz();
        $total = $model->compterQuestions();

        // Aucune question disponible : retour à l'accueil
        if ($total === 0) {
            return redirect()->to(base_url('quiz'));
        }

        $duree = $this->request->getPost('duree');
        $nb    = ($duree === 'tout') ? $total : (int) $duree;
        $nb    = max(1, min($nb, $total));

        $ids = $model->tirerIds($nb);

        session()->set('quiz', ['ids' => $ids, 'current' => 0, 'score' => 0]);

        return redirect()->to(base_url('quiz/question'));
    }


}