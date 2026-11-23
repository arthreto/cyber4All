<?php

namespace App\Controllers\Controllers_groupe_C;

use App\Controllers\BaseController;
use App\Entities\Utilisateur;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Session\Session;
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
 *        (synchroniserReponses() n'est plus ici : elle est dans QuestionReponseModel::synchroniser())
 *
 * Le comportement est identique : 404 si l'utilisateur n'est pas admin.
 */
abstract class DictAdminBaseController extends BaseController
{
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
}