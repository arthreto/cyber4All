<?php

namespace App\Controllers\Owasp;

use App\Controllers\BaseController;
use App\Models\Owasp\OwaspMenaceModel;
use App\Models\Owasp\OwaspTopModel;

class OwaspController extends BaseController
{
    protected OwaspMenaceModel $Menace;
    protected OwaspTopModel $Top;
    public function __construct()
    {
        $this->Menace = new OwaspMenaceModel();
        $this->Top = new OwaspTopModel();
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
            $data['nom_top'] = 'web';
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
            $data['nom_top'] = 'API';
        }
        if (!$data['Top']) { throw new \CodeIgniter\Exceptions\PageNotFoundException(); }
            return view('header', $data) .
                view('owasp/headerView', $data) .
                view('owasp/listeRisqueView', $data) .
                view('owasp/choixQuizView') .
                view('owasp/footerView');
    }

    public function getMenace($top)
    {
        $menace = $this->Menace->getMenace($top);
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
        return view('owasp/headerView', $data) .
            view('owasp/MenaceView', $data) .
            view('owasp/footerView');
    }

    public function getQuiz($top)
    {

    }
}