<?php

namespace App\Controllers\Owasp;

use App\Controllers\BaseController;
use App\Models\Owasp\OwaspMenaceModel;
use App\Models\Owasp\OwaspQuizModel;
use App\Models\Owasp\OwaspTopModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class OwaspController extends BaseController
{
    protected $helpers = ['url', 'html'];   // anchor(), base_url(), ol(), link_tag()

    protected OwaspMenaceModel $Menace;
    protected OwaspTopModel $Top;

    public function __construct()
    {
        $this->Menace = model(OwaspMenaceModel::class);
        $this->Top    = model(OwaspTopModel::class);
        $this->Quiz =  model(OwaspQuizModel::class);
    }

    /**
     * Données communes aux pages d'un Top : le Top, ses menaces (position => menace)
     * et les autres Tops. 404 si le Top n'existe pas.
     */
    private function chargerTop(string $top): array
    {
        $top      = strtoupper($top);
        $infosTop = $this->Top->getTopById($top);

        if ($infosTop === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $menaces = [];
        foreach ($this->Menace->getMenaces($top) as $index => $menace) {
            $menaces[$index + 1] = $menace;   // position affichée : 1, 2, 3...
        }

        return [
            'Top'        => $infosTop,
            'menaces'    => $menaces,
            'autresTops' => $this->Top->getAutresTops($top),
        ];
    }

    public function getMenaces(string $top): string
    {
        $data = $this->chargerTop($top);

        return view('header', $data) .
            view('owasp/headerView', $data) .
            view('owasp/listeRisqueView', $data) .
            view('owasp/choixQuizView') .
            view('owasp/footerView', $data);
    }

    public function getMenace(string $top, int $position): string
    {
        $data = $this->chargerTop($top);

        $entree = $data['menaces'][$position] ?? null;
        if ($entree === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $menace = $this->Menace->getMenace($data['Top']->id_top, $entree->id_menace);
        if ($menace === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $data['position']      = $position;
        $data['menace']        = $menace;
        $data['contreMesures'] = $this->Menace->getMenaceContreMesures($entree->id_menace);

        return view('header', $data) .
            view('owasp/headerView', $data) .
            view('owasp/MenaceView', $data) .
            view('owasp/footerView', $data);
    }

    private const NB_QUESTIONS = 5;
    private const NB_CHOIX     = 4;
    public function getQuiz($top)
    {
        $infos = $this->Top->getTopById(strtoupper($top));

        $data = [
            'Top'        => $infos,
            'nom_top'    => $top,
            'detail'     => false,
            'resultat'   => null,
            'autresTops' => $this->Top->getAutresTops($infos->id_top),
        ];

        if ($this->request->is('post') && $this->request->getPost('questions')) {
            // CORRECTION, revérifiée côté serveur
            $ids       = array_map('intval', (array) $this->request->getPost('questions'));
            $reponses  = (array) $this->request->getPost('reponses');
            $affichees = (array) $this->request->getPost('affichees');
            $score = 0;
            $questions = [];

            foreach ($ids as $idQues) {
                $q = $this->Quiz->find($idQues);
                if ($q === null) {
                    continue;
                }

                $bonnes = $this->Quiz->idsBonnesReponses($idQues);
                $coche  = array_map('intval', (array) ($reponses[$idQues] ?? []));
                sort($bonnes);
                sort($coche);
                $ok = $bonnes !== [] && $bonnes === $coche;
                $score += $ok ? 1 : 0;

                $q['ok']       = $ok;
                $q['bonnes']   = $bonnes;
                $q['cochees']  = $coche;
                $q['choix']    = $this->Quiz->propositionsParIds(array_map('intval', (array) ($affichees[$idQues] ?? [])));
                $q['multiple'] = count($bonnes) > 1;
                $questions[]   = $q;
            }

            $data['questions'] = $questions;
            $data['detail']    = true;
            $data['resultat']  = ['score' => $score, 'total' => count($questions)];
        } else {
            // NOUVEAU QUIZ
            $questions = $this->Quiz->questionsAleatoires($infos->id_top, self::NB_QUESTIONS);
            if ($questions === []) {
                throw PageNotFoundException::forPageNotFound();
            }

            foreach ($questions as &$q) {
                $bonnes    = $this->Quiz->bonnesReponses((int) $q['id_ques']);
                $mauvaises = $this->Quiz->mauvaisesReponses((int) $q['id_ques'], max(0, self::NB_CHOIX - count($bonnes)));
                $choix     = array_merge($bonnes, $mauvaises);
                shuffle($choix);

                $q['choix']    = $choix;
                $q['multiple'] = count($bonnes) > 1;
            }
            unset($q);

            $data['questions'] = $questions;
        }

        return view('header', $data) .
            view('owasp/headerView', $data) .
            view('owasp/quizView', $data) .
            view('owasp/footerView', $data);
    }
}