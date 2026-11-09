<section class="intro">
    <p class="intro__surtitre">Apprendre et S'éxércer</p>

    <h1 class="intro__titre">Sécurisation d'un mot de passe<span class="intro__point">.</span></h1>

    <p class="intro__texte">
        Un mot de passe solide est votre première ligne de défense. Choisissez
        comment vous souhaitez apprendre à mieux protéger vos comptes.
    </p>

    <div class="selection">

        <?= anchor(
                base_url('choisirMode/entrainement'),
                '<div class="top">
                <span class="num">01</span>
                <span class="circle"></span>
            </div>
            <div>
                <h3>Entraînement</h3>
                <p>Apprenez à reconnaître les bonnes pratiques et à construire un mot de passe réellement robuste.</p>
            </div>
            <div class="footer">
                <span>Apprendre · Comprendre · Sécuriser</span>
            </div>',
                [
                        'title' => "",
                        'class' => "btn-selection"
                ]
        ) ?>

        <?= anchor(
                base_url('choisirMode/exercice'),
                '<div class="top">
                <span class="num">02</span>
                <span class="circle"></span>
            </div>
            <div>
                <h3>Exercice</h3>
                <p>Testez vos connaissances en évaluant et en renforçant différents exemples de mots de passe.</p>
            </div>
            <div class="footer">
                <span>Tester · Corriger · Valider</span>
            </div>',
                [
                        'title' => "",
                        'class' => "btn-selection"
                ]
        ) ?>

    </div>
</section>