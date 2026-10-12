<?php

namespace App\Controllers\Controllers_groupe_C;

use App\Controllers\BaseController;
use App\Entities\Utilisateur;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Session\Session;


class Groupe_C_Admin extends BaseController
{
    protected Session $session;
    protected ?Utilisateur $utilisateur = null;


    public function __construct()
    {
        $this->session = session();

        $utilisateur = $this->session->get('utilisateur');

        if ($utilisateur && $utilisateur->role === 'admin') {
            $this->utilisateur = $utilisateur;

        } else {
            throw PageNotFoundException::forPageNotFound('Accès refusé');
        }
    }
    public function GroupeC_Admin(): string
    {


        $model = new \App\Models\Model_groupe_C\ModelAdminCmot_cle();
        $modelConcept = new \App\Models\Model_groupe_C\ModelAdminCmot_cle_categorie();


        $recherche = $this->request->getGet('recherche');
        if (!empty($recherche)) {
            $model->like('mot', $recherche, 'both', null, true);
        }

        $categorie = $this->request->getGet('concept');

        if (in_array($categorie, ['DISPONIBILITE', 'INTEGRITE', 'CONFIDENTIALITE', 'TRACABILITE'], true)) {
            $model->select('mot_cle.*')
                ->join('conceptcyber', 'conceptcyber.id_mot_cle = mot_cle.id')
                ->where('conceptcyber.' . $categorie, 1);
        }


        $data['mots'] = $model->findAll();
        $data['concept'] =  array_column($modelConcept->findAll(), null, 'id_mot_cle');




        return view('header')
            . view('notice')
            . view ('groupe_c/groupe_c_ADMIN/groupe_c_admin', $data)
            . view('footer');
    }
}