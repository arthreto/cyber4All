/**
 * Espace compréhension SIO - Administration
 * Seule exception à la règle 0 JavaScript :
 * renvoie le formulaire dès que le thème change, pour afficher les quiz de ce thème.
 * Le jeton CSRF part avec le formulaire, les champs déjà saisis sont conservés côté serveur.
 */
document.getElementById('champ-theme')?.addEventListener('change', function () {
    const action = document.createElement('input');
    action.type = 'hidden';
    action.name = 'action';
    action.value = 'choisir_theme';
    this.form.appendChild(action);
    this.form.submit();
});
