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

    public function getMenaces($top): string
    {
        if ($top=='WEB')
        {
            $data['Vulnerabilities'] = [];
            $tableau_temp = $this->Menace->getMenaces('WEB');
            foreach ($tableau_temp as $menaces)
            {
                $nom = $menaces->nom_menace;
                $data['Vulnerabilities'][$nom] = $menaces->id_menace;
            }
            $data['Top'] = $this->Top->getTopById($top);
        }
        else if ($top=='API'){
            $data['Vulnerabilities'] = [];
            $tableau_temp = $this->Menace->getMenaces('API');
            foreach ($tableau_temp as $menaces)
            {
                $nom = $menaces->nom_menace;
                $data['Vulnerabilities'][$nom] = ($menaces->id_menace)-10;
            }
            $data['Top'] = $this->Top->getTopById($top);
        }
        if (!$data['Top']) { throw new \CodeIgniter\Exceptions\PageNotFoundException(); }
            return view('owasp/headerView', $data) .
                view('owasp/listeRisqueView', $data) .
                view('owasp/footerView');
    }

    public function getMenace($id)
    {
        $menace = $this->Menace->getMenace($id);
        $data['nom'] = $menace[0]->nom_menace;
        $data['description'] = $menace[0]->desc_menace;
        $data['prog'] = $menace[0]->prog_menace;
        foreach ($menace as $element)
        {
            $i =+ 1;
            $data['prog_cm'][$i] = $element->prog_cm;
            $data['description_cm'][$i] = $element->desc_cm;
        }
        $data['Top'] = $this->Top->getTopById($top);
        return view('owasp/headerView') .
            view('owasp/apiMenaceView', $data) .
            view('owasp/footerView');
    }

}