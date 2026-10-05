<?php
/**
 * AFFICHAGE DES MOTS DU LEXIQUE
 *
 * Ce fichier gère l'affichage des mots selon différents contextes :
 * - Lexique complet (glossaire.php)
 * - Mots populaires (communautaire.php)
 * - Recherche et filtrage par catégorie
 *
 * Fonctionnalités :
 * - Système de vote pour contributeurs
 * - Redirection intelligente selon la provenance
 * - Gestion des tokens anti-CSRF
 */
session();
if (session()->has('utilisateur')):
    $utilisateur = session()->get('utilisateur');

    if ($utilisateur->role === "contributeur"):
        echo view('signalement/modal_signalement');
    endif;
endif;
?>

<div class="glossaire-container">
    <!-- En-tête du glossaire -->
    <header class="glossaire-header container--start">
        <div class="lexique-container-static">
            <div class="lexiques__title-static">
                <?php if (isset($contenu) && $contenu === "categorie" && isset($categorie)): ?>
                    <!-- Titre catégorie -->
                    <h1 class="lexique__categorie-static" data-category="<?= $categorie->id ?>">
                        Lexique de la catégorie : <?= esc($categorie->nom) ?>
                    </h1>
                <?php elseif ($contenu === "recherche" && isset($recherche)): ?>
                    <!-- Titre recherche -->
                    <h1 class="lexique__categorie-static" data-category="7">
                        Recherche : <?= esc($recherche) ?>
                    </h1>
                <?php elseif ($contenu === "glossaire"): ?>
                    <!-- Titre glossaire complet -->
                    <h1 class="lexique__categorie-static" data-category="8">
                        Tout le lexique de la cybersécurité
                    </h1>
                <?php endif; ?>
            </div>
        </div>
        <!-- Pagination  -->
        <?php if (!empty($pager)): ?>
            <div class="pagination">
                <?= $pager->links() ?>
            </div>
        <?php endif; ?>

        <?php if (isset($total) && $total !== 0): ?>
            <!-- Compteur de termes -->
            <p class="total-mots">
                Total : <?= $total ?> terme<?= $total > 1 ? 's'
                        : '' ?>
                <?= !empty($perPage) ? '- Affichage par ' . $perPage
                        : '' ?>
            </p>
        <?php endif; ?>
    </header>

    <?php if (isset($total) && $total === 0): ?>
        <!-- Message d'erreur si aucun mot trouvé -->
        <div class="mot-seul-error">
            <div class="error-icon">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h2 class="error-title">Aucun résultat trouvé</h2>
            <p class="error-message">
                Désolé, aucun terme ne correspond à votre recherche.
            </p>
            <a href="<?= base_url('glossaire') ?>" class="btn-retour">
                <i class="fa-solid fa-arrow-left"></i> Retour au glossaire
            </a>
        </div>
    <?php else : ?>

        <div class="mot-seul-categories">
            <span class="categories-label">
                <i class="fa-solid fa-tags"></i> Catégories :
                <button type="button" id="toggleCategoriesBtn" class="btn-toggle-categories">
                Afficher
                </button>
                <?php if (isset($categorie)) :
                    echo anchor(
                            'glossaire',  // URL ou route
                            esc($categorie->nom), // Texte du lien
                            [
                                    'class' => 'lexique__categorie lexique__categorie--remove',
                                    'title' => 'Catégorie : ' . esc($categorie->nom),
                                    'data-category' => $categorie->id
                            ]
                    );
                endif; ?>
            </span>
            <div class="categories-list" style="display: none">
                <?php if (isset($categories)) :

                    foreach ($categories as $cat):
                        echo anchor(
                                'glossaire/categorie/' . $cat->id,
                                esc($cat->nom),
                                [
                                        'class' => 'lexique__categorie',
                                        'title' => 'Catégorie : ' . esc($cat->nom),
                                        'data-category' => $cat->id
                                ]
                        );
                    endforeach;

                endif; ?>
            </div>
        </div>

        <!-- Navigation alphabétique latérale -->
        <nav class="sidebar" aria-label="Navigation alphabétique">
            <h2 class="sidebar-title">Navigation</h2>

            <div class="alphabet-nav" id="alphabetNav">
                <!-- Lien recherche -->
                <a href="#recherche"
                   class="letter-link available"
                   aria-label="Aller à la recherche">
                    🔍
                </a>

                <?php if (isset($glossaire)) : ?>
                    <!-- Lettres de l'alphabet -->
                    <?php foreach ($glossaire as $lettre => $mots): ?>
                        <a href="#section-<?= $lettre ?>"
                           class="letter-link available"
                           data-letter="<?= $lettre ?>"
                           aria-label="Aller à la lettre <?= $lettre ?>">
                            <?= $lettre ?>
                        </a>
                    <?php endforeach;
                endif; ?>
            </div>
        </nav>

        <!-- Contenu principal -->
        <main class="glossaire-main container">
            <?php foreach ($glossaire as $lettre => $mots): ?>
                <!-- Section par lettre -->
                <section id="section-<?= $lettre ?>" class="lexique-section">
                    <!-- Titre de la section -->
                    <h2 class="lexique-lettre"><?= esc($lettre) ?></h2>

                    <!-- Grille des mots -->
                    <div class="lexiques-wrapper">
                        <?php foreach ($mots as $mot): ?>
                            <!-- Carte d'un mot -->
                            <article class="lexique-container">
                                <!-- En-tête : Titre + Vote -->
                                <header class="lexique__header">
                                    <h3 class="lexique__title" title="<?= esc($mot->mot) ?>">
                                        <?= esc($mot->mot); ?>
                                        <?php if (!empty($topMots)): ?>
                                            <?= in_array($mot, $topMots) ?
                                                    '<i class="fa-sharp fa-solid fa-fire-flame-curved"></i>'
                                                    : ''
                                            ?>
                                        <?php endif; ?>
                                    </h3>
                                    <?php if (isset($utilisateur) && $utilisateur->role === "contributeur"): ?>
                                        <!-- Formulaire de vote -->
                                        <?= view('vote/form_vote', [
                                                'mot' => $mot,
                                                'tab_id_mots' => $tab_id_mots ?? [],
                                                'nb_votes' => $nb_votes ?? 0,
                                                'from' => $from ?? base_url('glossaire')
                                        ]); ?>
                                        <?= view('signalement/bouton_signalement', [
                                                'mot' => $mot,
                                                'from' => $from ?? base_url('glossaire')
                                        ]); ?>
                                    <?php endif; ?>
                                </header>

                                <!-- Définition -->
                                <p class="lexique__definition">
                                    <?= esc($mot->definition) ?>
                                </p>

                                <!-- Catégories -->
                                <?php $categories = $mot->getCategories(); ?>
                                <?php if (!empty($categories)): ?>
                                    <div class="lexique_categories">
                                        <?php foreach ($categories as $cat):
                                            echo anchor(
                                                    "glossaire/categorie/{$cat->id}", // URL
                                                    esc($cat->nom),                   // texte du lien
                                                    [
                                                            'class' => 'lexique__categorie',
                                                            'title' => 'Catégorie : ' . esc($cat->nom),
                                                            'data-category' => $cat->id
                                                    ]
                                            );
                                        endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($mot->definition_complete)): ?>
                                    <!-- Pied de carte : Lien "En savoir plus" -->
                                    <footer class="btn-more">
                                        <?= anchor(
                                                base_url('glossaire/mot/' . $mot->id),
                                                'En savoir plus <i class="fa-solid fa-arrow-right"></i>',
                                        ) ?>
                                    </footer>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
            <!-- Pagination  -->
            <?php if (!empty($pager)): ?>
                <div class="pagination">
                    <?= $pager->links() ?>
                </div>
            <?php endif; ?>
        </main>
    <?php endif; ?>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('toggleCategoriesBtn');
        const categoriesList = document.querySelector('.mot-seul-categories .categories-list');

        toggleBtn.addEventListener('click', function () {
            if (categoriesList.style.display === 'none') {
                categoriesList.style.display = 'block';
                toggleBtn.textContent = 'Masquer';
            } else {
                categoriesList.style.display = 'none';
                toggleBtn.textContent = 'Afficher';
            }
        });
    });
</script>