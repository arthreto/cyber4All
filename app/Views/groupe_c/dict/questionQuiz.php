<?= link_tag(base_url('/css/css_grp/grp.css')) ?>

<body>

<main>
    <div class="presentation presentation--retour">
        <div class="retour-question">
            <?php
            $image = ['src' => '/images/quiz/dict.png',
                    'alt' => 'Bouton retour',
                    'class' => 'btn-image_retour'];
            ?>
            <!--    echo img($image);-->
            <?= anchor(base_url() . 'quiz', img($image));
            ?>


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
        <?= form_open('quiz/question', ['id' => 'form-question']) ?>

        <div class="cartes">
            <div class="carte-question carte--d">
                <?= form_checkbox(['name' => 'reponses[]', 'id' => 'rep1', 'value' => '1']) ?>
                <?= form_label(
                        '<span class="lettre">D</span><span><strong>Disponibilité</strong></span>',
                        'rep1',
                        ['class' => 'carte-label']
                ) ?>
            </div>

            <div class="carte-question carte--i">
                <?= form_checkbox(['name' => 'reponses[]', 'id' => 'rep2', 'value' => '2']) ?>
                <?= form_label(
                        '<span class="lettre">I</span><span><strong>Intégrité</strong></span>',
                        'rep2',
                        ['class' => 'carte-label']
                ) ?>
            </div>

            <div class="carte-question carte--c">
                <?= form_checkbox(['name' => 'reponses[]', 'id' => 'rep3', 'value' => '3']) ?>
                <?= form_label(
                        '<span class="lettre">C</span><span><strong>Confidentialité</strong></span>',
                        'rep3',
                        ['class' => 'carte-label']
                ) ?>
            </div>

            <div class="carte-question carte--t">
                <?= form_checkbox(['name' => 'reponses[]', 'id' => 'rep4', 'value' => '4']) ?>
                <?= form_label(
                        '<span class="lettre">T</span><span><strong>Traçabilité</strong></span>',
                        'rep4',
                        ['class' => 'carte-label']
                ) ?>
            </div>
        </div>

        <div class="ligne-bouton">
            <?= form_submit('valider', 'Valider →', ['id' => 'btn-valider', 'class' => 'btn-val', 'disabled' => 'disabled']) ?>
        </div>

        <?= form_close() ?>
    </div>
                                    <!-- script en java pour desactivez le bouton si il n y a pas au moin une case coche -->
    <script>
        const form   = document.getElementById('form-question');
        const bouton = document.getElementById('btn-valider');
        const cases  = form.querySelectorAll('input[type="checkbox"]');

        function majBouton() {
            const auMoinsUne = Array.from(cases).some(c => c.checked);
            bouton.disabled = !auMoinsUne;
        }

        cases.forEach(c => c.addEventListener('change', majBouton));
        majBouton();
    </script>
</main>
