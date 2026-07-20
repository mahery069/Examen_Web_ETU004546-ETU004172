<!DOCTYPE html>
<html lang="fr">
<head>
<?php $this->setData(['title' => 'Connexion opérateur']); ?>
<?= $this->include('admin/_theme_head') ?>
</head>
<body class="bg-background text-foreground antialiased">
<?php helper('icon'); ?>

<div class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-sm">
        <div class="flex flex-col items-center gap-3 mb-8">
            <div class="size-12 rounded-xl bg-primary grid place-items-center text-primary-foreground font-bold text-lg">F</div>
            <div class="text-center">
                <div class="text-base font-semibold">FluxPay</div>
                <div class="text-[10px] font-mono uppercase tracking-widest text-muted-foreground">Terminal opérateur</div>
            </div>
        </div>

        <div class="bg-card border border-border rounded-xl p-6">
            <h1 class="text-lg font-semibold tracking-tight mb-1">Connexion</h1>
            <p class="text-sm text-muted-foreground mb-6">Accédez au back-office pour gérer le réseau.</p>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="mb-4 px-4 py-3 rounded-lg bg-success-10 text-success text-sm font-medium">
                    <?= esc(session()->getFlashdata('success')) ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="mb-4 px-4 py-3 rounded-lg bg-destructive-10 text-destructive text-sm">
                    <ul class="list-disc pl-4 space-y-0.5">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form class="space-y-4" action="<?= site_url('admin/login') ?>" method="post">
                <?= csrf_field() ?>
                <div class="flex flex-col gap-1.5">
                    <label for="login" class="text-xs font-medium text-muted-foreground">Identifiant</label>
                    <input type="text" id="login" name="login" autofocus required value="<?= esc(old('login')) ?>"
                           class="w-full px-3 py-2 rounded-lg border border-input bg-background text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
                <div class="flex flex-col gap-1.5">
                    <label for="mot_de_passe" class="text-xs font-medium text-muted-foreground">Mot de passe</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" required
                           class="w-full px-3 py-2 rounded-lg border border-input bg-background text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                </div>
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">
                    Se connecter
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-muted-foreground mt-6">
            Accès réservé à l'opérateur réseau.
        </p>
    </div>
</div>
</body>
</html>
