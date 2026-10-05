function afficherModal(idModal) {
    const dialog = document.getElementById(idModal);
    if (dialog) {
        dialog.showModal();
        // Focus sur le bouton de fermeture pour l'accessibilité
        const closeButton = dialog.querySelector('.button__modal--close');
        if (closeButton) {
            setTimeout(() => closeButton.focus(), 100);
        }
    }
}

// Fonction pour fermer une modal de projet
function fermerModal(idModal) {
    const dialog = document.getElementById(idModal);
    if (dialog) {
        dialog.close();
    }
}

(function() {
    const modal = document.getElementById('modal-signalement');
    const btnClose = document.querySelector('.btn-close-modal');
    const hiddenMotId = document.getElementById('hidden-mot-id');

    // Gérer tous les boutons "Signaler"
    document.querySelectorAll('.btn-signaler').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();

            // Récupérer l'ID du mot depuis l'attribut data
            const motId = this.getAttribute('data-mot-id');

            // Mettre à jour le champ caché avec l'ID du mot
            if (hiddenMotId) {
                hiddenMotId.value = motId;
            }

            // Réinitialiser le textarea
            const textarea = document.getElementById('motif');
            if (textarea) {
                textarea.value = '';
            }

            // Ouvrir la modal
            modal.showModal();
        });
    });

    // Fermer avec le bouton X
    if (btnClose) {
        btnClose.addEventListener('click', function() {
            modal.close();
        });
    }

    // Fermer en cliquant sur le backdrop
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.close();
        }
    });
})();
/* ==========================================
   JAVASCRIPT POUR quiz
   ========================================== */

