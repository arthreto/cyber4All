<?php
use CodeIgniter\Config\Services;
//récupère l'utilisateur
$session = Services::session();
if(session()->has('utilisateur')):
    $utilisateur = session()->get('utilisateur');
endif;?>
<?php $attributs = [
        'method'=>'POST',
        'class'=>'form-compte form-modifier-moncompte'
];
echo form_open(base_url('compte'),$attributs); ?>
    <h1>Modifier mes informations</h1>
    <div class="input-wrapper">
        <div class="input-container">
            <?php $attributs = [
                    'name'=>'nouvPseudo',
                    'id'=>'nouvPseudo',
                    'title'=>"Nouveau pseudo",
                    'class'=>'input-box-connexion',
                    'placeholder'=>'Nouveau pseudo',
                    'required'=>'required'
            ];
            echo form_input($attributs,esc($utilisateur->pseudo)??''); ?>
            <i class='fa-solid fa-user input-icon'></i>
        </div>
        <small class="champIndication">Entre 3 et 13 caractères
        </small>
    </div>
    <div class="input-wrapper">
        <div class="input-container">
            <?php $attributs = [
                    'name'=>'nouvEmail',
                    'id'=>'nouvEmail',
                    'title'=>"Nouvel email",
                    'class'=>'input-box-connexion',
                    'placeholder'=>'Nouvel email'
            ];
            echo form_input($attributs,esc($utilisateur->email)??''); ?>
            <i class="fa-solid fa-envelope input-icon"></i>
        </div>
        <label class="checkbox-container">
            <?php $attributs = [
                    'type'=>'checkbox',
                    'value'=>'supprimer',
                    'name'=>'suppEmail'
            ];
            echo form_input($attributs,'supprimer'); ?>
            <span class="checkbox-label checkbox-delete-label">Supprimer mon email</span>
        </label>
    </div>
    <?php $attributes = [
                'class' => 'btn-connexion',
                'name' => 'validDonnee'
        ];
        echo form_submit($attributes, 'Valider les modifications');
echo form_close(); ?>
