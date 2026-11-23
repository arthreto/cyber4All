<?php

/**
 * LAYOUT : cadre commun des pages d'administration du quiz Concept (groupe C).
 * Fichier : app/Views/groupe_c/groupe_c_ADMIN/layout_admin_groupeC.php
 *
 * QUI FAIT QUOI
 * -------------
 *  - Remplace l'assemblage fait avant dans les contrôleurs :
 *        view('header') . view('notice') . view(...) . view('footer')
 *  - Les vues admin_DICT_groupeC et admin_QUESTIONS_groupeC l'étendent avec $this->extend(),
 *    et placent leur contenu dans la section "contenu" (renderSection ci-dessous).
 *  - Il contient aussi ce qui était dupliqué dans chaque vue : feuille de style, <style> de fond, <body>
 *    et le pop-up de succès (message flash 'succes' envoyé par les contrôleurs).
 */

// Dans un layout, on inclut les autres vues avec $this->include() (et non view()) :
// c'est la méthode prévue par CI4, qui évite les conflits avec le rendu du layout en cours.
helper('html'); // charge link_tag()
?>
<?= $this->include('header') ?>
<?= $this->include('notice') ?>

    <!-- Feuille de style du groupe C -->
<?= link_tag(base_url('/css/groupe_c_css/groupe_c_css.css')) ?>
    <!-- Fond de page et marges. À déplacer un jour dans groupe_c_css.css -->
    <style>body { margin: 0; background: #f7f8fc; }</style>
    <body>

    <?php if (session()->getFlashdata('succes')): ?>
        <!-- Affiché seulement si un contrôleur a envoyé un message flash 'succes' (une seule fois) -->
        <div class="popup-succes">✔ <?= esc(session()->getFlashdata('succes')) ?></div>
    <?php endif; ?>

    <!-- Contenu propre à chaque page (défini dans la vue avec $this->section('contenu')) -->
    <?= $this->renderSection('contenu') ?>

    </body>
<?= $this->include('footer') ?>