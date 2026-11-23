<?php
/**
 * Page d'inscription.
 */
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (session()->has('utilisateur')) {
    $utilisateur = session()->get('utilisateur');
}
$erreurs = $erreurs ?? [];

$erreurChamp = function (string $champ) use ($erreurs): string {
    if (! isset($erreurs[$champ])) {
        return '';
    }
    return '<small class="champErreur" id="erreur-' . $champ . '">' . esc($erreurs[$champ]) . '</small>';
};

if (!isset($utilisateur)):?>
    <?php
    $dataForm = [
            'method' => 'POST',
            'class' => 'form-connexion',
            'novalidate' => 'novalidate',
    ];
    echo form_open(base_url('inscription'), $dataForm);
    ?>
    <h1>S'inscrire</h1>

    <?php if (isset($erreur)): ?>
        <h2><?= esc($erreur) ?></h2>
    <?php endif; ?>

    <div class="input-wrapper">
        <?= form_label('Identifiant', 'identifiant', ['class' => 'form-label--required']); ?>
        <div class="input-container">
            <?= form_input([
                    'type' => 'text',
                    'placeholder' => 'Votre identifiant',
                    'name' => 'identifiant',
                    'id' => 'identifiant',
                    'minlength' => '3',
                    'maxlength' => '13',
                    'autocomplete' => 'username',
                    'required' => 'required',
                    'class' => isset($erreurs['identifiant']) ? 'input--erreur' : '',
            ], $pseudo ?? ''); ?>
            <i class='fa-solid fa-user input-icon'></i>
        </div>
        <?= $erreurChamp('identifiant') ?>
        <small class="champIndication">Entre 3 et 13 caractères</small>
    </div>

    <div class="input-wrapper">
        <?= form_label('Email', 'email'); ?>
        <div class="input-container">
            <?= form_input([
                    'type' => 'email',
                    'placeholder' => 'Votre email (facultatif)',
                    'name' => 'email',
                    'id' => 'email',
                    'autocomplete' => 'email',
                    'class' => isset($erreurs['email']) ? 'input--erreur' : '',
            ], $email ?? ''); ?>
            <i class='fa-solid fa-envelope input-icon'></i>
        </div>
        <?= $erreurChamp('email') ?>
    </div>

    <div class="input-wrapper">
        <?= form_label('Mot de passe', 'mdp', ['class' => 'form-label--required']); ?>
        <div class="input-container">
            <?= form_input([
                    'type' => 'password',
                    'id' => 'mdp',
                    'name' => 'mdp',
                    'placeholder' => 'Votre mot de passe',
                    'autocomplete' => 'new-password',
                    'required' => 'required',
                    'class' => isset($erreurs['mdp']) ? 'input--erreur' : '',
            ]); ?>
            <i class="fa-solid fa-lock input-icon"></i>
            <?= form_button([
                    'type' => 'button',
                    'class' => 'input-icon input-icon--password-toggle',
                    'id' => 'togglePassword',
                    'title' => 'Afficher/Masquer le mot de passe'
            ], "<i class='fa-solid fa-eye'></i>"); ?>
        </div>
        <?= $erreurChamp('mdp') ?>
        <small class="champIndication">Au moins 13 caractères, avec :
            <br>• 1 minuscule • 1 majuscule
            <br>• 1 chiffre • 1 symbole parmi .*?!%@=$£[]+
            <br>• sans l'identifiant
        </small>
    </div>

    <div class="input-wrapper">
        <?= form_label('Confirmation du mot de passe', 'mdp_validation', ['class' => 'form-label--required']); ?>
        <div class="input-container">
            <?= form_input([
                    'type' => 'password',
                    'id' => 'mdp_validation',
                    'name' => 'mdp_validation',
                    'placeholder' => 'Vérifier votre mot de passe',
                    'autocomplete' => 'new-password',
                    'required' => 'required',
                    'class' => isset($erreurs['mdp_validation']) ? 'input--erreur' : '',
            ]); ?>
            <i class="fa-solid fa-lock input-icon"></i>
            <?= form_button([
                    'type' => 'button',
                    'class' => 'input-icon input-icon--password-toggle',
                    'id' => 'togglePassword2',
                    'title' => 'Afficher/Masquer le mot de passe'
            ], "<i class='fa-solid fa-eye'></i>"); ?>
        </div>
        <?= $erreurChamp('mdp_validation') ?>
    </div>

    <?= anchor(base_url('connexion'), "J'ai déjà un compte") ?>
    <?= form_button([
            'type' => 'submit',
            'class' => 'btn-submit-connexion',
            'name' => 'inscription',
            'value' => 'inscription'
    ], "S'inscrire <i class='fa-solid fa-right-to-bracket'></i>"); ?>
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

    document.getElementById('togglePassword')?.addEventListener('click', function () {
        togglePasswordVisibility('mdp', 'togglePassword');
    });

    document.getElementById('togglePassword2')?.addEventListener('click', function () {
        togglePasswordVisibility('mdp_validation', 'togglePassword2');
    });

    // Raccourci clavier pour basculer l'affichage (Ctrl + Alt + P)
    document.addEventListener('keydown', function (e) {
        if (e.ctrlKey && e.altKey && e.key === 'p') {
            e.preventDefault();
            togglePasswordVisibility('mdp', 'togglePassword');
        }
    });

    (function () {
        const form = document.querySelector('.form-connexion');
        const identifiant = document.getElementById('identifiant');
        const email = document.getElementById('email');
        const mdp = document.getElementById('mdp');
        const mdpValidation = document.getElementById('mdp_validation');
        if (!form || !mdp) return;

        function erreurIdentifiant() {
            const v = identifiant.value.trim();
            if (v === '') return 'Saisissez un identifiant';
            if (v.length < 3) return "L'identifiant doit faire au moins 3 caractères";
            if (v.length > 13) return "L'identifiant doit faire au plus 13 caractères";
            return '';
        }

        function erreurEmail() {
            if (email.value.trim() === '') return '';
            return email.validity.typeMismatch ? 'Adresse email invalide' : '';
        }

        function erreurMdp() {
            const v = mdp.value;
            const pseudo = identifiant.value.trim();
            if (v === '') return 'Saisissez un mot de passe';
            if (v.length < 13) return 'Le mot de passe doit faire au moins 13 caractères (' + v.length + ' actuellement)';
            if (!/[a-z]/.test(v)) return 'Le mot de passe doit contenir au moins une minuscule';
            if (!/[A-Z]/.test(v)) return 'Le mot de passe doit contenir au moins une majuscule';
            if (!/[0-9]/.test(v)) return 'Le mot de passe doit contenir au moins un chiffre';
            if (!/[.*?!%@=$£\[\]+]/.test(v)) return 'Le mot de passe doit contenir au moins un symbole parmi . * ? ! % @ = $ £ [ ] +';
            if (pseudo !== '' && v.toLowerCase().includes(pseudo.toLowerCase())) return "Le mot de passe ne doit pas contenir l'identifiant";
            return '';
        }

        function erreurConfirmation() {
            if (mdpValidation.value === '') return 'Confirmez le mot de passe';
            return mdpValidation.value !== mdp.value ? 'Les mots de passe ne correspondent pas' : '';
        }

        const champs = [
            [identifiant, erreurIdentifiant],
            [email, erreurEmail],
            [mdp, erreurMdp],
            [mdpValidation, erreurConfirmation],
        ];

        function afficher(champ, message) {
            const wrapper = champ.closest('.input-wrapper');
            let zone = wrapper.querySelector('.champErreur');
            if (!zone) {
                zone = document.createElement('small');
                zone.className = 'champErreur';
                zone.id = 'erreur-' + champ.id;
                champ.closest('.input-container').after(zone);
            }
            zone.textContent = message;
            zone.hidden = message === '';
            champ.classList.toggle('input--erreur', message !== '');
            if (message) champ.setAttribute('aria-describedby', zone.id);
            else champ.removeAttribute('aria-describedby');
        }

        // Une fois qu'un champ a déjà montré une erreur, il se met à jour pendant la saisie
        champs.forEach(([champ, verifier]) => {
            champ.addEventListener('input', () => {
                if (champ.classList.contains('input--erreur')) afficher(champ, verifier());
                if (champ === mdp && mdpValidation.classList.contains('input--erreur')) {
                    afficher(mdpValidation, erreurConfirmation());
                }
            });
            champ.addEventListener('blur', () => {
                if (champ.value !== '') afficher(champ, verifier());
            });
        });

        form.addEventListener('submit', (e) => {
            let premier = null;
            champs.forEach(([champ, verifier]) => {
                const message = verifier();
                afficher(champ, message);
                if (message && !premier) premier = champ;
            });
            if (premier) {
                e.preventDefault();
                premier.focus();
            }
        });
    })();
</script>
