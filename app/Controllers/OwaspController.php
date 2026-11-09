<?php

namespace App\Controllers;

use App\Models\OwaspModel;

class OwaspController extends BaseController
{
    protected OwaspModel $apiMenace;
    public function __construct()
    {
        $this->apiMenace = new OwaspModel();
    }

    public function getMenaces($num)
    {
            $data['Vulnerabilities'] = [];
            $tableau_temp = $this->apiMenace->getMenaces($num);
            foreach ($tableau_temp as $menaces)
            {
                $data['Vulnerabilities'][$menaces->nom_menace] = $menaces->id_menace;
            }
            return view('owasp/headerView') .
                view('owasp/listeRisqueView', $data) .
                view('owasp/footerView');
    }

    public function test1bis ($id)
    {
        $test = $this->apiMenace->test1($id);
        $data['nom'] = $test[0]->nom_menace;
        $data['description'] = $test[0]->desc_menace;
        $data['prog'] = $test[0]->prog_menace;
        foreach ($test as $x)
        {
            $Y =+ 1;
            $data['prog_cm'][$Y] = $x->prog_cm;
            $data['description_cm'][$Y] = $x->desc_cm;
        }
        return view('owasp/headerView') .
            view('owasp/apiMenaceView', $data) .
            view('owasp/footerView');
    }

}