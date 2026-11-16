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
        autoDownloadFontAwesome: false, // Font Awesome est déjà chargé par header.php
        sideBySideFullscreen: false,    // l'aperçu en direct reste dans la page

        // Texte inséré par le bouton "Tableau"
        insertTexts: {
            table: ['', '\n\n| Colonne 1 | Colonne 2 |\n| --- | --- |\n| Texte | Texte |\n\n'],
        },

        // Texte inséré par le bouton "Tableau"
        insertTexts: {
            table: ['', '\n\n| Colonne 1 | Colonne 2 |\n| --- | --- |\n| Texte | Texte |\n\n'],
        },

        // Boutons de la barre d'outils, avec les icônes d'origine d'EasyMDE ('|' = séparateur).
        // className : classes Font Awesome de l'icône (fa-solid, comme dans le reste du site) ;
        // title : bulle d'aide en français (EasyMDE y ajoute lui-même le raccourci clavier)
        toolbar: [
            { name: 'bold', action: EasyMDE.toggleBold, className: 'fa-solid fa-bold', title: 'Gras' },
            { name: 'italic', action: EasyMDE.toggleItalic, className: 'fa-solid fa-italic', title: 'Italique' },
            { name: 'heading-2', action: EasyMDE.toggleHeading2, className: 'fa-solid fa-heading fa-header-x fa-header-2', title: 'Titre de section' },
            { name: 'heading-3', action: EasyMDE.toggleHeading3, className: 'fa-solid fa-heading fa-header-x fa-header-3', title: 'Sous-titre' },
            '|',
            { name: 'unordered-list', action: EasyMDE.toggleUnorderedList, className: 'fa-solid fa-list-ul', title: 'Liste à puces' },
            { name: 'ordered-list', action: EasyMDE.toggleOrderedList, className: 'fa-solid fa-list-ol', title: 'Liste numérotée' },
            '|',
            { name: 'link', action: EasyMDE.drawLink, className: 'fa-solid fa-link', title: 'Lien' },
            { name: 'code', action: EasyMDE.toggleCodeBlock, className: 'fa-solid fa-code', title: 'Bloc de code' },
            { name: 'table', action: EasyMDE.drawTable, className: 'fa-solid fa-table', title: 'Tableau' },
            '|',
            // noDisable : ces boutons restent cliquables pendant l'aperçu
            { name: 'preview', action: EasyMDE.togglePreview, className: 'fa-solid fa-eye', title: 'Aperçu', noDisable: true },
            { name: 'side-by-side', action: EasyMDE.toggleSideBySide, className: 'fa-solid fa-table-columns', title: 'Aperçu en direct', noDisable: true },
        ],
    });
});
