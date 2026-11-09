<?php

namespace App\Controllers;

use App\Models\OwaspModel;

class OwaspController extends BaseController
{
    protected OwaspModel $Menace;
    protected OwaspModel $Top;
    public function __construct()
    {
        $this->Menace = new OwaspModel();
        $this->Top = new OwaspModel();
    }

    public function getMenaces($top)
    {
        if ($top==1)
        {
            $data['Vulnerabilities'] = [];
            $tableau_temp = $this->Menace->getMenacesWeb();
            foreach ($tableau_temp as $menaces)
            {
                $nom = $menaces->nom_menace;
                $description = $menaces->desc_menace;
                $data['Vulnerabilities'][$nom] = $description;
            }
            $data['Top'] = $this->Top->getTopById($top);
        }
        else if ($top==2){
            $data['Vulnerabilities'] = [];
            $tableau_temp = $this->Menace->getMenacesApi();
            foreach ($tableau_temp as $menaces)
            {
                $nom = $menaces->nom_menace;
                $description = $menaces->desc_menace;
                $data['Vulnerabilities'][$nom] = $description;
            }
            $data['Top'] = $this->Top->getTopById($top);
        }
        if (!$data['Top']) { throw new \CodeIgniter\Exceptions\PageNotFoundException(); }
            return view('owasp/headerView', $data) .
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