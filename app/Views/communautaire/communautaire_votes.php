<?php
if(session()->has('utilisateur')):
    $utilisateur = session()->get('utilisateur');
    if($utilisateur->role === 'contributeur'):?>
<div class="vote-section">
    <div class="header-populaire">
        <h2 class="section-title">
            📝 Les favoris de la communauté
        </h2>
    </div>
    <div class="word-list" id="wordList">
        <div class="container">
            <div class="lexiques-wrapper">
                <?php if(!empty($motsPopulaires)):
                    foreach ($motsPopulaires as $mot):?>
                        <div class="lexique-container">
                            <div class="lexique__header">
                                <h3 class="lexique__title"
                                    title="Nom du mot"><?= $mot->mot ?></h3>
                                <?php session();
                                if(isset($utilisateur) && ($utilisateur->role === "contributeur")): ?>
                                        <!-- Formulaire de vote -->
                                        <?= view('vote/form_vote', [
                                                'mot' => $mot,
                                                'tab_id_mots' => $tab_id_mots ?? [],
                                                'nb_votes' => $nb_votes,
                                                'from' => $from ?? base_url('glossaire')
                                        ]);
                                endif;?>
                            </div>
                            <p class="lexique__definition"
                               title="Définition du mot"><?= esc($mot->definition) ?></p>
                            <div class="lexique_categories">
                                <?php
                                $categoriesDuMot = $categoriesParMot[$mot->id] ?? [];
                                foreach ($categoriesDuMot as $categorie):
                                    echo anchor(
                                            "glossaire/categorie/" . $categorie->id, // URL dynamique
                                            esc($categorie->nom),                     // texte du lien sécurisé
                                            [
                                                    'class' => 'lexique__categorie',
                                                    'title' => 'Catégorie du mot',
                                                    'data-category' => $categorie->id
                                            ]
                                    );
                                endforeach; ?>
                            </div>
                            <div class="btn-more">
                                <?= anchor(
                                "glossaire/mot/" . esc($mot->id), // URL dynamique
                                'En savoir plus <i class="fa-solid fa-right"></i>', // texte du lien (HTML inclus)
                                );
                                ?>
                            </div>
                        </div>
                    <?php endforeach;
                else:?>
                    <div class="lexique-container mot-seul">
                        <h3 class="lexique__title mot-seul"
                            title="Aucun résultat">Aucun mots populaires cette semaine</h3>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
    <?php endif; ?>
<?php endif; ?>
