<section class="memo-page">

    <?= anchor(
            base_url('selectionmode/selectiontheme' ),
            '<span>←</span> Retour aux thèmes',
            [
                    'title' => "",
                    'class' => "memo-page__retour"
            ]
    ) ?>

    <div class="memo-page__entete">
        <div>
            <p class="intro__surtitre">Carte mémo</p>
            <h1 class="memo-page__titre"><?= esc($themes['titre']) ?></h1>
        </div>
        <p class="memo-page__aide">Cliquez sur la carte pour découvrir la réponse.</p>
    </div>

    <button type="button" class="memo" id="memo" aria-live="polite">
        <span class="memo__haut">
            <span class="memo__num" id="memo-num">01</span>
            <span class="memo__face" id="memo-face">Question</span>
        </span>
        <span class="memo__texte" id="memo-texte"></span>
        <span class="memo__bas">
            <span id="memo-action">Voir la réponse</span>
            <span>→</span>
        </span>
    </button>

</section>