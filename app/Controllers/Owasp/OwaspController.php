<?php

namespace App\Controllers\Owasp;

use App\Controllers\BaseController;
use App\Models\Owasp\OwaspMenaceModel;
use App\Models\Owasp\OwaspTopModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class OwaspController extends BaseController
{

    protected OwaspMenaceModel $Menace;
    protected OwaspTopModel $Top;

    public function __construct()
    {
        $this->Menace = model(OwaspMenaceModel::class);
        $this->Top    = model(OwaspTopModel::class);
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
        $data['utilisateur'] = session()->get('utilisateur');

        return view('header', $data) .
            view('owasp/headerView', $data) .
            view('owasp/listeRisqueView', $data) .
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
}