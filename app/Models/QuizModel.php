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
            log_message('error',count($this->findAll()));
            return $this->orderBy('titre', 'ASC')->findAll();
        } catch (DatabaseException $e) {
            log_message('error',"Erreur getAllQuiz : " . $e->getMessage());
            return [];
        }
    }



    public function getAllQuizCategoryById(int $id): array
    {
        // Requête ordonnée par le champ mot en ASC
        try {
            log_message('error',count($this->findAll()));

            return $this->where('id_theme', $id)->findAll();
        } catch (DatabaseException $e) {
            log_message('error',"Erreur getAllQuiz : " . $e->getMessage());
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
            log_message('error', "Erreur de création d'un quiz : " . $e->getMessage());
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
            log_message('error', "Erreur de modification d'un quiz : " . $e->getMessage());
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
                ->select('quiz.*, theme.nom AS theme_nom, COUNT(quiz_question.id_question) AS nb_questions')
                ->join('theme', 'theme.id = quiz.id_theme', 'left')
                ->join('quiz_question', 'quiz_question.id_quiz = quiz.id', 'left')
                ->groupBy('quiz.id')
                ->orderBy('theme.nom', 'ASC')
                ->orderBy('quiz.titre', 'ASC');

            if ($idTheme !== null && $idTheme !== -1) {
                $builder->where('quiz.id_theme', $idTheme);
            }

            return $builder->get()->getResultArray();

        } catch (DatabaseException $e) {
            log_message('error', "Erreur de récupération des quiz (admin) : " . $e->getMessage());
            return [];
        }
    }
}
