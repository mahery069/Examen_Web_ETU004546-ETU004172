<?= $this->extend('client/layout') ?>

<?= $this->section('content') ?>
<div class="bg-foreground text-background rounded-xl p-6 mb-8">
    <div class="text-xs uppercase tracking-widest opacity-60 mb-2">Numéro connecté</div>
    <div class="text-2xl font-bold tracking-tight font-mono"><?= esc($numero_telephone) ?></div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
    <a href="<?= url_to('solde') ?>" class="bg-card border border-border rounded-xl p-5 hover:border-primary hover:shadow-md transition-all">
        <div class="size-10 rounded-lg bg-primary-10 text-primary grid place-items-center mb-3"><?= icon('wallet', 'size-5') ?></div>
        <div class="font-medium mb-1">Mon solde</div>
        <div class="text-xs text-muted-foreground">Voir le solde actuel de votre compte.</div>
    </a>

    <a href="<?= url_to('depot') ?>" class="bg-card border border-border rounded-xl p-5 hover:border-primary hover:shadow-md transition-all">
        <div class="size-10 rounded-lg bg-success-10 text-success grid place-items-center mb-3"><?= icon('arrow-down-right', 'size-5') ?></div>
        <div class="font-medium mb-1">Dépôt</div>
        <div class="text-xs text-muted-foreground">Créditez votre compte instantanément.</div>
    </a>

    <a href="<?= url_to('retrait') ?>" class="bg-card border border-border rounded-xl p-5 hover:border-primary hover:shadow-md transition-all">
        <div class="size-10 rounded-lg bg-destructive-10 text-destructive grid place-items-center mb-3"><?= icon('arrow-up-right', 'size-5') ?></div>
        <div class="font-medium mb-1">Retrait</div>
        <div class="text-xs text-muted-foreground">Retirez de l'argent (frais selon barème).</div>
    </a>

    <a href="<?= url_to('transfert') ?>" class="bg-card border border-border rounded-xl p-5 hover:border-primary hover:shadow-md transition-all">
        <div class="size-10 rounded-lg bg-accent text-accent-foreground grid place-items-center mb-3"><?= icon('arrow-left-right', 'size-5') ?></div>
        <div class="font-medium mb-1">Transfert</div>
        <div class="text-xs text-muted-foreground">Envoyez de l'argent à un autre client.</div>
    </a>

    <a href="<?= url_to('envoi_multiple') ?>" class="bg-card border border-border rounded-xl p-5 hover:border-primary hover:shadow-md transition-all">
        <div class="size-10 rounded-lg bg-accent text-accent-foreground grid place-items-center mb-3"><?= icon('users', 'size-5') ?></div>
        <div class="font-medium mb-1">Envoi multiple</div>
        <div class="text-xs text-muted-foreground">Envoyez à plusieurs destinataires en une fois.</div>
    </a>
</div>

<div class="mt-6">
    <a href="<?= url_to('historique') ?>" class="inline-flex items-center gap-2 text-sm font-medium text-primary hover:underline">
        <?= icon('clock', 'size-4') ?> Voir l'historique complet des opérations
    </a>
</div>
<?= $this->endSection() ?>
