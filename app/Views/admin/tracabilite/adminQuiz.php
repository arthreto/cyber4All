<?php
/**
 * ADMINISTRATION - Espace compréhension SIO
 * Section Quiz (les onglets sont affichés par onglets.php, appelé dans le contrôleur)
 *
 * Données (AdminThemeController::quizSection) :
 * - $quizList    : quiz (id, titre, theme_nom, nb_questions, actif si la colonne existe)
 * - $themeList   : thèmes pour le filtre
 * - $themeSelect : thème filtré, -1 pour tous
 */
?>

    <!-- Barre d'actions : filtre + ajout -->
    <div class="sio-admin__actions">
        <?= form_open(base_url('floribondant/theme/quiz'), ['method' => 'post', 'class' => 'sio-admin__filtre']) ?>
            <div class="sio-admin__champ">
                <label for="champ-theme" class="glossary-form__label">Thème</label>
                <select id="champ-theme" name="id_theme" class="sio-admin__select">
                    <option value="-1" <?= $themeSelect == -1 ? 'selected' : '' ?>>Tous les thèmes</option>
                    <?php foreach ($themeList as $theme): ?>
                        <option value="<?= esc($theme->id) ?>" <?= $themeSelect == $theme->id ? 'selected' : '' ?>>
                            <?= esc($theme->nom) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <noscript>
                <button type="submit" name="action" value="choisir_theme" class="sio-admin__btn-secondaire">Filtrer</button>
            </noscript>
        <?= form_close() ?>

        <?= anchor(base_url('floribondant/theme/quiz/ajouter'), 'Ajouter un quiz', ['class' => 'glossary-form__trigger']) ?>
    </div>

    <!-- Tableau des quiz (classes du tableau des mots) -->
    <div class="mots-table">
        <?php if (empty($quizList)): ?>
            <div class="signalement-empty">
                <div class="signalement-empty__text">Aucun quiz pour ce thème.</div>
            </div>
        <?php else: ?>
        <div class="mots-table__wrapper">
            <table class="mots-table__table">
                <thead class="mots-table__header">
                <tr class="mots-table__header-row">
                    <th scope="col" class="mots-table__header-cell">Titre</th>
                    <th scope="col" class="mots-table__header-cell">Thème</th>
                    <th scope="col" class="mots-table__header-cell">Questions</th>
                    <th scope="col" class="mots-table__header-cell">Actif</th>
                    <th scope="col" class="mots-table__header-cell">Actions</th>
                </tr>
                </thead>
                <tbody class="mots-table__body">
                <?php foreach ($quizList as $quiz): ?>
                    <?php
                    $urlModifier = base_url('floribondant/theme/quiz/modifier/' . $quiz['id']);
                    $estActif = (int) ($quiz['actif'] ?? 1) === 1;
                    $nbQuestions = (int) $quiz['nb_questions'];
                    ?>
                    <tr class="mots-table__body-row<?= $estActif ? '' : ' sio-admin__ligne--inactive' ?>">
                        <td class="mots-table__cell">
                            <?= anchor($urlModifier, esc($quiz['titre']), ['class' => 'mots-table__link']) ?>
                        </td>
                        <td class="mots-table__cell"><span class="sio-admin__badge"><?= esc($quiz['theme_nom'] ?? '--') ?></span></td>
                        <td class="mots-table__cell"><?= $nbQuestions ?> question<?= $nbQuestions > 1 ? 's' : '' ?></td>
                        <td class="mots-table__cell"><?= $estActif ? 'Oui' : 'Non' ?></td>
                        <td class="mots-table__cell">
                            <div class="sio-admin__ligne-actions">
                                <?= anchor($urlModifier, 'Modifier', ['class' => 'mots-table__link']) ?>
                                <?= form_open(base_url('floribondant/theme/quiz/supprimer/' . $quiz['id']), ['class' => 'mots-table__delete-form']) ?>
                                    <?= form_hidden('id_quiz', $quiz['id']) ?>
                                    <button type="submit" class="sio-admin__btn-supprimer">Supprimer</button>
                                <?= form_close() ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?= script_tag(['src' => 'js/sio-admin-theme.js?v=' . filemtime(FCPATH . 'js/sio-admin-theme.js'), 'defer' => null]) ?>
