<?php

/**
 * VUE : gestion des questions du quiz Concept (administration, groupe C).
 * Fichier : app/Views/groupe_c/admin/questions.php
 *
 * RÔLE DE CETTE VUE
 * -----------------
 * Elle ne fait QU'AFFICHER. Elle ne lit pas la base et ne traite aucun formulaire :
 *   - le contrôleur DictQuestionAdminController::GroupeC_Questions() lui envoie les données (variables ci-dessous) ;
 *   - les formulaires envoient leurs données vers 3 routes, traitées par le même contrôleur :
 *       floribondant/groupe_c/admin/questions/ajouter    -> GroupeC_AjouterQuestion()
 *       floribondant/groupe_c/admin/questions/modifier   -> GroupeC_ModifierQuestion()
 *       floribondant/groupe_c/admin/questions/supprimer  -> GroupeC_SupprimerQuestion()
 *   - le style vient de public/css/groupe_c_css/admin.css (chargé par le layout).
 *
 * LES 3 AFFICHAGES POSSIBLES (colonne de droite)
 * ----------------------------------------------
 *   1. ?modifier=ID  -> $edition rempli  : formulaire de modification (question, explication, terme associé)
 *   2. ?creer=1      -> $creation = true : formulaire de création (le terme se choisit après)
 *   3. sinon                             : écran vide avec le bouton "Nouvelle question"
 *
 * LAYOUT ET MESSAGE DE SUCCÈS
 * --------------------------
 * La vue étend layout_admin_groupeC : l'en-tête, la notice, le CSS, le <body>, le pied de page
 * et le pop-up de succès (message flash 'succes') y sont déjà. Ici, il ne reste que le contenu de la page.
 *
 * FORMULAIRES : HELPER "form" DE CODEIGNITER 4
 * --------------------------------------------
 *   form_open()      -> <form> (action, attributs, champs cachés ; ajoute le jeton CSRF si le filtre csrf est global)
 *   form_label()     -> <label for="...">
 *   form_textarea()  -> <textarea> (valeur échappée automatiquement)
 *   form_dropdown()  -> <select> + <option> (valeur sélectionnée gérée toute seule)
 *   form_button()    -> <button>
 *   form_close()     -> </form>
 *   anchor()         -> <a href="..."> (les liens Modifier / Annuler / Nouvelle question)
 *   Les VALEURS (textarea, champs cachés de form_open) sont échappées automatiquement : pas de esc() dessus.
 *   Les LIBELLÉS des <option> ne le sont pas : on les passe dans esc().
 *
 * VARIABLES ENVOYÉES PAR LE CONTRÔLEUR
 * ------------------------------------
 * @var array<int, array<string, mixed>> $questions  toutes les questions (question_quiz + colonne "mot" de mot_cle)
 * @var array<string, mixed>|null        $edition    question en cours de modification (mode 1)
 * @var bool                             $creation   true si on est en mode création (mode 2)
 * @var array<int, array<string, mixed>> $mots       termes classés proposés dans la liste "Terme associé" (mode 1)
 */

helper('form'); // charge les fonctions form_open(), form_textarea(), etc. (sans effet si déjà chargé)

// Cette vue s'insère dans le layout (en-tête, notice, CSS, <body>, pied de page, pop-up de succès)
$this->extend('groupe_c/admin/layout_admin');

