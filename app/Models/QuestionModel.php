<?php

namespace App\Models;

use App\Entities\MotCle;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;
use App\Entities\Categorie;

/**
 * Ce model représente la gestion des Questions des Quiz
 * @author Arthur Vidal
 * @since 1.0.0
 * @package Cyber4all
 */
class QuestionModel extends Model
{
    protected $DBGroup = 'tracabilite';
    protected $table = 'question';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $allowedFields = ['id', 'libelle','id_extrait','image','bonne_reponse','explication','actif','date_ajout','date_modif'];
    //protected $returnType = \App\Entities\Quiz::class;

    /**
     * Crée une nouvelle question dans la base de données.
     *
     * Cette méthode insère les informations d'une question dans la
     * table `question` et renseigne automatiquement les dates
     * de création et de modification.
     *
     * En cas de succès, elle retourne l'identifiant de la question créée.
     * En cas d'échec, elle retourne false.
     *
     * @param array $donnees Données de la question à enregistrer.
     * @return int|false Identifiant de la question créée ou false en cas d'échec.
     *
     * @since 1.0.0
     * @author Arthur
     */
    public function creerQuestion(array $donnees): int|false
    {
        try {
            // Insertion de la question
            $idQuestion = $this->insert($donnees);

            // Retourne l'identifiant de la question créée
            return $idQuestion === false ? false : (int) $idQuestion;

        } catch (DatabaseException $e) {
            log_message('error', "Erreur de création d'une question : " . $e->getMessage());
            return false;
        }
    }
}