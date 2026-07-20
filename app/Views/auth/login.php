<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Connexion client</title>
</head>
<body>
    <h1>Connexion</h1>

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

    <form action="<?= url_to('login') ?>" method="post">
        <label for="numero_telephone">Numéro de téléphone</label>
        <input
            type="text"
            id="numero_telephone"
            name="numero_telephone"
            placeholder="0331234567"
            value="<?= old('numero_telephone') ?>"
        >
        <button type="submit">Se connecter</button>
    </form>

    <p>
        Si votre numéro n'est pas encore enregistré, un compte sera créé
        automatiquement (solde initial : 0), à condition que le préfixe de
        votre numéro soit reconnu par l'opérateur.
    </p>
</body>
</html>
