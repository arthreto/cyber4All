<?php
/**
 * ADMINISTRATION - Espace compréhension SIO
 * Formulaire d'ajout / de modification d'une question
 *
 * Vue en dur (exemple : modification de la question 12).
 * Zones à brancher signalées par « À brancher ».
 * Données prévues : $question, $themes, $idThemeChoisi, $quizDuTheme, $quizRattaches, $errors.
 *
 * Choix des quiz : on choisit d'abord le thème, puis on ajoute les quiz de ce thème un par un.
 * Seule exception à la règle 0 JS : public/js/sio-admin-theme.js renvoie le formulaire
 * quand le thème change. Le bouton dans <noscript> sert de secours.
 */
?>
<div class="sio-admin sio-admin--formulaire">

    <?= anchor(base_url('floribondant/theme'), '← Retour aux questions', ['class' => 'sio-admin__retour']) ?>

    <div class="glossary-form">
        <div class="glossary-form__header">
            <h2 class="glossary-form__dialog-title"><?= ($form->modifier != 0) ? 'Modifier la question' : 'Ajouter une question' ?></h2>
        </div>

        <!-- Exemple d'erreur de validation (à afficher seulement si $errors n'est pas vide) -->

        <?php if (!empty($errors)): ?>
            <div class="sio-admin__erreur-globale" role="alert">
                <strong>Le formulaire contient <?= count($errors) ?> erreur(s).</strong>

                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li style="margin-left: 5%;"><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>


        <?php
        // En modification, l'id de la question est dans l'URL : c'est ce qui indique le mode au contrôleur
        $actionFormulaire = ! empty($form->modifier)
            ? 'floribondant/theme/questions/modifier/' . (int) $form->modifier
            : 'floribondant/theme/questions/ajouter';
        ?>
        <?= form_open(base_url($actionFormulaire), ['class' => 'glossary-form__form']) ?>

        <!-- Quiz de la question -->
        <fieldset class="sio-admin__bloc-quiz">
            <legend class="glossary-form__label">Quiz de la question</legend>

            <div class="sio-admin__ligne">
                <div class="sio-admin__champ">
                    <label for="champ-theme" class="glossary-form__label">1. Thème</label>
                    <select id="champ-theme" name="id_theme" class="sio-admin__select">
                        <!-- À brancher : foreach ($themes as $theme) -->
                        <?php foreach ($themeList as $theme): ?>
                            <option value="<?= esc($theme->id) ?>"
                                    <?= $form->id_theme == $theme->id ? 'selected' : '' ?>>
                                <?= esc($theme->nom) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <noscript>
                    <button type="submit" name="action" value="choisir_theme" class="sio-admin__btn-secondaire">Afficher les quiz</button>
                </noscript>
            </div>

            <div class="sio-admin__ligne">
                <div class="sio-admin__champ">
                    <label for="champ-quiz" class="glossary-form__label">2. Quiz du thème Tokens</label>
                    <select id="champ-quiz" name="id_quiz_ajout" class="sio-admin__select">
                        <?php foreach ($quizList as $quiz):
                            echo '<option value="', $quiz->id, '">', $quiz->titre, '</option>';
                        endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="action" value="ajouter_quiz"
                        formnovalidate class="sio-admin__btn-principal">
                    Ajouter ce quiz
                </button>
            </div>

            <div class="sio-admin__rattaches">
                <p class="glossary-form__label">Question rattachée à <?=count($quizAdded) ?> quiz</p>
                <ul class="sio-admin__rattaches-liste">
                    <!-- À brancher : foreach ($quizRattaches as $quiz) + champ caché quiz[] -->
                    <?php foreach ($quizAdded as $id => $quiz):
                        echo '
                            <li class="sio-admin__rattache">
                                <span>', esc($quiz), '</span>
                                <button type="submit" name="retirer_quiz" value="', esc($id), '" formnovalidate class="sio-admin__btn-supprimer">Retirer</button>
                            </li>
                        ';
                    endforeach; ?>
                </ul>
            </div>
        </fieldset>

        <!-- Énoncé -->
        <div class="glossary-form__field">
            <label for="champ-libelle" class="glossary-form__label glossary-form__label--required">Libelle</label>
            <textarea id="champ-libelle" name="libelle" rows="3" class="glossary-form__input <?= isset($errors['libelle']) ? 'sio-admin__input--erreur' : '' ?>" required><?= esc($form->libelle) ?></textarea>

            <?php if (isset($errors['libelle'])): ?>
            <p id="erreur-libelle" class="sio-admin__erreur">
                <?= esc($errors['libelle']) ?>
            </p>
            <?php endif; ?>
        </div>

        <!-- Image -->
        <div class="glossary-form__field">
            <label for="champ-image" class="glossary-form__label">Chemin de l'image (facultatif)</label>
            <input type="text" id="champ-image" name="image" value="<?= esc($form->image ?? '') ?>"
                   placeholder="images/questions/jwt-expire.png" class="glossary-form__input <?= isset($errors['image']) ? 'sio-admin__input--erreur' : '' ?>" aria-describedby="aide-image">
            <p id="aide-image" class="glossary-form__help">Nom de l'image avec extension dans images/questions/</p>

            <?php if (isset($errors['image'])): ?>
                <p id="erreur-image" class="sio-admin__erreur">
                    <?= esc($errors['image']) ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- Détail (extrait affiché au-dessus de l'énoncé) -->
        <?php
        $modeDetail = $form->detail_mode ?? 'aucun';
        $modesDetail = [
            'aucun'    => 'Aucun',
            'existant' => 'Déjà existant',
            'nouveau'  => 'En créer un',
        ];

        // Extraits regroupés par type pour la liste déroulante, et extrait actuellement choisi
        $extraitsParType = [];
        $extraitChoisi = null;
        foreach ($extraitList as $extrait) {
            $extraitsParType[$extrait['type']][] = $extrait;
            if ((string) $extrait['id'] === (string) $form->id_extrait) {
                $extraitChoisi = $extrait;
            }
        }
        ?>
        <fieldset class="sio-admin__bloc-quiz">
            <legend class="glossary-form__label">Détail (facultatif)</legend>

            <div class="sio-admin__ligne">
                <div class="sio-admin__champ">
                    <label for="champ-detail-mode" class="glossary-form__label">Extrait</label>
                    <select id="champ-detail-mode" name="detail_mode" class="sio-admin__select" data-envoi-auto>
                        <?php foreach ($modesDetail as $valeur => $libelle): ?>
                            <option value="<?= esc($valeur, 'attr') ?>" <?= $modeDetail === $valeur ? 'selected' : '' ?>><?= esc($libelle) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <noscript>
                    <button type="submit" name="action" value="choisir_theme" formnovalidate class="sio-admin__btn-secondaire">Appliquer</button>
                </noscript>
            </div>

            <?php if (isset($errors['detail_mode'])): ?>
                <p class="sio-admin__erreur"><?= esc($errors['detail_mode']) ?></p>
            <?php endif; ?>

            <?php if (!empty($infoDetail)): ?>
                <p class="sio-admin__info-detail" role="status"><?= esc($infoDetail) ?></p>
            <?php endif; ?>

            <?php if (isset($errors['extrait'])): ?>
                <p class="sio-admin__erreur"><?= esc($errors['extrait']) ?></p>
            <?php endif; ?>

            <?php if ($modeDetail === 'existant'): ?>
                <!-- Choix parmi les extraits existants -->
                <div class="sio-admin__ligne">
                    <div class="sio-admin__champ">
                        <label for="champ-extrait" class="glossary-form__label glossary-form__label--required">Extrait existant</label>
                        <select id="champ-extrait" name="id_extrait" class="sio-admin__select <?= isset($errors['id_extrait']) ? 'sio-admin__input--erreur' : '' ?>" data-envoi-auto>
                            <option value="">— Choisir un extrait —</option>
                            <?php foreach ($extraitsParType as $type => $extraits): ?>
                                <optgroup label="<?= esc($typesExtrait[$type] ?? $type, 'attr') ?>">
                                    <?php foreach ($extraits as $extrait): ?>
                                        <option value="<?= esc($extrait['id'], 'attr') ?>" <?= $extraitChoisi !== null && $extraitChoisi['id'] == $extrait['id'] ? 'selected' : '' ?>>
                                            <?= esc($extrait['titre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <noscript>
                        <button type="submit" name="action" value="choisir_theme" formnovalidate class="sio-admin__btn-secondaire">Voir l'extrait</button>
                    </noscript>
                </div>

                <?php if (isset($errors['id_extrait'])): ?>
                    <p class="sio-admin__erreur"><?= esc($errors['id_extrait']) ?></p>
                <?php endif; ?>

                <?php if ($extraitChoisi !== null): ?>
                    <?php $actionExtrait = $form->extrait_action ?? null; ?>

                    <?php if ($actionExtrait === 'modifier'): ?>
                        <!-- Modification de l'extrait choisi -->
                        <div class="sio-admin__edition-extrait">
                            <div class="glossary-form__field">
                                <label for="champ-edit-titre" class="glossary-form__label glossary-form__label--required">Titre</label>
                                <input type="text" id="champ-edit-titre" name="edit_titre" maxlength="100"
                                       value="<?= esc($form->edit_titre ?? '', 'attr') ?>"
                                       class="glossary-form__input <?= isset($errors['edit_titre']) ? 'sio-admin__input--erreur' : '' ?>">
                                <?php if (isset($errors['edit_titre'])): ?>
                                    <p class="sio-admin__erreur"><?= esc($errors['edit_titre']) ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="glossary-form__field">
                                <label for="champ-edit-type" class="glossary-form__label glossary-form__label--required">Type</label>
                                <select id="champ-edit-type" name="edit_type" class="sio-admin__select <?= isset($errors['edit_type']) ? 'sio-admin__input--erreur' : '' ?>">
                                    <?php foreach ($typesExtrait as $valeur => $libelle): ?>
                                        <option value="<?= esc($valeur, 'attr') ?>" <?= ($form->edit_type ?? '') === $valeur ? 'selected' : '' ?>><?= esc($libelle) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['edit_type'])): ?>
                                    <p class="sio-admin__erreur"><?= esc($errors['edit_type']) ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="glossary-form__field">
                                <label for="champ-edit-contenu" class="glossary-form__label glossary-form__label--required">Contenu</label>
                                <textarea id="champ-edit-contenu" name="edit_contenu" rows="6"
                                          class="glossary-form__input sio-admin__code <?= isset($errors['edit_contenu']) ? 'sio-admin__input--erreur' : '' ?>"><?= esc($form->edit_contenu ?? '') ?></textarea>
                                <?php if (isset($errors['edit_contenu'])): ?>
                                    <p class="sio-admin__erreur"><?= esc($errors['edit_contenu']) ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="sio-admin__ligne-actions">
                                <button type="submit" name="action" value="enregistrer_extrait" formnovalidate class="sio-admin__btn-principal">Enregistrer l'extrait</button>
                                <button type="submit" name="action" value="annuler_extrait" formnovalidate class="sio-admin__btn-secondaire">Annuler</button>
                            </div>
                        </div>

                    <?php else: ?>
                        <!-- Aperçu de l'extrait choisi -->
                        <pre class="sio-admin__apercu-extrait sio-admin__code"><?= esc($extraitChoisi['contenu']) ?></pre>

                        <div class="sio-admin__ligne-actions">
                            <button type="submit" name="action" value="modifier_extrait" formnovalidate class="sio-admin__btn-secondaire">Modifier l'extrait</button>
                            <button type="submit" name="action" value="supprimer_extrait" formnovalidate class="sio-admin__btn-supprimer">Supprimer l'extrait</button>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

            <?php elseif ($modeDetail === 'nouveau'): ?>
                <!-- Création d'un nouvel extrait -->
                <div class="glossary-form__field">
                    <label for="champ-extrait-titre" class="glossary-form__label glossary-form__label--required">Titre</label>
                    <input type="text" id="champ-extrait-titre" name="extrait_titre" maxlength="100"
                           value="<?= esc($form->extrait_titre ?? '', 'attr') ?>"
                           placeholder="JWT signé en HS256"
                           class="glossary-form__input <?= isset($errors['extrait_titre']) ? 'sio-admin__input--erreur' : '' ?>">
                    <?php if (isset($errors['extrait_titre'])): ?>
                        <p class="sio-admin__erreur"><?= esc($errors['extrait_titre']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="glossary-form__field">
                    <label for="champ-extrait-type" class="glossary-form__label glossary-form__label--required">Type</label>
                    <select id="champ-extrait-type" name="extrait_type" class="sio-admin__select <?= isset($errors['extrait_type']) ? 'sio-admin__input--erreur' : '' ?>">
                        <option value="">— Choisir un type —</option>
                        <?php foreach ($typesExtrait as $valeur => $libelle): ?>
                            <option value="<?= esc($valeur, 'attr') ?>" <?= ($form->extrait_type ?? '') === $valeur ? 'selected' : '' ?>><?= esc($libelle) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['extrait_type'])): ?>
                        <p class="sio-admin__erreur"><?= esc($errors['extrait_type']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="glossary-form__field">
                    <label for="champ-extrait-contenu" class="glossary-form__label glossary-form__label--required">Contenu</label>
                    <textarea id="champ-extrait-contenu" name="extrait_contenu" rows="6"
                              class="glossary-form__input sio-admin__code <?= isset($errors['extrait_contenu']) ? 'sio-admin__input--erreur' : '' ?>"><?= esc($form->extrait_contenu ?? '') ?></textarea>
                    <?php if (isset($errors['extrait_contenu'])): ?>
                        <p class="sio-admin__erreur"><?= esc($errors['extrait_contenu']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </fieldset>

        <!-- Bonne réponse -->
        <fieldset class="glossary-form__field sio-admin__fieldset">
            <legend class="glossary-form__label glossary-form__label--required">Bonne réponse</legend>
            <div class="sio-admin__radios">
                <label for="champ-vrai" class="sio-admin__choix">
                    <input type="radio" id="champ-vrai" name="bonne_reponse" value="1" <?= $form->bonne_reponse == 1 ? 'checked' : '' ?>>
                    Vrai
                </label>
                <label for="champ-faux" class="sio-admin__choix">
                    <input type="radio" id="champ-faux" name="bonne_reponse" value="0" <?= $form->bonne_reponse == 0 ? 'checked' : '' ?>>
                    Faux
                </label>
            </div>
        </fieldset>

        <!-- Explication (exemple en erreur) -->

        <div class="glossary-form__field">
            <label for="champ-explication" class="glossary-form__label glossary-form__label--required">
                Explication
            </label>

            <textarea
                    id="champ-explication"
                    name="explication"
                    rows="4"
                    class="glossary-form__input <?= isset($errors['explication']) ? 'sio-admin__input--erreur' : '' ?>"
                        <?= isset($errors['explication']) ? 'aria-invalid="true" aria-describedby="erreur-explication"' : '' ?>><?= esc($form->explication ?? '') ?>
            </textarea>

            <?php if (isset($errors['explication'])): ?>
                <p id="erreur-explication" class="sio-admin__erreur">
                <?= esc($errors['explication']) ?>
            </p>
            <?php endif; ?>
        </div>

        <!-- Active -->
        <div class="glossary-form__field">
            <label for="champ-actif" class="sio-admin__choix">
                <input type="hidden" name="actif" value="0">
                <input type="checkbox" id="champ-actif" name="actif" value="1" <?= $form->actif == 1 ? 'checked' : '' ?>> Question active (visible dans les quiz)
            </label>
        </div>

        <div class="glossary-form__field glossary-form__field--row">
            <button type="submit" name="action" value="enregistrer" class="glossary-form__submit">Enregistrer</button>
        </div>

        <?= form_close() ?>
    </div>
</div>

<!-- Exception autorisée à la règle 0 JS : uniquement pour le sélecteur de thème -->
<?= script_tag(['src' => 'js/sio-admin-theme.js?v=' . filemtime(FCPATH . 'js/sio-admin-theme.js'), 'defer' => null]) ?>
