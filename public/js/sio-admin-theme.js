/**
 * Espace Traçabilité - Administration
 * Le moins de JavaScript possible : la page fonctionne sans, ce script ne fait que du confort.
 *
 * 1. Position de défilement : chaque action (filtre, tri, modification, suppression, action sur
 *    un extrait…) recharge la page. Sans ce script, on revient tout en haut.
 *    On mémorise donc la position avant de partir et on la remet au retour sur la même page :
 *    - envoi d'un formulaire : on revient au même endroit après le rechargement ;
 *    - clic sur « Modifier » / « Ajouter » depuis une liste : on retrouve la liste au même
 *      endroit après l'enregistrement ou le clic sur « Retour » ;
 *    - clic sur un onglet : la section s'ouvre en haut.
 *    Le bouton « Enregistrer » n'est pas mémorisé : en cas d'erreur, la page s'ouvre en haut,
 *    sur le résumé des erreurs.
 *
 * 2. Thème (formulaires d'une question et d'un quiz) : n'affiche que les blocs du thème coché.
 *    Chaque bloc porte l'attribut data-theme="id du thème". Les champs des blocs masqués sont
 *    désactivés : un champ désactivé n'est pas envoyé avec le formulaire.
 *    Sans JavaScript, tous les blocs restent affichés (le contrôleur vérifie le thème).
 *
 * 3. Formulaire d'un quiz : recherche dans les questions, limite du nombre de questions
 *    (champ « Nombre de questions ») et récapitulatif dans la barre du bas.
 *    Sans JavaScript, la recherche est masquée et le contrôleur vérifie la limite.
 *
 * @author Arthur
 */
