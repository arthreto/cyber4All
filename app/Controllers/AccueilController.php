<?php

namespace App\Controllers;

use App\Entities\MotCle;
use App\Entities\Utilisateur;
use App\Models\CategorieModel;
use App\Models\LienUtileModel;
use App\Models\MotCle\GlossaireModel;
use App\Models\MotCle\MotCleModel;
use App\Models\MotCle\MotCleVoteModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Session\Session;

/**
 * Ce controller permet la gestion de la page d'accueil
 * @since 1.0.0
 * @package Cyber4all
 * @author Benjamin Lorieul
 */
class AccueilController extends BaseController
{
    protected CategorieModel $categorie_model;
    protected MotCleModel $motCle_model;
    protected LienUtileModel $lienUtile_model;
    protected GlossaireModel $glossaire_model;
    protected Session $session;
    protected ?Utilisateur $utilisateur = null;
    protected MotCleVoteModel $motCleVote_model;


    public function __construct()
    {
        $this->categorie_model = new categorieModel();
        $this->motCle_model = new motCleModel();
        $this->motCleVote_model = new motCleVoteModel();
        $this->glossaire_model = new GlossaireModel();
        $this->lienUtile_model = new LienUtileModel();
        $this->session = session();
        $utilisateur = $this->session->get('utilisateur');
        if ($utilisateur && in_array($utilisateur->role, ['contributeur', 'admin'], true)) {
            $this->utilisateur = $utilisateur;
        }

        if ($utilisateur && $utilisateur->role === 'admin') {
            throw PageNotFoundException::forPageNotFound('Accès refusé');
        }
    }

    /**
     * Récupère 10 mots aléatoires.
     * Récupère les liens utiles.
     * @return string Affichage principale de l'accueil
     *
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    public function index(): string
    {
        $data = [
            'motsAleatoires' => $this->glossaire_model->getGlossaireRand(10),
            'liensUtiles' => $this->lienUtile_model->getAllLiensUtiles(),
            'topMots' => $this->motCleVote_model->getMotsPopulairesSemainePrecedente(5),
        ];

        return view('header', $data)
            . view('notice')
            . view('recherche')
            . view('home/carrousel_mots')
            . view('home/topMots')
            . view('home/lien_utile')
            . view('footer');
    }
}