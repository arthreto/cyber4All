<?php

namespace App\Controllers\Controllers_groupe_C;

use App\Models\Model_groupe_C\MotCleModel;
use App\Models\Model_groupe_C\ConceptCyberModel;
use CodeIgniter\HTTP\RedirectResponse;
use Config\Database;

/**
 * CONTRÔLEUR : gestion des termes du quiz Concept (administration, groupe C).
 *
 * QUI FAIT QUOI
 * -------------
 *  - DictAdminBaseController (parent) : vérifie que l'utilisateur est admin (sinon 404)
 *                                       et fournit synchroniserReponses().
 *  - Cette classe                     : affiche la page des termes et traite ses 3 formulaires.
 *  - Vue affichée                     : groupe_c/admin/termes
 *                                       (qui étend le layout layout_admin_groupeC)
 *  - Modèles utilisés                 : MotCleModel (table mot_cle)
 *                                       et ConceptCyberModel (table conceptcyber).
 *  - La table question_quiz n'a pas de modèle : un seul accès via le query builder (voir GroupeC_Modifier).
 *    Les transactions restent dans le contrôleur.
 *
 * LES 4 MÉTHODES
 * --------------
 *  GroupeC_Admin()      GET  : affiche la page (liste + formulaire de droite)
 *  GroupeC_Modifier()   POST : enregistre les cases D/I/C/T d'un terme
 *  GroupeC_Ajouter()    POST : classe un terme existant (toutes les cases à 0)
 *  GroupeC_Supprimer()  POST : retire un terme du classement
 *
 * VALIDATION : chaque méthode POST vérifie les données avec $this->validate().
 * Si c'est invalide, on redirige sans message (comme avant).
 */
class DictConceptAdminController extends DictAdminBaseController
{
    /**
     * Les 4 catégories DICT.
     * clé    = nom de la colonne dans la table conceptcyber
     * valeur = libellé affiché dans la vue
     * Source unique : sert à valider le filtre ET à remplir la vue (liste déroulante, puces, cases à cocher).
     */
    private const CATEGORIES = [
        'disponibilite'   => 'Disponibilité',
        'integrite'       => 'Intégrité',
        'confidentialite' => 'Confidentialité',
        'tracabilite'     => 'Traçabilité',
    ];

    /** Règle de validation commune : un id de terme valide (entier > 0). */
    private const REGLES_ID_MOT = [
        'id_mot_cle' => 'required|is_natural_no_zero',
    ];

    /**
     * AFFICHE la page des termes (route GET /floribondant/QuizzConcept)
     */
    public function GroupeC_Admin(): string
    {
        // Les modèles = des objets qui parlent à une table de la base
        $model        = new MotCleModel();            // table mot_cle
        $modelConcept = new ConceptCyberModel();  // table conceptcyber

        // --- Barre de recherche : si on a tapé quelque chose, on filtre sur le mot ---
        $recherche = (string) $this->request->getGet('recherche'); // valeur de ?recherche=...
        if (! empty($recherche)) {
            $model->like('mot', $recherche, 'both', null, true); // "mot" contient le texte tapé
        }

        // --- Filtre par catégorie (liste déroulante D/I/C/T) ---
        $categorie = (string) $this->request->getGet('concept'); // valeur de ?concept=...
        // in_array(..., true) vérifie que la valeur est autorisée (sécurité : évite une injection SQL,
        // car la valeur sert de nom de colonne dans la requête)
        if (in_array($categorie, array_keys(self::CATEGORIES), true)) {
            $model->select('mot_cle.*')
                ->join('conceptcyber', 'conceptcyber.id_mot_cle = mot_cle.id') // on relie les 2 tables
                ->where('conceptcyber.' . $categorie, 1);                      // garde ceux dont la case = 1
        }

        // --- Données envoyées à la vue (colonne de gauche : le tableau) ---
        // On transforme la liste des concepts en tableau indexé par id du mot,
        // pour retrouver facilement les D/I/C/T d'un mot dans la vue
        $concepts = array_column($modelConcept->findAll(), null, 'id_mot_cle');

        $data['categories']    = self::CATEGORIES; // pour la liste déroulante, les puces et les cases à cocher
        $data['recherche']     = $recherche;       // pour réafficher le texte dans la barre de recherche
        $data['conceptFiltre'] = $categorie;       // pour garder la catégorie sélectionnée dans la liste
        $data['concept']       = $concepts;

        // Tous les termes (filtrés ou non), MAIS on ne garde que ceux déjà classés (présents dans conceptcyber).
        // Avant, c'était la vue qui sautait les autres avec "continue" : on le fait ici pour garder la vue sans logique.
        $data['mots'] = array_filter(
            $model->findAll(),
            static fn (array $mot): bool => isset($concepts[$mot['id']])
        );

        // --- Colonne de droite : mode modification ---
        $idModif                = $this->request->getGet('modifier'); // ?modifier=12
        $data['edition']        = $idModif ? $model->find($idModif) : null;     // le terme à modifier
        $data['conceptEdition'] = $idModif ? ($concepts[$idModif] ?? null) : null; // ses cases cochées

        // --- Colonne de droite : mode ajout (?creer=1) ---
        $data['creation'] = $this->request->getGet('creer') !== null; // true si ?creer est dans l'URL

        // Liste déroulante "Terme à ajouter" : les mots pas encore classés
        $data['motsDisponibles'] = $data['creation'] ? $model->getMotsNonClasses() : [];

        // La vue étend le layout (en-tête, notice, CSS, pied de page) : plus besoin de les assembler ici
        return view('groupe_c/admin/termes', $data);
    }

