<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\LienUtileModel;
use CodeIgniter\Config\Services;
use App\Entities\LienUtile;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Ce controller permet la gestion d'action des liens utiles
 * @since 1.0.0
 * @package Cyber4all
 * @author Benjamin Lorieul
 */
class LienUtileController extends BaseController
{
    /**
     * Permet d'ajouter un nouveau lien utile
     * @return \CodeIgniter\HTTP\RedirectResponse
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function insert()
    {
        // Vérification de la session et du role de l'utilisateur
        if (!$this->session->has('utilisateur')
            && !in_array($this->session->get('utilisateur')->role,
                ['admin'], true)) {
            throw new PageNotFoundException('Accès refusé');
        }

        $session = Services::session();
        // Récupérer l'id du mot si passé en url
        $validation = service('validation');
        //Règles de validation des données
        $validation->setRules([
            'titre' => 'required|min_length[3]|max_length[100]',
            'description' => 'required|min_length[3]|max_length[100]',
            'lien' => 'required'
        ],
            //gestion des messages d'erreurs
            [
                'titre' => ['required' => 'Le titre est obligatoire',
                    'min_length' => '3 car minimum',
                    'max_length' => '100 car maximum'],
                'description' => ['required' => 'La description est obligatoire',
                    'min_length' => '3 car minimum',
                    'max_length' => '100 car maximum'],
                'lien' => ['required' => 'Le lien est obligatoire']
            ]);
        //récupère les données
        $dataForm = [
            'titre' => $this->request->getPost('nouvTitre'),
            'description' => $this->request->getPost('nouvDescription'),
            'lien' => $this->request->getPost('nouvLien')
        ];
        //contrôle de validation des règles sur les données
        if ($validation->run($dataForm)) {
            $dataValide = $validation->getValidated();
            $lien_utile = new LienUtile(
                $dataValide
            );
            //Verifier la connexion de l'utilisateur
            $lien_utile_model = new LienUtileModel();
            if ($lien_utile_model->ajoutLienUtile($lien_utile)) {
                //notice ajout de lien réussi
                $data['noticeType'] = "SUCCES";
                $data['noticeTitre'] = "Ajout de lien utile";
                $data['noticeDetails'] = 'Le lien utile a bien été ajouté';
                session()->setFlashdata('infoLienUtile', $data);
                return redirect()->to(base_url());
            } else {
                //notice ajout de lien échoué
                $data['noticeType'] = "ERREUR";
                $data['noticeTitre'] = "Ajout de lien utile";
                $data['noticeDetails'] = 'Le lien utile a bien été ajouté';
                session()->setFlashdata('infoLienUtile', $data);
                return redirect()->to(base_url());
            }
        } else {
            log_message('warning', 'Erreur lors de l\'ajout de lien utile', []);
            return redirect()->to(base_url());
        }
    }
}