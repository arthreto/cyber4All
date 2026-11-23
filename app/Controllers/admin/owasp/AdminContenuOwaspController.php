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
        $this->Menace = model(OwaspMenaceModel::class);
        $this->Top    = model(OwaspTopModel::class);
    }

    public function index()
    {
        return view('header') .
            view('admin/owasp/choixContenu') .
            view('footer');
    }

    public function choixTop($id)
    {
        $data = [
            'menacesWeb' => $this->Menace->getMenaces('WEB'),
            'menacesApi' => $this->Menace->getMenaces('API'),
        ];
        return view('header') .
            view('admin/owasp/tabOwaspContenu', $data) .
            view('footer');
    }

    public function addElement()
    {

    }
}