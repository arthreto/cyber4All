<?php

namespace App\Controllers\Controllers_groupe_C;

// Hérite de DICT_Admin_Base_GroupeC : on récupère le contrôle admin et synchroniserReponses()
class DICT_Question_Admin_GroupeC extends DICT_Admin_Base_GroupeC
{
    /**
     * AFFICHE la page des questions (route GET /floribondant/questions)
     */
    public function GroupeC_Questions(): string
    {
        $db = \Config\Database::connect(); // connexion à la base

        // --- Colonne de gauche : la liste de toutes les questions ---
        // On récupère chaque question de question_quiz + le mot associé (colonne "mot" de mot_cle)
        $data['questions'] = $db->table('question_quiz')
            ->select('question_quiz.*, mot_cle.mot')
            ->join('mot_cle', 'mot_cle.id = question_quiz.id_mot_cle', 'left') // left = garde aussi les questions SANS terme
            ->orderBy('question_quiz.id', 'DESC')                              // les plus récentes en premier
            ->get()->getResultArray();

        // --- Colonne de droite : mode modification (?modifier=5) ---
        $idModif         = (int) $this->request->getGet('modifier'); // id de la question à modifier (0 si absent)
        $data['edition'] = $idModif
            ? $db->table('question_quiz')->where('id', $idModif)->get()->getRowArray() // la question à modifier
            : null;

        // --- Colonne de droite : mode création (?creer=1) ---
        $data['creation'] = $this->request->getGet('creer') !== null; // true si ?creer est dans l'URL

        // --- Liste déroulante "Terme associé" ---
        // On propose les termes classés (présents dans conceptcyber)
        // qui ne sont pas déjà pris par une AUTRE question de question_quiz (une seule question par terme)
        $data['mots'] = $db->table('mot_cle')
            ->select('mot_cle.id, mot_cle.mot')
            ->join('conceptcyber', 'conceptcyber.id_mot_cle = mot_cle.id') // seulement les termes classés
            ->orderBy('mot_cle.mot', 'ASC')                                // ordre alphabétique
            ->get()->getResultArray();

        // On assemble la page : en-tête + notice + notre vue + pied de page
        return view('header')
            . view('notice')
            . view('groupe_c/groupe_c_ADMIN/admin_QUESTIONS_groupeC', $data)
            . view('footer');
    }

    /**
     * CRÉE une question dans question_quiz (route POST groupe_c/admin/questions/ajouter)
     * Elle est créée sans terme, on l'associe ensuite.
     */
    public function GroupeC_AjouterQuestion()
    {
        $db          = \Config\Database::connect();
        $question    = trim((string) $this->request->getPost('question'));    // trim = enlève les espaces autour
        $explication = trim((string) $this->request->getPost('explication'));

        // Une question vide est refusée : on revient au formulaire de création
        if ($question === '') {
            return redirect()->to(base_url('floribondant/questions') . '?creer=1');
        }

        $db->table('question_quiz')->insert([
            'id_mot_cle'  => null,                                      // pas encore de terme associé
            'question'    => $question,
            'explication' => $explication !== '' ? $explication : null, // vide = NULL en base
        ]);

        // On ouvre directement la question créée (insertID = son id) pour choisir son terme
        return redirect()->to(base_url('floribondant/Questions') . '?modifier=' . $db->insertID());
    }

    /**
     * ENREGISTRE une question et son terme associé (route POST groupe_c/admin/questions/modifier)
     */
    public function GroupeC_ModifierQuestion()
    {
        $db          = \Config\Database::connect();
        $idQuestion  = (int) $this->request->getPost('id_question');        // champ caché du formulaire
        $idMot       = (int) $this->request->getPost('id_mot_cle') ?: null; // 0 ("Aucun terme") devient null
        $question    = trim((string) $this->request->getPost('question'));
        $explication = trim((string) $this->request->getPost('explication'));

        // On n'enregistre que si l'id est valide et la question non vide
        if ($idQuestion > 0 && $question !== '') {
            $db->transStart(); // transaction : tout ou rien

            $db->table('question_quiz')->where('id', $idQuestion)->update([
                'question'    => $question,
                'explication' => $explication !== '' ? $explication : null,
                'id_mot_cle'  => $idMot, // le terme associé choisi dans la liste
            ]);

            // On recalcule question_reponse d'après les cases D/I/C/T (conceptcyber) du terme choisi
            $this->synchroniserReponses($db, $idQuestion, $idMot);

            $db->transComplete();
        }

        // On reste sur la question en cours de modification
        return redirect()->to(base_url('floribondant/Questions') . '?modifier=' . $idQuestion);
    }

    /**
     * SUPPRIME une question (route POST groupe_c/admin/questions/supprimer)
     */
    public function GroupeC_SupprimerQuestion()
    {
        $db         = \Config\Database::connect();
        $idQuestion = (int) $this->request->getPost('id_question');

        if ($idQuestion > 0) { // sécurité : on ignore un id invalide
            $db->transStart();

            // 1. D'abord ses lignes dans question_reponse (sinon la clé étrangère peut bloquer)
            $db->table('question_reponse')->where('id_question', $idQuestion)->delete();
            // 2. Puis la question elle-même dans question_quiz
            $db->table('question_quiz')->where('id', $idQuestion)->delete();

            $db->transComplete();
        }

        // Retour à la liste, sans question ouverte
        return redirect()->to(base_url('floribondant/Questions'));
    }
}