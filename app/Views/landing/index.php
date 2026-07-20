<!DOCTYPE html>
<html lang="fr">
<head>
<?php $this->setData(['title' => 'Accueil']); ?>
<?= $this->include('admin/_theme_head') ?>
</head>
<body class="bg-background text-foreground antialiased">
<?php helper('icon'); ?>

<div class="min-h-screen flex flex-col items-center justify-center p-6">
    <div class="flex flex-col items-center gap-3 mb-10">
        <div class="size-14 rounded-xl bg-primary grid place-items-center text-primary-foreground font-bold text-xl">F</div>
        <div class="text-center">
            <div class="text-xl font-semibold">FluxPay</div>
            <div class="text-[10px] font-mono uppercase tracking-widest text-muted-foreground">Mobile Money</div>
        </div>
    </div>

    <p class="text-sm text-muted-foreground mb-8 text-center max-w-md">
        Choisissez votre espace pour continuer.
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 w-full max-w-2xl">
        <a href="<?= site_url('admin/login') ?>" class="group bg-card border border-border rounded-xl p-6 hover:border-primary hover:shadow-md transition-all">
            <div class="size-12 rounded-lg bg-primary-10 text-primary grid place-items-center mb-4">
                <?= icon('layout-dashboard', 'size-6') ?>
            </div>
            <h2 class="text-base font-semibold mb-1">Espace Opérateur</h2>
            <p class="text-sm text-muted-foreground mb-4">
                Gérez les préfixes réseau, les barèmes de frais, la situation des gains et des comptes clients.
            </p>
            <span class="inline-flex items-center gap-1.5 text-sm font-medium text-primary group-hover:underline">
                Accéder au back-office <?= icon('chevron-right', 'size-4') ?>
            </span>
        </a>

        <a href="<?= site_url('connexion') ?>" class="group bg-card border border-border rounded-xl p-6 hover:border-primary hover:shadow-md transition-all">
            <div class="size-12 rounded-lg bg-success-10 text-success grid place-items-center mb-4">
                <?= icon('wallet', 'size-6') ?>
            </div>
            <h2 class="text-base font-semibold mb-1">Espace Client</h2>
            <p class="text-sm text-muted-foreground mb-4">
                Connectez-vous avec votre numéro de téléphone pour consulter votre solde et effectuer des opérations.
            </p>
            <span class="inline-flex items-center gap-1.5 text-sm font-medium text-primary group-hover:underline">
                Se connecter <?= icon('chevron-right', 'size-4') ?>
            </span>
        </a>
    </div>
</div>
</body>
</html>
