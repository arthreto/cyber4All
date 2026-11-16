<?php

namespace App\Controllers\Controllers_groupe_C;

// Hérite de DICT_Admin_Base_GroupeC : on récupère donc le contrôle admin et synchroniserReponses()
class DICT_Concept_Admin_groupeC extends DICT_Admin_Base_GroupeC
{
    /**
     * AFFICHE la page des termes (route GET /floribondant/QuizzConcept)
     */
    public function GroupeC_Admin(): string
    {
        // Les modèles = des objets qui parlent à une table de la base
        $model        = new \App\Models\Model_groupe_C\ModelAdminCmot_cle();            // table mot_cle
        $modelConcept = new \App\Models\Model_groupe_C\ModelAdminCmot_cle_categorie();  // table conceptcyber

        // --- Barre de recherche : si on a tapé quelque chose, on filtre sur le mot ---
        $recherche = $this->request->getGet('recherche'); // valeur de ?recherche=...
        if (!empty($recherche)) {
            $model->like('mot', $recherche, 'both', null, true); // "mot" contient le texte tapé
        }

        // --- Filtre par catégorie (liste déroulante D/I/C/T) ---
        $categorie = $this->request->getGet('concept'); // valeur de ?concept=...
        // in_array(..., true) vérifie que la valeur est autorisée (sécurité : évite une injection SQL)
        if (in_array($categorie, ['disponibilite', 'integrite', 'confidentialite', 'tracabilite'], true)) {
            $model->select('mot_cle.*')
                ->join('conceptcyber', 'conceptcyber.id_mot_cle = mot_cle.id') // on relie les 2 tables
                ->where('conceptcyber.' . $categorie, 1);                      // garde ceux dont la case = 1
        }

        // --- Données envoyées à la vue (colonne de gauche : le tableau) ---
        $data['mots']    = $model->findAll(); // tous les termes (filtrés ou non)
        // On transforme la liste des concepts en tableau indexé par id du mot,
        // pour retrouver facilement les D/I/C/T d'un mot dans la vue
        $data['concept'] = array_column($modelConcept->findAll(), null, 'id_mot_cle');

        // --- Colonne de droite : mode modification ---
        $idModif                = $this->request->getGet('modifier'); // ?modifier=12
        $data['edition']        = $idModif ? $model->find($idModif) : null;                // le terme à modifier
        $data['conceptEdition'] = $idModif ? ($data['concept'][$idModif] ?? null) : null;  // ses cases cochées

        // --- Colonne de droite : mode ajout (?creer=1) ---
        $data['creation'] = $this->request->getGet('creer') !== null; // true si ?creer est dans l'URL

        // Liste déroulante "Terme à ajouter" : les mots pas encore classés
        $data['motsDisponibles'] = [];
        if ($data['creation']) {
            $data['motsDisponibles'] = \Config\Database::connect()
                ->table('mot_cle')
                ->select('mot_cle.id, mot_cle.mot')
                ->join('conceptcyber', 'conceptcyber.id_mot_cle = mot_cle.id', 'left') // left = garde aussi les mots sans ligne dans conceptcyber
                ->where('conceptcyber.id_mot_cle IS NULL')                              // ...et on ne garde que ceux-là
                ->orderBy('mot_cle.mot', 'ASC')                                         // tri alphabétique
                ->get()->getResultArray();
        }

        // On assemble la page : en-tête + notice + notre vue + pied de page
        return view('header')
            . view('notice')
            . view('groupe_c/groupe_c_ADMIN/admin_DICT_groupeC', $data)
            . view('footer');
    }

    /**
     * ENREGISTRE les cases D/I/C/T d'un terme (route POST groupe_c/admin/modifier)
     */
    public function GroupeC_Modifier()
    {
        $modelConcept = new \App\Models\Model_groupe_C\ModelAdminCmot_cle_categorie();
        $db           = \Config\Database::connect();
        $idMot        = (int) $this->request->getPost('id_mot_cle'); // id du terme (champ caché du formulaire)

        // Une case cochée envoie "1", une case décochée n'envoie rien : on convertit en 1 / 0
        $valeurs = [
            'disponibilite'   => $this->request->getPost('disponibilite') ? 1 : 0,
            'integrite'       => $this->request->getPost('integrite') ? 1 : 0,
            'confidentialite' => $this->request->getPost('confidentialite') ? 1 : 0,
            'tracabilite'     => $this->request->getPost('tracabilite') ? 1 : 0,
        ];

        // Transaction : soit TOUT est enregistré, soit RIEN (évite les données à moitié mises à jour)
        $db->transStart();

        $modelConcept->update($idMot, $valeurs); // on met à jour les cases du terme

        // On recalcule les bonnes réponses de chaque question liée à ce terme
        $questions = $db->table('question_quiz')->select('id')->where('id_mot_cle', $idMot)->get()->getResultArray();
        foreach ($questions as $q) {
            $this->synchroniserReponses($db, (int) $q['id'], $idMot); // méthode de DICT_Admin_Base_GroupeC
        }

        $db->transComplete(); // fin de la transaction : validation

        // On revient sur la page, avec le terme toujours ouvert en modification
        return redirect()->to(base_url('floribondant/DICT') . '?modifier=' . $idMot);
    }

    /**
     * CLASSE un terme existant dans le quiz (route POST groupe_c/admin/ajouter)
     * Il est ajouté à conceptcyber avec toutes les cases à 0, on les cochera ensuite.
     */
    public function GroupeC_Ajouter()
    {
        $idMot = (int) $this->request->getPost('id_mot_cle'); // terme choisi dans la liste déroulante
        $db    = \Config\Database::connect();

        // Sécurités : le mot existe bien dans le glossaire ? n'est-il pas déjà classé ?
        $motExiste   = $db->table('mot_cle')->where('id', $idMot)->countAllResults() > 0;
        $dejaPresent = $db->table('conceptcyber')->where('id_mot_cle', $idMot)->countAllResults() > 0;

        if ($motExiste && !$dejaPresent) {
            $db->table('conceptcyber')->insert([
                'id_mot_cle'      => $idMot,
                'disponibilite'   => 0, // aucune catégorie cochée au départ
                'integrite'       => 0,
                'confidentialite' => 0,
                'tracabilite'     => 0,
            ]);
        }

        // On ouvre directement le terme en modification pour cocher ses catégories
        return redirect()->to(base_url('floribondant/DICT') . '?modifier=' . $idMot);
    }

    /**
     * RETIRE un terme du classement (route POST groupe_c/admin/supprimer)
     * Le mot reste dans le glossaire, il n'est juste plus dans le quiz.
     */
    public function GroupeC_Supprimer()
    {
        $idMot = (int) $this->request->getPost('id_mot_cle'); // id du terme (champ caché du formulaire)

        if ($idMot > 0) { // sécurité : on ignore un id invalide
            \Config\Database::connect()
                ->table('conceptcyber')        // table des classements D/I/C/T
                ->where('id_mot_cle', $idMot)  // la ligne de ce terme
                ->delete();                    // on la supprime
        }

        // Retour à la liste, sans terme ouvert
        return redirect()->to(base_url('floribondant/DICT'));
    }
}