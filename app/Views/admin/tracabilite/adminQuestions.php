<?php
/**
 * ADMINISTRATION - Espace compréhension SIO
 * Section Questions (les onglets sont affichés par onglets.php, appelé dans le contrôleur)
 *
 * Vue en dur. Zones à brancher signalées par « À brancher ».
 * Données prévues : $questions (liste), $quizList (filtre), $idQuizFiltre.
 * Penser à esc() sur toutes les données venant de la base.
 */
?>


    <!-- Barre d'actions : filtre + ajout -->
    <div class="sio-admin__actions">
        <?= form_open(base_url('floribondant/theme'), ['method' => 'post', 'class' => 'sio-admin__filtre']) ?>
            <div class="sio-admin__champ">
                <label for="champ-theme" class="glossary-form__label">Quiz</label>
                <select id="champ-theme" name="id_theme" class="sio-admin__select">
                    <!-- À brancher : foreach ($quizList as $quiz) -->

                    <option value="-1" <?= $themeSelect == -1 ? 'selected' : '' ?>>Tous les quiz</option>
                    <?php foreach ($themeList as $theme): ?>
                        <option value="<?= esc($theme->id) ?>" <?= $themeSelect == $theme->id ? 'selected' : '' ?>>
                            <?= esc($theme->nom) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <noscript>
                <button type="submit" name="action" value="choisir_theme" class="sio-admin__btn-secondaire">Afficher les quiz</button>
            </noscript>
        <?= form_close() ?>

        <?= anchor(base_url('floribondant/theme/questions/ajouter'), 'Ajouter une question', ['class' => 'glossary-form__trigger']) ?>
    </div>

    <!-- Tableau des questions (classes du tableau des mots) -->
    <div class="mots-table">
        <div class="mots-table__wrapper">
            <table class="mots-table__table">
                <thead class="mots-table__header">
                <tr class="mots-table__header-row">
                    <th scope="col" class="mots-table__header-cell">Énoncé</th>
                    <th scope="col" class="mots-table__header-cell">Quiz</th>
                    <th scope="col" class="mots-table__header-cell">Bonne réponse</th>
                    <th scope="col" class="mots-table__header-cell">Active</th>
                    <th scope="col" class="mots-table__header-cell">Dernière modification</th>
                    <th scope="col" class="mots-table__header-cell">Actions</th>
                </tr>
                </thead>
                <tbody class="mots-table__body">
                <!-- À brancher : foreach ($questions as $question) -->
                <?php foreach ($questionList as $question): ?>
                    <tr class="mots-table__body-row">
                        <td class="mots-table__cell">
                            <?= anchor(base_url('floribondant/theme/questions/modifier/' . $question['id']), esc($question['libelle']), ['class' => 'mots-table__link']) ?>
                        </td>
                        <td class="mots-table__cell">
                            <ul class="sio-admin__quiz-liste">

                                <?php foreach ($question['quiz'] as $quiz): ?>
                                    <li><?= esc($quiz['titre']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                        <td class="mots-table__cell"><span class="sio-admin__badge"><?= $question['bonne_reponse'] == 1 ? 'Vrai' : 'Faux' ?></span></td>
                        <td class="mots-table__cell"><?= $question['actif'] == 1 ? 'Oui' : 'Non' ?></td>
                        <td class="mots-table__cell mots-table__date"><?= isset($question['date_modif']) ? $question['date_modif'] : $question['date_ajout'] ?></td>
                        <td class="mots-table__cell">
                            <div class="sio-admin__ligne-actions">
                                <?= anchor(base_url('floribondant/theme/questions/modifier/' . $question['id']), 'Modifier', ['class' => 'mots-table__link']) ?>
                                <?= form_open(base_url('floribondant/theme/questions/supprimer'), ['class' => 'mots-table__delete-form']) ?>
                                    <?= form_hidden('id_question', '12') ?>
                                    <button type="submit" class="sio-admin__btn-supprimer">Supprimer</button>
                                <?= form_close() ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= script_tag(['src' => 'js/sio-admin-theme.js', 'defer' => null]) ?>