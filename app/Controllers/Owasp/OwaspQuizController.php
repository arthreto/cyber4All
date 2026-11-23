<?php

namespace App\Controllers\Owasp;

use App\Controllers\BaseController;
use App\Models\Owasp\OwaspQuizModel;
use App\Models\Owasp\OwaspTopModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class OwaspQuizController extends BaseController
{
    protected OwaspTopModel $Top;
    protected OwaspQuizModel $Quiz;
    public function __construct()
    {
        $this->Top    = model(OwaspTopModel::class);
        $this->Quiz =  model(OwaspQuizModel::class);
    }
    private const NB_QUESTIONS = 5;
    private const NB_CHOIX     = 4;

    public function getQuiz($top)
    {
        $infos = $this->Top->getTopById(strtoupper($top));

        $cle  = 'quiz_' . strtolower($top);
        $data = [
            'Top'        => $infos,
            'nom_top'    => $top,
            'detail'     => false,
            'resultat'   => null,
            'autresTops' => $this->Top->getAutresTops($infos->id_top),
        ];

        $postees   = $this->request->is('post') ? (array) $this->request->getPost('reponse') : [];
        $questions = session()->get($cle);

        if ($postees !== [] && is_array($questions)) {
            // CORRECTION sur les questions mémorisées à l'affichage
            $score   = 0;
            $details = [];

            foreach ($questions as $q) {
                $id      = (int) $q['id_question'];
                $bonnes  = $this->Quiz->idsBonnesReponses($id);
                $choisie = (int) ($postees[$id] ?? 0);
                $ok      = in_array($choisie, $bonnes, true);
                $score  += $ok ? 1 : 0;

                $details[$id] = ['choisie' => $choisie, 'bonne' => $ok ? $choisie : ($bonnes[0] ?? 0)];
            }

            $data['questions'] = $questions;
            $data['resultat']  = ['score' => $score, 'total' => count($questions), 'details' => $details];
        } else {
            // NOUVEAU QUIZ
            $questions = $this->Quiz->questionsAleatoires($infos->id_top, self::NB_QUESTIONS);
            if ($questions === []) {
                throw PageNotFoundException::forPageNotFound();
            }

            foreach ($questions as &$q) {
                $id        = (int) $q['id_question'];
                $bonne     = array_slice($this->Quiz->bonnesReponses($id), 0, 1);   // une seule bonne réponse
                $mauvaises = $this->Quiz->mauvaisesReponses($id, max(0, self::NB_CHOIX - count($bonne)));
                $choix     = array_merge($bonne, $mauvaises);
                shuffle($choix);

                $q['reponses'] = array_map(static fn ($c) => [
                    'id_reponse'      => (int) $c['id_prop'],
                    'libelle_reponse' => $c['libelle_prop'],
                ], $choix);
            }
            unset($q);

            session()->set($cle, $questions);   // sans l'info « laquelle est bonne »
            $data['questions'] = $questions;
        }

        return view('header', $data) .
            view('owasp/headerView', $data) .
            view('owasp/quizView', $data) .
            view('owasp/footerView', $data);
    }
}