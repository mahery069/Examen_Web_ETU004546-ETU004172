<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Transfert</title>
</head>
<body>
    <h1>Effectuer un transfert</h1>

    <?php if (session()->getFlashdata('erreur')) : ?>
        <p><?= esc(session()->getFlashdata('erreur')) ?></p>
    <?php endif; ?>

    <?php if (session()->getFlashdata('erreurs')) : ?>
        <ul>
            <?php foreach (session()->getFlashdata('erreurs') as $erreur) : ?>
                <li><?= esc($erreur) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form action="<?= url_to('transferer') ?>" method="post">
        <label for="numero_destinataire">Numéro du destinataire</label>
        <input
            type="text"
            id="numero_destinataire"
            name="numero_destinataire"
            placeholder="0331234567"
            value="<?= old('numero_destinataire') ?>"
        >

        <label for="montant">Montant à transférer</label>
        <input
            type="text"
            id="montant"
            name="montant"
            placeholder="10000"
            value="<?= old('montant') ?>"
        >

        <button type="submit">Transférer</button>
    </form>

    <p>
        Des frais seront automatiquement calculés selon le barème en vigueur
        et ajoutés au montant transféré (débités de votre compte).
    </p>

    <p>
        <a href="<?= url_to('tableau_de_bord') ?>">Retour au tableau de bord</a>
    </p>
</body>
</html>
