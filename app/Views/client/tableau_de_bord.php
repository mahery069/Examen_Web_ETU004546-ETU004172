<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Espace client</title>
</head>
<body>
    <h1>Bienvenue</h1>

    <p>Vous êtes connecté avec le numéro : <?= esc($numero_telephone) ?></p>

    <ul>
        <li><a href="<?= url_to('solde') ?>">Voir mon solde</a></li>
        <li>Dépôt : à venir</li>
        <li>Retrait : à venir</li>
        <li>Transfert : à venir</li>
        <li>Historique : à venir</li>
    </ul>

    <p>
        <a href="<?= url_to('logout') ?>">Se déconnecter</a>
    </p>
</body>
</html>
