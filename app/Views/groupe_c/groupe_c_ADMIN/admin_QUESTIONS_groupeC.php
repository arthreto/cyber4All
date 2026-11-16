<?= link_tag(base_url('/css/groupe_c_css/groupe_c_css.css')) ?>
<style>body { margin: 0; background: #f7f8fc; }</style>
<body>

<div class="quiz-concept">

    <!-------------------- EN-TÊTE ---------------->
    <div class="entete">
        <div>
            <p class="petit-titre">Administration · Quiz Concept</p>
            <h1 class="titre">Gestion des questions</h1>
            <p class="sous-titre">Crée les questions du quiz et associe-les à un terme.</p>
        </div>

    </div>

    <div class="colonnes">

        <!------------------ COLONNE GAUCHE : liste des questions --------------------->
        <div class="carte">
            <div class="tableau-defilant">
                <table class="tableau">
                    <thead>
                    <tr><th>Question et explication</th><th>Terme associé</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                    <!-- $questions vient du contrôleur (question_quiz + mot_cle) -->
                    <?php foreach ($questions as $q): ?>
                        <tr>
                            <td>
                                <p class="nom-terme"><?= esc($q['question']) ?></p>
                                <p class="definition-terme"><?= esc($q['explication']) ?></p>
                            </td>
                            <td>
                                <?php if (!empty($q['mot'])): ?>
                                    <span class="puce"><?= esc($q['mot']) ?></span>
                                <?php else: ?>
                                    <span class="aide">Non associée</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <!-- ?modifier=ID ouvre le formulaire de droite -->
                                    <a href="?modifier=<?= $q['id'] ?>" class="bouton bouton-modifier">Modifier</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!------------------ COLONNE DROITE ---------------------->
        <?php if (!empty($edition)): ?>

            <!-- MODIFICATION : question, explication et terme associé -->
            <div class="carte" id="formulaire">
                <div class="carte-titre">
                    <h2>Modifier la question</h2>
                </div>

                <form class="formulaire" method="post" action="<?= base_url('floribondant/groupe_c/admin/questions/modifier') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_question" value="<?= esc($edition['id']) ?>">

                    <div class="champ">
                        <label class="etiquette" for="question">Question</label>
                        <textarea class="zone-texte" id="question" name="question" required><?= esc($edition['question']) ?></textarea>
                    </div>

                    <div class="champ">
                        <label class="etiquette" for="explication">Explication (feedback)</label>
                        <textarea class="zone-texte" id="explication" name="explication"><?= esc($edition['explication'] ?? '') ?></textarea>
                        <p class="aide">Affichée après la réponse du quiz (1 à 2 phrases).</p>
                    </div>

                    <!-- Liste déroulante : $mots = termes classés et libres -->
                    <div class="champ">
                        <label class="etiquette" for="id_mot_cle">Terme associé</label>
                        <select class="filtre" id="id_mot_cle" name="id_mot_cle">
                            <option value="">— Aucun terme —</option>
                            <?php foreach ($mots as $m): ?>
                                <option value="<?= esc($m['id']) ?>"
                                        <?= ($edition['id_mot_cle'] ?? null) == $m['id'] ? 'selected' : '' ?>>
                                    <?= esc($m['mot']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="boutons-formulaire">
                        <!-- Ce bouton envoie le 2ème formulaire (form-supprimer) plus bas -->
                        <button type="submit" form="form-supprimer" class="bouton bouton-supprimer"
                                onclick="return confirm('Supprimer cette question ?')">Supprimer</button>
                        <a href="<?= current_url() ?>" class="bouton bouton-annuler">Annuler</a>
                        <button type="submit" class="bouton bouton-principal">Enregistrer</button>
                    </div>
                </form>

                <!-- Formulaire de suppression (séparé, déclenché par le bouton Supprimer) -->
                <form id="form-supprimer" method="post" action="<?= base_url('floribondant/groupe_c/admin/questions/supprimer') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_question" value="<?= esc($edition['id']) ?>">
                </form>
            </div>

        <?php elseif ($creation): ?>

            <!-- CRÉATION : question + explication seulement, le terme se choisit après -->
            <div class="carte" id="formulaire">
                <div class="carte-titre">
                    <h2>Nouvelle question</h2>
                    <p>Tu pourras l'associer à un terme ensuite</p>
                </div>

                <form class="formulaire" method="post" action="<?= base_url('floribondant/groupe_c/admin/questions/ajouter') ?>">
                    <?= csrf_field() ?>

                    <div class="champ">
                        <label class="etiquette" for="question">Question</label>
                        <textarea class="zone-texte" id="question" name="question" required
                                  placeholder="Ex. : Un équipement filtre le trafic réseau… Quels critères protège-t-il ?"></textarea>
                    </div>

                    <div class="champ">
                        <label class="etiquette" for="explication">Explication (feedback)</label>
                        <textarea class="zone-texte" id="explication" name="explication"
                                  placeholder="Ex. : Un pare-feu filtre le trafic selon des règles…"></textarea>
                        <p class="aide">Affichée après la réponse du quiz (1 à 2 phrases).</p>
                    </div>

                    <div class="boutons-formulaire">
                        <a href="<?= current_url() ?>" class="bouton bouton-annuler">Annuler</a>
                        <button type="submit" class="bouton bouton-principal">Créer</button>
                    </div>
                </form>
            </div>

        <?php else: ?>

            <!-- ÉCRAN DE BASE : rien de sélectionné -->
            <div class="carte" id="formulaire">
                <div class="vide">
                    <div class="vide-icone">+</div>
                    <h2>Sélectionne une question</h2>
                    <p>Choisis une question à modifier, ou crées-en une nouvelle.</p>
                    <a href="?creer=1" class="bouton bouton-principal">Nouvelle question</a>
                </div>
            </div>

        <?php endif; ?>

    </div>
</div>

</body>