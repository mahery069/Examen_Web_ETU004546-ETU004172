<?= $this->extend('client/layout') ?>

<?= $this->section('content') ?>
<div class="max-w-md">
    <div class="bg-card border border-border rounded-xl p-6">
        <div class="size-10 rounded-lg bg-accent text-accent-foreground grid place-items-center mb-4"><?= icon('arrow-left-right', 'size-5') ?></div>
        <h2 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground mb-4">Transférer de l'argent</h2>

        <form class="space-y-4" action="<?= url_to('transferer') ?>" method="post">
            <div class="flex flex-col gap-1.5">
                <label for="numero_destinataire" class="text-xs font-medium text-muted-foreground">Numéro du destinataire</label>
                <input type="text" id="numero_destinataire" name="numero_destinataire" placeholder="0331234567"
                       value="<?= esc(old('numero_destinataire')) ?>"
                       class="w-full px-3 py-2 rounded-lg border border-input bg-background text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ring">
            </div>
            <div class="flex flex-col gap-1.5">
                <label for="montant" class="text-xs font-medium text-muted-foreground">Montant (Ar)</label>
                <input type="number" step="0.01" min="0" id="montant" name="montant" placeholder="10000"
                       value="<?= esc(old('montant')) ?>"
                       class="w-full px-3 py-2 rounded-lg border border-input bg-background text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ring">
            </div>
            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">
                <?= icon('check', 'size-4') ?> Transférer
            </button>
        </form>
    </div>

    <p class="text-xs text-muted-foreground mt-4 text-center">
        Des frais sont automatiquement calculés selon le barème en vigueur et débités de votre compte, en plus du montant transféré.
    </p>
</div>
<?= $this->endSection() ?>
