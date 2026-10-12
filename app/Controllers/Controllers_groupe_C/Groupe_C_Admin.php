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

        $idModif                = $this->request->getGet('modifier');
        $data['edition']        = $idModif ? $model->find($idModif) : null;
        $data['conceptEdition'] = $idModif ? ($data['concept'][$idModif] ?? null) : null;
        $data['creation']       = $this->request->getGet('creer') !== null;

        return view('header')
            . view('notice')
            . view ('groupe_c/groupe_c_ADMIN/groupe_c_admin', $data)
            . view('footer');
    }





    public function GroupeC_Modifier()
    {
        $modelConcept = new \App\Models\Model_groupe_C\ModelAdminCmot_cle_categorie();

        $modelConcept->update($this->request->getPost('id_mot_cle'), [
            'DISPONIBILITE'   => $this->request->getPost('DISPONIBILITE') ? 1 : 0,
            'INTEGRITE'       => $this->request->getPost('INTEGRITE') ? 1 : 0,
            'CONFIDENTIALITE' => $this->request->getPost('CONFIDENTIALITE') ? 1 : 0,
            'TRACABILITE'     => $this->request->getPost('TRACABILITE') ? 1 : 0,
        ]);

        return redirect()->to(base_url('groupe_c/groupe_c_ADMIN/groupe_c_admin'));
    }
}