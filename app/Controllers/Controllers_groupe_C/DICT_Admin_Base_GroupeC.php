<?php

namespace App\Controllers\Controllers_groupe_C;

use App\Controllers\BaseController;
use App\Entities\Utilisateur;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Session\Session;

// "abstract" = cette classe sert de modèle, on ne l'appelle jamais directement via une route.
// Les autres contrôleurs en héritent pour récupérer son code.
abstract class DICT_Admin_Base_GroupeC extends BaseController
{
    protected Session $session;               // la session de l'utilisateur connecté
    protected ?Utilisateur $utilisateur = null; // l'utilisateur connecté (null si personne)

    // Le constructeur s'exécute automatiquement à chaque fois qu'une page admin est ouverte
    public function __construct()
    {
        $this->session = session();
        $utilisateur = $this->session->get('utilisateur'); // on récupère l'utilisateur en session

        // Si l'utilisateur existe ET qu'il est admin : accès autorisé
        if ($utilisateur && $utilisateur->role === 'admin') {
            $this->utilisateur = $utilisateur;
        } else {
            // Sinon on affiche une 404 (on cache l'existence de la page)
            throw PageNotFoundException::forPageNotFound('Accès refusé');
        }
    }

    /**
     * Met à jour les "bonnes réponses" d'une question (table question_reponse)
     * en fonction des cases D/I/C/T cochées sur le terme associé.
     *
     * $db         = la connexion à la base
     * $idQuestion = la question à mettre à jour
     * $idMot      = le terme associé (ou null si aucun)
     */
    protected function synchroniserReponses($db, int $idQuestion, ?int $idMot): void
    {
        // 1. On efface d'abord toutes les anciennes bonnes réponses de cette question
        $db->table('question_reponse')->where('id_question', $idQuestion)->delete();

        // 2. Pas de terme associé = pas de bonnes réponses, on s'arrête là
        if (!$idMot) {
            return;
        }

        // 3. On lit les cases cochées du terme (table conceptcyber)
        $concept = $db->table('conceptcyber')->where('id_mot_cle', $idMot)->get()->getRowArray();
        if (!$concept) {
            return; // le terme n'est pas encore classé : rien à faire
        }

        // 4. Correspondance entre le nom de la colonne et l'id du concept en base
        $ids = ['disponibilite' => 1, 'integrite' => 2, 'confidentialite' => 3, 'tracabilite' => 4];

        // 5. Pour chaque case cochée (valeur 1), on crée une bonne réponse
        foreach ($ids as $colonne => $idConcept) {
            if ((int) $concept[$colonne] === 1) {
                $db->table('question_reponse')->insert([
                    'id_question' => $idQuestion,
                    'id_concept'  => $idConcept,
                ]);
            }
        }
    }
}