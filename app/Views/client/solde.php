<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Mon solde</title>
</head>
<body>
    <h1>Mon solde</h1>

    <p>Numéro de téléphone : <?= esc($numero_telephone) ?></p>

    <p>Solde actuel : <?= esc(number_format((float) $solde, 2, ',', ' ')) ?> Ar</p>

    <p>
        <a href="<?= url_to('tableau_de_bord') ?>">Retour au tableau de bord</a>
    </p>
</body>
</html>
