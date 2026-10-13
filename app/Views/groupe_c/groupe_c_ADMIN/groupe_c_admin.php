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
        <a href="?creer=1" class="bouton bouton-principal">+ Ajouter un terme</a>
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
                    </div>

                    <div class="boutons-formulaire">
                        <a href="<?= current_url() ?>" class="bouton bouton-annuler">Annuler</a>
                        <button type="submit" class="bouton bouton-principal">Enregistrer</button>
                    </div>
                </form>
            </div>

        <?php elseif ($creation): ?>

            <!-- Création d'un terme -->
            <div class="carte" id="formulaire">

                <div class="carte-titre">
                    <h2>Ajouter un terme</h2>
                    <p>Nouveau contenu pour le quiz</p>
                </div>

                <div class="formulaire">

                    <div class="champ">
                        <label class="etiquette" for="nom">Nom du terme <span class="obligatoire">*</span></label>
                        <input class="saisie" id="nom" type="text" placeholder="Ex. : Journalisation">
                    </div>

                    <div class="champ">
                        <label class="etiquette" for="definition">Définition <span class="obligatoire">*</span></label>
                        <textarea class="zone-texte" id="definition" placeholder="Une phrase claire, niveau débutant"></textarea>
                    </div>

                    <div class="champ">
                        <span class="etiquette">Catégorie(s) associée(s) <span class="obligatoire">*</span></span>
                        <div class="choix">
                            <label class="choix-case choix-d">
                                <input type="checkbox" name="categories[]" value="1">
                                <span class="choix-contenu"><span class="lettre">D</span> Disponibilité</span>
                            </label>
                            <label class="choix-case choix-i">
                                <input type="checkbox" name="categories[]" value="2">
                                <span class="choix-contenu"><span class="lettre">I</span> Intégrité</span>
                            </label>
                            <label class="choix-case choix-c">
                                <input type="checkbox" name="categories[]" value="3">
                                <span class="choix-contenu"><span class="lettre">C</span> Confidentialité</span>
                            </label>
                            <label class="choix-case choix-t">
                                <input type="checkbox" name="categories[]" value="4">
                                <span class="choix-contenu"><span class="lettre">T</span> Traçabilité</span>
                            </label>
                        </div>
                        <p class="aide">Un terme peut avoir plusieurs catégories.</p>
                    </div>

                    <div class="champ">
                        <label class="etiquette" for="pourquoi">Pourquoi ces catégories ?</label>
                        <textarea class="zone-texte" id="pourquoi" placeholder="Explication affichée après la réponse du quiz"></textarea>
                    </div>

                    <div class="boutons-formulaire">
                        <a href="<?= current_url() ?>" class="bouton bouton-annuler">Annuler</a>
                        <button type="submit" class="bouton bouton-principal">Enregistrer</button>
                    </div>

                </div>
            </div>

        <?php else: ?>

            <!-- Écran par défaut -->
            <div class="carte" id="formulaire">
                <div class="vide">
                    <div class="vide-icone">+</div>
                    <h2>Sélectionne un terme</h2>
                    <p>Choisis une ligne à modifier ou crée une nouvelle notion.</p>
                    <a href="?creer=1" class="bouton bouton-principal">Créer un terme</a>
                </div>
            </div>

        <?php endif; ?>

    </div>
</div>

</body>