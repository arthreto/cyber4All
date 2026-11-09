<?php
/**
 * ADMINISTRATION - Espace compréhension SIO
 * Section Questions (les onglets sont affichés par onglets.php, appelé dans le contrôleur)
 *
 */
?>


    <!-- Barre d'actions : filtre + ajout -->
    <div class="sio-admin__actions">
        <?= form_open(base_url('floribondant/theme'), ['method' => 'post', 'class' => 'sio-admin__filtre']) ?>
            <div class="sio-admin__champ">
                <?= form_label('Quiz', 'champ-theme', ['class' => 'glossary-form__label']) ?>
                <?= form_dropdown(
                    'id_theme',
                    ['-1' => 'Tous les quiz'] + esc(array_column($themeList, 'nom', 'id')),
                    (string) $themeSelect,
                    ['id' => 'champ-theme', 'class' => 'sio-admin__select']
                ) ?>
            </div>
            <noscript>
                <?= form_button([
                    'type'    => 'submit',
                    'name'    => 'action',
                    'value'   => 'choisir_theme',
                    'content' => 'Afficher les quiz',
                    'class'   => 'sio-admin__btn-secondaire',
                ]) ?>
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
                <?php foreach ($questionList as $question): ?>
                    <tr class="mots-table__body-row">
                        <td class="mots-table__cell">
                            <div class="sio-admin__enonce">
                                <?php if (!empty($question['image'])): ?>
                                    <?= img([
                                        'src'     => esc('images/questions/' . $question['image'], 'attr'),
                                        'alt'     => 'Illustration de la question',
                                        'class'   => 'sio-admin__miniature',
                                        'loading' => 'lazy',
                                    ]) ?>
                                <?php endif; ?>
                                <?= anchor(base_url('floribondant/theme/questions/modifier/' . $question['id']), esc($question['libelle']), ['class' => 'mots-table__link']) ?>
                            </div>
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
                                <?= form_open(base_url('floribondant/theme/questions/supprimer/' . $question['id']), ['class' => 'mots-table__delete-form']) ?>
                                    <?= form_hidden('id_question', (string) $question['id']) ?>
                                    <?= form_button(['type' => 'submit', 'content' => 'Supprimer', 'class' => 'sio-admin__btn-supprimer']) ?>
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

<?= script_tag(['src' => 'js/sio-admin-theme.js?v=' . filemtime(FCPATH . 'js/sio-admin-theme.js'), 'defer' => null]) ?>