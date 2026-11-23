<main class="admin-top-container">

    <h1 class="admin-title">Administration OWASP</h1>

    <div class="admin-columns">

        <!-- TOP WEB -->
        <section class="admin-top-card web">
            <header>
                <h2>Owasp Web Top <?= count($menacesWeb) ?></h2>
            </header>

            <ul class="admin-risk-list">

                <?php foreach ($menacesWeb as $menace): ?>

                    <li class="admin-risk-item">

                        <div class="risk-content">
                            <strong><?= esc($menace->nom_menace) ?></strong>
                        </div>

                        <div class="risk-actions">

                            <a
                                href="<?= site_url('admin/menace/edit/' . $menace->id_menace) ?>"
                                class="action-btn edit-btn"
                                title="Modifier">
                                ✏️
                            </a>

                            <a
                                href="<?= site_url('admin/menace/delete/' . $menace->id_menace) ?>"
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
                        href="<?= site_url('admin/menace/create/web') ?>"
                        class="add-risk-btn">
                        ➕ Ajouter une menace
                    </a>

                </li>

            </ul>
        </section>

        <!-- TOP API -->
        <section class="admin-top-card api">
            <header>
                <h2>OWASP API</h2>
            </header>

            <ul class="admin-risk-list">

                <?php foreach ($menacesApi as $menace): ?>

                    <li class="admin-risk-item">

                        <div class="risk-content">
                            <strong><?= esc($menace->nom_menace) ?></strong>
                        </div>

                        <div class="risk-actions">

                            <a
                                href="<?= site_url('admin/menace/edit/' . $menace->id_menace) ?>"
                                class="action-btn edit-btn"
                                title="Modifier">
                                ✏️
                            </a>

                            <a
                                href="<?= site_url('admin/menace/delete/' . $menace->id_menace) ?>"
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
                        href="<?= site_url('admin/menace/create/api') ?>"
                        class="add-risk-btn">
                        ➕ Ajouter une menace
                    </a>

                </li>

            </ul>
        </section>

    </div>

</main>