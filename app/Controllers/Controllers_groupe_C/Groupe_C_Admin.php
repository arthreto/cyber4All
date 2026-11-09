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

        $valeurs = [
            'DISPONIBILITE'   => $this->request->getPost('DISPONIBILITE') ? 1 : 0,
            'INTEGRITE'       => $this->request->getPost('INTEGRITE') ? 1 : 0,
            'CONFIDENTIALITE' => $this->request->getPost('CONFIDENTIALITE') ? 1 : 0,
            'TRACABILITE'     => $this->request->getPost('TRACABILITE') ? 1 : 0,
        ];

        $db->transStart();

        // 1. Concepts cyber du terme
        $modelConcept->update($idMot, $valeurs);

        // 2. Question + explication : création ou modification
        $idQuestion = null;
        $existante  = $db->table('question_quiz')->where('id_mot_cle', $idMot)->get()->getRowArray();

        if ($existante) {
            $idQuestion = (int) $existante['id'];

            $maj = ['explication' => $explication !== '' ? $explication : null];
            if ($question !== '') {
                $maj['question'] = $question;   // on n'efface jamais une question existante
            }
            $db->table('question_quiz')->where('id', $idQuestion)->update($maj);

        } elseif ($question !== '') {
            $db->table('question_quiz')->insert([
                'id_mot_cle'  => $idMot,
                'question'    => $question,
                'explication' => $explication !== '' ? $explication : null,
            ]);
            $idQuestion = $db->insertID();
        }

        // 3. Bonnes réponses de la question (question_reponse) synchronisées avec les cases cochées
        if ($idQuestion) {
            $db->table('question_reponse')->where('id_question', $idQuestion)->delete();

            $ids = ['DISPONIBILITE' => 1, 'INTEGRITE' => 2, 'CONFIDENTIALITE' => 3, 'TRACABILITE' => 4];
            foreach ($ids as $colonne => $idConcept) {
                if ($valeurs[$colonne] === 1) {
                    $db->table('question_reponse')->insert([
                        'id_question' => $idQuestion,
                        'id_concept'  => $idConcept,
                    ]);
                }
            }
        }

        $db->transComplete();

        // On reste sur le terme en cours de modification
        return redirect()->to(base_url('floribondant/QuizzConcept') . '?modifier=' . $idMot);
    }
    public function GroupeC_Ajouter()
    {
        $idMot = (int) $this->request->getPost('id_mot_cle');
        $db    = \Config\Database::connect();

        $motExiste   = $db->table('mot_cle')->where('id', $idMot)->countAllResults() > 0;
        $dejaPresent = $db->table('conceptcyber')->where('id_mot_cle', $idMot)->countAllResults() > 0;

        if ($motExiste && !$dejaPresent) {
            $db->table('conceptcyber')->insert([
                'id_mot_cle'      => $idMot,
                'DISPONIBILITE'   => 0,
                'INTEGRITE'       => 0,
                'CONFIDENTIALITE' => 0,
                'TRACABILITE'     => 0,
            ]);
        }

        return redirect()->to(base_url('floribondant/QuizzConcept') . '?modifier=' . $idMot);
    }
    public function GroupeC_Supprimer()
    {
        $idMot = (int) $this->request->getPost('id_mot_cle');

        if ($idMot > 0) {
            \Config\Database::connect()
                ->table('conceptcyber')
                ->where('id_mot_cle', $idMot)
                ->delete();
        }

        return redirect()->to(base_url('floribondant/QuizzConcept'));
    }
}