// Tout ce qui suit jusqu'à endSection() est injecté à l'endroit de renderSection('contenu') du layout
$this->section('contenu');
?>

    <div class="quiz-concept">

        <!-- ===================== EN-TÊTE ===================== -->
        <div class="entete">
            <div>
                <p class="petit-titre">Administration · Quiz Concept</p>
                <h1 class="titre">Gestion des questions</h1>
                <p class="sous-titre">Crée les questions du quiz et associe-les à un terme.</p>
            </div>

        </div>

        <div class="colonnes">

            <!-- ============================================================
                 COLONNE GAUCHE : liste des questions
                 ============================================================ -->
            <div class="carte">
                <div class="tableau-defilant">
                    <table class="tableau">
                        <thead>
                        <tr>
                            <th>Question et explication</th>
                            <th>Terme associé</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <!-- $questions vient du contrôleur (question_quiz + mot_cle) : une ligne par question -->
                        <?php foreach ($questions as $q): ?>
                            <tr>
                                <!-- Colonne 1 : la question et son explication -->
                                <td>
                                    <p class="nom-terme"><?= esc($q['question']) ?></p>
                                    <p class="definition-terme"><?= esc($q['explication']) ?></p>
                                </td>

                                <!-- Colonne 2 : le terme associé, ou "Non associée" s'il n'y en a pas -->
                                <td>
                                    <?php if (! empty($q['mot'])): ?>
                                        <span class="puce"><?= esc($q['mot']) ?></span>
                                    <?php else: ?>
                                        <span class="aide">Non associée</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Colonne 3 : boutons Modifier et Supprimer -->
                                <td>
                                    <div class="actions">
                                        <!-- ?modifier=ID recharge la page en mode modification (formulaire de droite) -->
                                        <?= anchor(current_url() . '?modifier=' . (int) $q['id'], 'Modifier', ['class' => 'bouton bouton-modifier']) ?>

                                        <!-- Suppression directement depuis la liste, avec confirmation.
                                             Envoi (POST) vers la route "questions/supprimer" ;
                                             le 3e paramètre de form_open() = champ caché id_question.
                                             csrf_field() = jeton CSRF (supprimable si le filtre csrf est déjà global). -->
                                        <?= form_open(
                                                'floribondant/groupe_c/admin/questions/supprimer',
                                                [],
                                                ['id_question' => $q['id']]
                                        ) ?>
                                        <?= csrf_field() ?>
                                        <?= form_button([
                                                'type'    => 'submit',
                                                'class'   => 'bouton bouton-supprimer',
                                                'onclick' => "return confirm('Suppression de la question\\n\\n" . addslashes($q['question']) . "\\n\\nCette action est définitive. Continuer ?')",
                                                'content' => 'Supprimer',
                                        ]) ?>
                                        <?= form_close() ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ============================================================
                 COLONNE DROITE : modification / création / écran vide
                 ============================================================ -->
            <?php if (! empty($edition)): ?>

                <?php
                // Options de la liste "Terme associé" : '' = "Aucun terme", puis id => mot (échappé)
                $optionsMots = ['' => '— Aucun terme —'] + array_map('esc', array_column($mots, 'mot', 'id'));
                ?>

                <!-- MODE 1 : modification de la question, de l'explication et du terme associé -->
                <div class="carte" id="formulaire">
                    <div class="carte-titre">
                        <h2>Modifier la question</h2>
                    </div>

                    <!-- Envoi (POST) vers la route "questions/modifier".
                         3e paramètre de form_open() = champ caché : l'id de la question.
                         csrf_field() = jeton CSRF (supprimable si le filtre csrf est déjà global). -->
                    <?= form_open(
                            'floribondant/groupe_c/admin/questions/modifier',
                            ['class' => 'formulaire'],
                            ['id_question' => $edition['id']]
                    ) ?>
                    <?= csrf_field() ?>

                    <!-- Rappel : form_textarea() ajoute cols/rows par défaut (40/10).
                         On remet 20/2 (valeurs du navigateur) pour garder le même rendu qu'avant. -->
                    <div class="champ">
                        <?= form_label('Question*', 'question', ['class' => 'etiquette']) ?>
                        <?= form_textarea([
                                'name'     => 'question',
                                'id'       => 'question',
                                'class'    => 'zone-texte',
                                'value'    => $edition['question'],
                                'cols'     => '20',
                                'rows'     => '2',
                                'required' => 'required',
                        ]) ?>
                    </div>

                    <div class="champ">
                        <?= form_label('Explication (feedback)', 'explication', ['class' => 'etiquette']) ?>
                        <?= form_textarea([
                                'name'  => 'explication',
                                'id'    => 'explication',
                                'class' => 'zone-texte',
                                'value' => $edition['explication'] ?? '',
                                'cols'  => '20',
                                'rows'  => '2',
                        ]) ?>
                        <p class="aide">Affichée après la réponse du quiz (1 à 2 phrases).</p>

                    </div>

                    <!-- Liste déroulante : $mots = termes classés.
                         Le terme actuel de la question est présélectionné (3e paramètre).
                         "Aucun terme" (valeur vide) retire l'association. -->
                    <div class="champ">
                        <?= form_label('Terme associé', 'id_mot_cle', ['class' => 'etiquette']) ?>
                        <?= form_dropdown('id_mot_cle', $optionsMots, (string) ($edition['id_mot_cle'] ?? ''), [
                                'id'    => 'id_mot_cle',
                                'class' => 'filtre',
                        ]) ?>
                    </div>

                    <div class="boutons-formulaire">
                        <?= anchor(current_url(), 'Annuler', ['class' => 'bouton bouton-annuler']) ?>
                        <?= form_button([
                                'type'    => 'submit',
                                'class'   => 'bouton bouton-principal',
                                'content' => 'Enregistrer',
                        ]) ?>
                    </div>
                    <?= form_close() ?>
                </div>

            <?php elseif (! empty($creation)): ?>

                <!-- MODE 2 : création. Question + explication seulement, le terme se choisit après -->
                <div class="carte" id="formulaire">
                    <div class="carte-titre">
                        <h2>Nouvelle question</h2>
                    </div>

                    <!-- Envoi (POST) vers la route "questions/ajouter".
                         Après la création, le contrôleur ouvre la question en mode modification. -->
                    <?= form_open('floribondant/groupe_c/admin/questions/ajouter', ['class' => 'formulaire']) ?>
                    <?= csrf_field() ?>

                    <div class="champ">
                        <?= form_label('Question*', 'question', ['class' => 'etiquette']) ?>
                        <?= form_textarea([
                                'name'        => 'question',
                                'id'          => 'question',
                                'class'       => 'zone-texte',
                                'cols'        => '20',
                                'rows'        => '2',
                                'required'    => 'required',
                                'placeholder' => 'Ex. : Un équipement filtre le trafic réseau… Quels critères protège-t-il ?',
                        ]) ?>
                    </div>

                    <div class="champ">
                        <?= form_label('Explication (feedback)', 'explication', ['class' => 'etiquette']) ?>
                        <?= form_textarea([
                                'name'        => 'explication',
                                'id'          => 'explication',
                                'class'       => 'zone-texte',
                                'cols'        => '20',
                                'rows'        => '2',
                                'placeholder' => 'Ex. : Un pare-feu filtre le trafic selon des règles…',
                        ]) ?>
                        <p class="aide">
                            Affichée après la réponse du quiz (1 à 2 phrases).
                        </p>
                    </div>
                    <?php if (session()->has('erreur')): ?>
                        <div class="popup-erreur">
                            ✖ <?= esc(session('erreur')) ?>
                        </div>
                    <?php endif; ?>

                    <div class="boutons-formulaire">
                        <?= anchor(current_url(), 'Annuler', ['class' => 'bouton bouton-annuler']) ?>
                        <?= form_button([
                                'type'    => 'submit',
                                'class'   => 'bouton bouton-principal',
                                'content' => 'Créer',
                        ]) ?>
                    </div>
                    <?= form_close() ?>
                </div>

            <?php else: ?>

                <!-- MODE 3 : écran de base (rien de sélectionné) -->
                <div class="carte" id="formulaire">
                    <div class="vide">
                        <div class="vide-icone">+</div>
                        <h2>Sélectionne une question</h2>
                        <p>Choisis une question à modifier, ou crées-en une nouvelle.</p>
                        <!-- ?creer=1 recharge la page en mode création -->
                        <?= anchor(current_url() . '?creer=1', 'Nouvelle question', ['class' => 'bouton bouton-principal']) ?>
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </div>

<?php $this->endSection() ?>