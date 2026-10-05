<?php
use CodeIgniter\Config\Services;
$session = Services::session();
if(session()->has('utilisateur')):
    $utilisateur = session()->get('utilisateur');
endif;?>
<div class="form-compte">
    <h1>Mon compte</h1>
    <div class="input-wrapper">
        <div class="input-container">
            <input type='text'
                   name='pseudo'
                   value="<?= "Pseudo : ".$utilisateur->pseudo ?>"
                   title="Votre pseudo"
                   class='input-box-connexion'
                   disabled
            >
            <i class='fa-solid fa-user input-icon'></i>
        </div>
    </div>

    <div class="input-wrapper">
        <div class="input-container">
            <input type='text'
                   id='email'
                   value="<?= $utilisateur->email!=null?"Email : ".$utilisateur->email:'Email non rensiegné' ?>"
                   name='email'
                   title="Votre email"
                   class='input-box-connexion'
                   disabled
            >
            <i class="fa-solid fa-envelope input-icon"></i>
        </div>
    </div>

    <div class="input-wrapper">
        <div class="input-container">
            <input type='text'
                   id='role'
                   value="<?= "Rôle : ".$utilisateur->role ?>"
                   name='role'
                   title="Votre rôle"
                   class='input-box-connexion'
                   disabled>
            <?php if ($utilisateur->role === "contributeur"): ?>
                <i class="fa-solid fa-user-shield input-icon"></i>
            <?php endif;?>
            <?php if ($utilisateur->role === "admin"): ?>
                <i class="fa-solid fa-shield-halved input-icon"></i>
            <?php endif;?>
        </div>
    </div>
</div>