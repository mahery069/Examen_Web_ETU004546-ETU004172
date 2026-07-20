<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Historique des opérations</title>
</head>
<body>
    <h1>Historique des opérations</h1>

    <?php if (empty($lignes)) : ?>
        <p>Aucune opération pour le moment.</p>
    <?php else : ?>
        <table border="1" cellpadding="5">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Contact</th>
                    <th>Montant</th>
                    <th>Frais</th>
                    <th>Effet sur le solde</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lignes as $ligne) : ?>
                    <tr>
                        <td><?= esc($ligne['date']) ?></td>
                        <td><?= esc($ligne['libelle']) ?></td>
                        <td><?= $ligne['contrepartie'] !== null ? esc($ligne['contrepartie']) : '-' ?></td>
                        <td><?= esc(number_format($ligne['montant'], 2, ',', ' ')) ?> Ar</td>
                        <td><?= esc(number_format($ligne['frais'], 2, ',', ' ')) ?> Ar</td>
                        <td>
                            <?= $ligne['montant_signe'] >= 0 ? '+' : '' ?><?= esc(number_format($ligne['montant_signe'], 2, ',', ' ')) ?> Ar
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p>
        <a href="<?= url_to('tableau_de_bord') ?>">Retour au tableau de bord</a>
    </p>
</body>
</html>