(function () {
    'use strict';

    // =====================================================================
    // 1. Position de défilement
    // =====================================================================

    var CLE = 'sio-admin-positions';
    var DUREE_ENVOI = 60 * 1000;        // le temps d'un aller-retour avec le serveur
    var DUREE_LIEN = 30 * 60 * 1000;    // le temps de modifier un élément puis de revenir

    // sessionStorage peut être indisponible (navigation privée, stockage bloqué) : on ignore
    function lire() {
        try {
            return JSON.parse(sessionStorage.getItem(CLE)) || {};
        } catch (e) {
            return {};
        }
    }

    function ecrire(positions) {
        try {
            sessionStorage.setItem(CLE, JSON.stringify(positions));
        } catch (e) {
            // rien : on perd seulement le confort
        }
    }

    function memoriser(duree) {
        var positions = lire();
        positions[location.pathname] = { y: window.scrollY, fin: Date.now() + duree };
        ecrire(positions);
    }

    function oublier(chemin) {
        var positions = lire();
        delete positions[chemin];
        ecrire(positions);
    }

    // ---------- Retour à la position mémorisée pour cette page ----------
    var positions = lire();
    var memo = positions[location.pathname];

    if (memo) {
        oublier(location.pathname);

        if (Date.now() < memo.fin) {
            // behavior 'instant' : saut direct, sans l'animation de scroll-behavior: smooth (style.css)
            var restaurer = function () {
                window.scrollTo({ top: memo.y, left: 0, behavior: 'instant' });
            };
            restaurer();
            // Les images chargées ensuite peuvent décaler la page : on recale une fois tout chargé
            window.addEventListener('load', restaurer, { once: true });
        }
    }

    // ---------- Mémorisation avant de quitter la page ----------
    document.addEventListener('submit', function (evenement) {
        // Envoi annulé (ex. confirm() refusé) : on ne garde rien
        if (evenement.defaultPrevented) {
            return;
        }

        var bouton = evenement.submitter;
        if (bouton && bouton.value === 'enregistrer') {
            return;
        }

        memoriser(DUREE_ENVOI);
    });

    document.addEventListener('click', function (evenement) {
        var lien = evenement.target.closest('a[href]');
        if (!lien) {
            return;
        }

        if (lien.classList.contains('sio-admin__onglet')) {
            // Nouvelle section : elle s'ouvre en haut
            oublier(new URL(lien.href, location.href).pathname);
        } else if (lien.closest('.mots-table') || lien.classList.contains('glossary-form__trigger')) {
            memoriser(DUREE_LIEN);
        }
    });

    // =====================================================================
    // 2. Thème : n'afficher que les blocs du thème coché
    // =====================================================================

    var radiosTheme = document.querySelectorAll('input[name="id_theme"]');
    var blocsTheme = document.querySelectorAll('[data-theme]');
    var consignesTheme = document.querySelectorAll('.sio-admin__choisir-theme');

    // Appelée après chaque changement de thème : remplacée par la partie 3 sur le formulaire d'un quiz
    var apresChangementTheme = function () {};

    function afficherTheme() {
        var radioCoche = document.querySelector('input[name="id_theme"]:checked');
        var idTheme = radioCoche ? radioCoche.value : null;

        blocsTheme.forEach(function (bloc) {
            var visible = bloc.dataset.theme === idTheme;
            bloc.hidden = !visible;

            bloc.querySelectorAll('input, textarea, select, button').forEach(function (champ) {
                champ.disabled = !visible;
            });
        });

        // « Choisissez d'abord le thème » : affiché seulement si aucun thème n'est coché
        consignesTheme.forEach(function (consigne) {
            consigne.hidden = idTheme !== null;
        });

        apresChangementTheme();
    }

    // =====================================================================
    // 3. Formulaire d'un quiz : recherche, limite et récapitulatif
    // =====================================================================

    var recherche = document.getElementById('champ-recherche');
    var resultat = document.querySelector('.sio-admin__recherche-resultat');
    var recap = document.querySelector('.sio-admin__recap');
    var champNombre = document.getElementById('champ-nb-question');

    // Cases des questions, relues à chaque fois : une question créée dans le modal
    // (sio-admin-modal-question.js) ajoute une case après le chargement de la page
    function casesQuestions() {
        return document.querySelectorAll('input[name="questions[]"]');
    }

    // Vrai si la case est dans le bloc d'un thème masqué (désactivée par la partie 2)
    function dansThemeMasque(element) {
        var bloc = element.closest('[data-theme]');
        return bloc !== null && bloc.hidden;
    }

    // Texte en minuscules et sans accents : "evenement" trouve "événement"
    function simplifier(texte) {
        return texte.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
    }

    function pluriel(nombre, mot) {
        return nombre + ' ' + mot + (nombre > 1 ? 's' : '');
    }

    if (recherche && recap && champNombre) {
        // Questions présentes dans le quiz à l'ouverture de la page
        var auDepart = [];
        document.querySelectorAll('.sio-admin__questions--quiz input[name="questions[]"]').forEach(function (caseQuestion) {
            auDepart.push(caseQuestion.value);
        });

        // ---------- Recherche ----------
        recherche.closest('.sio-admin__recherche').hidden = false;

        recherche.addEventListener('input', function () {
            var mot = simplifier(recherche.value.trim());
            var trouvees = 0;

            document.querySelectorAll('.sio-admin__choix--question').forEach(function (etiquette) {
                var correspond = simplifier(etiquette.textContent).indexOf(mot) !== -1;
                etiquette.hidden = !correspond;

                // On ne compte que les questions visibles (thème coché ou « Dans ce quiz »)
                if (correspond && !dansThemeMasque(etiquette)) {
                    trouvees++;
                }
            });

            if (mot === '') {
                resultat.textContent = '';
            } else if (trouvees === 0) {
                resultat.textContent = 'Aucune question ne contient « ' + recherche.value.trim() + ' ».';
            } else {
                resultat.textContent = trouvees + ' question' + (trouvees > 1 ? 's trouvées' : ' trouvée') + '.';
            }
        });

        // Entrée dans la recherche ne doit pas envoyer le formulaire
        recherche.addEventListener('keydown', function (evenement) {
            if (evenement.key === 'Enter') {
                evenement.preventDefault();
            }
        });

        // ---------- Limite et récapitulatif ----------
        var mettreAJourQuiz = function () {
            var max = parseInt(champNombre.value, 10) || 0;
            var cases = casesQuestions();

            // 1. On réactive les cases bloquées par la limite (sauf celles d'un thème masqué)
            cases.forEach(function (caseQuestion) {
                if (caseQuestion.dataset.limite === 'oui' && !dansThemeMasque(caseQuestion)) {
                    caseQuestion.disabled = false;
                }
                delete caseQuestion.dataset.limite;
            });

            // 2. Questions cochées qui seront envoyées (une case désactivée n'est pas envoyée)
            var cochees = [];
            cases.forEach(function (caseQuestion) {
                if (caseQuestion.checked && !caseQuestion.disabled && cochees.indexOf(caseQuestion.value) === -1) {
                    cochees.push(caseQuestion.value);
                }
            });

            // 3. Limite atteinte : on bloque les cases non cochées
            if (max > 0 && cochees.length >= max) {
                cases.forEach(function (caseQuestion) {
                    if (!caseQuestion.checked && !caseQuestion.disabled) {
                        caseQuestion.disabled = true;
                        caseQuestion.dataset.limite = 'oui';
                    }
                });
            }

            // 4. Récapitulatif dans la barre du bas
            var ajoutees = cochees.filter(function (id) { return auDepart.indexOf(id) === -1; }).length;
            var retirees = auDepart.filter(function (id) { return cochees.indexOf(id) === -1; }).length;

            var texte = cochees.length + (max > 0 ? ' / ' + max : '') + ' question' + (cochees.length > 1 ? 's' : '');

            if (max > 0 && cochees.length > max) {
                texte += ' : décochez-en ' + (cochees.length - max) + ' pour pouvoir enregistrer.';
            } else if (ajoutees === 0 && retirees === 0) {
                texte += ' : aucun changement.';
            } else {
                var changements = [];
                if (ajoutees > 0) {
                    changements.push(pluriel(ajoutees, 'ajoutée'));
                }
                if (retirees > 0) {
                    changements.push(pluriel(retirees, 'retirée'));
                }
                texte += ' : ' + changements.join(', ') + '.';
            }

            recap.textContent = texte;
            recap.hidden = false;
        };

        // Un seul écouteur sur la page : il voit aussi les cases ajoutées plus tard
        document.addEventListener('change', function (evenement) {
            if (evenement.target.matches('input[name="questions[]"]')) {
                mettreAJourQuiz();
            }
        });
        champNombre.addEventListener('input', mettreAJourQuiz);

        // Changement de thème : les cases actives ont changé, on recalcule
        apresChangementTheme = mettreAJourQuiz;
    }

    // ---------- Démarrage de la partie 2 (après la partie 3, qui en dépend) ----------
    if (radiosTheme.length > 0 && blocsTheme.length > 0) {
        radiosTheme.forEach(function (radio) {
            radio.addEventListener('change', afficherTheme);
        });
        afficherTheme();
    } else {
        apresChangementTheme();
    }
})();
