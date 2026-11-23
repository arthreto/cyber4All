<?php

namespace App\Controllers\Controllers_groupe_C;

use App\Controllers\BaseController;
use App\Entities\Utilisateur;
use App\Models\Model_groupe_C\ConceptCyberModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Session\Session;
use Config\Database;
use Psr\Log\LoggerInterface;

/**
 * CONTRÔLEUR PARENT des pages d'administration du quiz Concept (groupe C).
 *
 * QUI FAIT QUOI
 * -------------
 *  - Elle est héritée par :
 *        Dict_Concept_Admin_controller   (page des termes)
 *        Dict_Question_Admin_Controller  (page des questions)
 *  - Elle fournit à ses enfants :
 *        1. le contrôle d'accès admin (initController) : exécuté à chaque ouverture d'une page admin
 *        2. synchroniserReponses()                      : recalcule les bonnes réponses d'une question
 *
 * Le comportement est identique : 404 si l'utilisateur n'est pas admin.
 */
abstract class DictAdminBaseController extends BaseController
{
    /**
     * Correspondance entre le nom de la colonne (table conceptcyber) et l'id du concept en base.
     * Utilisée par synchroniserReponses().
     */
    private const ID_CONCEPTS = [
        'disponibilite'   => 1,
        'integrite'       => 2,
        'confidentialite' => 3,
        'tracabilite'     => 4,
    ];

    protected Session $session;                 // la session de l'utilisateur connecté
    protected ?Utilisateur $utilisateur = null; // l'utilisateur connecté (null si personne)

    /**
     * S'exécute automatiquement à chaque fois qu'une page admin est ouverte.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);

        $this->session = session();
        $utilisateur   = $this->session->get('utilisateur'); // on récupère l'utilisateur en session

        // Si l'utilisateur existe ET qu'il est admin : accès autorisé
        if ($utilisateur && $utilisateur->role === 'admin') {
            $this->utilisateur = $utilisateur;

            return;
        }

        // Sinon on affiche une 404 (on cache l'existence de la page)
        throw PageNotFoundException::forPageNotFound('Accès refusé');
    }

    /**
     * Met à jour les "bonnes réponses" d'une question (table question_reponse)
     * en fonction des cases D/I/C/T cochées sur le terme associé.
     *
     * Appelée par :
     *  - Dict_Concept_Admin_controller::GroupeC_Modifier()          (quand on change les cases d'un terme)
     *  - Dict_Question_Admin_Controller::GroupeC_ModifierQuestion()  (quand on change le terme d'une question)
     *
     * $idQuestion = la question à mettre à jour
     * $idMot      = le terme associé (ou null si aucun)
     */
    protected function synchroniserReponses(int $idQuestion, ?int $idMot): void
    {
        // Pas de modèle pour question_reponse : on utilise le query builder.
        // Database::connect() renvoie la connexion partagée : elle fait donc partie de la transaction du contrôleur.
        $reponses = Database::connect()->table('question_reponse');

        // 1. On efface d'abord toutes les anciennes bonnes réponses de cette question
        $reponses->where('id_question', $idQuestion)->delete();

        // 2. Pas de terme associé = pas de bonnes réponses, on s'arrête là
        if (! $idMot) {
            return;
        }

        // 3. On lit les cases cochées du terme (table conceptcyber)
        $concept = (new ConceptCyberModel())->getParMot($idMot); // modèle conceptcyber
        if (! $concept) {
            return; // le terme n'est pas encore classé : rien à faire
        }

        // 4. Pour chaque case cochée (valeur 1), on crée une bonne réponse
        foreach (self::ID_CONCEPTS as $colonne => $idConcept) {
            if ((int) $concept[$colonne] === 1) {
                $reponses->insert([
                    'id_question' => $idQuestion,
                    'id_concept'  => $idConcept,
                ]);
            }
        }
    }
}