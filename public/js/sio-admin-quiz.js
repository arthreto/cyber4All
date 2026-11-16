/**
 * Formulaire d'un quiz (formQuiz.php) : recherche dans les questions et récapitulatif des changements.
 * Sans JavaScript, le formulaire fonctionne quand même (le champ de recherche reste masqué).
 *
 * @author Arthur
 */
document.addEventListener('DOMContentLoaded', () => {
    const recherche = document.getElementById('champ-recherche');
    const resultat = document.querySelector('.sio-admin__recherche-resultat');
    const recap = document.querySelector('.sio-admin__recap');
    const groupes = document.querySelectorAll('.sio-admin__groupe');
    const cases = document.querySelectorAll('input[name="questions[]"]');

    if (!recherche || cases.length === 0) {
        return;
    }

    // Texte en minuscules et sans accents : "evenement" trouve "événement"
    const simplifier = (texte) => texte.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');

    // Questions présentes dans le quiz à l'ouverture de la page
    const auDepart = new Set([...document.querySelectorAll('.sio-admin__groupe--quiz input[name="questions[]"]')].map((c) => c.value));

    // ---------- Recherche ----------
    recherche.closest('.sio-admin__recherche').hidden = false;
    let groupesOuverts = null; // groupes ouverts avant la recherche, rouverts quand on l'efface

    recherche.addEventListener('input', () => {
        const mot = simplifier(recherche.value.trim());

        if (mot !== '' && groupesOuverts === null) {
            groupesOuverts = [...groupes].map((groupe) => groupe.open);
        }

        let trouvees = 0;
        groupes.forEach((groupe, i) => {
            let dansLeGroupe = 0;

            groupe.querySelectorAll('.sio-admin__choix--question').forEach((etiquette) => {
                const correspond = simplifier(etiquette.textContent).includes(mot);
                etiquette.hidden = !correspond;
                dansLeGroupe += correspond ? 1 : 0;
            });

            if (mot === '') {
                groupe.hidden = false;
                groupe.open = groupesOuverts ? groupesOuverts[i] : groupe.open;
            } else {
                // Groupe affiché et ouvert seulement s'il contient une question trouvée
                groupe.hidden = dansLeGroupe === 0;
                groupe.open = dansLeGroupe > 0;
            }
            trouvees += dansLeGroupe;
        });

        if (mot === '') {
            groupesOuverts = null;
            resultat.textContent = '';
        } else {
            resultat.textContent = trouvees === 0
                ? 'Aucune question ne contient « ' + recherche.value.trim() + ' ».'
                : trouvees + ' question' + (trouvees > 1 ? 's trouvées' : ' trouvée') + '.';
        }
    });

    // Entrée dans la recherche ne doit pas envoyer le formulaire
    recherche.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
        }
    });

    // ---------- Récapitulatif ----------
    const mettreAJourRecap = () => {
        const cochees = new Set([...cases].filter((c) => c.checked).map((c) => c.value));
        const ajoutees = [...cochees].filter((id) => !auDepart.has(id)).length;
        const retirees = [...auDepart].filter((id) => !cochees.has(id)).length;
        const pluriel = (n, mot) => n + ' ' + mot + (n > 1 ? 's' : '');

        if (ajoutees === 0 && retirees === 0) {
            recap.textContent = 'Aucun changement dans les questions';
        } else {
            const changements = [];
            if (ajoutees > 0) changements.push(pluriel(ajoutees, 'ajoutée'));
            if (retirees > 0) changements.push(pluriel(retirees, 'retirée'));
            recap.textContent = changements.join(', ') + ' : le quiz aura ' + pluriel(cochees.size, 'question') + '.';
        }
        recap.hidden = false;
    };

    cases.forEach((caseQuestion) => {
        caseQuestion.addEventListener('change', () => {
            // Une même question peut apparaître dans plusieurs thèmes : toutes ses cases suivent
            cases.forEach((autre) => {
                if (autre.value === caseQuestion.value) {
                    autre.checked = caseQuestion.checked;
                }
            });
            mettreAJourRecap();
        });
    });

    mettreAJourRecap();
});