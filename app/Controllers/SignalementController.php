<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SignalementModel;
use App\Entities\Signalement;
use App\Entities\Utilisateur;
use CodeIgniter\Config\Services;
use CodeIgniter\Validation\Validation;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Session\Session;

/**
 * Ce controller permet la gestion des signalements
 * @since 1.0.0
 * @package Cyber4all
 * @author Benjamin Lorieul
 */
class SignalementController extends BaseController
{
    protected SignalementModel $signalement_model;
    protected Signalement $signalement;
    protected Validation $validation;
    protected Session $session;
    protected ?Utilisateur $utilisateur = null; // nullable puisque pas obligatoire

    public function __construct()
    {
        $this->signalement_model = new SignalementModel();
        $this->session = session();
        $this->validation = Services::validation();
        $this->signalement = new Signalement();
        // Vérification de la session et du rôle de l'utilisateur
        $utilisateur = $this->session->get('utilisateur');

        if ($utilisateur && in_array($utilisateur->role, ['contributeur', 'admin'], true)) {
            $this->utilisateur = $utilisateur;

        } else {
            throw PageNotFoundException::forPageNotFound('Accès refusé');
        }
    }

    public function ajouterSignalement()
    {
        // si l'utilisateur n'est pas contributeur alors, il n'a pas accès
        if ($this->utilisateur->role !== 'contributeur') {
            throw new PageNotFoundException('Accès refusé');
        }

        $validation = service('validation');

        // Règles de validation des données
        $validation->setRules(
            [
                'motif' => 'required|min_length[15]|max_length[100]',
                'idMot' => 'required|is_not_unique[mot_cle.id]' // Correction du nom du champ
            ],
            // Gestion des messages d'erreurs
            [
                'motif' => [
                    'required' => 'Le motif est obligatoire',
                    'min_length' => '15 caractères minimum',
                    'max_length' => '100 caractères maximum'
                ],
                'idMot' => [
                    'required' => 'L\'ID du mot est obligatoire',
                    'is_not_unique' => 'L\'ID n\'existe pas dans la base de données.'
                ]
            ]
        );

        // Récupère les données
        $dataForm = [
            'motif' => $this->request->getPost('motif'),
            'idMot' => $this->request->getPost('idMot'), // Correction : cohérence avec le nom du champ
            'id_utilisateur' => $this->utilisateur->id
        ];

        // Contrôle de validation des règles sur les données
        if ($validation->run($dataForm)) {
            $dataValide = $validation->getValidated();

            // Préparer les données pour l'entité Signalement
            $signalementData = [
                'motif' => $dataValide['motif'],
                'id_mot_cle' => $dataValide['idMot'],
                'id_utilisateur' => $dataForm['id_utilisateur']
            ];

            // Créer l'entité Signalement
            $signalement = new Signalement($signalementData);

            // Insérer le signalement
            $result = $this->signalement_model->signalerMotCle($signalement);

            // Gestion des résultats
            switch ($result) {
                case -1:
                    $this->session->setFlashdata('notice', [
                        'type' => 'error',
                        'titre' => "Échec lors du signalement",
                        'details' => "Vous avez déjà signaler ce mot ce mois-ci",
                    ]);
                    break;
                case true :
                    $this->session->setFlashdata('notice', [
                        'type' => 'success',
                        'titre' => "Signalement pris en compte",
                        'details' => "Si d'autres contributeurs viennent a signalé ce mot, il sera provisoirement masqué jusqu'à ce qu'un administrateur le modifie",
                    ]);
                    break;
                default :
                    $this->session->setFlashdata('notice', [
                        'type' => 'error',
                        'titre' => "Échec lors du signalement",
                        'details' => "Le signalement n'a pas été pri sen compte",
                    ]);
                    break;
            }
            return redirect()->back();
        } else {
            // Validation échouée
            $this->session->setFlashdata('errors', $validation->getErrors());
            return redirect()->back();
        }
    }

    public function supprimerSignalement()
    {
        // si l'utilisateur n'est pas admin il n'a pas accès
        if ($this->utilisateur->role !== 'admin') {
            throw new PageNotFoundException('Accès refusé');
        }

        // Vérification de la session et du role de l'utilisateur
        if (!$this->session->has('utilisateur')
            || !in_array($this->session->get('utilisateur')->role, ['admin'], true)) {
            throw new PageNotFoundException("Accès refuser");
        }

        $this->validation->setRules([
            'id_signalement' => 'required'
        ],
            //gestion des messages d'erreurs
            [
                'id_signalement' => ['required' => "L'id du signalement est obligatoire"]
            ]);

        //récupère les données
        $dataForm = [
            'id_signalement' => (int)$this->request->getPost('idSignalement')
        ];

        //contrôle de validation des règles sur les données
        if ($this->validation->run($dataForm)) {
            $dataValide = $this->validation->getValidated();
            // Récupère le mot par son id
            $signalement = $this->signalement_model->getSignalementById($dataValide['id_signalement']);
            if (!$signalement) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('$sgnalement introuvable');
            }
            // Faire le switch du statut
            $result = $this->signalement_model->supprimerSignalement($signalement->id_signalement);

            // Gestion des résultats
            if ($result) {
                $this->session->setFlashdata('notice', [
                    'type' => 'success',
                    'titre' => "Suppression réussie",
                    'details' => "Le signalement {$signalement->id_signalement} à bien été supprimé",
                ]);
            } else {
                $this->session->setFlashdata('notice', [
                    'type', "error",
                    'titre' => "Suppression échouée",
                    'details' => "Le signalement {$signalement->id_signalement} a rencontré un problème, il n'a pas été supprimé",
                ]);
            }
            return redirect()->back();
        } else {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Données invalides');
        }
    }
}