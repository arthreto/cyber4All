/**
 * Espace compréhension SIO - Administration
 * Seule exception à la règle 0 JavaScript :
 * renvoie le formulaire dès que le thème change (liste #champ-theme,
 * ou toute liste marquée data-envoi-auto), pour afficher les éléments de ce thème.
 * Le jeton CSRF part avec le formulaire, les champs déjà saisis sont conservés côté serveur.
 */
document.querySelectorAll('#champ-theme, select[data-envoi-auto]').forEach(function (liste) {
    liste.addEventListener('change', function () {
        const action = document.createElement('input');
        action.type = 'hidden';
        action.name = 'action';
        action.value = 'choisir_theme';
        this.form.appendChild(action);
        this.form.submit();
    });
});
