/**
 * Espace Traçabilité - Administration
 * Modal « Nouvelle question » du formulaire d'un quiz (formQuiz.php).
 *
 * Le formulaire de question est chargé dans un iframe (adresse avec ?modal=1) : il garde
 * tout son fonctionnement (extraits, erreurs…) et le quiz en cours de saisie n'est pas perdu.
 * Les deux pages se parlent avec postMessage(), seulement entre pages du même site.
 *
 * Ce fichier sert des deux côtés :
 * 1. Page du quiz : ouvre le modal (thème et quiz présélectionnés), le ferme,
 *    et ajoute la question enregistrée, cochée, dans « Dans ce quiz ».
 * 2. Page dans le modal (body.sio-admin-modal) : prévient la page du quiz
 *    (« Annuler », Échap, question enregistrée, nouveau jeton CSRF).
 *
 * Sans JavaScript, le lien « Créer une nouvelle question » ouvre un nouvel onglet.
 *
 * @author Arthur
 */
(function () {
    'use strict';

    var FERMER = 'sio-question:fermer';
    var ENREGISTREE = 'sio-question:enregistree';
    var CSRF = 'sio-question:csrf';

    // =====================================================================
    // 2. Page affichée dans le modal
    // =====================================================================

    if (document.body.classList.contains('sio-admin-modal')) {
        if (window.parent === window) {
            return; // page ouverte seule, hors du modal : rien à prévenir
        }

        var prevenir = function (message) {
            window.parent.postMessage(message, window.location.origin);
        };

        // Le jeton CSRF change à chaque envoi : la page du quiz doit utiliser le nouveau
        if (document.body.dataset.csrfNom) {
            prevenir({ type: CSRF, nom: document.body.dataset.csrfNom, valeur: document.body.dataset.csrfValeur });
        }

        // Question enregistrée : on la transmet, la page du quiz ferme le modal
        var enregistree = document.getElementById('question-enregistree');
        if (enregistree) {
            prevenir({ type: ENREGISTREE, question: JSON.parse(enregistree.dataset.question) });
        }

        // « Annuler » et Échap ferment le modal
        document.addEventListener('click', function (evenement) {
            if (evenement.target.closest('[data-modal-fermer]')) {
                prevenir({ type: FERMER });
            }
        });

        document.addEventListener('keydown', function (evenement) {
            if (evenement.key === 'Escape') {
                prevenir({ type: FERMER });
            }
        });

        return;
    }

    // =====================================================================
    // 1. Page du quiz
    // =====================================================================

    var lien = document.querySelector('[data-modal-question]');
    var dialog = document.getElementById('modal-question');

    if (!lien || !dialog || typeof dialog.showModal !== 'function') {
        return; // sans modal, le lien ouvre un nouvel onglet
    }

    var cadre = dialog.querySelector('iframe');
    var formulaireQuiz = document.querySelector('.sio-admin .glossary-form__form');

    // ---------- Ouverture ----------
    lien.addEventListener('click', function (evenement) {
        evenement.preventDefault();

        var adresse = new URL(lien.href);
        adresse.searchParams.set('modal', '1');

        // Thème coché dans le quiz, et quiz lui-même s'il est déjà enregistré
        var themeCoche = document.querySelector('input[name="id_theme"]:checked');
        if (themeCoche) {
            adresse.searchParams.set('id_theme', themeCoche.value);
        }
        if (lien.dataset.quiz) {
            adresse.searchParams.set('quiz', lien.dataset.quiz);
        }

        cadre.src = adresse.href;
        dialog.showModal();
    });

    // ---------- Fermeture : croix, Échap (dans la page du quiz) ou message de l'iframe ----------
    dialog.querySelector('[data-modal-fermer]').addEventListener('click', function () {
        dialog.close();
    });

    // Modal fermé : on vide l'iframe, la prochaine ouverture repart d'un formulaire vide
    dialog.addEventListener('close', function () {
        cadre.src = 'about:blank';
        lien.focus();
    });

    // ---------- Ajout de la question enregistrée dans « Dans ce quiz » ----------
    function ajouterQuestion(question) {
        var liste = document.querySelector('.sio-admin__questions--quiz');
        var id = String(question.id);

        // Déjà présente (modification) : on la coche simplement
        var existante = document.getElementById('question-' + id);
        if (existante) {
            existante.checked = true;
            existante.dispatchEvent(new Event('change', { bubbles: true }));
            return;
        }

        var themeCoche = document.querySelector('input[name="id_theme"]:checked');
        var autreTheme = themeCoche !== null && themeCoche.value !== String(question.id_theme);

        // Même structure que $caseQuestion dans formQuiz.php
        var etiquette = document.createElement('label');
        etiquette.htmlFor = 'question-' + id;
        etiquette.className = 'sio-admin__choix sio-admin__choix--carte sio-admin__choix--question'
            + (autreTheme ? ' sio-admin__choix--autre-theme' : '');

        var caseQuestion = document.createElement('input');
        caseQuestion.type = 'checkbox';
        caseQuestion.name = 'questions[]';
        caseQuestion.id = 'question-' + id;
        caseQuestion.value = id;
        caseQuestion.checked = true;

        var libelle = document.createElement('span');
        libelle.className = 'sio-admin__question-libelle';
        libelle.textContent = question.libelle; // textContent : le texte n'est pas interprété comme du HTML

        etiquette.append(caseQuestion, libelle);

        if (autreTheme) {
            etiquette.append(badge('Autre thème', 'faux'));
        }
        etiquette.append(question.bonne_reponse === '1' ? badge('Vrai', 'vrai') : badge('Faux', 'faux'));

        liste.prepend(etiquette);
        liste.hidden = false;
        liste.scrollTop = 0;

        var aucune = document.querySelector('.sio-admin__aucune-question-quiz');
        if (aucune) {
            aucune.hidden = true;
        }

        var nombre = liste.querySelectorAll('.sio-admin__choix--question').length;
        var texteNombre = document.querySelector('.sio-admin__nb-questions-quiz');
        if (texteNombre) {
            texteNombre.textContent = nombre + ' question' + (nombre > 1 ? 's' : '');
        }

        // Limite et récapitulatif de la barre du bas (sio-admin-theme.js)
        caseQuestion.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function badge(texte, variante) {
        var span = document.createElement('span');
        span.className = 'sio-admin__badge sio-admin__badge--' + variante;
        span.textContent = texte;
        return span;
    }

    // ---------- Messages de l'iframe ----------
    window.addEventListener('message', function (evenement) {
        // Seulement les messages de notre iframe, venant du même site
        if (evenement.origin !== window.location.origin || evenement.source !== cadre.contentWindow) {
            return;
        }

        var message = evenement.data || {};

        if (message.type === CSRF && formulaireQuiz) {
            // Nouveau jeton : sans lui, l'enregistrement du quiz serait refusé
            var champ = formulaireQuiz.querySelector('input[name="' + message.nom + '"]');
            if (champ) {
                champ.value = message.valeur;
            }
        } else if (message.type === ENREGISTREE) {
            ajouterQuestion(message.question);
            dialog.close();
        } else if (message.type === FERMER) {
            dialog.close();
        }
    });
})();
