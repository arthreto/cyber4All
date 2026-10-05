<?php if(session()->has('utilisateur')):
    $utilisateur = session()->get('utilisateur');
endif;
if (isset($utilisateur) && $utilisateur->role === "admin"):
    $attributes = [
            'type' => 'button',
            'class' => 'link-form__trigger',
            'onclick' => "afficherModal('dialog_lien_utile')"
    ];
    echo form_button($attributes, '🔗 Ajouter une redirection'); ?>

    <div id='dialog_lien_utile' class="link-form__dialog" aria-modal="true">
        <div class="link-form__header">
            <h2 class="link-form__dialog-title">Ajouter un lien de redirection</h2>
            <?php $attributs = [
                    'type' => 'button',
                    'class' => 'link-form__close',
                    'onclick' => "fermerModal('dialog_lien_utile')",
                    'aria-label' => 'Fermer la fenêtre de détails'
            ];
            echo form_button($attributs, 'Fermer'); ?>
        </div>

        <?php $attributs = [
                'method' => 'POST',
                'class' => 'link-form__form'
        ];
        echo form_open(base_url('ajoutLien'), $attributs); ?>

        <!-- Titre -->
        <div class="link-form__field">
            <?php
            echo form_label('Titre', 'nouvTitre', ['class' => 'link-form__label link-form__label--required']);

            $attributs = [
                    'name' => 'nouvTitre',
                    'id' => 'nouvTitre',
                    'title' => "Titre du lien à ajouter",
                    'class' => 'link-form__input',
                    'placeholder' => 'Titre du lien à ajouter',
                    'required' => 'required'
            ];
            echo form_input($attributs, ''); ?>
        </div>

        <!-- Description -->
        <div class="link-form__field">
            <?php
            echo form_label('Description', 'nouvDescription', ['class' => 'link-form__label link-form__label--required']);

            $attributs = [
                    'name' => 'nouvDescription',
                    'id' => 'nouvDescription',
                    'class' => 'link-form__textarea',
                    'title' => "Description du lien à ajouter",
                    'placeholder' => 'Description du lien utile',
                    'required' => 'required'
            ];
            echo form_textarea($attributs, ''); ?>
            <p class="link-form__help">Décrivez brièvement le contenu ou l'utilité de ce lien</p>
        </div>

        <!-- Lien -->
        <div class="link-form__field">
            <?php
            echo form_label('URL', 'nouvLien', ['class' => 'link-form__label link-form__label--required']); ?>
            <div class="link-form__input-wrapper">
                <span class="link-form__input-icon">🔗</span>
                <?php
                $attributs = [
                        'name' => 'nouvLien',
                        'id' => 'nouvLien',
                        'type' => 'url',
                        'title' => "Lien du lien à ajouter",
                        'class' => 'link-form__input link-form__input--with-icon',
                        'placeholder' => 'https://exemple.com',
                        'required' => 'required'
                ];
                echo form_input($attributs, ''); ?>
            </div>
            <p class="link-form__help">L'URL doit commencer par http:// ou https://</p>
        </div>

        <?php $attributes = [
                'class' => 'link-form__submit'
        ];
        echo form_submit($attributes, '<span class="link-form__icon">➕</span> Ajouter le lien');
        echo form_close(); ?>
    </div>
<?php endif; ?>