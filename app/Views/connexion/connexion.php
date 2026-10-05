<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (session()->has('utilisateur')) {
    $utilisateur = session()->get('utilisateur');
}
if (!isset($utilisateur)):?>
    <?php if (!isset($utilisateur)): ?>
        <?php
        $dataForm = [
                'method' => 'POST',
                'class' => 'form-connexion'
        ];
        echo form_open('connexion', $dataForm);
        ?>
        <h1>Se connecter</h1>
        <?php if (isset($_GET['erreur']) && $_GET['erreur'] == 99): ?>
            <h2>Utilisateur non identifié, mot de passe ou pseudo incorrect</h2>
        <?php endif; ?>

        <div class="input-wrapper">
            <?= form_label('Identifiant', 'input_contact-name',
                            ['for' => 'identifiant',
                            'class' => 'form-label--required',]); ?>
            <div class="input-container">
                <?php
                $attributs_user = [
                        'type' => 'text',
                        'placeholder' => 'Votre identifiant',
                        'class' => 'input-box-connexion',
                        'name' => 'identifiant',
                        'oninvalid' => "this.setCustomValidity('Saisir votre identifiant')",
                        'onchange' => "this.setCustomValidity('')",
                        'title' => "Saisir votre identifiant",
                        'required' => 'required'
                ];
                echo form_input($attributs_user, $identifiant ?? ''); ?>
                <i class='fa-solid fa-user input-icon'></i>
            </div>
            <small class="champIndication">Entre 3 et 13 caractères</small>
        </div>

        <div class="input-wrapper">
            <?= form_label('Mot de passe', 'input_contact-name',
                            ['for' => 'mdp',
                            'class' => 'form-label--required',]); ?>
            <div class="input-container">
                <?php
                $attributs_mdp = [
                        'type' => 'password',
                        'id' => 'mdp',
                        'name' => 'mdp',
                        'class' => 'input-box-connexion',
                        'placeholder' => 'Votre mot de passe',
                        'pattern' => '^(?=.*[A-Z])(?=.*[0-9])(?=.*[\?!*@=%$£\[\]+\/\\\.&]).{13,255}$',
                        'min' => '13',
                        'title' => 'Saisir le mot de passe',
                        'oninvalid' => "this.setCustomValidity('Le mot de passe doit contenir au minimum 13 caractères')",
                        'onchange' => "this.setCustomValidity('')",
                        'required' => 'required'
                ];
                echo form_input($attributs_mdp); ?>
                <i class="fa-solid fa-lock input-icon"></i>
                <?php
                $attributs_mdp_toggle = [
                        'type' => 'button',
                        'class' => 'input-icon input-icon--password-toggle',
                        'id' => 'togglePassword',
                        'title' => 'Afficher/Masquer le mot de passe'
                ];
                echo form_button($attributs_mdp_toggle, "<i class='fa-solid fa-eye'></i>");
                ?>
            </div>
            <small class="champIndication">Minimum 13 caractères</small>
        </div>

        <?php //= anchor(base_url(),'Mot de passe oublié ?') ?>
        <?php
        $dataButton = [
                'type' => 'submit',
                'class' => 'btn-submit-connexion'
        ];
        echo form_button($dataButton, "Se connecter <i class='fa-solid fa-right-to-bracket'></i>"); ?>

        <p class="register-link-connexion">Vous n'avez pas de compte ?
            <?= anchor(base_url('inscription'), "<span class='important'>S'inscrire</span>") ?>
        </p>
        <?= form_close(); ?>
    <?php endif; ?>

    <?php if (isset($utilisateur)):
        $dataForm = ["method" => "POST",
                "class" => "form-connexion"];
        echo form_open('deconnexion', $dataForm); ?>
        <h1>Heureux de te voir : <?= $utilisateur->pseudo; ?></h1>
        <?php
        $dataButton = [
                'type' => 'submit',
                'class' => 'btn-submit-connexion',
                'name' => 'deconnexion',
                'value' => 'deconnexion'
        ];
        echo form_button($dataButton, "Se déconnecter <i class='fa-solid fa-right-to-bracket'></i>");
        echo form_close();
        ?>
    <?php endif; ?>
<?php endif; ?>

<script>
    // Fonction pour basculer l'affichage du mot de passe
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('mdp');
        const toggleButton = document.getElementById('togglePassword');
        const toggleIcon = toggleButton.querySelector('i');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.className = 'fa-solid fa-eye-slash';
            toggleButton.classList.add('visible');
            toggleButton.title = 'Masquer le mot de passe';
        } else {
            passwordInput.type = 'password';
            toggleIcon.className = 'fa-solid fa-eye';
            toggleButton.classList.remove('visible');
            toggleButton.title = 'Afficher le mot de passe';
        }
    }

    // Événement pour le bouton de basculement
    document.getElementById('togglePassword').addEventListener('click', togglePasswordVisibility);

    // Raccourci clavier pour basculer l'affichage (Ctrl + Alt + P)
    document.addEventListener('keydown', function (e) {
        if (e.ctrlKey && e.altKey && e.key === 'p') {
            e.preventDefault();
            togglePasswordVisibility();
        }
    });

    // Animation au focus des inputs
    const inputs = document.querySelectorAll('.input-box-connexion');
    inputs.forEach(input => {
        input.addEventListener('focus', function () {
            this.parentElement.style.transform = 'translateY(-2px)';
        });

        input.addEventListener('blur', function () {
            this.parentElement.style.transform = 'translateY(0)';
        });
    });
</script>
