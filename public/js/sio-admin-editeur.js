/**
 * Éditeur Markdown du contenu des leçons (EasyMDE)
 * Exception à la règle 0 JS de l'administration.
 * Sans JavaScript, le textarea classique reste utilisable.
 */

// Attend que la page soit entièrement chargée avant de chercher le champ
document.addEventListener('DOMContentLoaded', () => {

    // Récupère le textarea du contenu (id défini dans formLecon.php)
    const champ = document.getElementById('champ-contenu');

    // Si le champ n'existe pas sur cette page, ou si la bibliothèque n'a pas chargé
    // (CDN indisponible), on s'arrête : le textarea classique reste affiché
    if (!champ || !window.EasyMDE) {
        return;
    }

    // L'éditeur masque le textarea : le navigateur ne peut plus afficher son message
    // "champ obligatoire" et bloquerait l'envoi sans explication.
    // La vérification reste faite par le contrôleur (règle "required").
    champ.removeAttribute('required');

    // Création de l'éditeur à la place du textarea
    new EasyMDE({
        element: champ,       // le textarea à remplacer
        forceSync: true,      // recopie le texte dans le textarea à chaque frappe, pour qu'il soit envoyé
        spellChecker: false,  // désactive le correcteur intégré, uniquement en anglais
        status: false,        // masque la barre du bas (nombre de mots, de lignes)
        minHeight: '400px',   // hauteur minimale de la zone de saisie

        // Boutons de la barre d'outils ('|' = séparateur)
        toolbar: [
            'bold', 'italic', 'heading', '|',          // gras, italique, titres
            'quote', 'unordered-list', 'ordered-list', '|', // citation, listes à puces et numérotées
            'link', 'code', 'table', '|',              // lien, bloc de code, tableau
            'preview', 'side-by-side', 'fullscreen',   // aperçu, aperçu côte à côte, plein écran
        ],
    });
});