<?php

/**
 * RÔLE DE CETTE VUE
 * -----------------
 * Elle ne fait QU'AFFICHER. Elle ne lit pas la base et ne traite aucun formulaire :
 *   - le contrôleur Dict_Concept_Admin_controller::GroupeC_Admin() lui envoie les données (variables ci-dessous) ;
 *   - les formulaires envoient leurs données vers 3 routes, traitées par le même contrôleur :
 *       floribondant/groupe_c/admin/modifier   -> GroupeC_Modifier()
 *       floribondant/groupe_c/admin/supprimer  -> GroupeC_Supprimer()
 *       floribondant/groupe_c/admin/ajouter    -> GroupeC_Ajouter()
 *   - le style vient de public/css/groupe_c_css/groupe_c_css.css (chargé par le layout).
 *
 * LAYOUT
 * ------
 * La vue étend layout_admin_groupeC : l'en-tête, la notice, le CSS, le <body>, le pied de page
 * et le pop-up de succès (message flash) y sont déjà. Ici, il ne reste que le contenu de la page.
 *
 * LES 3 AFFICHAGES POSSIBLES (colonne de droite)
 * ----------------------------------------------
 *   1. ?modifier=ID  -> $edition rempli  : formulaire de modification des catégories
 *   2. ?creer=1      -> $creation = true : formulaire d'ajout d'un terme du glossaire
 *   3. sinon                             : écran vide avec le bouton "Ajouter un terme"
 *
 * LES 4 CATÉGORIES (DICT)
 * -----------------------
 *   D = Disponibilité | I = Intégrité | C = Confidentialité | T = Traçabilité
 *   Ce sont aussi les 4 colonnes de la table conceptcyber (valeur 1 = le terme est dans la catégorie).
 *
 * FORMULAIRES : HELPER "form" DE CODEIGNITER 4
 * --------------------------------------------
 *   form_open()      -> <form> (action, attributs, champs cachés ; ajoute le jeton CSRF si le filtre csrf est global)
 *   form_input()     -> <input> (ici type "search")
 *   form_dropdown()  -> <select> + <option> (valeur sélectionnée gérée toute seule)
 *   form_checkbox()  -> <input type="checkbox"> (cochée si 'checked' => true)
 *   form_label()     -> <label for="...">
 *   form_button()    -> <button>
 *   form_close()     -> </form>
 *   anchor()         -> <a href="..."> (les liens Modifier / Annuler / Retour / Ajouter)
 *   Les VALEURS passées à form_input() / form_open() (champs cachés) sont échappées automatiquement :
 *   pas besoin de esc() dessus. Les LIBELLÉS des <option> ne le sont pas : on les passe dans esc().
 *
 * VARIABLES ENVOYÉES PAR LE CONTRÔLEUR
 * ------------------------------------
 * @var array<string, string>            $categories       clé => libellé (disponibilite => Disponibilité, ...)
 * @var string                           $recherche        texte tapé dans la barre de recherche
 * @var string                           $conceptFiltre    catégorie filtrée ('' = toutes)
 * @var array<int, array<string, mixed>> $mots             termes à lister (id, mot, definition), déjà filtrés
 * @var array<int, array<string, mixed>> $concept          cases D/I/C/T, indexées par id du terme
 * @var array<string, mixed>|null        $edition          terme en cours de modification (mode 1)
 * @var array<string, mixed>|null        $conceptEdition   cases D/I/C/T de ce terme (mode 1)
 * @var bool                             $creation         true si on est en mode ajout (mode 2)
 * @var array<int, array<string, mixed>> $motsDisponibles  termes du glossaire pas encore classés (mode 2)
 */

helper('form'); // charge les fonctions form_open(), form_input(), etc. (sans effet si déjà chargé)

// Cette vue s'insère dans le layout (en-tête, notice, CSS, <body>, pied de page, pop-up de succès)
$this->extend('groupe_c/admin/layout_admin');

