<?php
/**
 * ADMINISTRATION - Espace compréhension SIO
 * Section Quiz (les onglets sont affichés par onglets.php, appelé dans le contrôleur)
 *
 * Vue en dur. Zones à brancher signalées par « À brancher ».
 * Données prévues : $quizList (liste, avec le nombre de questions), $themes (filtre), $idThemeFiltre.
 * Penser à esc() sur toutes les données venant de la base.
 */
?>


    <!-- Barre d'actions : filtre + ajout -->
    <div class="sio-admin__actions">
        <?= form_open(base_url('floribondant/theme/quiz'), ['method' => 'get', 'class' => 'sio-admin__filtre']) ?>
            <div class="sio-admin__champ">
                <label for="filtre-theme" class="glossary-form__label">Thème</label>
                <select id="filtre-theme" name="id_theme" class="sio-admin__select">
                    <!-- À brancher : foreach ($themes as $theme) -->
                    <option value="">Tous les thèmes</option>
                    <option value="1">Tokens</option>
                    <option value="2">Sessions</option>
                    <option value="3">Logs</option>
                </select>
            </div>
            <button type="submit" class="sio-admin__btn-secondaire">Filtrer</button>
        <?= form_close() ?>

        <?= anchor(base_url('floribondant/theme/quiz/ajouter'), 'Ajouter un quiz', ['class' => 'glossary-form__trigger']) ?>
    </div>

    <!-- Tableau des quiz (classes du tableau des mots) -->
    <div class="mots-table">
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
                <!-- À brancher : foreach ($quizList as $quiz) -->
                <tr class="mots-table__body-row">
                    <td class="mots-table__cell">
                        <?= anchor(base_url('floribondant/theme/quiz/update/3'), 'Reconnaître un token valide', ['class' => 'mots-table__link']) ?>
                    </td>
                    <td class="mots-table__cell"><span class="sio-admin__badge">Tokens</span></td>
                    <td class="mots-table__cell">
                        <?= anchor(base_url('floribondant/theme?id_quiz=3'), '3 questions', ['class' => 'mots-table__link']) ?>
                    </td>
                    <td class="mots-table__cell">Oui</td>
                    <td class="mots-table__cell">
                        <div class="sio-admin__ligne-actions">
                            <?= anchor(base_url('floribondant/theme/quiz/update/3'), 'Modifier', ['class' => 'mots-table__link']) ?>
                            <?= form_open(base_url('floribondant/theme/quiz/supprimer'), ['class' => 'mots-table__delete-form']) ?>
                                <?= form_hidden('id_quiz', '3') ?>
                                <button type="submit" class="sio-admin__btn-supprimer">Supprimer</button>
                            <?= form_close() ?>
                        </div>
                    </td>
                </tr>
                <tr class="mots-table__body-row">
                    <td class="mots-table__cell">
                        <?= anchor(base_url('floribondant/theme/quiz/update/4'), 'JWT : structure et signature', ['class' => 'mots-table__link']) ?>
                    </td>
                    <td class="mots-table__cell"><span class="sio-admin__badge">Tokens</span></td>
                    <td class="mots-table__cell">
                        <?= anchor(base_url('floribondant/theme?id_quiz=4'), '6 questions', ['class' => 'mots-table__link']) ?>
                    </td>
                    <td class="mots-table__cell">Oui</td>
                    <td class="mots-table__cell">
                        <div class="sio-admin__ligne-actions">
                            <?= anchor(base_url('floribondant/theme/quiz/update/4'), 'Modifier', ['class' => 'mots-table__link']) ?>
                            <?= form_open(base_url('floribondant/theme/quiz/supprimer'), ['class' => 'mots-table__delete-form']) ?>
                                <?= form_hidden('id_quiz', '4') ?>
                                <button type="submit" class="sio-admin__btn-supprimer">Supprimer</button>
                            <?= form_close() ?>
                        </div>
                    </td>
                </tr>
                <tr class="mots-table__body-row">
                    <td class="mots-table__cell">
                        <?= anchor(base_url('floribondant/theme/quiz/update/5'), 'Cookies et attributs de session', ['class' => 'mots-table__link']) ?>
                    </td>
                    <td class="mots-table__cell"><span class="sio-admin__badge">Sessions</span></td>
                    <td class="mots-table__cell">
                        <?= anchor(base_url('floribondant/theme?id_quiz=5'), '7 questions', ['class' => 'mots-table__link']) ?>
                    </td>
                    <td class="mots-table__cell">Oui</td>
                    <td class="mots-table__cell">
                        <div class="sio-admin__ligne-actions">
                            <?= anchor(base_url('floribondant/theme/quiz/update/5'), 'Modifier', ['class' => 'mots-table__link']) ?>
                            <?= form_open(base_url('floribondant/theme/quiz/supprimer'), ['class' => 'mots-table__delete-form']) ?>
                                <?= form_hidden('id_quiz', '5') ?>
                                <button type="submit" class="sio-admin__btn-supprimer">Supprimer</button>
                            <?= form_close() ?>
                        </div>
                    </td>
                </tr>
                <tr class="mots-table__body-row sio-admin__ligne--inactive">
                    <td class="mots-table__cell">
                        <?= anchor(base_url('floribondant/theme/quiz/update/7'), 'Lire les codes HTTP', ['class' => 'mots-table__link']) ?>
                    </td>
                    <td class="mots-table__cell"><span class="sio-admin__badge">Logs</span></td>
                    <td class="mots-table__cell">
                        <?= anchor(base_url('floribondant/theme?id_quiz=7'), '10 questions', ['class' => 'mots-table__link']) ?>
                    </td>
                    <td class="mots-table__cell">Non</td>
                    <td class="mots-table__cell">
                        <div class="sio-admin__ligne-actions">
                            <?= anchor(base_url('floribondant/theme/quiz/update/7'), 'Modifier', ['class' => 'mots-table__link']) ?>
                            <?= form_open(base_url('floribondant/theme/quiz/supprimer'), ['class' => 'mots-table__delete-form']) ?>
                                <?= form_hidden('id_quiz', '7') ?>
                                <button type="submit" class="sio-admin__btn-supprimer">Supprimer</button>
                            <?= form_close() ?>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
