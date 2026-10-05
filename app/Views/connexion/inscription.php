<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (session()->has('utilisateur')) {
    $utilisateur = session()->get('utilisateur');
}
if (!isset($utilisateur)):?>
    <?php
    $dataForm = [
            'method' => 'POST',
            'class' => 'form-connexion'
    ];
    echo form_open(base_url('inscription'), $dataForm);
    ?>
    <h1>S'inscrire</h1>

    <?php if (isset($erreur)): ?>
        <h2><?= $erreur ?></h2>
    <?php endif; ?>

    <div class="input-wrapper">
        <?= form_label('Identifiant', 'identifiant',
                ['for' => 'identifiant',
                        'class' => 'form-label--required',]); ?>
        <div class="input-container">
            <?php
            $attributs_user = [
                    'type' => 'text',
                    'placeholder' => 'Votre identifiant',
                    'name' => 'identifiant',
                    'id' => 'identifiant',
                    'oninvalid' => "this.setCustomValidity('Saisir votre identifiant')",
                    'onchange' => "this.setCustomValidity('')",
                    'title' => "Saisir votre identifiant",
                    'required' => 'required'
            ]; ?>
            <?= form_input($attributs_user, $pseudo ?? ''); ?>
            <i class='fa-solid fa-user input-icon'></i>
        </div>
        <small class="champIndication">Entre 3 et 13 caractères</small>
    </div>

    <div class="input-wrapper">
        <?= form_label('Email', 'email',
                ['for' => 'identifiant']); ?>
        <div class="input-container">
            <?php
            $attributs_email = [
                    'type' => 'email',
                    'placeholder' => 'Votre email',
                    'name' => 'email',
                    'id' => 'email',
                    'oninvalid' => "this.setCustomValidity('Saisir votre email')",
                    'onchange' => "this.setCustomValidity('')",
                    'title' => "Saisir votre email"
            ]; ?>
            <?= form_input($attributs_email, $email ?? ''); ?>
            <i class='fa-solid fa-envelope input-icon'></i>
        </div>
        <small class="champIndication">Format email valide requis</small>
    </div>

    <div class="input-wrapper">
        <?= form_label('Mot de passe', 'mdp',
                ['for' => 'mdp',
                        'class' => 'form-label--required',]); ?>
        <div class="input-container">
            <?php
            // Expression régulière compacte :
            // (?=.*[a-z])   -> au moins une minuscule
            // (?=.*[A-Z])   -> au moins une majuscule
            // (?=.*[0-9])   -> au moins un chiffre
            // (?=.*[.*?!%@=$£\[\]+]) -> au moins un symbole autorisé
            // .{1,12}       -> entre 13 et 255 caractères
            $pattern = "^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[.!*?%@=\$£\[\]+]).{13,255}$";
            $attributs_mdp = [
                    'type' => 'password',
                    'id' => 'mdp',
                    'name' => 'mdp',
                    'placeholder' => 'Votre mot de passe',
                    'pattern' => $pattern,
                    'minlength' => '13',
                    'title' => 'Saisir le mot de passe',
                    'oninvalid' => "this.setCustomValidity('Le mot de passe doit contenir au minimum 13 caractères')",
                    'onchange' => "this.setCustomValidity('')",
                    'required' => 'required'
            ]; ?>
            <?= form_input($attributs_mdp); ?>
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

    <div class="input-wrapper">
        <?= form_label('Confirmation du mot de passe', 'mdp_validation',
                ['for' => 'mdp_validation',
                        'class' => 'form-label--required',]); ?>
        <div class="input-container">
            <?php
            $attributs_mdp_validation = [
                    'type' => 'password',
                    'id' => 'mdp_validation',
                    'name' => 'mdp_validation',
                    'placeholder' => 'Vérifier votre mot de passe',
                    'pattern' => $pattern,
                    'minlength' => '13',
                    'title' => 'Saisir le mot de passe',
                    'oninvalid' => "this.setCustomValidity('Le mot de passe doit contenir au minimum 13 caractères')",
                    'onchange' => "this.setCustomValidity('')",
                    'required' => 'required'
            ]; ?>
            <?= form_input($attributs_mdp_validation); ?>
            <i class="fa-solid fa-lock input-icon"></i>
            <?php $attributs_mdp_toggle2 = [
                    'type' => 'button',
                    'class' => 'input-icon input-icon--password-toggle',
                    'id' => 'togglePassword2',
                    'title' => 'Afficher/Masquer le mot de passe'
            ]; ?>
            <?= form_button($attributs_mdp_toggle2, "<i class='fa-solid fa-eye'></i>");
            ?>
        </div>
        <small class="champIndication">Doit contenir au moins:
            <br>• 1 minuscule • 1 Majuscule
            <br>• 1 chiffre • 1 symbole parmi .*?!%@=$£\[]+
            <br>• Ne doit pas contenir le pseudo
        </small>

        <?php
        // Affichage des erreurs de validation
        if (isset($validation) && $validation->getErrors()): ?>
            <div class="alert alert-danger">
                <?= $validation->listErrors() ?>
            </div>
        <?php endif; ?>
    </div>

    <?= anchor(base_url('connexion'), "J'ai déjà un compte") ?>
    <?php
    $dataButton = [
            'type' => 'submit',
            'class' => 'btn-submit-connexion',
            'name' => 'inscription',
            'value' => 'inscription'
    ];
    echo form_button($dataButton, "S'inscrire <i class='fa-solid fa-right-to-bracket'></i>"); ?>
    <?= form_close(); ?>
<?php endif; ?>

<script>
    // Fonction pour basculer l'affichage du mot de passe
    function togglePasswordVisibility(inputId, buttonId) {
        const passwordInput = document.getElementById(inputId);
        const toggleButton = document.getElementById(buttonId);
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

    // Événements pour les boutons de basculement
    document.getElementById('togglePassword').addEventListener('click', function () {
        togglePasswordVisibility('mdp', 'togglePassword');
    });

    document.getElementById('togglePassword2').addEventListener('click', function () {
        togglePasswordVisibility('mdp_validation', 'togglePassword2');
    });

    // Raccourci clavier pour basculer l'affichage (Ctrl + Alt + P)
    document.addEventListener('keydown', function (e) {
        if (e.ctrlKey && e.altKey && e.key === 'p') {
            e.preventDefault();
            togglePasswordVisibility('mdp', 'togglePassword');
        }
    });

    // Animation au focus des inputs
    const inputs = document.querySelectorAll('.input-box-connexion, input[type="text"], input[type="email"], input[type="password"]');
    inputs.forEach(input => {
        input.addEventListener('focus', function () {
            this.parentElement.style.transform = 'translateY(-2px)';
        });

        input.addEventListener('blur', function () {
            this.parentElement.style.transform = 'translateY(0)';
        });
    });

    // Vérification de la correspondance des mots de passe
    const mdp = document.getElementById('mdp');
    const mdpValidation = document.getElementById('mdp_validation');

    mdpValidation.addEventListener('input', function () {
        if (mdp.value !== mdpValidation.value) {
            mdpValidation.setCustomValidity('Les mots de passe ne correspondent pas');
        } else {
            mdpValidation.setCustomValidity('');
        }
    });

    mdp.addEventListener('input', function () {
        if (mdpValidation.value !== '' && mdp.value !== mdpValidation.value) {
            mdpValidation.setCustomValidity('Les mots de passe ne correspondent pas');
        } else {
            mdpValidation.setCustomValidity('');
        }
    });
</script>