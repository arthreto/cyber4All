<?= link_tag(base_url('/css/css_grp/grp.css')) ?>

<body>
<main>
    <div class="presentation">
        <div class="retour-question">
            <?= anchor(
                    base_url('quiz'),
                    '← Quitter',
                    [
                            'title' => "Tout le lexique de la cybersécurité",
                            'class' => "btn-retour-question"
                    ]
            ) ?>

        </div>
    </div>
    <div class="progression-ligne">
        <div class="progression">
            <div class="prog-infos">
                <span class="prog-question">Question <?= esc($numero) ?> / <?= esc($total) ?></span>
                <span class="prog-score">Score : <?= esc($score) ?></span>
            </div>

            <progress class="prog-barre" value="<?= esc($numero) ?>" max="<?= esc($total) ?>"></progress>
        </div>
    </div>


    <div class="panneau">
        <div class="titre_quiz">
            <h1><?= esc($question['mot']) ?></h1>
        </div>
        <div class="sous_titre">
            <p><?= esc($question['question']) ?></p>
            <p>Plusieurs réponses peuvent être justes</p>
        </div>
        <form action="<?= base_url('quiz/question') ?>" method="post">
            <?= csrf_field() ?>

            <div class="cartes">
                <label class="carte-question carte--d">
                    <input type="checkbox" name="reponses[]" value="1">
                    <span class="lettre">D</span>
                    <span><strong>Disponibilité</strong><br>
                <small>Est-ce accessible ?</small></span>
                </label>

                <label class="carte-question carte--i">
                    <input type="checkbox" name="reponses[]" value="2">
                    <span class="lettre">I</span>
                    <span><strong>Intégrité</strong><br>
                <small>Est-ce intact ?</small></span>
                </label>

                <label class="carte-question carte--c">
                    <input type="checkbox" name="reponses[]" value="3">
                    <span class="lettre">C</span>
                    <span><strong>Confidentialité</strong><br>
                <small>Qui peut voir ?</small></span>
                </label>

                <label class="carte-question carte--t">
                    <input type="checkbox" name="reponses[]" value="4">
                    <span class="lettre">T</span>
                    <span><strong>Traçabilité</strong><br>
                <small>Qui a fait quoi ?</small></span>
                </label>
            </div>

            <div class="ligne-bouton">
                <button type="submit" class="btn-val">Valider →</button>
            </div>
        </form>
        </div>
</main>
