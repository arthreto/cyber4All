<?php
/**
 * AFFICHAGE DES MOTS DU LEXIQUE
 *
 * Ce fichier gère l'affichage des mots selon différents contextes :
 * - Lexique complet (glossaire.phpOLD)
 * - Mots populaires (communautaire.php)
 * - Recherche et filtrage par catégorie
 *
 * Fonctionnalités :
 * - Système de vote pour contributeurs
 * - Redirection intelligente selon la provenance
 * - Gestion des tokens anti-CSRF
 */
use App\Models\VoteModel;
$vote_model = new VoteModel();
$id_session = session_id();
if(session()->has('utilisateur')):
    $utilisateur = session()->get('utilisateur');
endif;
if ($id_session):?>
    <div class="user-stats">
        <h2 class="section-title">
            👤 Vos statistiques
        </h2>

        <div class="user-info">
            <div class="username"><?= esc($utilisateur->pseudo) ?></div>

            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-number"><?= esc($nb_votes_total??0) ?></span>
                    <span class="stat-label">Votes totaux</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number"><?= esc($nb_votes??0) ?></span>
                    <span class="stat-label">Votes cette semaine</span>
                </div>
            </div>
        </div>

        <div class="word-list statistique" id="wordList">
                <?php if (!empty($mots)): ?>
                    <div class="lexiques-wrapper lexique-wrapper--communautaire">
                        <?php foreach ($mots as $mot):?>
                            <div class="lexique-container">
                                <div class="lexique__header">
                                    <h3 class="lexique__title"
                                        title="Nom du mot"><?= esc($mot->mot) ?></h3>
                                </div>
                                <div class="lexique_categories">
                                    <?php if(isset($utilisateur) && ($utilisateur->role === "contributeur")):
                                        echo view('vote/form_vote',['mot' => $mot,
                                            'tab_id_mots' => $tab_id_mots ?? [],
                                            'nb_votes' => $nb_votes ?? 0,
                                            'from' => $from ?? base_url('glossaire')
                                        ]);
                                    endif;?>
                                </div>
                                <div class="btn-more">
                                    <?= anchor(
                                            "glossaire/mot/" . esc($mot->id), // URL dynamique
                                            'En savoir plus <i class="fa-solid fa-right"></i>', // texte du lien (HTML inclus)
                                            );
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php else: ?>
                    <div class="lexique-container mot-seul">
                        <h3 class="lexique__title mot-seul"
                            title="Aucun résultat">Allez voter vos termes favoris de la semaine !</h3>
                    </div>
                <?php endif; ?>
        </div>
    </div>
<?php endif; ?>