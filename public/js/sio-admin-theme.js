/**
 * Espace Traçabilité - Administration
 * Le moins de JavaScript possible : la page fonctionne sans, ce script ne fait que du confort.
 *
 * 1. Envoi automatique : renvoie le formulaire dès qu'une liste change (#champ-theme,
 *    ou toute liste marquée data-envoi-auto). Le bouton dans <noscript> sert de secours.
 *
 * 2. Position de défilement : chaque action (filtre, ajout / retrait, modification,
 *    suppression…) recharge la page. Sans ce script, on revient tout en haut.
 *    On mémorise donc la position avant de partir et on la remet au retour sur la même page :
 *    - envoi d'un formulaire : on revient au même endroit après le rechargement ;
 *    - clic sur « Modifier » / « Ajouter » depuis une liste : on retrouve la liste au même
 *      endroit après l'enregistrement ou le clic sur « Retour » ;
 *    - clic sur un onglet : la section s'ouvre en haut.
 *    Le bouton « Enregistrer » n'est pas mémorisé : en cas d'erreur, la page s'ouvre en haut,
 *    sur le résumé des erreurs.
 */
(function () {
    'use strict';

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

    // ---------- Envoi automatique quand une liste change ----------
    document.querySelectorAll('#champ-theme, select[data-envoi-auto]').forEach(function (liste) {
        liste.addEventListener('change', function () {
            var action = document.createElement('input');
            action.type = 'hidden';
            action.name = 'action';
            action.value = 'choisir_theme';
            this.form.appendChild(action);

            // form.submit() ne déclenche pas l'événement submit : on mémorise ici
            memoriser(DUREE_ENVOI);
            this.form.submit();
        });
    });
})();
