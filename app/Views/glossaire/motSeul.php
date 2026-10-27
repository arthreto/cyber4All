<?php
session();
$utilisateur = session()->get("utilisateur");
?>

<main class="container mot-seul-page">
    <?php if(isset($contenu) && $contenu === "mot_seul"): ?>

        <?php if(!isset($mot) || $mot == null): ?>
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

        <?php else: ?>
            <!-- Affichage du mot -->
            <article class="mot-seul-article">

                <!-- En-tête avec titre et vote -->
                <header class="mot-seul-header">
                    <div class="mot-seul-title-wrapper">
                        <h1 class="mot-seul-title"><?= esc($mot->mot) ?></h1>

                        <!-- Date de publication -->
                        <?php $time = \CodeIgniter\I18n\Time::parse($mot->date_ajout, 'Europe/Paris', 'fr_FR'); ?>
                        <p class="mot-seul-date">
                            <i class="fa-regular fa-calendar"></i>
                            Publié le <?= $time->toLocalizedString('d MMMM yyyy') ?>
                        </p>
                    </div>

                    <?php if(isset($utilisateur) && $utilisateur->role === "contributeur"): ?>
                        <!-- Bouton de vote (si contributeur) -->
                        <div class="mot-seul-vote">
                            <?= view('vote/form_vote', [
                                'mot' => $mot,
                                'tab_id_mots' => $tab_id_mots ?? [],
                                'nb_votes' => $nb_votes ?? 0,
                                'from' => $from ?? base_url('glossaire')
                            ]); ?>
                            <!-- Bouton de signalement (si contributeur) -->
                            <?= view('signalement/bouton_signalement', [
                                    'mot' => $mot,
                                    'from' => $from ?? base_url('glossaire')
                            ]); ?>
                        </div>
                    <?php endif; ?>
                </header>

                <!-- Catégories -->
                <?php if(!empty($categoriesMot)): ?>
                    <div class="mot-seul-categories">
                        <span class="categories-label">
                            <i class="fa-solid fa-tags"></i> Catégories :
                        </span>
                        <div class="categories-list">
                            <?php foreach ($categoriesMot as $categorie): ?>
                                <a href="<?= base_url("glossaire/categorie/{$categorie->id}") ?>"
                                   class="lexique__categorie"
                                   title="Voir tous les termes de cette catégorie"
                                   data-category="<?= $categorie->id ?>">
                                    <?= esc($categorie->nom) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Définition courte -->
                <section class="mot-seul-section">
                    <h2 class="section-title">
                        <i class="fa-solid fa-book"></i> Définition
                    </h2>
                    <p class="mot-seul-definition">
                        <?= nl2br(esc($mot->definition)) ?>
                    </p>
                </section>

                <!-- Images (si présentes) -->
                <?php if(!empty($images) && is_array($images)): ?>
                    <section class="mot-seul-images">
                        <?php foreach ($images as $image): ?>
                            <figure class="mot-seul-figure">
                                <?= img([
                                    'src' => base_url(IMAGES_PATH . $image->image),
                                    'alt' => $image->alt ?? 'Image illustrative de la définition',
                                    'class' => 'mot-seul-image'
                                ]) ?>
                                <?php if(!empty($image->legend)): ?>
                                    <figcaption class="mot-seul-legend">
                                        <i class="fa-solid fa-image"></i>
                                        <?= esc($image->legend) ?>
                                    </figcaption>
                                <?php endif; ?>
                            </figure>
                        <?php endforeach; ?>
                    </section>
                <?php endif; ?>

                <!-- Définition complète -->
                <?php if(!empty($mot->definition_complete)): ?>
                    <section class="mot-seul-section">
                        <h2 class="section-title">
                            <i class="fa-solid fa-align-left"></i> Explication détaillée
                        </h2>
                        <div class="mot-seul-definition-complete">
                            <?= nl2br(esc($mot->definition_complete)) ?>
                        </div>
                    </section>
                <?php endif; ?>

                <?php if ($mot->id == 21): ?>
                    <!-- Choix du Top 10 -->
                    <section class="mot-seul-section top10-choix">
                        <p class="top10-eyebrow">Consulter les classements</p>
                        <h2 class="section-title">Choisissez un Top 10</h2>
                        <p class="top10-intro">Retrouvez pour chaque risque une explication en français et une contre-mesure applicable.</p>

                        <div class="top10-cards">
                            <a href="<?= base_url('top10/web') ?>" class="top10-card top10-card--web">
                                <span class="top10-tag">Web · 2025</span>
                                <h3>Top 10 Vulnérabilités Web</h3>
                                <span class="top10-link">Consulter le classement</span>
                            </a>

                            <a href="<?= base_url('top10/api') ?>" class="top10-card top10-card--api">
                                <span class="top10-tag">API · 2023</span>
                                <h3>Top 10 Vulnérabilités API</h3>
                                <span class="top10-link">Consulter le classement</span>
                            </a>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Pied de page avec boutons d'action -->
                <footer class="mot-seul-footer">
                    <a href="<?= base_url('glossaire') ?>" class="btn-tertiaire">
                        <i class="fa-solid fa-arrow-left"></i>
                        Retour au glossaire
                    </a>

                    <div class="mot-seul-share">
                        <span class="share-label">Partager :</span>
                        <button class="btn-share" onclick="copyToClipboard()" title="Copier le lien">
                            <i class="fa-solid fa-link"></i>
                        </button>
                    </div>
                </footer>

            </article>

        <?php endif; ?>

    <?php endif; ?>
</main>
<?php if(isset($utilisateur) && $utilisateur->role === "contributeur"):
    echo view('signalement/modal_signalement', ['mot'=>$mot]);
endif; ?>
<script>
    function copyToClipboard() {
        const url = window.location.href;
        navigator.clipboard.writeText(url).then(() => {
            // Feedback visuel
            const btn = event.currentTarget;
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-check"></i>';
            btn.classList.add('copied');

            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.classList.remove('copied');
            }, 2000);
        }).catch(err => {
            console.error('Erreur lors de la copie:', err);
        });
    }
</script>