<?php
/**
 * Page de connexion.
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
    return '<small class="champErreur">' . esc($erreurs[$champ]) . '</small>';
};
?>
<?php if (!isset($utilisateur)): ?>
    <?php
    $dataForm = [
            'method' => 'POST',
            'class' => 'form-connexion'
    ];
    echo form_open('connexion', $dataForm);
    ?>
    <h1>Se connecter</h1>

    <?php if (isset($erreur)): ?>
        <h2><?= esc($erreur) ?></h2>
    <?php endif; ?>

    <div class="input-wrapper">
        <?= form_label('Identifiant', 'identifiant', ['class' => 'form-label--required']); ?>
        <div class="input-container">
            <?= form_input([
                    'type' => 'text',
                    'placeholder' => 'Votre identifiant',
                    'class' => 'input-box-connexion' . (isset($erreurs['identifiant']) ? ' input--erreur' : ''),
                    'name' => 'identifiant',
                    'id' => 'identifiant',
                    'autocomplete' => 'username',
                    'required' => 'required'
            ], $identifiant ?? ''); ?>
            <i class='fa-solid fa-user input-icon'></i>
        </div>
        <?= $erreurChamp('identifiant') ?>
    </div>

    <div class="input-wrapper">
        <?= form_label('Mot de passe', 'mdp', ['class' => 'form-label--required']); ?>
        <div class="input-container">
            <?= form_input([
                    'type' => 'password',
                    'id' => 'mdp',
                    'name' => 'mdp',
                    'class' => 'input-box-connexion' . (isset($erreurs['mdp']) ? ' input--erreur' : ''),
                    'placeholder' => 'Votre mot de passe',
                    'autocomplete' => 'current-password',
                    'required' => 'required'
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
    </div>

    <?= form_button([
            'type' => 'submit',
            'class' => 'btn-submit-connexion'
    ], "Se connecter <i class='fa-solid fa-right-to-bracket'></i>"); ?>

    <p class="register-link-connexion">Vous n'avez pas de compte ?
        <?= anchor(base_url('inscription'), "<span class='important'>S'inscrire</span>") ?>
    </p>
    <?= form_close(); ?>

<?php else:
    $dataForm = ["method" => "POST",
            "class" => "form-connexion"];
    echo form_open('deconnexion', $dataForm); ?>
    <h1>Heureux de te voir : <?= esc($utilisateur->pseudo); ?></h1>
    <?= form_button([
            'type' => 'submit',
            'class' => 'btn-submit-connexion',
            'name' => 'deconnexion',
            'value' => 'deconnexion'
    ], "Se déconnecter <i class='fa-solid fa-right-to-bracket'></i>"); ?>
    <?= form_close(); ?>
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

    document.getElementById('togglePassword')?.addEventListener('click', togglePasswordVisibility);

    // Raccourci clavier pour basculer l'affichage (Ctrl + Alt + P)
    document.addEventListener('keydown', function (e) {
        if (e.ctrlKey && e.altKey && e.key === 'p' && document.getElementById('mdp')) {
            e.preventDefault();
            togglePasswordVisibility();
        }
    });
</script>
