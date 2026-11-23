/**
 * Page d'un quiz Vrai / Faux : une question à la fois.
 * Sans JavaScript, toutes les questions restent affichées l'une sous l'autre.
 * Vue : app/Views/quiz/quiz.php
 */
document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('.quiz-joueur');
    const form = document.getElementById('quiz-form');
    if (!page || !form) return;

    const questions = Array.from(form.querySelectorAll('.quiz-question'));
    const cases = Array.from(form.querySelectorAll('[data-quiz-aller]'));
    const compteur = form.querySelector('[data-quiz-repondues]');
    const alerte = form.querySelector('[data-quiz-alerte]');
    const barre = form.querySelector('[data-quiz-barre]');
    const corrige = page.classList.contains('quiz-joueur--corrige');
    if (questions.length === 0) return;

    page.classList.add('quiz-joueur--js');
    // La validation se fait ici : le navigateur ne peut pas signaler un champ masqué
    form.noValidate = true;

    let courante = 0;

    const estRepondue = (q) => q.querySelector('input[type="radio"]:checked') !== null;

    function afficher(index, focus = true) {
        courante = Math.max(0, Math.min(index, questions.length - 1));
        questions.forEach((q, i) => { q.hidden = i !== courante; });
        cases.forEach((c, i) => {
            c.classList.toggle('quiz-nav__case--courante', i === courante);
            if (i === courante) c.setAttribute('aria-current', 'step');
            else c.removeAttribute('aria-current');
        });
        history.replaceState(null, '', '#question-' + (courante + 1));
        if (focus) {
            questions[courante].setAttribute('tabindex', '-1');
            questions[courante].focus({ preventScroll: true });
            if (window.innerWidth < 900) {
                questions[courante].scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    }

    function majProgression() {
        if (corrige) return;
        let n = 0;
        questions.forEach((q, i) => {
            const ok = estRepondue(q);
            if (ok) n++;
            cases[i]?.classList.toggle('quiz-nav__case--repondue', ok);
        });
        if (compteur) compteur.textContent = n;
        if (barre) {
            barre.setAttribute('aria-valuenow', n);
            barre.style.setProperty('--quiz-progression', (n / questions.length) * 100 + '%');
        }
        if (alerte && n === questions.length) alerte.hidden = true;
    }

    cases.forEach((c, i) => {
        c.addEventListener('click', (e) => {
            e.preventDefault();
            afficher(i);
        });
    });

    form.addEventListener('click', (e) => {
        if (e.target.closest('[data-quiz-suivant]')) afficher(courante + 1);
        if (e.target.closest('[data-quiz-precedent]')) afficher(courante - 1);
    });

    form.addEventListener('change', (e) => {
        if (e.target.matches('input[type="radio"]')) majProgression();
    });

    form.addEventListener('submit', (e) => {
        const manquante = questions.findIndex((q) => !estRepondue(q));
        if (manquante === -1) return;
        e.preventDefault();
        const restantes = questions.filter((q) => !estRepondue(q)).length;
        if (alerte) {
            alerte.textContent = restantes === 1
                ? 'Il reste 1 question sans réponse.'
                : `Il reste ${restantes} questions sans réponse.`;
            alerte.hidden = false;
        }
        afficher(manquante);
    });

    // Flèches gauche / droite pour changer de question (hors champs de saisie)
    document.addEventListener('keydown', (e) => {
        if (e.altKey || e.ctrlKey || e.metaKey) return;
        if (e.target.matches('input[type="radio"]')) return;
        if (e.target.matches('input, textarea, select')) return;
        if (e.key === 'ArrowRight') afficher(courante + 1);
        if (e.key === 'ArrowLeft') afficher(courante - 1);
    });

    const ancre = /^#question-(\d+)$/.exec(location.hash);
    afficher(ancre ? Number(ancre[1]) - 1 : 0, false);
    majProgression();
});
