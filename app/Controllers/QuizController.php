<?php

namespace App\Controllers;

use App\Models\QuizModel;
use App\Models\ThemeModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Ce controller permet l'affichage des quiz Vrai / Faux
 * de l'Espace compréhension SIO (partie publique).
 *
 * @since 2.0.0
 * @package Cyber4all
 * @author Arthur
 */
class QuizController extends BaseController
{
    protected ThemeModel $theme_model;
    protected QuizModel $quiz_model;

    public function __construct()
    {
        $this->theme_model = new ThemeModel();
        $this->quiz_model = new QuizModel();
    }

    /**
     * Page de sélection des quiz
     *
     * @param int|null $idTheme Identifiant du thème affiché.
     * @return string
     */
    public function index(?int $idTheme = null)
    {
        $themeList = $this->theme_model->getAllThemes();

        if ($themeList === []) {
            throw PageNotFoundException::forPageNotFound();
        }

        // Sans thème dans l'URL : premier thème de la liste
        $themeActif = $themeList[0];

        if ($idTheme !== null) {
            $themeActif = null;

            foreach ($themeList as $theme) {
                if ((int) $theme->id === $idTheme) {
                    $themeActif = $theme;
                    break;
                }
            }

            if ($themeActif === null) {
                throw PageNotFoundException::forPageNotFound();
            }
        }

        $data = [
            'themeList'  => $themeList,
            'themeActif' => $themeActif,
            'quizList' => $this->quiz_model->getAllQuizCategoryById($idTheme ?? 1),
        ];

        return view('header', $data)
            . view('notice')
            . view('quiz/selectionQuiz', $data)
            . view('footer');
    }
}
