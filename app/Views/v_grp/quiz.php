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

        <div class="sous_titre">
            <p>Teste ta compréhension des pilliers de la sécurité</p>
            <p>Plusieurs réponses peuvent être justes</p>

        </div>
    </div>
    <div class="image_dict">
        <?php $image = ['src' => '/images/quiz/dict.png',
                'alt' => 'schema dict',
                'class' => 'image_dict'
        ];
        echo img($image)
        ?>
    </div>

    <div class="panneau">
        <h2>Les 4 Pilier du DICT</h2>


        <div class="cartes">

            <label class="carte carte--d">
                <span class="lettre">D</span>
                <span>
            <strong>Disponibilité</strong><br>
            <small> Assurer que les données et les services sont accessibles et utilisables au moment voulu par les personnes autorisées.</small>
        </span>
                <span class="termes"><?= esc($nbD) ?> termes</span>
            </label>

            <label class="carte carte--i">
                <span class="lettre">I</span>
                <span>
            <strong>Intégrité</strong><br>
            <small> Garantir que les données sont exactes, complètes et qu'elles n'ont pas été modifiées ou détruites de façon non autorisée</small>
        </span>
                <span class="termes"><?= esc($nbI) ?> termes</span>
            </label>

            <label class="carte carte--c">
                <span class="lettre">C</span>
                <span>
            <strong>Confidentialité</strong><br>
            <small> Empêcher que les informations ne soient divulguées à des personnes, entités ou processus non autorisés . </small>
        </span>
                <span class="termes"><?= esc($nbC) ?> termes</span>
            </label>

            <label class="carte carte--t">
                <span class="lettre">T</span>
                <span>
            <strong>Traçabilité</strong><br>
            <small> Enregistrer et journaliser les actions et événements pour pouvoir identifier qui a fait quoi et quand sur le système. </small>
        </span>
                <span class="termes"><?= esc($nbT) ?> termes</span>
            </label>
        </div>
        <div class="ligne-duree">
            <div>
                <h2>Durée du quiz</h2>
                <p>Combien de question voulez vous ?</p>
            </div>

            <form action="<?= base_url('quiz/start') ?>" method="post">
                <?= csrf_field() ?>

                <div class="duree">
                    <label><input type="radio" name="duree" value="5" checked><span>5</span></label>
                    <label><input type="radio" name="duree" value="10"><span>10</span></label>
                    <label><input type="radio" name="duree" value="tout"><span>Tout</span></label>
                </div>



        </div>

        <div class="ligne-bouton">
            <button type="submit" class="btn-lancer">Lancer le quiz →</button>
            </form>
        </div>
    </div>
</main>