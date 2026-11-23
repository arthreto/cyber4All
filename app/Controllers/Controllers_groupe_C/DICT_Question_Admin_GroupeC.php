<?php

namespace App\Controllers\Controllers_groupe_C;

use App\Models\Model_groupe_C\ModelAdminCmot_cle;
use CodeIgniter\HTTP\RedirectResponse;
use Config\Database;

/**
 * CONTRÔLEUR : gestion des questions du quiz Concept (administration, groupe C).
 *
 * QUI FAIT QUOI
 * -------------
 *  - DICT_Admin_Base_GroupeC (parent) : vérifie que l'utilisateur est admin (sinon 404)
 *                                       et fournit synchroniserReponses().
 *  - Cette classe                     : affiche la page des questions et traite ses 3 formulaires.
 *  - Vue affichée                     : groupe_c/groupe_c_ADMIN/admin_QUESTIONS_groupeC
 *                                       (qui étend le layout layout_admin_groupeC)
 *  - Modèle utilisé                   : ModelAdminCmot_cle (liste des termes classés).
 *  - question_quiz et question_reponse n'ont pas de modèle : leurs requêtes restent ici (query builder),
 *    comme dans ton code d'origine. Les transactions aussi.
 *
 * LES 4 MÉTHODES
 * --------------
 *  GroupeC_Questions()          GET  : affiche la page (liste + formulaire de droite)
 *  GroupeC_AjouterQuestion()    POST : crée une question (sans terme)
 *  GroupeC_ModifierQuestion()   POST : enregistre une question et son terme associé
 *  GroupeC_SupprimerQuestion()  POST : supprime une question et ses bonnes réponses
 *
 * VALIDATION : chaque méthode POST vérifie les données avec $this->validate().
 */
class DICT_Question_Admin_GroupeC extends DICT_Admin_Base_GroupeC
{
    /** Création : la question est obligatoire (une question faite d'espaces est refusée). */
    private const REGLES_AJOUT = [
        'question'    => 'required',
        'explication' => 'permit_empty',
    ];

    /** Modification : id valide, question obligatoire, terme optionnel (vide = "Aucun terme"). */
    private const REGLES_MODIF = [
        'id_question' => 'required|is_natural_no_zero',
        'question'    => 'required',
        'explication' => 'permit_empty',
        'id_mot_cle'  => 'permit_empty|is_natural',
    ];

    /** Suppression : id valide. */
    private const REGLES_SUPPR = [
        'id_question' => 'required|is_natural_no_zero',
    ];

    /**
     * AFFICHE la page des questions (route GET /floribondant/questions)
     */
    public function GroupeC_Questions(): string
    {
        $db = Database::connect(); // connexion à la base

        // --- Colonne de gauche : la liste de toutes les questions + leur mot associé ---
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
        // On propose les termes classés (présents dans conceptcyber).
        // NB : la requête ne retire PAS les termes déjà pris par une autre question :
        // la règle "une seule question par terme" n'est pas vérifiée ici.
        $data['mots'] = (new ModelAdminCmot_cle())->getMotsClasses(); // modèle mot_cle

        // La vue étend le layout (en-tête, notice, CSS, pied de page) : plus besoin de les assembler ici
        return view('groupe_c/groupe_c_ADMIN/admin_QUESTIONS_groupeC', $data);
    }

    /**
     * CRÉE une question dans question_quiz (route POST groupe_c/admin/questions/ajouter)
     * Appelée par le formulaire "Nouvelle question" de la vue.
     * Elle est créée sans terme, on l'associe ensuite.
     */
    public function GroupeC_AjouterQuestion(): RedirectResponse
    {
        // Une question vide est refusée : on revient au formulaire de création
        if (! $this->validate(self::REGLES_AJOUT)) {
            return redirect()->to(site_url('floribondant/questions') . '?creer=1');
        }

        $explication = trim((string) $this->request->getPost('explication'));

        $db = Database::connect();
        $db->table('question_quiz')->insert([
            'id_mot_cle'  => null,                                      // pas encore de terme associé
            'question'    => trim((string) $this->request->getPost('question')), // trim = enlève les espaces autour
            'explication' => $explication !== '' ? $explication : null, // vide = NULL en base
        ]);

        // On ouvre directement la question créée (insertID = son id) pour choisir son terme
        return redirect()->to(site_url('floribondant/Questions') . '?modifier=' . $db->insertID());
    }

    /**
     * ENREGISTRE une question et son terme associé (route POST groupe_c/admin/questions/modifier)
     * Appelée par le formulaire "Modifier la question" de la vue.
     */
    public function GroupeC_ModifierQuestion(): RedirectResponse
    {
        // Données invalides (id incorrect ou question vide) : retour à la liste sans rien enregistrer
        if (! $this->validate(self::REGLES_MODIF)) {
            return redirect()->to(site_url('floribondant/Questions'));
        }

        $idQuestion  = (int) $this->request->getPost('id_question');        // champ caché du formulaire
        $idMot       = (int) $this->request->getPost('id_mot_cle') ?: null; // 0 ("Aucun terme") devient null
        $explication = trim((string) $this->request->getPost('explication'));
        $db          = Database::connect(); // sert uniquement à la transaction

        $db->transStart(); // transaction : tout ou rien

        $db->table('question_quiz')->where('id', $idQuestion)->update([
            'question'    => trim((string) $this->request->getPost('question')),
            'explication' => $explication !== '' ? $explication : null,
            'id_mot_cle'  => $idMot, // le terme associé choisi dans la liste
        ]);

        // On recalcule question_reponse d'après les cases D/I/C/T (conceptcyber) du terme choisi
        $this->synchroniserReponses($idQuestion, $idMot); // méthode du contrôleur parent

        $db->transComplete();

        // On reste sur la question en cours de modification
        return redirect()->to(site_url('floribondant/Questions') . '?modifier=' . $idQuestion);
    }

    /**
     * SUPPRIME une question (route POST groupe_c/admin/questions/supprimer)
     * Appelée par le bouton "Supprimer" de chaque ligne de la liste.
     */
    public function GroupeC_SupprimerQuestion(): RedirectResponse
    {
        if ($this->validate(self::REGLES_SUPPR)) {
            $idQuestion = (int) $this->request->getPost('id_question');
            $db         = Database::connect(); // sert uniquement à la transaction

            $db->transStart();

            // 1. D'abord ses lignes dans question_reponse (sinon la clé étrangère peut bloquer)
            $db->table('question_reponse')->where('id_question', $idQuestion)->delete();
            // 2. Puis la question elle-même dans question_quiz
            $db->table('question_quiz')->where('id', $idQuestion)->delete();

            $db->transComplete();
        }

        // Retour à la liste, sans question ouverte
        return redirect()->to(site_url('floribondant/Questions'));
    }
}