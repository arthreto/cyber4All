<?= link_tag(base_url('/css/css_grp/grp.css')) ?>

<body>
<main>

    <div class="presentation">
        <div class="nom_train">
            <p>Entrainement DICT</p>
        </div>

        <div class="titre_quiz">
            <h1>Le bon terme.</h1>
            <h1>
                La bonne
                <span class="span_titre">Catégorie.</span>
            </h1>
        </div>


    </div>
    <div class="image_dict">
        <?php $image = ['src' => '/images/quiz/dict_retour.png',
                'alt' => 'schema dict',
                'class' => 'img-dict'
        ];
        echo img($image)
        ?>
    </div>


    <div class="panneau">
        <h2>Les 4 Piliers du DICT</h2>


        <div class="cartes">

            <label class="carte carte--d">
                <span class="lettre">D</span>
                <span>
            <strong>Disponibilité</strong><br>
            <small> Assurer que les données et les services sont accessibles et utilisables au moment voulu par les personnes autorisées.</small>
        </span>
                <span class="termes">

                <?= anchor('glossaire/concept/disponibilite', esc($nbD) . ' termes', ['class' => 'lien-termes']) ?>
                </span>
            </label>

            <label class="carte carte--i">
                <span class="lettre">I</span>
                <span>
            <strong>Intégrité</strong><br>
            <small> Garantir que les données sont exactes, complètes et qu'elles n'ont pas été modifiées ou détruites de façon non autorisée</small>
        </span>
                <span class="termes">
                    <?= anchor('glossaire/concept/integrite',
                            esc($nbI) . ' termes',
                            ['class' => 'lien-termes']) ?></span>
            </label>

            <label class="carte carte--c">
                <span class="lettre">C</span>
                <span>
            <strong>Confidentialité</strong><br>
            <small> Empêcher que les informations ne soient divulguées à des personnes, entités ou processus non autorisés . </small>
        </span>
                <span class="termes">
                    <?= anchor('glossaire/concept/confidentialite',
                            esc($nbC) . ' termes',
                            ['class' => 'lien-termes']) ?>
                </span>
            </label>

            <label class="carte carte--t">
                <span class="lettre">T</span>
                <span>
            <strong>Traçabilité</strong><br>
            <small> Enregistrer et journaliser les actions et événements pour pouvoir identifier qui a fait quoi et quand sur le système. </small>
        </span>
                <span class="termes">
                    <?= anchor('glossaire/concept/tracabilite',
                            esc($nbT) . ' termes',
                            ['class' => 'lien-termes']) ?>
                </span>
            </label>
        </div>
    </div>
    <div class="quiz">
        <h1><span class="span_titre">Quiz</span></h1>
    </div>
    <?php $verrouille = empty($utilisateur); ?>

    <?php
    $connecte = session()->has('utilisateur');
    $verrouille = !$connecte;

    // l'attribut n'existe que si l'utilisateur n'est pas connecté
    $bloque = $verrouille ? ['disabled' => 'disabled'] : [];
    ?>

    <?= form_open('quiz/start', ['class' => 'quiz-form']) ?>

    <div class="quiz-form-corps">
        <div class="intro-quiz">
            <p class="sous_titre">Teste ta compréhension des piliers de la sécurité.</p>
            <p class="sous_titre">Plusieurs réponses peuvent être justes.</p>
            <p class="sous_titre">Une réponse partielle ne donne pas de points</p>
        </div>

        <div class="ligne-duree">
            <div class="texte-duree">
                <h2>Durée du quiz</h2>
                <p>Combien de questions voulez-vous ?</p>
            </div>

            <div class="duree <?= $verrouille ? 'duree--verrouillee' : '' ?>">
                <?= form_radio(array_merge(['name' => 'duree', 'id' => 'duree5', 'value' => '5', 'checked' => true], $bloque)) ?>
                <?= form_label('5', 'duree5') ?>

                <?= form_radio(array_merge(['name' => 'duree', 'id' => 'duree10', 'value' => '10', 'checked' => false], $bloque)) ?>
                <?= form_label('10', 'duree10') ?>

                <?= form_radio(array_merge(['name' => 'duree', 'id' => 'duree15', 'value' => '15', 'checked' => false], $bloque)) ?>
                <?= form_label('15', 'duree15') ?>
            </div>
        </div>
    </div>

    <div class="ligne-bouton">
        <?php if ($verrouille): ?>
            <div class="msg-connexion">
                <span class="msg-connexion-icone">🔒</span>
                <p>Connectez-vous pour faire le quiz →</p>
                <?= anchor('connexion', 'Connexion', [
                        'title' => "Se connecter ou s'inscrire",
                        'class' => 'btn-connex-quiz',
                ]) ?>
            </div>
        <?php else: ?>
            <button type="submit" class="btn-lancer">Lancer le quiz →</button>
        <?php endif; ?>
    </div>
    <?= form_close() ?>
</main>