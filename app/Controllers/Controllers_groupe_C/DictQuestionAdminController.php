<?php

namespace App\Controllers\Controllers_groupe_C;

use App\Models\Model_groupe_C\MotCleModel;
use App\Models\Model_groupe_C\QuestionQuizModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * CONTRÔLEUR : gestion des questions du quiz Concept (administration, groupe C).
 *
 * QUI FAIT QUOI
 * -------------
 *  - DictAdminBaseController (parent) : vérifie que l'utilisateur est admin (sinon 404)
 *                                       (contrôle d'accès).
 *  - Cette classe                     : affiche la page des questions et traite ses 3 formulaires.
 *  - Vue affichée                     : groupe_c/admin/questions
 *                                       (qui étend le layout layout_admin_groupeC)
 *  - Modèles utilisés                 : QuestionQuizModel (question_quiz) et MotCleModel (liste des termes classés).
 *  - Aucune requête SQL ici : tout passe par les modèles (les transactions sont dans QuestionQuizModel).
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
class DictQuestionAdminController extends DictAdminBaseController
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
        $model = new QuestionQuizModel(); // table question_quiz

        // --- Colonne de gauche : la liste de toutes les questions + leur mot associé ---
        $data['questions'] = $model->getAvecMot();

        // --- Colonne de droite : mode modification (?modifier=5) ---
        $idModif         = (int) $this->request->getGet('modifier'); // id de la question à modifier (0 si absent)
        $data['edition'] = $idModif ? $model->find($idModif) : null;  // la question à modifier

        // --- Colonne de droite : mode création (?creer=1) ---
        $data['creation'] = $this->request->getGet('creer') !== null; // true si ?creer est dans l'URL

        // --- Liste déroulante "Terme associé" ---
        // On propose les termes classés (présents dans conceptcyber).
        // NB : la requête ne retire PAS les termes déjà pris par une autre question :
        // la règle "une seule question par terme" n'est pas vérifiée ici.
        $data['mots'] = (new MotCleModel())->getMotsClasses(); // modèle mot_cle

        // La vue étend le layout (en-tête, notice, CSS, pied de page) : plus besoin de les assembler ici
        return view('groupe_c/admin/questions', $data);
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

        $question = trim((string) $this->request->getPost('question'));

        $model = new QuestionQuizModel();

        $questionExistante = $model
            ->where('question', $question)
            ->first();

        if ($questionExistante !== null) {
            return redirect()
                ->to(site_url('floribondant/Questions') . '?creer=1')
                ->with('erreur', 'Cette question existe déjà.');
        }

        // insert() renvoie l'id de la nouvelle question
        $idQuestion = (new QuestionQuizModel())->insert([
            'id_mot_cle'  => null,                                      // pas encore de terme associé
            'question' => $question, // trim = enlève les espaces autour
            'explication' => $explication !== '' ? $explication : null, // vide = NULL en base
        ]);

        // On ouvre directement la question créée pour choisir son terme
        return redirect()->to(site_url('floribondant/Questions') . '?modifier=' . $idQuestion);
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

        // modifierQuestion() fait tout en une transaction : enregistre la question et son terme,
        // puis recalcule les bonnes réponses (question_reponse).
        (new QuestionQuizModel())->modifierQuestion(
            $idQuestion,
            trim((string) $this->request->getPost('question')),
            $explication !== '' ? $explication : null,
            $idMot
        );

        // On retourne sur la liste de questions
        return redirect()->to(site_url('floribondant/Questions'));
    }

    /**
     * SUPPRIME une question (route POST groupe_c/admin/questions/supprimer)
     * Appelée par le bouton "Supprimer" de chaque ligne de la liste.
     */
    public function GroupeC_SupprimerQuestion(): RedirectResponse
    {
        if ($this->validate(self::REGLES_SUPPR)) {
            // supprimerQuestion() supprime ses bonnes réponses puis la question, en une transaction
            (new QuestionQuizModel())->supprimerQuestion((int) $this->request->getPost('id_question'));
        }

        // Retour à la liste, sans question ouverte
        return redirect()->to(site_url('floribondant/Questions'));
    }
}