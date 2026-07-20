<?= $this->extend('client/layout') ?>

<?= $this->section('content') ?>
<div class="max-w-md">
    <div class="bg-card border border-border rounded-xl p-6">
        <div class="size-10 rounded-lg bg-accent text-accent-foreground grid place-items-center mb-4"><?= icon('receipt', 'size-5') ?></div>
        <h2 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground mb-4">Récapitulatif du transfert</h2>

        <dl class="space-y-3 text-sm mb-6">
            <div class="flex items-center justify-between">
                <dt class="text-muted-foreground">Destinataire</dt>
                <dd class="font-mono font-medium"><?= esc($numero_destinataire) ?></dd>
            </div>
            <div class="flex items-center justify-between">
                <dt class="text-muted-foreground">Montant envoyé (net reçu)</dt>
                <dd class="font-mono font-medium"><?= number_format($montant, 2, ',', ' ') ?> Ar</dd>
            </div>
            <div class="flex items-center justify-between">
                <dt class="text-muted-foreground">Frais de transfert</dt>
                <dd class="font-mono"><?= number_format($frais_transfert, 2, ',', ' ') ?> Ar</dd>
            </div>
            <?php if ($inclure_frais_retrait): ?>
                <div class="flex items-center justify-between">
                    <dt class="text-muted-foreground">Frais de retrait pris en charge</dt>
                    <dd class="font-mono"><?= number_format($frais_retrait_estime, 2, ',', ' ') ?> Ar</dd>
                </div>
            <?php endif; ?>
            <div class="flex items-center justify-between pt-3 border-t border-border">
                <dt class="font-medium">Total débité de votre compte</dt>
                <dd class="font-mono font-semibold text-destructive"><?= number_format($total_debit, 2, ',', ' ') ?> Ar</dd>
            </div>
        </dl>

        <?php if ($inclure_frais_retrait): ?>
            <div class="mb-6 px-4 py-3 rounded-lg bg-success-10 text-success text-xs">
                Le destinataire recevra <?= number_format($montant, 2, ',', ' ') ?> Ar et pourra les retirer
                sans frais (à hauteur de <?= number_format($frais_retrait_estime, 2, ',', ' ') ?> Ar prépayés).
            </div>
        <?php else: ?>
            <div class="mb-6 px-4 py-3 rounded-lg bg-accent text-accent-foreground text-xs">
                Le destinataire recevra <?= number_format($montant, 2, ',', ' ') ?> Ar net. Ses propres frais de
                retrait, s'il retire cet argent plus tard, resteront à sa charge.
            </div>
        <?php endif; ?>

        <div class="flex gap-3">
            <a href="<?= url_to('transfert') ?>" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-border text-sm font-medium hover:bg-background transition-colors">
                Modifier
            </a>
            <form action="<?= url_to('transferer') ?>" method="post" class="flex-1">
                <input type="hidden" name="numero_destinataire" value="<?= esc($numero_destinataire, 'attr') ?>">
                <input type="hidden" name="montant" value="<?= esc((string) $montant, 'attr') ?>">
                <input type="hidden" name="inclure_frais_retrait" value="<?= $inclure_frais_retrait ? '1' : '0' ?>">
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">
                    <?= icon('check', 'size-4') ?> Confirmer
                </button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
