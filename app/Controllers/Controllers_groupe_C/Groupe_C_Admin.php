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

        if (in_array($categorie, ['disponibilite', 'integrite', 'confidentialite', 'tracabilite'], true)) {
            $model->select('mot_cle.*')
                ->join('conceptcyber', 'conceptcyber.id_mot_cle = mot_cle.id')
                ->where('conceptcyber.' . $categorie, 1);
        }

        $data['mots'] = $model->findAll();
        $data['concept'] =  array_column($modelConcept->findAll(), null, 'id_mot_cle');

        $idModif                = $this->request->getGet('modifier');
        $data['edition']        = $idModif ? $model->find($idModif) : null;
        $data['conceptEdition'] = $idModif ? ($data['concept'][$idModif] ?? null) : null;
        $data['questionEdition'] = $idModif
            ? \Config\Database::connect()->table('question_quiz')->where('id_mot_cle', (int) $idModif)->get()->getRowArray()
            : null;
        $data['creation']       = $this->request->getGet('creer') !== null;
        $data['motsDisponibles'] = [];
        if ($data['creation']) {
            $data['motsDisponibles'] = \Config\Database::connect()
                ->table('mot_cle')
                ->select('mot_cle.id, mot_cle.mot')
                ->join('conceptcyber', 'conceptcyber.id_mot_cle = mot_cle.id', 'left')
                ->where('conceptcyber.id_mot_cle IS NULL')
                ->orderBy('mot_cle.mot', 'ASC')
                ->get()
                ->getResultArray();
        }

        return view('header')
            . view('notice')
            . view ('groupe_c/groupe_c_ADMIN/groupe_c_admin', $data)
            . view('footer');
    }





    public function GroupeC_Modifier()
    {
        $modelConcept = new \App\Models\Model_groupe_C\ModelAdminCmot_cle_categorie();
        $db           = \Config\Database::connect();

        $idMot       = (int) $this->request->getPost('id_mot_cle');
        $question    = trim((string) $this->request->getPost('question'));
        $explication = trim((string) $this->request->getPost('explication'));

        $modelConcept->update($this->request->getPost('id_mot_cle'), [
            'disponibilite'   => $this->request->getPost('disponibilite') ? 1 : 0,
            'integrite'       => $this->request->getPost('integrite') ? 1 : 0,
            'confidentialite' => $this->request->getPost('confidentialite') ? 1 : 0,
            'tracabilite'     => $this->request->getPost('tracabilite') ? 1 : 0,
        ]);

        return redirect()->to(base_url('floribondant/QuizzConcept'));
    }
}
