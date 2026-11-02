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

    public function getMenaces()
    {
            $data['apiVulnerabilities'] = [];
            $tableau_temp = $this->apiMenace->getMenaces();
            foreach ($tableau_temp as $menaces)
            {
                $nom = $menaces->nom_menace;
                $description = $menaces->desc_menace;
                $data['apiVulnerabilities'][$nom] = $description;
            }
            return view('owasp/headerView') .
                view('owasp/listeRisqueView', $data) .
                view('owasp/footerView');
    }

}