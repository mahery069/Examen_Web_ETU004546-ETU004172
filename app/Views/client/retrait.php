<?= $this->extend('client/layout') ?>

<?= $this->section('content') ?>
<div class="max-w-md">
    <div class="bg-card border border-border rounded-xl p-6">
        <div class="size-10 rounded-lg bg-destructive-10 text-destructive grid place-items-center mb-4"><?= icon('arrow-up-right', 'size-5') ?></div>
        <h2 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground mb-4">Montant à retirer</h2>

        <form class="space-y-4" action="<?= url_to('retirer') ?>" method="post">
            <div class="flex flex-col gap-1.5">
                <label for="montant" class="text-xs font-medium text-muted-foreground">Montant (Ar)</label>
                <input type="number" step="0.01" min="0" id="montant" name="montant" placeholder="10000"
                       value="<?= esc(old('montant')) ?>"
                       class="w-full px-3 py-2 rounded-lg border border-input bg-background text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ring">
            </div>
            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">
                <?= icon('check', 'size-4') ?> Retirer
            </button>
        </form>
    </div>

    <?php if ((float) $credit_frais_retrait > 0): ?>
        <div class="mt-4 px-4 py-3 rounded-lg bg-success-10 text-success text-xs text-center">
            Vous bénéficiez d'un crédit de <?= number_format((float) $credit_frais_retrait, 2, ',', ' ') ?> Ar
            sur vos frais de retrait (issu d'un transfert reçu avec frais inclus).
        </div>
    <?php endif; ?>

    <p class="text-xs text-muted-foreground mt-4 text-center">
        Des frais sont automatiquement calculés selon le barème en vigueur et ajoutés au montant retiré.
    </p>
</div>
<?= $this->endSection() ?>
