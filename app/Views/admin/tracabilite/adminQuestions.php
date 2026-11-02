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

        <a href="<?= base_url('floribondant/theme/questions/ajouter') ?>" class="glossary-form__trigger">Ajouter une question</a>
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
                <tr class="mots-table__body-row">
                    <td class="mots-table__cell">
                        <a href="<?= base_url('floribondant/theme/questions/update/12') ?>" class="mots-table__link">
                            Un JWT dont la date d'expiration (exp) est dépassée reste valide tant que sa signature est correcte.
                        </a>
                    </td>
                    <td class="mots-table__cell">
                        <ul class="sio-admin__quiz-liste">
                            <li>Reconnaître un token valide</li>
                            <li>JWT : structure et signature</li>
                        </ul>
                    </td>
                    <td class="mots-table__cell"><span class="sio-admin__badge">Faux</span></td>
                    <td class="mots-table__cell">Oui</td>
                    <td class="mots-table__cell mots-table__date">12/10/2026</td>
                    <td class="mots-table__cell">
                        <div class="sio-admin__ligne-actions">
                            <a href="<?= base_url('floribondant/theme/questions/update/12') ?>" class="mots-table__link">Modifier</a>
                            <?= form_open(base_url('floribondant/theme/questions/supprimer'), ['class' => 'mots-table__delete-form']) ?>
                                <?= form_hidden('id_question', '12') ?>
                                <button type="submit" class="sio-admin__btn-supprimer">Supprimer</button>
                            <?= form_close() ?>
                        </div>
                    </td>
                </tr>
                <tr class="mots-table__body-row">
                    <td class="mots-table__cell">
                        <a href="<?= base_url('floribondant/theme/questions/update/13') ?>" class="mots-table__link">
                            Après une déconnexion, le serveur doit pouvoir refuser un token même s'il n'est pas encore expiré.
                        </a>
                    </td>
                    <td class="mots-table__cell">
                        <ul class="sio-admin__quiz-liste">
                            <li>Reconnaître un token valide</li>
                        </ul>
                    </td>
                    <td class="mots-table__cell"><span class="sio-admin__badge">Vrai</span></td>
                    <td class="mots-table__cell">Oui</td>
                    <td class="mots-table__cell mots-table__date">08/10/2026</td>
                    <td class="mots-table__cell">
                        <div class="sio-admin__ligne-actions">
                            <a href="<?= base_url('floribondant/theme/questions/update/13') ?>" class="mots-table__link">Modifier</a>
                            <?= form_open(base_url('floribondant/theme/questions/supprimer'), ['class' => 'mots-table__delete-form']) ?>
                                <?= form_hidden('id_question', '13') ?>
                                <button type="submit" class="sio-admin__btn-supprimer">Supprimer</button>
                            <?= form_close() ?>
                        </div>
                    </td>
                </tr>
                <tr class="mots-table__body-row">
                    <td class="mots-table__cell">
                        <a href="<?= base_url('floribondant/theme/questions/update/14') ?>" class="mots-table__link">
                            Un token dont la signature est valide donne accès à toutes les ressources de l'application.
                        </a>
                    </td>
                    <td class="mots-table__cell">
                        <ul class="sio-admin__quiz-liste">
                            <li>Reconnaître un token valide</li>
                            <li>JWT : structure et signature</li>
                        </ul>
                    </td>
                    <td class="mots-table__cell"><span class="sio-admin__badge">Faux</span></td>
                    <td class="mots-table__cell">Oui</td>
                    <td class="mots-table__cell mots-table__date">08/10/2026</td>
                    <td class="mots-table__cell">
                        <div class="sio-admin__ligne-actions">
                            <a href="<?= base_url('floribondant/theme/questions/update/14') ?>" class="mots-table__link">Modifier</a>
                            <?= form_open(base_url('floribondant/theme/questions/supprimer'), ['class' => 'mots-table__delete-form']) ?>
                                <?= form_hidden('id_question', '14') ?>
                                <button type="submit" class="sio-admin__btn-supprimer">Supprimer</button>
                            <?= form_close() ?>
                        </div>
                    </td>
                </tr>
                <tr class="mots-table__body-row sio-admin__ligne--inactive">
                    <td class="mots-table__cell">
                        <a href="<?= base_url('floribondant/theme/questions/update/15') ?>" class="mots-table__link">
                            Une clé d'API peut être placée sans risque dans l'URL d'une requête.
                        </a>
                    </td>
                    <td class="mots-table__cell">
                        <ul class="sio-admin__quiz-liste">
                            <li>JWT : structure et signature</li>
                        </ul>
                    </td>
                    <td class="mots-table__cell"><span class="sio-admin__badge">Faux</span></td>
                    <td class="mots-table__cell">Non</td>
                    <td class="mots-table__cell mots-table__date">02/10/2026</td>
                    <td class="mots-table__cell">
                        <div class="sio-admin__ligne-actions">
                            <a href="<?= base_url('floribondant/theme/questions/update/15') ?>" class="mots-table__link">Modifier</a>
                            <?= form_open(base_url('floribondant/theme/questions/supprimer'), ['class' => 'mots-table__delete-form']) ?>
                                <?= form_hidden('id_question', '15') ?>
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

<script src="<?= base_url('js/sio-admin-theme.js') ?>" defer></script>