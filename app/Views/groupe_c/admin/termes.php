
<?php

/**
 * RÔLE DE CETTE VUE
 * -----------------
 * Elle ne fait QU'AFFICHER. Elle ne lit pas la base et ne traite aucun formulaire :
 *   - le contrôleur Dict_Concept_Admin_controller::GroupeC_Admin() lui envoie les données ;
 *   - les formulaires envoient leurs données vers les routes correspondantes ;
 *   - le style vient de public/css/groupe_c_css/groupe_c_css.css.
 *
 * LAYOUT
 * ------
 * La vue étend groupe_c/admin/layout_admin.
 *
 * LES 3 AFFICHAGES POSSIBLES
 * --------------------------
 *   1. ?modifier=ID  -> $edition rempli
 *   2. ?creer=1      -> $creation = true
 *   3. sinon         -> écran vide
 */

helper('form');

$this->extend('groupe_c/admin/layout_admin');
$this->section('contenu');

$optionsFiltre = ['' => 'Toutes les catégories'] + array_map('esc', $categories);
?>

<div class="quiz-concept">

    <!-- EN-TÊTE -->
    <div class="entete">
        <div>
            <p class="petit-titre">Administration · Quiz Concept</p>
            <h1 class="titre">Gestion des termes</h1>
            <p class="sous-titre">Ajoute, modifie et classe les termes utilisés dans le quiz DICT.</p>
        </div>
    </div>

    <div class="colonnes">

        <!-- COLONNE GAUCHE : liste des termes -->
        <div class="carte">

            <?= form_open('', ['method' => 'get', 'class' => 'barre-outils']) ?>

            <?= form_input([
                    'type'        => 'search',
                    'name'        => 'recherche',
                    'value'       => $recherche,
                    'class'       => 'saisie recherche',
                    'placeholder' => 'Rechercher un terme…',
            ]) ?>

            <?= form_dropdown('concept', $optionsFiltre, (string) $conceptFiltre, [
                    'class'    => 'filtre',
                    'onchange' => 'this.form.submit()',
            ]) ?>

            <?= form_close() ?>

            <!-- Tableau des termes -->
            <div class="tableau-defilant">
                <table class="tableau">
                    <thead>
                    <tr>
                        <th>Terme et définition</th>
                        <th>Catégories</th>
                        <th>Actions</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($mots as $motCle): ?>
                        <tr>

                            <!-- Terme et définition -->
                            <td>
                                <p class="nom-terme"><?= esc($motCle['mot']) ?></p>
                                <p class="definition-terme"><?= esc($motCle['definition']) ?></p>
                            </td>

                            <!-- Catégories -->
                            <td>
                                <div class="puces">
                                    <?php foreach (array_keys($categories) as $cle): ?>
                                        <?php if ((int) $concept[$motCle['id']][$cle] === 1): ?>
                                            <?php $lettre = strtoupper($cle[0]); ?>

                                            <span class="puce puce-<?= strtolower($lettre) ?>">
                                                <?= $lettre ?>
                                            </span>

                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </td>

                            <!-- Actions -->
                            <td>
                                <div class="actions" style="flex-direction: column;">

                                    <?= anchor(
                                            current_url() . '?modifier=' . (int) $motCle['id'],
                                            'Modifier',
                                            ['class' => 'bouton bouton-modifier']
                                    ) ?>

                                    <?= form_open(
                                            'floribondant/groupe_c/admin/supprimer',
                                            [],
                                            ['id_mot_cle' => $motCle['id']]
                                    ) ?>

                                    <?= csrf_field() ?>

                                    <?= form_button([
                                            'type'    => 'submit',
                                            'class'   => 'bouton bouton-supprimer',
                                            'onclick' => "return confirm('Supprimer le terme : " . addslashes($motCle['mot']) . " ?')",
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


        <!-- COLONNE DROITE : carte + message de succès -->
        <div class="colonne-droite">

            <?php if (! empty($edition)): ?>

                <!-- MODE 1 : MODIFICATION -->
                <div class="carte" id="formulaire">

                    <div class="carte-titre">
                        <h2>Modifier les concepts cyber</h2>
                        <p><?= esc($edition['mot']) ?></p>
                    </div>

                    <?= form_open(
                            'floribondant/groupe_c/admin/modifier',
                            ['class' => 'formulaire'],
                            ['id_mot_cle' => $edition['id']]
                    ) ?>

                    <?= csrf_field() ?>

                    <div class="champ">
                        <div class="choix">

                            <?php foreach ($categories as $cle => $libelle): ?>
                                <?php $lettre = strtoupper($cle[0]); ?>

                                <label class="choix-case choix-<?= strtolower($lettre) ?>">

                                    <?= form_checkbox([
                                            'name'    => $cle,
                                            'value'   => '1',
                                            'checked' => (int) ($conceptEdition[$cle] ?? 0) === 1,
                                    ]) ?>

                                    <span class="choix-contenu">
                                        <span class="lettre"><?= $lettre ?></span>
                                        <?= esc($libelle) ?>
                                    </span>

                                </label>

                            <?php endforeach; ?>

                        </div>
                    </div>

                    <div class="boutons-formulaire">

                        <?= anchor(
                                current_url(),
                                'Annuler',
                                ['class' => 'bouton bouton-annuler']
                        ) ?>

                        <?= form_button([
                                'type'    => 'submit',
                                'class'   => 'bouton bouton-principal',
                                'content' => 'Enregistrer',
                        ]) ?>

                    </div>

                    <?= form_close() ?>

                </div>


            <?php elseif (! empty($creation)): ?>

                <!-- MODE 2 : AJOUT D'UN TERME -->
                <div class="carte" id="formulaire">

                    <div class="carte-titre">
                        <h2>Ajouter un terme</h2>
                        <p>Termes pas encore classés</p>
                    </div>

                    <?php if (empty($motsDisponibles)): ?>

                        <div class="formulaire">
                            <p class="aide">Tous les termes sont déjà ajoutés.</p>

                            <div class="boutons-formulaire">
                                <?= anchor(
                                        current_url(),
                                        'Retour',
                                        ['class' => 'bouton bouton-annuler']
                                ) ?>
                            </div>
                        </div>

                    <?php else: ?>

                        <?php
                        $optionsMots = ['' => 'Choisir un terme…']
                                + array_map('esc', array_column($motsDisponibles, 'mot', 'id'));
                        ?>

                        <?= form_open(
                                'floribondant/groupe_c/admin/ajouter',
                                ['class' => 'formulaire']
                        ) ?>

                        <?= csrf_field() ?>

                        <div class="champ">

                            <?= form_label(
                                    'Terme à ajouter',
                                    'id_mot_cle',
                                    ['class' => 'etiquette']
                            ) ?>

                            <?= form_dropdown('id_mot_cle', $optionsMots, '', [
                                    'id'       => 'id_mot_cle',
                                    'class'    => 'filtre',
                                    'onchange' => 'if (this.value) this.form.submit()',
                            ]) ?>

                            <p class="aide">Ajouter un terme sans catégorie</p>

                        </div>

                        <div class="boutons-formulaire">

                            <?= anchor(
                                    current_url(),
                                    'Annuler',
                                    ['class' => 'bouton bouton-annuler']
                            ) ?>

                        </div>

                        <?= form_close() ?>

                    <?php endif; ?>

                </div>


            <?php else: ?>

                <!-- MODE 3 : ÉCRAN VIDE -->
                <div class="carte" id="formulaire">

                    <div class="vide">

                        <div class="vide-icone">+</div>

                        <h2>Sélectionne un terme</h2>

                        <p>
                            Choisis une ligne à modifier ou ajoute un terme
                            existant au classement.
                        </p>

                        <?= anchor(
                                current_url() . '?creer=1',
                                'Ajouter un terme',
                                ['class' => 'bouton bouton-principal']
                        ) ?>

                    </div>

                </div>

            <?php endif; ?>


            <!-- MESSAGE DE SUCCÈS : SOUS LA CARTE DE DROITE -->
            <?php if (session()->has('succes')): ?>

                <div class="popup-succes">
                    ✔ <?= esc(session('succes')) ?>
                </div>

            <?php endif; ?>

        </div>
        <!-- FIN COLONNE DROITE -->

    </div>

</div>

<?php $this->endSection() ?>