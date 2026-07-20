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
        <li><a href="<?= url_to('depot') ?>">Effectuer un dépôt</a></li>
        <li><a href="<?= url_to('retrait') ?>">Effectuer un retrait</a></li>
        <li><a href="<?= url_to('transfert') ?>">Effectuer un transfert</a></li>
        <li><a href="<?= url_to('historique') ?>">Voir l'historique des opérations</a></li>
    </ul>

    <p>
        <a href="<?= url_to('logout') ?>">Se déconnecter</a>
    </p>
</body>
</html>
