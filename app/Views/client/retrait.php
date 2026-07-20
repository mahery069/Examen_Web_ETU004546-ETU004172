<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Retrait</title>
</head>
<body>
    <h1>Effectuer un retrait</h1>

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

    <form action="<?= url_to('retirer') ?>" method="post">
        <label for="montant">Montant à retirer</label>
        <input
            type="text"
            id="montant"
            name="montant"
            placeholder="10000"
            value="<?= old('montant') ?>"
        >
        <button type="submit">Retirer</button>
    </form>

    <p>
        Des frais seront automatiquement calculés selon le barème en vigueur
        et ajoutés au montant retiré.
    </p>

    <p>
        <a href="<?= url_to('tableau_de_bord') ?>">Retour au tableau de bord</a>
    </p>
</body>
</html>
