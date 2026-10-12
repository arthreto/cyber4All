<?php
/**
 * PAGE D'UNE LEÇON - Espace compréhension SIO
 *
 * Vue en dur (contenu d'exemple : leçon « Les tokens »).
 * Les zones à brancher sur la base sont signalées par « À brancher ».
 * Penser à esc() sur toutes les données venant de la base.
 *
 * Données prévues (à passer depuis ThemeController) :
 * - $lecon          : la leçon affichée (titre, contenu, dateModif, auteur)
 * - $theme          : le thème de la leçon
 * - $leconsTheme    : les leçons du même thème (liste de droite)
 * - $quizTheme      : les quiz actifs du thème (bloc « S'entraîner »)
 * - $leconSuivante  : la leçon suivante du thème (ou null)
 */
?>

<div class="lecon-page">

    <!-- Fil d'Ariane -->
    <nav class="lecon-ariane" aria-label="Fil d'Ariane">
        <ol class="lecon-ariane__liste">
            <li class="lecon-ariane__item">Comprendre les accès</li>
            <li class="lecon-ariane__item"><a href="#" class="lecon-ariane__lien">Tokens</a></li> <!-- À brancher : thème -->
            <li class="lecon-ariane__item" aria-current="page">Les tokens</li> <!-- À brancher : titre de la leçon -->
        </ol>
    </nav>

    <div class="lecon-page__grille">

        <!-- ===== Contenu de la leçon ===== -->
        <article class="lecon">

            <header class="lecon__entete">
                <p class="lecon__theme">Thème : Tokens</p> <!-- À brancher -->
                <h1 class="lecon__titre">Les tokens</h1> <!-- À brancher -->
                <p class="lecon__meta">
                    Rédigé par <span class="lecon__auteur">[pseudo de l'auteur]</span>
                    · Mis à jour le <time datetime="2026-10-12">12/10/2026</time>
                </p> <!-- À brancher : auteur + date_modif -->
            </header>

            <!-- À brancher : tout ce bloc vient de sio_lecon.contenu -->
            <div class="lecon__contenu">
                <p class="lecon__intro">
                    Un token est une preuve que le serveur remet à un client après une authentification.
                    Le client le renvoie à chaque requête, et le serveur décide à partir de lui si la demande est acceptée.
                </p>

                <section class="lecon__section">
                    <h2 class="lecon__sous-titre">Trois formes courantes</h2>
                    <div class="lecon__cartes">
                        <div class="lecon__carte">
                            <h3 class="lecon__carte-titre">JWT</h3>
                            <p>Trois parties encodées : en-tête, contenu, signature. Il transporte lui-même ses informations.</p>
                        </div>
                        <div class="lecon__carte">
                            <h3 class="lecon__carte-titre">Cookie de session</h3>
                            <p>Un identifiant aléatoire. Les données restent sur le serveur.</p>
                        </div>
                        <div class="lecon__carte">
                            <h3 class="lecon__carte-titre">Clé d'API</h3>
                            <p>Une chaîne longue durée qui identifie une application plutôt qu'une personne.</p>
                        </div>
                    </div>
                </section>

                <section class="lecon__section">
                    <h2 class="lecon__sous-titre">Comment savoir si un token est valide ?</h2>
                    <div class="lecon__tableau-conteneur">
                        <table class="lecon__tableau">
                            <thead>
                                <tr>
                                    <th scope="col">Problème</th>
                                    <th scope="col">Ce que le serveur vérifie</th>
                                    <th scope="col">Indice</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th scope="row">Expiré</th>
                                    <td>Le timestamp d'expiration est-il dépassé ?</td>
                                    <td><code>exp &lt; heure actuelle</code></td>
                                </tr>
                                <tr>
                                    <th scope="row">Mauvais droits</th>
                                    <td>Le scope couvre-t-il l'action demandée ?</td>
                                    <td><code>scope: "lecture"</code> sur un DELETE</td>
                                </tr>
                                <tr>
                                    <th scope="row">Falsifié</th>
                                    <td>La signature recalculée correspond-elle ?</td>
                                    <td><code>signature invalide</code></td>
                                </tr>
                                <tr>
                                    <th scope="row">Volé</th>
                                    <td>L'utilisation est-elle cohérente avec l'habitude ?</td>
                                    <td>IP suspecte ou inhabituelle</td>
                                </tr>
                                <tr>
                                    <th scope="row">Révoqué</th>
                                    <td>Le token figure-t-il sur la liste noire ?</td>
                                    <td>logout, blacklist</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="lecon__section">
                    <h2 class="lecon__sous-titre">Lire un JWT</h2>
<pre class="lecon__code">en-tête . contenu . signature

{"alg":"HS256"}
{"sub":42,"scope":"lecture","exp":1791720000}
HMAC(en-tête + contenu, clé secrète)</pre>
                </section>
            </div>

            <!-- S'entraîner : quiz du même thème -->
            <section class="lecon-entrainement" aria-labelledby="lecon-entrainement-titre">
                <h2 id="lecon-entrainement-titre" class="lecon-entrainement__titre">S'entraîner</h2>
                <ul class="lecon-entrainement__liste">
                    <!-- À brancher : foreach ($quizTheme as $quiz) -->
                    <li class="lecon-entrainement__item">
                        <span class="lecon-entrainement__quiz">
                            Reconnaître un token valide
                            <span class="lecon-entrainement__nb">3 questions</span>
                        </span>
                        <a href="#" class="btn-connexion lecon-entrainement__btn">Commencer</a>
                    </li>
                    <li class="lecon-entrainement__item">
                        <span class="lecon-entrainement__quiz">
                            JWT : structure et signature
                            <span class="lecon-entrainement__nb">6 questions</span>
                        </span>
                        <a href="#" class="btn-connexion lecon-entrainement__btn">Commencer</a>
                    </li>
                </ul>
            </section>

            <!-- Leçon suivante du thème -->
            <nav class="lecon-suite" aria-label="Leçon suivante">
                <a href="#" class="lecon-suite__lien">Leçon suivante : Clés d'API, bonnes pratiques →</a> <!-- À brancher -->
            </nav>
        </article>

        <!-- ===== Leçons du thème uniquement ===== -->
        <aside class="lecon-sommaire" aria-labelledby="lecon-sommaire-titre">
            <h2 id="lecon-sommaire-titre" class="lecon-sommaire__titre">Thème Tokens · 3 leçons</h2> <!-- À brancher -->
            <ol class="lecon-sommaire__liste">
                <!-- À brancher : foreach ($leconsTheme as $l), aria-current="page" sur la leçon affichée -->
                <li><a href="#" class="lecon-sommaire__lien lecon-sommaire__lien--actif" aria-current="page">Les tokens</a></li>
                <li><a href="#" class="lecon-sommaire__lien">Clés d'API, bonnes pratiques</a></li>
                <li><a href="#" class="lecon-sommaire__lien">Révocation et liste noire</a></li>
            </ol>
            <a href="#" class="lecon-sommaire__quiz">Quiz Vrai / Faux</a>
        </aside>

    </div>
</div>
