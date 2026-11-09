<?php
/**
 * ADMINISTRATION - Espace compréhension SIO
 * Section Leçons (les onglets sont affichés par onglets.php, appelé dans le contrôleur)
 *
 */
?>


    <!-- Barre d'actions : filtre + ajout -->
    <div class="sio-admin__actions">
        <?= form_open(base_url('floribondant/theme/lecons'), ['method' => 'get', 'class' => 'sio-admin__filtre']) ?>
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
                <!-- À brancher : foreach ($lecons as $lecon) -->


                <tr class="mots-table__body-row">
                    <td class="mots-table__cell">
                        <?= anchor(base_url('floribondant/theme/lecons/modifier/1'), 'Les tokens', ['class' => 'mots-table__link']) ?>
                    </td>
                    <td class="mots-table__cell"><span class="sio-admin__badge">Tokens</span></td>
                    <td class="mots-table__cell">admin_arthur</td>
                    <td class="mots-table__cell mots-table__date">01/10/2026</td>
                    <td class="mots-table__cell mots-table__date">12/10/2026</td>
                    <td class="mots-table__cell">
                        <div class="sio-admin__ligne-actions">
                            <?= anchor(base_url('floribondant/theme/lecons/modifier/1'), 'Modifier', ['class' => 'mots-table__link']) ?>
                            <?= form_open(base_url('floribondant/theme/lecons/supprimer'), ['class' => 'mots-table__delete-form']) ?>
                                <?= form_hidden('id_lecon', '1') ?>
                                <button type="submit" class="sio-admin__btn-supprimer">Supprimer</button>
                            <?= form_close() ?>
                        </div>
                    </td>
                </tr>
                <tr class="mots-table__body-row">
                    <td class="mots-table__cell">
                        <?= anchor(base_url('floribondant/theme/lecons/modifier/2'), "Clés d'API, bonnes pratiques", ['class' => 'mots-table__link']) ?>
                    </td>
                    <td class="mots-table__cell"><span class="sio-admin__badge">Tokens</span></td>
                    <td class="mots-table__cell">admin_arthur</td>
                    <td class="mots-table__cell mots-table__date">02/10/2026</td>
                    <td class="mots-table__cell mots-table__date">--</td>
                    <td class="mots-table__cell">
                        <div class="sio-admin__ligne-actions">
                            <?= anchor(base_url('floribondant/theme/lecons/modifier/2'), 'Modifier', ['class' => 'mots-table__link']) ?>
                            <?= form_open(base_url('floribondant/theme/lecons/supprimer'), ['class' => 'mots-table__delete-form']) ?>
                                <?= form_hidden('id_lecon', '2') ?>
                                <button type="submit" class="sio-admin__btn-supprimer">Supprimer</button>
                            <?= form_close() ?>
                        </div>
                    </td>
                </tr>
                <tr class="mots-table__body-row">
                    <td class="mots-table__cell">
                        <?= anchor(base_url('floribondant/theme/lecons/modifier/4'), "Fonctionnement d'une session", ['class' => 'mots-table__link']) ?>
                    </td>
                    <td class="mots-table__cell"><span class="sio-admin__badge">Sessions</span></td>
                    <td class="mots-table__cell">admin_benjamin</td>
                    <td class="mots-table__cell mots-table__date">03/10/2026</td>
                    <td class="mots-table__cell mots-table__date">09/10/2026</td>
                    <td class="mots-table__cell">
                        <div class="sio-admin__ligne-actions">
                            <?= anchor(base_url('floribondant/theme/lecons/modifier/4'), 'Modifier', ['class' => 'mots-table__link']) ?>
                            <?= form_open(base_url('floribondant/theme/lecons/supprimer'), ['class' => 'mots-table__delete-form']) ?>
                                <?= form_hidden('id_lecon', '4') ?>
                                <button type="submit" class="sio-admin__btn-supprimer">Supprimer</button>
                            <?= form_close() ?>
                        </div>
                    </td>
                </tr>
                <tr class="mots-table__body-row">
                    <td class="mots-table__cell">
                        <?= anchor(base_url('floribondant/theme/lecons/modifier/7'), 'Lire une ligne de log', ['class' => 'mots-table__link']) ?>
                    </td>
                    <td class="mots-table__cell"><span class="sio-admin__badge">Logs</span></td>
                    <td class="mots-table__cell">admin_arthur</td>
                    <td class="mots-table__cell mots-table__date">05/10/2026</td>
                    <td class="mots-table__cell mots-table__date">--</td>
                    <td class="mots-table__cell">
                        <div class="sio-admin__ligne-actions">
                            <?= anchor(base_url('floribondant/theme/lecons/modifier/7'), 'Modifier', ['class' => 'mots-table__link']) ?>
                            <?= form_open(base_url('floribondant/theme/lecons/supprimer'), ['class' => 'mots-table__delete-form']) ?>
                                <?= form_hidden('id_lecon', '7') ?>
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
