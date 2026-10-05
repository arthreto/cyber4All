<?php if (session()->has('utilisateur')):
    $utilisateur = session()->get('utilisateur');
endif;
if (isset($utilisateur) && $utilisateur->role === "admin"):?>

    <div class="glossary-form">
        <div class="glossary-form__header">
            <h2 class="glossary-form__dialog-title">
                <?= ! empty($motCle) ? 'Modifier un mot du glossaire' : 'Ajouter un mot au glossaire' ?>
            </h2>
            <?php if (isset($nbSignalements)) : ?>

                <?php if ($nbSignalements >= 3): ?>
                    <span class="total-signalements total-signalements--critical">
                        <?= $nbSignalements ?> signalements
                    </span>

                <?php elseif ($nbSignalements < 3 && $nbSignalements > 0): ?>
                    <span class="total-signalements total-signalements--badge" data-count="<?= $nbSignalements ?>">
                        Signalements en attente :
                    </span>

                <?php else: ?>
                    <span class="total-signalements total-signalements--ok">
                        Aucun signalement
                    </span>
                <?php endif; ?>

            <?php endif; ?>

        </div>

        <?php $attributs = [
                'method' => 'POST',
                'class' => 'glossary-form__form',
                'enctype' => 'multipart/form-data'
        ];
        if (empty($actionAdmin)):
            echo form_open(base_url(! empty($motCle)
                    ? 'floribondant/mots/update/' . $motCle->id
                    : 'floribondant/mots/ajouter'), $attributs);

        else:
            echo form_open(base_url($actionAdmin . $motCle->id), $attributs);
        endif ?>

        <!-- Mot -->
        <div class="glossary-form__field" id="form_motcle">
            <?php
            echo form_label('Mot', 'nouvMot', ['class' => 'glossary-form__label glossary-form__label--required']);

            $attributs = [
                    'name' => 'nouvMot',
                    'id' => 'nouvMot',
                    'title' => "Mot à ajouter",
                    'class' => 'glossary-form__input',
                    'placeholder' => 'Mot à ajouter',
                    'required' => 'required',
                    'minlength' => 3,
            ];
            echo form_input($attributs, $motCle->mot ?? ''); ?>
            <p class="glossary-form__help">3 caractères minimum</p>
        </div>

        <!-- Catégories -->
        <div class="glossary-form__field" id="form_categories">
            <?= form_label('Catégories', '', ['class' => 'glossary-form__label']) ?>

            <div class="glossary-form__checkboxes">
                <?php if (! empty($categoriesOptions)):
                    foreach ($categoriesOptions as $id => $label): ?>
                        <?php
                        $checked = in_array($id, $categoriesSelect ?? []);
                        echo form_checkbox([
                                'name' => 'nouvCategories[]',
                                'id' => 'categorie_' . $id,
                                'value' => $id,
                                'checked' => $checked,
                                'class' => 'glossary-form__checkbox'
                        ]);
                        ?>
                        <?= form_label($label, 'categorie_' . $id, ['class' => 'glossary-form__checkbox-label']) ?><br>
                    <?php endforeach;
                endif; ?>
            </div>

            <p class="glossary-form__help">Cochez une ou plusieurs catégories</p>
        </div>

        <!-- Définition -->
        <div class="glossary-form__field" id="form_definition">
            <?php
            echo form_label('Définition', 'nouvDefinition', ['class' => 'glossary-form__label glossary-form__label--required']);

            $attributs = [
                    'name' => 'nouvDefinition',
                    'id' => 'nouvDefinition',
                    'rows' => 1,
                    'title' => "Définition du mot à ajouter",
                    'class' => 'glossary-form__input',
                    'placeholder' => 'Définition du mot à ajouter',
                    'minlength' => 25,
            ];
            echo form_textarea($attributs, $motCle->definition ?? ''); ?>
            <p class="glossary-form__help">25 caractères minimum</p>
        </div>
        <!-- Définition complète -->
        <div class="glossary-form__field" id="form_defintion_complete">
            <?php
            echo form_label('Définition complète', 'nouvDefinitionComplete', ['class' => 'glossary-form__label']);

            $attributs = [
                    'name' => 'nouvDefinitionComplete',
                    'id' => 'nouvDefinitionComplete',
                    'rows' => 8,
                    'title' => "Définition complète du mot à ajouter",
                    'class' => 'glossary-form__input',
                    'placeholder' => 'Définition complète du mot à ajouter',
                    'minlength' => 50,
            ];
            echo form_textarea($attributs, $motCle->definition_complete ?? ''); ?>
            <p class="glossary-form__help">50 caractères minimum, obligatoire si une image y est associée</p>
        </div>

        <!-- Image -->
        <div class="glossary-form__field" id="form_image">
            <?php
            echo form_label('Image complémentaire', 'nouvImage', ['class' => 'glossary-form__label']);

            $attributs = [
                    'name' => 'nouvImage',
                    'id' => 'nouvImage',
                    'title' => "Image complémentaire du mot à ajouter",
                    'class' => 'glossary-form__file',
                    'accept' => 'image/*'
            ];
            echo form_upload($attributs, '') ?>
            <p class="glossary-form__help">Formats acceptés : JPG, PNG, GIF (max 5 Mo)</p>
            <?php $cpt = 0;
            if (!empty($images)):?>
                <p class="glossary-form__help">Existante: </p>
                <?php foreach ($images as $image):
                    $cpt++ ?>
                    <p class="glossary-form__help"><?= $cpt . '&nbsp;-&nbsp;' . $image->image ?></p>
                <?php endforeach;
            endif; ?>

            <?php
            // Ajout d'un indicateur visuel conditionnel
            echo form_label('Légende', 'nouvLegend', ['class' => 'glossary-form__label']);

            $attributs = [
                    'name' => 'nouvLegend',
                    'id' => 'nouvLegend',
                    'title' => "Légende complémentaire du mot à ajouter",
                    'class' => 'glossary-form__input',
                    'minlength' => 3,
                    'maxlength' => 100,
            ];
            echo form_input($attributs); ?>
            <p class="glossary-form__help">3 à 50 caractères maximum</p>

            <?php
            // Ajout d'un indicateur visuel conditionnel
            echo form_label('Text alternatif', 'nouvAlt', ['class' => 'glossary-form__label']);

            $attributs = [
                    'name' => 'nouvAlt',
                    'id' => 'nouvAlt',
                    'title' => "Text alternatif du mot à ajouter",
                    'class' => 'glossary-form__input',
                    'minlength' => 3,
                    'maxlength' => 255,
            ];
            echo form_input($attributs); ?>
            <p class="glossary-form__help">3 à 255 caractères maximum</p>
        </div>

        <div class="glossary-form__field glossary-form__field--row" id="form_submit">
            <?php $attributesValider = [
                    'class' => 'glossary-form__submit glossary-form__submit--valider',
                    'id' => 'form_valider_motcle',
                    'name' => 'actionMot',
            ];
            $attributesRefuser = [
                    'class' => 'glossary-form__submit glossary-form__submit--refuser',
                    'id' => 'form_refuser_motcle',
                    'name' => 'actionMot',
            ];
            $attributes = [
                    'class' => 'glossary-form__submit',
                    'id' => 'form_modifier_motcle',
                    'name' => 'actionMot',
            ];
            if (empty($actionAdmin)):
                echo form_submit($attributes, !empty($motCle) ? 'Modifier' : 'Ajouter');

            else:
                echo form_hidden('idMotCle', !empty($motCle) ? (string)$motCle->id : '');

                echo form_submit($attributesValider, !empty($motCle) ? 'valider' : '');
                echo form_submit($attributesRefuser, !empty($motCle) ? 'refuser' : '');
            endif;
            echo form_close(); ?>
        </div>
    </div>

    <!-- Script JavaScript pour validation des champs requis dynamique -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const imageInput = document.getElementById('nouvImage');
            const legendInput = document.getElementById('nouvLegend');
            const defComplInput = document.getElementById('nouvDefinitionComplete');
            const legendLabel = legendInput.previousElementSibling;
            const defComplLabel = defComplInput.previousElementSibling;

            imageInput.addEventListener('change', function () {
                if (this.files && this.files.length > 0) {
                    // Image ajoutée : rendre la légende obligatoire
                    legendInput.setAttribute('required', 'required');
                    defComplInput.setAttribute('required', 'required');

                    // Ajouter la classe CSS pour le label requis
                    if (!legendLabel.classList.contains('glossary-form__label--required')) {
                        legendLabel.className = 'glossary-form__label glossary-form__label--required';
                        defComplLabel.className = 'glossary-form__label glossary-form__label--required';
                    }
                } else {
                    // Pas d'image : légende et définiton complète optionnelle
                    legendInput.removeAttribute('required');
                    defComplInput.removeAttribute('required');

                    legendLabel.classList.remove('glossary-form__label--required');
                    defComplLabel.classList.remove('glossary-form__label--required');
                }
            });
        });
    </script>

<?php endif; ?>