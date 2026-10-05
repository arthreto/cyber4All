<?php

namespace App\Controllers;

use App\Entities\Utilisateur;
use App\Models\CategorieModel;
use App\Models\ImageModel;
use App\Models\MotCle\GlossaireModel;
use App\Models\MotCle\MotCleModel;
use App\Models\SignalementModel;
use App\Models\VoteModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Session\Session;
use \Collator;
use App\Models\MotCle\MotCleVoteModel;

/**
 * Ce controller permet la gestion de la page du glossaire
 * @since 1.0.0
 * @package Cyber4all
 * @author Benjamin Lorieul
 */
class GlossaireController extends BaseController
{
    protected GlossaireModel $glossaire_model;
    protected ?Utilisateur $utilisateur = null; // nullable puisque pas obligatoire
    protected VoteModel $vote_model;
    protected MotCleModel $motCle_model;
    protected CategorieModel $categorie_model;
    protected ImageModel $image_model;
    protected Session $session;
    protected SignalementModel $signalement_model;
//    protected Collator $collator;
    protected MotCleVoteModel $motCleVote_model;

    public function __construct()
    {
        $this->vote_model = new VoteModel();
        $this->motCle_model = new MotCleModel();
        $this->categorie_model = new CategorieModel();
        $this->glossaire_model = new GlossaireModel();
        $this->session = \Config\Services::session();
        $this->image_model = new ImageModel();
        $this->signalement_model = new SignalementModel();
        $this->motCleVote_model = new MotCleVoteModel();
//        $this->collator = new Collator("fr_FR");

        // Vérification de la session et du rôle de l'utilisateur
        $utilisateur = $this->session->get('utilisateur');

        if ($utilisateur && in_array($utilisateur->role, ['contributeur', 'admin'], true)) {
            $this->utilisateur = $utilisateur;
        }

        if ($utilisateur && $utilisateur->role === 'admin') {
            throw PageNotFoundException::forPageNotFound('Accès refusé');
        }
    }

    /**
     * Affichage complet du glossaire
     * ou d'une recherche sur tout le glossaire
     * @param string|null $recherche
     * @return string
     */
    public function afficherGlossaire()
    {
        $recherche = trim($this->request->getGet('recherche') ?? '');

        $perPage = 20;
        $glossaire = $this->glossaire_model
            ->getGlossairePaginate($perPage, $recherche);

        $glossaireAlpha = $this->grouperParLettre($glossaire);

        $data = [
            'glossaire' => $glossaireAlpha,
            'perPage' => $perPage,
            'total' => !empty($recherche)
                ? count($glossaire)
                : $this->motCle_model->getNbTotalGlossaire(),
            'from' => base_url('glossaire'),
            'pager' => $this->glossaire_model->pager,
            'categories' => $this->categorie_model->getAllCategories(),
            'contenu' => empty($recherche) ? 'glossaire' : 'recherche',
            'recherche' => $recherche,
            'topMots' => $this->motCleVote_model->getMotsPopulairesSemainePrecedente(10),
        ];

        $data = array_merge($data, $this->setDataGlossaire());

        return view('header', $data)
            . view('notice')
            . view('recherche')
            . view('glossaire/glossaire')
            . view('footer');
    }

    public function rechercherGlossaire()
    {
        if (!$this->request->is('post')) {
            return redirect()->back();
        }

        $recherche = trim($this->request->getPost('recherche'));

        return redirect()->to(
            base_url('glossaire?recherche=' . urlencode($recherche))
        );
    }

    /**
     * afficherCategorie permet d'afficher le glossaire lié à
     * l'id de la catégorie passé en paramètre
     * @param int $idCat
     * @return string
     */
    public function afficherCategorie(int $idCat)
    {

        $glossaire = $this->glossaire_model->getGlossaireCategorie($idCat); // Glossaire de la catégorie

        // Groupement alphabétique
        $glossaireAlpha = $this->grouperParLettre($glossaire);

        $data = [
            'glossaire' => $glossaireAlpha,
            'total' => count($glossaire),
            'from' => base_url('glossaire'),
            'categorie' => $this->categorie_model->getCategorieById($idCat),
            'categories' => $this->categorie_model->getAllCategories(),
            'contenu' => 'categorie',
        ];
        $data = array_merge($data, $this->setDataGlossaire());  // Permet de fusionner les $data

        return view('header', $data)
            . view('notice')
            . view('recherche')
            . view('glossaire/glossaire')
            . view('footer');
    }

    /**
     * Détail d'un mot
     */
    public function afficherMot($idMot)
    {
        $data = [
            'mot' => $this->motCle_model->getMotCleById($idMot),
            'categoriesMot' => $this->categorie_model->getCategoriesMotcle($idMot),
            'images' => $this->image_model->getImagesByMotId($idMot),
            'from' => base_url('glossaire'),
            'contenu' => 'mot_seul',
        ];

        if ($this->utilisateur) {
            $data['tab_id_mots'] = $this->vote_model->getIdMotCleVotesSemaineActuelleByUtilisateurId($this->utilisateur->id);
            $data['nb_votes'] = $this->vote_model->getNbVotesSemaineActuelleByUtilisateurId($this->utilisateur->id);
            $data = array_merge($data, $this->setDataGlossaire()); // Permet de fusionner les $data
            // ids des mots déjà signalés par l'utilisateur
            $data['motsSignales'] = $this->signalement_model->getSignalementIdsUtilisateur($this->utilisateur->id);
        }

        $data['categories'] = $this->categorie_model->getAllCategories();
        return view('header', $data)
            . view('notice')
            . view('recherche')
            . view('glossaire/motSeul')
            . view('footer');
    }



    // ==============================
    // MÉTHODES PRIVÉES UTILITAIRES
    // ==============================

    /**
     * Groupement alphabétique
     */
    private function grouperParLettre($mots)
    {
        $grouped = [];

        foreach ($mots as $mot) {
            $lettre = mb_strtoupper(mb_substr($mot->mot, 0, 1));
            $grouped[$lettre][] = $mot;
        }

//        ksort($grouped); // prie par code ASCII
//        $collator = $this->collator;
//
//        uksort($grouped, function ($a, $b) use ($collator) {
//            return $collator->compare($a, $b);
//        });
        return $grouped;
    }

    /**
     * setDataMotsGlossaire permet de définir les variables de data.
     * Utiliser pour afficher le glossaire complet ou le lexique d'une catégorie avec une navbar.
     * @return array Tableau contenant les variables de data
     * @since 1.0.0
     * @author Benjamin Lorieul
     */
    private function setDataGlossaire(): array
    {
        // Vérification de la session et du role de l'utilisateur
        if ($this->utilisateur) {
            $data['tab_id_mots'] = $this->vote_model->getIdMotCleVotesSemaineActuelleByUtilisateurId($this->utilisateur->id);
            $data['nb_votes'] = $this->vote_model->getNbVotesSemaineActuelleByUtilisateurId($this->utilisateur->id);
            $data['motsSignales'] = $this->signalement_model->getSignalementIdsUtilisateur($this->utilisateur->id);
        }

        return $data ?? [];
    }
}