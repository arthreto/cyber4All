/**
 * Éditeur Markdown du contenu d'une leçon (EasyMDE)
 *
 * @author Raphael Bierlaire
 */

document.addEventListener('DOMContentLoaded', () => {
    const champ = document.getElementById('champ-contenu');

    // Pas de champ sur la page, ou bibliothèque non chargée : on garde la zone de texte classique
    if (!champ || !window.EasyMDE) {
        return;
    }

    // L'éditeur masque la zone de texte : le navigateur ne pourrait plus afficher
    // son message "champ obligatoire". La vérification est faite par le contrôleur.
    champ.removeAttribute('required');

    new EasyMDE({
        element: champ,                 // la zone de texte à remplacer
        forceSync: true,                // recopie le texte dans la zone de texte, pour qu'il soit envoyé
        spellChecker: false,            // le correcteur intégré est uniquement en anglais
        status: false,                  // pas de compteur de mots en bas
        minHeight: '300px',             // pas de hauteur maximale : l'éditeur s'agrandit avec le texte,
                                        // c'est la page qui défile (pas de barre de défilement dans l'éditeur)
        placeholder: 'Écrivez la leçon ici…',
        autoDownloadFontAwesome: false, // boutons en texte : les icônes sont inutiles
        sideBySideFullscreen: false,    // l'aperçu en direct reste dans la page

        // Texte inséré par le bouton "Tableau"
        insertTexts: {
            table: ['', '\n\n| Colonne 1 | Colonne 2 |\n| --- | --- |\n| Texte | Texte |\n\n'],
        },

        // Boutons de la barre d'outils ('|' = séparateur)
        toolbar: [
            { name: 'bold', action: EasyMDE.toggleBold, text: 'Gras' },
            { name: 'italic', action: EasyMDE.toggleItalic, text: 'Italique' },
            { name: 'heading-2', action: EasyMDE.toggleHeading2, text: 'Titre' },
            { name: 'heading-3', action: EasyMDE.toggleHeading3, text: 'Sous-titre' },
            '|',
            { name: 'unordered-list', action: EasyMDE.toggleUnorderedList, text: 'Liste' },
            { name: 'ordered-list', action: EasyMDE.toggleOrderedList, text: 'Liste numérotée' },
            '|',
            { name: 'link', action: EasyMDE.drawLink, text: 'Lien' },
            { name: 'code', action: EasyMDE.toggleCodeBlock, text: 'Code' },
            { name: 'table', action: EasyMDE.drawTable, text: 'Tableau' },
            '|',
            // noDisable : ces boutons restent cliquables pendant l'aperçu
            { name: 'preview', action: EasyMDE.togglePreview, text: 'Aperçu', noDisable: true },
            { name: 'side-by-side', action: EasyMDE.toggleSideBySide, text: 'Aperçu en direct', noDisable: true },
        ],
    });
});
