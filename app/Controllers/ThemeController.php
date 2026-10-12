<?php

namespace App\Controllers;

use App\Entities\Utilisateur;
use App\Models\CategorieModel;
use App\Models\ImageModel;
use App\Models\MotCle\GlossaireModel;
use App\Models\MotCle\MotCleModel;
use App\Models\SignalementModel;
use App\Models\ThemeModel;
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

    public function __construct()
    {
        $this->theme_model = new ThemeModel();
    }

    /**
     * Affichage d'une page de leçon
     */
    public function afficherPageLecon(int $idLecon)
    {
        $data = [
            'themeList' => $this->theme_model->getAllThemes(),
        ];

        return view('header', $data)
            . view('notice')
            . view('recherche')
            . view('glossaire/glossaire')
            . view('footer');
    }
}