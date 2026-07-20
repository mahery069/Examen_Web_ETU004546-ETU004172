<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Tableau de bord</title>
</head>
<body>
    <h1>Tableau de bord</h1>

    <p>Numéro de téléphone : <?= esc($numero_telephone) ?></p>

    <h2>Solde actuel</h2>
    <p><?= esc(number_format((float) $solde, 2, ',', ' ')) ?> Ar</p>
    <p><a href="<?= url_to('solde') ?>">Voir le détail</a></p>

    <h2>Actions rapides</h2>
    <ul>
        <li><a href="<?= url_to('depot') ?>">Effectuer un dépôt</a></li>
        <li><a href="<?= url_to('retrait') ?>">Effectuer un retrait</a></li>
        <li><a href="<?= url_to('transfert') ?>">Effectuer un transfert</a></li>
    </ul>

    <h2>Dernières opérations</h2>
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
        <p><a href="<?= url_to('historique') ?>">Voir tout l'historique</a></p>
    <?php endif; ?>

    <p>
        <a href="<?= url_to('logout') ?>">Se déconnecter</a>
    </p>
</body>
</html>
