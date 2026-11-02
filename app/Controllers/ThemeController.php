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

    public function __construct()
    {
        $this->theme_model = new ThemeModel();
        $this->lecon_model  = new LeconModel();
    }


    /**
     * Affichage d'une page de leçon
     */
    public function afficherPageLecon(int $idTheme, ?int $idLecon = null)
    {
        $leconsTheme = $this->lecon_model->getLeconsByTheme($idTheme);

        // Thème inexistant ou sans leçon
        if ($leconsTheme === []) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // URL theme/idTheme : redirection vers la première leçon du thème
        if ($idLecon === null) {
            return redirect()->to(site_url('theme/' . $idTheme . '/' . $leconsTheme[0]->id));
        }

        $lecon = $this->lecon_model->getLeconById($idLecon);

        // Leçon inexistante, ou qui n'appartient pas au thème de l'URL
        if ($lecon === null || $lecon->id_theme !== $idTheme) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Leçon suivante : celle qui suit la leçon courante dans la liste du thème
        $leconSuivante = null;
        foreach ($leconsTheme as $i => $l) {
            if ($l->id === $idLecon) {
                $leconSuivante = $leconsTheme[$i + 1] ?? null;
                break;
            }
        }

        $data = [
            'themeList' => $this->theme_model->getAllThemes(),
        ];

        $dataLecon = [
            'lecon'         => $lecon,
            'theme'         => $this->theme_model->find($idTheme),
            'leconsTheme'   => $leconsTheme,
            'leconSuivante' => $leconSuivante,
        ];

        return view('header', $data)
            . view('notice')
            . view('lecon/lecon', $dataLecon)
            . view('footer');
    }
}