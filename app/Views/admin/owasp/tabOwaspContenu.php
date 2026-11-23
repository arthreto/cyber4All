<main class="admin-top-container">

    <h1 class="admin-title">Administration OWASP</h1>

    <div class="admin-columns">

        <section class="admin-top-card web">
            <header>
                <h2><?= esc($top->nom_top) ?> <?= count($menaces) ?></h2>
            </header>

            <ul class="admin-risk-list">

                <?php foreach ($menaces as $menace): ?>

                    <li class="admin-risk-item">

                        <div class="risk-content">
                            <strong><?= esc($menace->nom_menace) ?></strong>
                        </div>

                        <div class="risk-actions">

                            <a
                                href="<?= esc(site_url('floribondant/owasp/contenu/' . $top->id_top . '/modifier/' . $menace->id_menace), 'attr') ?>"
                                class="action-btn edit-btn"
                                title="Modifier">
                                ✏️
                            </a>

                            <a
                                href="<?= site_url('floribondant/owasp/menace/delete/' . $menace->id_menace) ?>"
                                class="action-btn delete-btn"
                                title="Supprimer"
                                onclick="return confirm('Supprimer cette menace et toutes ses contre-mesures ?');">
                                🗑️
                            </a>

                        </div>

                    </li>

                <?php endforeach; ?>

                <li class="admin-add-item">

                    <a
                        href="<?= esc(site_url('floribondant/owasp/menace/create/' . rawurlencode($top->id_top)), 'attr') ?>"
                        class="add-risk-btn">
                        ➕ Ajouter une menace
                    </a>

                </li>

            </ul>
        </section>


    </div>

</main>