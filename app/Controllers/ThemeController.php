<?php

namespace App\Controllers;

use App\Entities\Utilisateur;
use App\Models\CategorieModel;
use App\Models\ImageModel;
use App\Models\MotCle\GlossaireModel;
use App\Models\MotCle\MotCleModel;
use App\Models\SignalementModel;
use App\Models\ThemeModel;
use App\Models\LeconModel;
use App\Models\VoteModel;
use App\Models\UtilisateurModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Session\Session;
use \Collator;
use App\Models\MotCle\MotCleVoteModel;

/**
 * Ce controller permet la gestion de la page des leçons
 * @since 1.0.0
 * @package Cyber4all
 * @author Benjamin Lorieul
 */
class ThemeController extends BaseController
{
    protected ThemeModel $theme_model;
    protected LeconModel $lecon_model;
    protected UtilisateurModel $utilisateur_model;

    public function __construct()
    {
        $this->theme_model = new ThemeModel();
        $this->lecon_model  = new LeconModel();
        $this->utilisateur_model = new UtilisateurModel();
    }


    /**
     * Affichage d'une page de leçon
     */
    public function afficherPageLecon(int $idTheme, ?int $numero = null)
    {
        $leconsTheme = $this->lecon_model->getLeconsByTheme($idTheme);

        // Thème inexistant ou sans leçon
        if ($leconsTheme === []) {
            throw PageNotFoundException::forPageNotFound();
        }

        // URL theme/idTheme : redirection vers la première leçon du thème
        if ($numero === null) {
            return redirect()->to(site_url('theme/' . $idTheme . '/1'));
        }

        // Contrôle de bornes
        $nbLecons = count($leconsTheme);
        if ($numero < 1 || $numero > $nbLecons) {
            throw PageNotFoundException::forPageNotFound();
        }

        $lecon = $this->lecon_model->getLeconById($leconsTheme[$numero - 1]->id);

        // Leçon introuvable (erreur SQL ou supprimée entre les deux requêtes)
        if ($lecon === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        // Recherche de l'auteur grâce à son ID
        $auteur = null;
        if ($lecon->id_auteur !== null) {
            $auteur = $this->utilisateur_model->getUtilisateurById($lecon->id_auteur);
        }

        $data = [
            'themeList' => $this->theme_model->getAllThemes(),
        ];

        $dataLecon = [
            'lecon'         => $lecon,
            'theme'         => $this->theme_model->find($idTheme),
            'idTheme'       => $idTheme,
            'numero'        => $numero,
            'auteur'       => $auteur,
            'leconsTheme'   => $leconsTheme,
            'leconSuivante' => $leconsTheme[$numero] ?? null,
        ];

        return view('header', $data)
            . view('notice')
            . view('lecon/lecon', $dataLecon)
            . view('footer');
    }
}