// Tout ce qui suit jusqu'à endSection() est injecté à l'endroit de renderSection('contenu') du layout
$this->section('contenu');

// Options de la liste déroulante de filtre : la clé '' = "pas de filtre"
$optionsFiltre = ['' => 'Toutes les catégories'] + array_map('esc', $categories);
?>

    <div class="quiz-concept">

        <!-- ===================== EN-TÊTE ===================== -->
        <div class="entete">
            <div>
                <p class="petit-titre">Administration · Quiz Concept</p>
                <h1 class="titre">Gestion des termes</h1>
                <p class="sous-titre">Ajoute, modifie et classe les termes utilisés dans le quiz DICT.</p>
            </div>
        </div>

        <div class="colonnes">

            <!-- ============================================================
                 COLONNE GAUCHE : liste des termes + recherche par concept
                 ============================================================ -->
            <div class="carte">

                <!-- Barre de recherche : formulaire en GET (action vide = la page actuelle),
                     le contrôleur lit "recherche" et "concept" dans l'URL -->
                <?= form_open('', ['method' => 'get', 'class' => 'barre-outils']) ?>
                <?= form_input([
                        'type'        => 'search',
                        'name'        => 'recherche',
                        'value'       => $recherche,
                        'class'       => 'saisie recherche',
                        'placeholder' => 'Rechercher un terme…',
                ]) ?>

                <!-- Filtre par catégorie : se valide tout seul au changement (onchange) -->
                <?= form_dropdown('concept', $optionsFiltre, (string) $conceptFiltre, [
                        'class'    => 'filtre',
                        'onchange' => 'this.form.submit()',
                ]) ?>
                <?= form_close() ?>

                <!-- Tableau : une ligne par terme -->
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
                                <!-- Colonne 1 : le mot et sa définition -->
                                <td>
                                    <p class="nom-terme"><?= esc($motCle['mot']) ?></p>
                                    <p class="definition-terme"><?= esc($motCle['definition']) ?></p>
                                </td>

                                <!-- Colonne 2 : une puce (D, I, C ou T) pour chaque catégorie à 1 -->
                                <td>
                                    <div class="puces">
                                        <?php foreach (array_keys($categories) as $cle): ?>
                                            <?php if ((int) $concept[$motCle['id']][$cle] === 1): ?>
                                                <?php $lettre = strtoupper($cle[0]); // 1re lettre de la clé = D, I, C ou T ?>
                                                <!-- Classe CSS : puce-d, puce-i, puce-c ou puce-t -->
                                                <span class="puce puce-<?= strtolower($lettre) ?>"><?= $lettre ?></span>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </td>

                                <!-- Colonne 3 : "Modifier" puis "Supprimer" juste en dessous (flex-direction: column) -->
                                <td>
                                    <div class="actions" style="flex-direction: column;">
                                        <!-- Le lien recharge la page en mode modification (?modifier=ID) -->
                                        <?= anchor(current_url() . '?modifier=' . (int) $motCle['id'], 'Modifier', ['class' => 'bouton bouton-modifier']) ?>

                                        <!-- Retire le terme du classement, avec confirmation.
                                             Envoi (POST) vers la route "supprimer" ;
                                             le 3e paramètre de form_open() = champ caché id_mot_cle.
                                             csrf_field() = jeton CSRF (supprimable si le filtre csrf est déjà global). -->
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

            <!-- ============================================================
                 COLONNE DROITE : modification / ajout / écran vide
                 ============================================================ -->
            <?php if (! empty($edition)): ?>

                <!-- MODE 1 : modification des catégories d'un terme -->
                <div class="carte" id="formulaire">

                    <div class="carte-titre">
                        <h2>Modifier les concepts cyber</h2>
                        <p><?= esc($edition['mot']) ?></p>
                    </div>

                    <!-- Envoi (POST) vers la route "modifier".
                         3e paramètre de form_open() = champs cachés : ici l'id du terme (id_mot_cle).
                         csrf_field() = jeton CSRF (supprimable si le filtre csrf est déjà global, voir en-tête). -->
                    <?= form_open(
                            'floribondant/groupe_c/admin/modifier',
                            ['class' => 'formulaire'],
                            ['id_mot_cle' => $edition['id']]
                    ) ?>
                    <?= csrf_field() ?>

                    <!-- 4 cases à cocher (une par catégorie), cochées si la valeur en base vaut 1.
                         Une case décochée n'est pas envoyée : le contrôleur la traduit en 0. -->
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
                                        <span class="lettre"><?= $lettre ?></span> <?= esc($libelle) ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="boutons-formulaire">
                        <?= anchor(current_url(), 'Annuler', ['class' => 'bouton bouton-annuler']) ?>

                        <?= form_button([
                                'type'    => 'submit',
                                'class'   => 'bouton bouton-principal',
                                'content' => 'Enregistrer',
                        ]) ?>
                    </div>

                    <?php if (session()->has('succes')): ?>
                        <div class="popup-succes">
                            ✔ <?= esc(session('succes')) ?>
                        </div>
                    <?php endif; ?>

                    <?= form_close() ?>
                </div>

            <?php elseif (! empty($creation)): ?>

                <!-- MODE 2 : ajout d'un terme qui existe déjà dans le glossaire (mais pas encore classé) -->
                <div class="carte" id="formulaire">

                    <div class="carte-titre">
                        <h2>Ajouter un terme</h2>
                        <p>Termes pas encore classés</p>
                    </div>

                    <?php if (empty($motsDisponibles)): ?>
                        <!-- Plus aucun terme à ajouter -->
                        <div class="formulaire">
                            <p class="aide">Tous les termes sont déjà ajoutés.</p>
                            <div class="boutons-formulaire">
                                <?= anchor(current_url(), 'Retour', ['class' => 'bouton bouton-annuler']) ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php
                        // Options de la liste : '' = "Choisir un terme…", puis id => mot (échappé)
                        $optionsMots = ['' => 'Choisir un terme…'] + array_map('esc', array_column($motsDisponibles, 'mot', 'id'));
                        ?>
                        <!-- Envoi (POST) vers la route "ajouter" : le choix dans la liste valide tout seul (onchange).
                             Le terme est ajouté SANS catégorie ; on lui en donne ensuite via "Modifier". -->
                        <?= form_open('floribondant/groupe_c/admin/ajouter', ['class' => 'formulaire']) ?>
                        <?= csrf_field() ?>

                        <div class="champ">
                            <?= form_label('Terme à ajouter', 'id_mot_cle', ['class' => 'etiquette']) ?>
                            <?= form_dropdown('id_mot_cle', $optionsMots, '', [
                                    'id'       => 'id_mot_cle',
                                    'class'    => 'filtre',
                                    'onchange' => 'if (this.value) this.form.submit()',
                            ]) ?>
                            <p class="aide">Ajouter un terme sans catégorie</p>
                        </div>


                        <div class="boutons-formulaire">
                            <?= anchor(current_url(), 'Annuler', ['class' => 'bouton bouton-annuler']) ?>
                        </div>

                        <?php if (session()->has('succes')): ?>
                            <div class="popup-succes">
                                ✔ <?= esc(session('succes')) ?>
                            </div>
                        <?php endif; ?>

                        <?= form_close() ?>
                    <?php endif; ?>

                </div>

            <?php else: ?>

                <!-- MODE 3 : écran de base (ni modification ni ajout en cours) -->
                <div class="carte" id="formulaire">
                    <div class="vide">
                        <div class="vide-icone">+</div>
                        <h2>Sélectionne un terme</h2>
                        <p>Choisis une ligne à modifier ou ajoute un terme existant au classement.</p>
                        <!-- ?creer=1 recharge la page en mode ajout -->
                        <?= anchor(current_url() . '?creer=1', 'Ajouter un terme', ['class' => 'bouton bouton-principal']) ?>
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </div>

<?php $this->endSection() ?>