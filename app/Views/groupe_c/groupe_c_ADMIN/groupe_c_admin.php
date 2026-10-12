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
        <a href="#formulaire" class="bouton bouton-principal">+ Ajouter un terme</a>
    </div>

    <div class="colonnes">

        <!-- ===== COLONNE GAUCHE : la liste ===== -->
        <div class="carte">

            <div class="barre-outils">
                <input class="saisie recherche" type="search" placeholder="Rechercher un terme ou une définition…">
                <select class="filtre">
                    <option>Toutes les catégories</option>
                    <option>Disponibilité</option>
                    <option>Intégrité</option>
                    <option>Confidentialité</option>
                    <option>Traçabilité</option>
                </select>
            </div>

            <div class="tableau-defilant">
                <table class="tableau">
                    <thead>
                    <tr><th>Terme et définition</th><th>Catégories</th><th>Actions</th></tr>
                    </thead>
                    <?php foreach ($mots as $mot_cle) : ?>
                        <?php $conceptCyber = $concept[$mot_cle['id']] ?? null; ?>
                        <tr>
                            <td>
                                <p class="nom-terme"><?= esc($mot_cle['mot']) ?></p>
                                <p class="definition-terme"><?= esc($mot_cle['definition']) ?></p>
                            </td>
                            <td>
                                <div class="puces">
                                    <?php if ($conceptCyber && $conceptCyber['DISPONIBILITE'] == 1): ?>
                                        <span class="puce puce-d">D</span>
                                    <?php endif; ?>
                                    <?php if ($conceptCyber && $conceptCyber['INTEGRITE'] == 1): ?>
                                        <span class="puce puce-i">I</span>
                                    <?php endif; ?>
                                    <?php if ($conceptCyber && $conceptCyber['CONFIDENTIALITE'] == 1): ?>
                                        <span class="puce puce-c">C</span>
                                    <?php endif; ?>
                                    <?php if ($conceptCyber && $conceptCyber['TRACABILITE'] == 1): ?>
                                        <span class="puce puce-t">T</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="actions">
                                    <button class="bouton bouton-modifier">Modifier</button>
                                    <button class="bouton bouton-supprimer">Supprimer</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <span class="page-actuelle">1</span>
                <a href="#">2</a>
                <a href="#">3</a>
                <a href="#">Next</a>
                <a href="#">Last</a>
            </div>
        </div>

        <!-- ===== COLONNE DROITE : le formulaire ===== -->
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
                    <button type="button" class="bouton bouton-annuler">Annuler</button>
                    <button type="submit" class="bouton bouton-principal">Enregistrer</button>
                </div>

            </div>
        </div>

    </div>
</div>

</body>