    /**
     * ENREGISTRE les cases D/I/C/T d'un terme (route POST groupe_c/admin/modifier)
     * Appelée par le formulaire "Modifier les concepts cyber" de la vue.
     */
    public function GroupeC_Modifier(): RedirectResponse
    {
        // Données invalides (id absent ou incorrect) : retour à la page sans message
        if (! $this->validate(self::REGLES_ID_MOT)) {
            return redirect()->to(site_url('floribondant/DICT'));
        }

        $modelConcept = new ConceptCyberModel();
        $db           = Database::connect(); // sert uniquement à la transaction
        $idMot        = (int) $this->request->getPost('id_mot_cle'); // champ caché du formulaire

        // Une case cochée est envoyée ("1"), une case décochée n'est PAS envoyée : on la traduit en 0
        $valeurs = [];
        foreach (array_keys(self::CATEGORIES) as $colonne) {
            $valeurs[$colonne] = $this->request->getPost($colonne) ? 1 : 0;
        }

        $db->transStart(); // transaction : tout ou rien

        // 1. On enregistre les nouvelles cases du terme (table conceptcyber)
        $modelConcept->update($idMot, $valeurs);

        // 2. On recalcule les bonnes réponses de toutes les questions liées à ce terme
        $questions = $db->table('question_quiz')->select('id')->where('id_mot_cle', $idMot)->get()->getResultArray();
        foreach ($questions as $q) {
            $this->synchroniserReponses((int) $q['id'], $idMot); // méthode du contrôleur parent
        }

        $db->transComplete();

        // transStatus() = true si TOUTES les requêtes de la transaction ont réussi
        if ($db->transStatus()) {
            // ->with('succes', ...) crée un message flash : affiché une seule fois à la prochaine page
            return redirect()->to(site_url('floribondant/DICT') . '?modifier=' . $idMot)
                ->with('succes', 'Le terme a bien été modifié.');
        }

        // Échec : on redirige SANS message de succès, donc pas de pop-up
        return redirect()->to(site_url('floribondant/DICT') . '?modifier=' . $idMot);
    }

    /**
     * CLASSE un terme existant dans le quiz (route POST groupe_c/admin/ajouter)
     * Appelée par la liste déroulante "Terme à ajouter" de la vue.
     * Le terme est ajouté avec les 4 cases à 0 : on les coche ensuite via "Modifier".
     */
    public function GroupeC_Ajouter(): RedirectResponse
    {
        if (! $this->validate(self::REGLES_ID_MOT)) {
            return redirect()->to(site_url('floribondant/DICT'));
        }

        $idMot        = (int) $this->request->getPost('id_mot_cle');
        $modelConcept = new ConceptCyberModel(); // table conceptcyber

        // Deux vérifications : le mot existe bien, et il n'est pas déjà classé
        $motExiste   = (new MotCleModel())->find($idMot) !== null; // table mot_cle
        $dejaPresent = $modelConcept->estClasse($idMot);

        if ($motExiste && ! $dejaPresent) {
            $modelConcept->insert([
                'id_mot_cle'      => $idMot,
                'disponibilite'   => 0,
                'integrite'       => 0,
                'confidentialite' => 0,
                'tracabilite'     => 0,
            ]);

            // Ajout réussi : message flash => pop-up
            return redirect()->to(site_url('floribondant/DICT') . '?modifier=' . $idMot)
                ->with('succes', 'Le terme a bien été ajouté.');
        }

        // Le mot n'existe pas ou est déjà classé : pas de message de succès
        return redirect()->to(site_url('floribondant/DICT') . '?modifier=' . $idMot);
    }

    /**
     * RETIRE un terme du classement (route POST groupe_c/admin/supprimer)
     * Appelée par le bouton "Supprimer" de la vue. Le terme reste dans mot_cle (glossaire),
     * seule sa ligne dans conceptcyber est supprimée.
     */
    public function GroupeC_Supprimer(): RedirectResponse
    {
        if (! $this->validate(self::REGLES_ID_MOT)) {
            return redirect()->to(site_url('floribondant/DICT'));
        }

        $idMot = (int) $this->request->getPost('id_mot_cle');

        // retirer() renvoie true si une ligne a réellement été supprimée
        if ((new ConceptCyberModel())->retirer($idMot)) {
            return redirect()->to(site_url('floribondant/DICT'))
                ->with('succes', 'Le terme a bien été supprimé du classement.');
        }

        return redirect()->to(site_url('floribondant/DICT'));
    }
}