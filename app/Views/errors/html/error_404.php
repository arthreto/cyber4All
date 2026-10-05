<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= lang('Errors.pageNotFound') ?> | Cyber4all</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            text-align: center;
            max-width: 600px;
        }

        h1 {
            font-size: 120px;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        h2 {
            font-size: 2em;
            color: #2d3436;
            margin-bottom: 15px;
        }

        p {
            font-size: 1.1em;
            color: #636e72;
            margin-bottom: 40px;
        }

        .btn {
            display: inline-block;
            padding: 15px 35px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 0 10px;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            h1 { font-size: 80px; }
            h2 { font-size: 1.5em; }
            .btn { display: block; margin: 10px auto; }
        }
    </style>
</head>
<body>
<div class="container">
    <h1>404</h1>
    <h2>Page introuvable</h2>
    <p>La page que vous recherchez n'existe pas.</p>
    <p>
        <?php if (ENVIRONMENT !== 'production') : ?>
            <?= nl2br(esc($message)) ?>
        <?php else : ?>
            <?= lang('Errors.sorryCannotFind') ?>
        <?php endif; ?>
    </p>
    <?php if (! empty($utilisateur = session()->get('utilisateur'))
        && $utilisateur->role === 'admin') :
        echo anchor(base_url('floribondant/mots'), "Retour à l'accueil", ['class' => 'btn']);
    else :
        echo anchor(base_url(), "Retour à l'accueil", ['class' => 'btn']);
        echo anchor(base_url('glossaire'), "Voir le glossaire", ['class' => 'btn']);
    endif; ?>
</div>
</body>
</html>
