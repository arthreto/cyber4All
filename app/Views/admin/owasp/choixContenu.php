<main>
    <table>
        <thead>
            <tr>
                <th scope="col">Top</th>
                <th scope="col">Modifier</th>
                <th scope="col">Supprimer</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tops as $top): ?>
                <tr>
                    <td><?= anchor((base_url('floribondant/owasp/contenu/' . $top->id_top)), esc($top->nom_top)) ?></td>
                    <td><button type="button" disabled>Modifier</button></td>
                    <td><button type="button" disabled>Supprimer</button></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3"><button type="button" style="width: 100%; padding: 1rem;" disabled>➕ Ajouter un top</button></td>
            </tr>
        </tbody>
    </table>
    <p>Les actions d’ajout, de modification et de suppression des tops ne sont pas encore disponibles.</p>
</main>