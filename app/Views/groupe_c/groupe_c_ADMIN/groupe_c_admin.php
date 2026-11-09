<?= link_tag(base_url('/css/groupe_c_css/groupe_c_css.css')) ?>
<style>body { margin: 0; background: #f7f8fc; }</style>
<body>

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

        <!-- ===== COLONNE GAUCHE : la liste ===== -->
        <div class="carte">

            <form method="get" class="barre-outils">
                <input class="saisie recherche" type="search" name="recherche"
                       value="<?= esc($_GET['recherche'] ?? '') ?>"
                       placeholder="Rechercher un terme…">

                <select class="filtre" name="concept" onchange="this.form.submit()">
                    <option value="">Toutes les catégories</option>
                    <option value="DISPONIBILITE" <?= ($_GET['concept'] ?? '') === 'DISPONIBILITE' ? 'selected' : '' ?>>Disponibilité</option>
                    <option value="INTEGRITE" <?= ($_GET['concept'] ?? '') === 'INTEGRITE' ? 'selected' : '' ?>>Intégrité</option>
                    <option value="CONFIDENTIALITE" <?= ($_GET['concept'] ?? '') === 'CONFIDENTIALITE' ? 'selected' : '' ?>>Confidentialité</option>
                    <option value="TRACABILITE" <?= ($_GET['concept'] ?? '') === 'TRACABILITE' ? 'selected' : '' ?>>Traçabilité</option>
                </select>
            </form>

            <div class="tableau-defilant">
                <table class="tableau">
                    <thead>
                    <tr><th>Terme et définition</th><th>Catégories</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($mots as $mot_cle) : ?>
                        <?php
                        $conceptCyber = $concept[$mot_cle['id']] ?? null;
                        if (!$conceptCyber) continue;
                        ?>
                        <tr>
                            <td>
                                <p class="nom-terme"><?= esc($mot_cle['mot']) ?></p>
                                <p class="definition-terme"><?= esc($mot_cle['definition']) ?></p>
                            </td>
                            <td>
                                <div class="puces">
                                    <?php if ($conceptCyber['DISPONIBILITE'] == 1): ?>
                                        <span class="puce puce-d">D</span>
                                    <?php endif; ?>
                                    <?php if ($conceptCyber['INTEGRITE'] == 1): ?>
                                        <span class="puce puce-i">I</span>
                                    <?php endif; ?>
                                    <?php if ($conceptCyber['CONFIDENTIALITE'] == 1): ?>
                                        <span class="puce puce-c">C</span>
                                    <?php endif; ?>
                                    <?php if ($conceptCyber['TRACABILITE'] == 1): ?>
                                        <span class="puce puce-t">T</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="?modifier=<?= $mot_cle['id'] ?>" class="bouton bouton-modifier">Modifier</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- ===== COLONNE DROITE ===== -->
        <?php if (!empty($edition)): ?>

            <!-- Modification des catégories d'un terme -->
            <div class="carte" id="formulaire">

                <div class="carte-titre">
                    <h2>Modifier les concepts cyber</h2>
                    <p><?= esc($edition['mot']) ?></p>
                </div>

                <form class="formulaire" method="post" action="<?= base_url('floribondant/groupe_c/admin/modifier') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_mot_cle" value="<?= esc($edition['id']) ?>">

                    <div class="champ">
                        <div class="choix">
                            <label class="choix-case choix-d">
                                <input type="checkbox" name="DISPONIBILITE" value="1"
                                        <?= !empty($conceptEdition['DISPONIBILITE']) ? 'checked' : '' ?>>
                                <span class="choix-contenu"><span class="lettre">D</span> Disponibilité</span>
                            </label>
                            <label class="choix-case choix-i">
                                <input type="checkbox" name="INTEGRITE" value="1"
                                        <?= !empty($conceptEdition['INTEGRITE']) ? 'checked' : '' ?>>
                                <span class="choix-contenu"><span class="lettre">I</span> Intégrité</span>
                            </label>
                            <label class="choix-case choix-c">
                                <input type="checkbox" name="CONFIDENTIALITE" value="1"
                                        <?= !empty($conceptEdition['CONFIDENTIALITE']) ? 'checked' : '' ?>>
                                <span class="choix-contenu"><span class="lettre">C</span> Confidentialité</span>
                            </label>
                            <label class="choix-case choix-t">
                                <input type="checkbox" name="TRACABILITE" value="1"
                                        <?= !empty($conceptEdition['TRACABILITE']) ? 'checked' : '' ?>>
                                <span class="choix-contenu"><span class="lettre">T</span> Traçabilité</span>
                            </label>
                        </div>
                        <div class="champ">
                            <label class="etiquette" for="question">Question associée</label>
                            <textarea class="zone-texte" id="question" name="question"
                                      placeholder="Ex. : Un équipement filtre le trafic réseau… Quels critères protège-t-il ?"><?= esc($questionEdition['question'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="boutons-formulaire">
                        <button type="submit" form="form-supprimer" class="bouton bouton-supprimer"
                                onclick="return confirm('Supprimer ce terme et sa question ?')">Supprimer</button>
                        <a href="<?= current_url() ?>" class="bouton bouton-annuler">Annuler</a>
                        <button type="submit" class="bouton bouton-principal">Enregistrer</button>
                    </div>
                </form>

                <!-- Formulaire de suppression (déclenché par le bouton Supprimer ci-dessus) -->
                <form id="form-supprimer" method="post" action="<?= base_url('floribondant/groupe_c/admin/supprimer') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_mot_cle" value="<?= esc($edition['id']) ?>">
                </form>
            </div>

        <?php elseif ($creation): ?>

        <!-- Ajout d'un terme existant dans les concepts cyber -->
        <div class="carte" id="formulaire">

            <div class="carte-titre">
                <h2>Ajouter un terme</h2>
                <p>Termes pas encore classés</p>
            </div>

            <?php if (empty($motsDisponibles)): ?>
                <div class="formulaire">
                    <p class="aide">Tous les termes sont déjà ajoutés.</p>
                    <div class="boutons-formulaire">
                        <a href="<?= current_url() ?>" class="bouton bouton-annuler">Retour</a>
                    </div>
                </div>
            <?php else: ?>
                <form class="formulaire" method="post" action="<?= base_url('floribondant/groupe_c/admin/ajouter') ?>">
                    <?= csrf_field() ?>

                    <div class="champ">
                        <label class="etiquette" for="id_mot_cle">Terme à ajouter</label>
                        <select class="filtre" id="id_mot_cle" name="id_mot_cle"
                                onchange="if (this.value) this.form.submit()">
                            <option value="">Choisir un terme…</option>
                            <?php foreach ($motsDisponibles as $m): ?>
                                <option value="<?= esc($m['id']) ?>"><?= esc($m['mot']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="aide">     Terme est ajouté sans catégorie</p>
                    </div>

                    <div class="boutons-formulaire">
                        <a href="<?= current_url() ?>" class="bouton bouton-annuler">Annuler</a>
                    </div>
                </form>
            <?php endif; ?>

        </div>

        <?php else: ?>

        <!-- Écran par défaut -->
        <div class="carte" id="formulaire">
            <div class="vide">
                <div class="vide-icone">+</div>
                <h2>Sélectionne un terme</h2>
                <p>Choisis une ligne à modifier ou ajoute un terme existant au classement.</p>
                <a href="?creer=1" class="bouton bouton-principal">Ajouter un terme</a>
            </div>
        </div>

        <?php endif; ?>

    </div>
</div>

</body>