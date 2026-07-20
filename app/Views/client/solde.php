<?= $this->extend('client/layout') ?>

<?= $this->section('content') ?>
<div class="max-w-md">
    <div class="bg-foreground text-background rounded-xl p-8 text-center mb-6">
        <div class="text-xs uppercase tracking-widest opacity-60 mb-3">Solde actuel</div>
        <div class="text-4xl font-bold tracking-tight">
            <?= number_format((float) $solde, 2, ',', ' ') ?> <span class="text-lg font-mono opacity-60">Ar</span>
        </div>
        <div class="text-xs font-mono opacity-60 mt-3"><?= esc($numero_telephone) ?></div>
    </div>

    <div class="grid grid-cols-3 gap-3">
        <a href="<?= url_to('depot') ?>" class="bg-card border border-border rounded-xl p-4 text-center hover:border-primary transition-all">
            <?= icon('arrow-down-right', 'size-5 mx-auto mb-1 text-success') ?>
            <div class="text-xs font-medium">Dépôt</div>
        </a>
        <a href="<?= url_to('retrait') ?>" class="bg-card border border-border rounded-xl p-4 text-center hover:border-primary transition-all">
            <?= icon('arrow-up-right', 'size-5 mx-auto mb-1 text-destructive') ?>
            <div class="text-xs font-medium">Retrait</div>
        </a>
        <a href="<?= url_to('transfert') ?>" class="bg-card border border-border rounded-xl p-4 text-center hover:border-primary transition-all">
            <?= icon('arrow-left-right', 'size-5 mx-auto mb-1 text-primary') ?>
            <div class="text-xs font-medium">Transfert</div>
        </a>
    </div>
</div>
<?= $this->endSection() ?>
