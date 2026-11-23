<?php

namespace App\Controllers;

use App\Models\QuestionModel;
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
    protected QuestionModel $question_model;

    public function __construct()
    {
        $this->theme_model = new ThemeModel();
        $this->quiz_model = new QuizModel();
        $this->question_model = new QuestionModel();
    }

    /**
     * @param int $themeId Identifiant du thème
     * @param int $quizNum Numéro du Quiz par rapport au thème
     * @return string
     */
    public function jouerQuiz(int $themeId, int $quizNum) {
        $themeList = $this->theme_model->getAllThemes();
        $themeNom = null;

        foreach ($themeList as $theme) {
            if ($theme->id == $themeId) {
                $themeNom = $theme->nom;
                break;
            }
        }

        $quizInfo = $this->quiz_model->getQuizByPosition($themeId, $quizNum);

        // Numéro de quiz inexistant, ou quiz supprimé (désactivé) : page introuvable.
        // Le quiz désactivé garde sa position : les quiz suivants gardent leur adresse.
        if ($quizInfo === null || (int) ($quizInfo->actif ?? 1) !== 1) {
            throw PageNotFoundException::forPageNotFound();
        }

        $questionQuizList = $this->question_model->getQuestionFromQuiz($quizInfo->id);

        $data = [
            'themeList'  => $themeList,
            'themeId'    => $themeId,
            'themeNom' => $themeNom,
            'quizNum'    => $quizNum,
            'quizData'  => $quizInfo,
            'questionQuizList' => $questionQuizList,
            'nbQuestions' => count($questionQuizList),
        ];

        if ($this->request->is('post')) {
            $data['corrige'] = 1;
            $data['resultatParQuestion'] = [];
            $data['reponsesUtilisateur'] = [];
            $reponses = (array) ($this->request->getPost('reponses') ?? []);
            $num = 1;
            $bonneReponse = 0;
            foreach ($questionQuizList as $question) {
                $reponseUtilisateur = $reponses[$num] ?? null;
                $data['reponsesUtilisateur'][$num] = $reponseUtilisateur;
                $resultatQuestion = ($reponseUtilisateur !== null && $reponseUtilisateur == $question['bonne_reponse']) ? 'Vrai' : 'Faux';
                $data['resultatParQuestion'][$num] = $resultatQuestion;

                if ($resultatQuestion == 'Vrai') {
                    $bonneReponse++;
                }

                $num++;
            }
            $data['bonneReponseResultat'] = $bonneReponse;
        }

        return view('header', $data)
            . view('notice')
            . view('quiz/quiz', $data)
            . view('footer');
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
            // Quiz actifs du thème affiché (sans thème dans l'URL : le premier thème)
            'quizList' => $this->quizActifsAvecPosition((int) $themeActif->id),
            // Lien « Revoir les leçons » seulement si le thème a une leçon active (sinon 404)
            'aDesLecons' => in_array((int) $themeActif->id, model(\App\Models\LeconModel::class)->getIdsThemesAvecLecon(), true),
        ];

        return view('header', $data)
            . view('notice')
            . view('quiz/selectionQuiz', $data)
            . view('footer');
    }

    /**
     * Quiz actifs d'un thème, avec leur position dans l'adresse (quiz/idTheme/position).
     * La position compte aussi les quiz désactivés : elle ne change pas quand un quiz est supprimé.
     *
     * @param int $idTheme Identifiant du thème
     * @return array Liste de ['quiz' => Quiz, 'position' => int]
     */
    private function quizActifsAvecPosition(int $idTheme): array
    {
        $liste = [];
        $position = 1;

        foreach ($this->quiz_model->getAllQuizCategoryById($idTheme) as $quiz) {
            if ((int) ($quiz->actif ?? 1) === 1) {
                $liste[] = ['quiz' => $quiz, 'position' => $position];
            }
            $position++;
        }

        return $liste;
    }
}
