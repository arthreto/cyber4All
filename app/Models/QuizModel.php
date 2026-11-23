<?php

namespace App\Models;

use App\Entities\MotCle;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;
use App\Entities\Categorie;

/**
 * Ce model représente la gestion des Themes de la table theme
 * @author Benjamin Lorieul
 * @since 1.0.0
 * @package Cyber4all
 */
class QuizModel extends Model
{
    protected $DBGroup = 'tracabilite';
    protected $table = 'quiz';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['id', 'titre','id_theme'];
    protected $returnType = \App\Entities\Quiz::class;

    /**
     * Récupère tout les quiz de la base de données triées par ordre alphabétique.
     *
     * Cette méthode effectue une requête sur la table `quiz` et retourne
     * tout les thèmes sous forme d'un tableau d'objets `Quiz`, triées
     * par le champ `titre` en ordre croissant. En cas d'erreur (ex. problème de
     * connexion ou de requête), un message d'erreur est logué et un tableau vide
     * est retourné.
     *
     * @return Categorie[] Tableau d'objets `Quiz` représentant toutes les catégories.
     *
     * @since 1.0.0
     * @author Arthur
     */
    public function getAllQuizCategory(): array
    {
        // Requête ordonnée par le champ mot en ASC
        try {
            return $this->orderBy('titre', 'ASC')->findAll();
        } catch (DatabaseException $e) {
            log_message('error', "Erreur getAllQuiz : {exception}", ['exception' => $e]);
            return [];
        }
    }



    /**
     * Récupère les quiz d'un thème.
     *
     * @param int  $id              Identifiant du thème
     * @param bool $actifsSeulement true pour la page publique (quiz actifs seulement),
     *                              false pour l'administration (tous les quiz)
     * @return array Liste des quiz, vide en cas d'erreur
     */
    public function getAllQuizCategoryById(int $id, bool $actifsSeulement = false): array
    {
        try {
            $this->where('id_theme', $id);

            if ($actifsSeulement) {
                $this->where('actif', 1);
            }

            return $this->orderBy('id', 'ASC')->findAll();
        } catch (DatabaseException $e) {
            log_message('error', "Erreur getAllQuiz : {exception}", ['exception' => $e]);
            return [];
        }
    }

    /**
     * Nombre de questions actives de chaque quiz d'un thème (celles réellement jouées,
     * même filtre que QuestionModel::getQuestionFromQuiz()).
     *
     * @param int $idTheme Identifiant du thème
     * @return array Tableau [id_quiz => nombre de questions], vide en cas d'erreur
     */
    public function getNbQuestionsActivesParQuiz(int $idTheme): array
    {
        try {
            $lignes = $this->db->table($this->table)
                ->select('quiz.id, COUNT(DISTINCT question.id) AS nb')
                ->join('quiz_question', 'quiz_question.id_quiz = quiz.id', 'left')
                ->join('question', 'question.id = quiz_question.id_question AND question.actif = 1', 'left')
                ->where('quiz.id_theme', $idTheme)
                ->groupBy('quiz.id')
                ->get()
                ->getResultArray();

            $compteurs = [];
            foreach ($lignes as $ligne) {
                $compteurs[(int) $ligne['id']] = (int) $ligne['nb'];
            }

            return $compteurs;

        } catch (DatabaseException $e) {
            log_message('error', "Erreur de comptage des questions des quiz : " . $e->getMessage());
            return [];
        }
    }

    /**
     * Garde uniquement les champs qui existent dans la table `quiz`.
     *
     * @param array $donnees Données du formulaire.
     * @return array Données filtrées.
     */
    private function filtrerColonnes(array $donnees): array
    {
        $colonnes = $this->db->getFieldNames($this->table);

        return array_intersect_key($donnees, array_flip($colonnes));
    }

    /**
     * Récupère un quiz selon sa position dans une catégorie.
     *
     * @param int $themeId Identifiant du thème
     * @param int $position Position du quiz dans la catégorie
     * La position compte tous les quiz du thème, actifs ou non (par id) : désactiver un quiz
     * ne décale pas les suivants, l'adresse d'un quiz reste la même. Un quiz désactivé
     * est renvoyé quand même : c'est l'appelant qui affiche alors une page introuvable.
     *
     * @return object|null Quiz correspondant ou null si inexistant
     */
    public function getQuizByPosition(int $themeId, int $position)
    {
        if ($position < 1) {
            return null;
        }

        return $this->where('id_theme', $themeId)
            ->orderBy('id', 'ASC')
            ->findAll()[$position - 1] ?? null;
    }

    /**
     * Crée un quiz.
     *
     * @param array $donnees Données du quiz
     * @return int|false Identifiant du quiz créé, false en cas d'échec.
     */
    public function creerQuiz(array $donnees): int|false
    {
        try {
            $idQuiz = $this->db->table($this->table)->insert($this->filtrerColonnes($donnees))
                ? $this->db->insertID()
                : false;

            return $idQuiz === false ? false : (int) $idQuiz;

        } catch (DatabaseException $e) {
            log_message('error', "Erreur de création d'un quiz : {exception}", ['exception' => $e]);
            return false;
        }
    }

    /**
     * Modifie un quiz.
     *
     * @param array $donnees Données du quiz (id_theme, titre, description, actif).
     * @param int $idQuiz Identifiant du quiz.
     * @return bool True si la modification réussit, false sinon.
     */
    public function updateQuiz(array $donnees, int $idQuiz): bool
    {
        try {
            return $this->db->table($this->table)
                ->where($this->primaryKey, $idQuiz)
                ->update($this->filtrerColonnes($donnees));

        } catch (DatabaseException $e) {
            log_message('error', "Erreur de modification d'un quiz : {exception}", ['exception' => $e]);
            return false;
        }
    }

    /**
     * Récupère les quiz pour la page d'administration, avec le nom du thème
     * et le nombre de questions rattachées. Triés par thème puis par titre.
     *
     * @param int|null $idTheme Filtre sur un thème, null ou -1 pour tous les quiz.
     * @return array Liste des quiz (tableaux), vide en cas d'erreur.
     */
    public function getQuizAdmin(?int $idTheme = null): array
    {
        try {
            $builder = $this->db->table($this->table)
                // nb_prevu : nombre de questions fixé pour le quiz ; nb_questions : questions actives réellement liées
                ->select('quiz.*, quiz.nb_questions AS nb_prevu, theme.nom AS theme_nom, COUNT(question.id) AS nb_questions')
                ->join('theme', 'theme.id = quiz.id_theme', 'left')
                ->join('quiz_question', 'quiz_question.id_quiz = quiz.id', 'left')
                ->join('question', 'question.id = quiz_question.id_question AND question.actif = 1', 'left')
                ->groupBy('quiz.id')
                ->orderBy('theme.nom', 'ASC')
                ->orderBy('quiz.titre', 'ASC');

            if ($idTheme !== null && $idTheme !== -1) {
                $builder->where('quiz.id_theme', $idTheme);
            }

            return $builder->get()->getResultArray();

        } catch (DatabaseException $e) {
            log_message('error', "Erreur de récupération des quiz (admin) : {exception}", ['exception' => $e]);
            return [];
        }
    }
}
