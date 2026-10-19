<?php

namespace App\Controllers;

use App\Models\OwaspApiMenaceModel;

class OwaspApiMenaceController extends BaseController
{
    protected OwaspApiMenaceModel $apiMenace;
    public function __construct()
    {
        $this->apiMenace = new OwaspApiMenaceModel();
    }

    public function getMenace()
    {
        if (strtolower($this->request->getMethod()) === 'post')
        {
            $data = [];
            $compteur_temp= 1;
            $tableau_temp = $this->apiMenace->getMenace();
            foreach ($tableau_temp as $menaces)
            {
                $data['apiVulnerabilities'] =
                    [$menaces[$compteur_temp]->nom_menace, $menaces[$compteur_temp]->desc_menace];
                $compteur_temp += 1;
            }
            return view('owaspApi/headerView') .
                view('owaspApi/listeRisqueView', $data) .
                view('owaspApi/footerView');
        }
    }

}