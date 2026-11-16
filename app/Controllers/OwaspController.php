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
            // 0 = le top 10 Web
            $tableau_temp = $this->Menace->getMenaces(0);
            foreach ($tableau_temp as $menaces)
            {
                $nom = $menaces->nom_menace;
                $data['Vulnerabilities'][$nom] = $menaces->id_menace;
            }
            $data['Top'] = $this->Top->getTopById($top);
            $data['nom_top'] = 'web';
        }
        else if ($top==2){
            $data['Vulnerabilities'] = [];
            // 10 = le top 10 Api
            $tableau_temp = $this->Menace->getMenaces(10);
            foreach ($tableau_temp as $menaces)
            {
                $nom = $menaces->nom_menace;
                $data['Vulnerabilities'][$nom] = $menaces->id_menace - 10;
            }
            $data['Top'] = $this->Top->getTopById($top);
            $data['nom_top'] = 'api';
        }
        if (!$data['Top']) { throw new \CodeIgniter\Exceptions\PageNotFoundException(); }
            return view('owasp/headerView', $data) .
                view('owasp/listeRisqueView', $data) .
                view('owasp/footerView');
    }

    public function test1bis ($top, $num)
    {
        if ($top === 'web')
        {
            $test = $this->Menace->test1($num);
            $data['nom'] = $test[0]->nom_menace;
            $data['description'] = $test[0]->desc_menace;
            $data['prog'] = $test[0]->prog_menace;
            $Y = 0;
            foreach ($test as $x)
            {
                $Y += 1;
                $data['prog_cm'][$Y] = $x->prog_cm;
                $data['description_cm'][$Y] = $x->desc_cm;
            }
            $data['Top'] = $this->Top->getTopById(1);
        }
        if ($top==='api')
        {
            $test = $this->Menace->test1($num + 10);
            $data['nom'] = $test[0]->nom_menace;
            $data['description'] = $test[0]->desc_menace;
            $data['prog'] = $test[0]->prog_menace;
            $Y = 0;
            foreach ($test as $x)
            {
                $Y += 1;
                $data['prog_cm'][$Y] = $x->prog_cm;
                $data['description_cm'][$Y] = $x->desc_cm;
            }
            $data['Top'] = $this->Top->getTopById(2);
        }
        return view('owasp/headerView', $data) .
            view('owasp/apiMenaceView', $data) .
            view('owasp/footerView');
    }
}