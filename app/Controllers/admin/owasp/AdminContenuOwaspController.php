<?php

namespace App\Controllers\admin\owasp;

use App\Controllers\BaseController;
use App\Models\Owasp\OwaspMenaceModel;
use App\Models\Owasp\OwaspTopModel;

class AdminContenuOwaspController extends BaseController
{
    protected OwaspMenaceModel $menace;
    protected OwaspTopModel $top;

    public function __construct()
    {
        $this->menace = model(OwaspMenaceModel::class);
        $this->top    = model(OwaspTopModel::class);
    }

    public function index()
    {
        $data = ['tops' => $this->top->findAll()];
        return view('header') .
            view('admin/owasp/choixContenu', $data) .
            view('footer');
    }

    public function choixTop($id)
    {
        $top = $this->top->getTopById($id);
        if ($top === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'top' => $top,
            'menaces' => $this->menace->getMenaces($id),
        ];
        return view('header') .
            view('admin/owasp/tabOwaspContenu', $data) .
            view('footer');
    }

    public function modifElement(string $top, int $id)
    {
        $menace = $this->menace->getMenace($top, $id);
        if ($menace === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'menace' => $menace,
            'contreMesures' => $this->menace->getMenaceContreMesures($id),
        ];

        return view('header') .
            view('admin/owasp/modifElement', $data) .
            view('footer');
    }
}