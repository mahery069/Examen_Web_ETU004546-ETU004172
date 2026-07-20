<!DOCTYPE html>
<html lang="fr">
<head>
<?php $this->setData(['title' => 'Connexion client']); ?>
<?= $this->include('admin/_theme_head') ?>
</head>
<body class="bg-background text-foreground antialiased">
<?php helper('icon'); ?>

<div class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-sm">
        <div class="flex flex-col items-center gap-3 mb-8">
            <div class="size-12 rounded-xl bg-success grid place-items-center text-success-foreground">
                <?= icon('wallet', 'size-6') ?>
            </div>
            <div class="text-center">
                <div class="text-base font-semibold">FluxPay</div>
                <div class="text-[10px] font-mono uppercase tracking-widest text-muted-foreground">Espace client</div>
            </div>
        </div>

        <div class="bg-card border border-border rounded-xl p-6">
            <h1 class="text-lg font-semibold tracking-tight mb-1">Connexion</h1>
            <p class="text-sm text-muted-foreground mb-6">
                Saisissez votre numéro de téléphone pour accéder à votre compte.
            </p>

            <?php if (session()->getFlashdata('erreur')): ?>
                <div class="mb-4 px-4 py-3 rounded-lg bg-destructive-10 text-destructive text-sm">
                    <?= esc(session()->getFlashdata('erreur')) ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('erreurs')): ?>
                <div class="mb-4 px-4 py-3 rounded-lg bg-destructive-10 text-destructive text-sm">
                    <ul class="list-disc pl-4 space-y-0.5">
                        <?php foreach (session()->getFlashdata('erreurs') as $erreur): ?>
                            <li><?= esc($erreur) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form class="space-y-4" action="<?= url_to('login') ?>" method="post">
                <div class="flex flex-col gap-1.5">
                    <label for="numero_telephone" class="text-xs font-medium text-muted-foreground">Numéro de téléphone</label>
                    <input type="text" id="numero_telephone" name="numero_telephone" autofocus placeholder="0331234567"
                           value="<?= esc(old('numero_telephone')) ?>"
                           class="w-full px-3 py-2 rounded-lg border border-input bg-background text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">
                    <?= icon('log-in', 'size-4') ?> Se connecter
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-muted-foreground mt-6">
            Si votre numéro n'est pas encore enregistré, un compte sera créé automatiquement
            (solde initial : 0 Ar), à condition que le préfixe soit reconnu par l'opérateur.
        </p>

        <p class="text-center text-xs text-muted-foreground mt-3">
            <a href="<?= site_url('/') ?>" class="hover:underline">← Retour à l'accueil</a>
        </p>
    </div>
</div>
</body>
</html>
