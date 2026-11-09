<?php
/**
 * ADMINISTRATION - Espace compréhension SIO
 * Section Leçons (les onglets sont affichés par onglets.php, appelé dans le contrôleur)
 *
 */
?>


    <!-- Barre d'actions : filtre + ajout -->
    <div class="sio-admin__actions">
        <?= form_open(base_url('floribondant/theme/lecons'), ['method' => 'post', 'class' => 'sio-admin__filtre']) ?>
        <div class="sio-admin__champ">
            <label for="champ-theme" class="glossary-form__label">Quiz</label>
            <select id="champ-theme" name="id_theme" class="sio-admin__select">

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

        <?= anchor(base_url('floribondant/theme/lecons/ajouter'), 'Ajouter une leçon', ['class' => 'glossary-form__trigger']) ?>
    </div>

    <!-- Tableau des leçons (classes du tableau des mots) -->
    <div class="mots-table">
        <div class="mots-table__wrapper">
            <table class="mots-table__table">
                <thead class="mots-table__header">
                <tr class="mots-table__header-row">
                    <th scope="col" class="mots-table__header-cell">Titre</th>
                    <th scope="col" class="mots-table__header-cell">Thème</th>
                    <th scope="col" class="mots-table__header-cell">Auteur</th>
                    <th scope="col" class="mots-table__header-cell">Ajoutée le</th>
                    <th scope="col" class="mots-table__header-cell">Dernière modification</th>
                    <th scope="col" class="mots-table__header-cell">Actions</th>
                </tr>
                </thead>
                <tbody class="mots-table__body">
                <?php foreach ($leconList as $lecon): ?>

                <tr class="mots-table__body-row">
                    <td class="mots-table__cell">
                        <?= anchor(
                                'floribondant/theme/lecons/update/' . $lecon->id,
                                esc($lecon->titre),
                                ['class' => 'mots-table__link']
                        ) ?>
                    </td>
                    <td class="mots-table__cell"><span class="sio-admin__badge"><?= esc($lecon->nom_theme) ?></span></td>

                    <td class="mots-table__cell"><?= isset($lecon->user_modificateur) ? esc($lecon->user_modificateur->pseudo) : esc($lecon->user_auteur->pseudo) ?></td>
                    <td class="mots-table__cell mots-table__date"><?= esc($lecon->date_ajout) ?></td>
                    <td class="mots-table__cell mots-table__date"><?= esc($lecon->date_modif) ?></td>
                    <td class="mots-table__cell">
                        <div class="sio-admin__ligne-actions">
                            <?= anchor(base_url('floribondant/theme/lecons/update/1'), 'Modifier', ['class' => 'mots-table__link']) ?>
                            <?= form_open(base_url('floribondant/theme/lecons/supprimer'), ['class' => 'mots-table__delete-form']) ?>
                                <?= form_hidden('id_lecon', '1') ?>
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