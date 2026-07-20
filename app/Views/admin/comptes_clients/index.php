<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-card border border-border rounded-xl p-5">
        <div class="text-xs uppercase tracking-wider text-muted-foreground mb-1">Clients trouvés</div>
        <div class="text-2xl font-bold"><?= count($clients) ?></div>
    </div>
    <div class="bg-card border border-border rounded-xl p-5">
        <div class="text-xs uppercase tracking-wider text-muted-foreground mb-1">Total des soldes <?= $recherche !== null ? 'affichés' : '' ?></div>
        <div class="text-2xl font-bold text-primary"><?= number_format($totalSoldes, 2, ',', ' ') ?> Ar</div>
    </div>
    <div class="bg-card border border-border rounded-xl p-5">
        <div class="text-xs uppercase tracking-wider text-muted-foreground mb-1">Recherche active</div>
        <div class="text-2xl font-bold font-mono"><?= $recherche !== null ? esc($recherche) : '—' ?></div>
    </div>
</div>

<div class="bg-card border border-border rounded-xl overflow-hidden">
    <form class="px-6 py-4 border-b border-border flex items-center gap-3" action="<?= site_url('admin/comptes-clients') ?>" method="get">
        <div class="flex-1 flex items-center gap-2 px-3 py-2 bg-background border border-border rounded-lg">
            <?= icon('search', 'size-4') ?>
            <input type="text" name="recherche" placeholder="Rechercher par numéro de téléphone…" value="<?= esc($recherche ?? '') ?>"
                   class="flex-1 bg-transparent outline-none text-sm">
        </div>
        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">
            Rechercher
        </button>
        <?php if ($recherche !== null): ?>
            <a href="<?= site_url('admin/comptes-clients') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-border text-sm hover:bg-muted transition-colors">
                <?= icon('rotate-ccw', 'size-4') ?> Réinitialiser
            </a>
        <?php endif; ?>
    </form>

    <?php if (empty($clients)): ?>
        <p class="px-6 py-10 text-sm text-muted-foreground text-center">
            Aucun client trouvé<?= $recherche !== null ? ' pour "' . esc($recherche) . '"' : '' ?>.
        </p>
    <?php else: ?>
        <table class="w-full">
            <thead class="bg-background border-b border-border">
                <tr class="text-[11px] font-mono uppercase text-muted-foreground">
                    <th class="px-6 py-3 text-left font-medium">Numéro</th>
                    <th class="px-6 py-3 text-left font-medium">Client depuis</th>
                    <th class="px-6 py-3 text-right font-medium">Solde actuel (Ar)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                <?php foreach ($clients as $client): ?>
                    <tr class="hover:bg-background/50 transition-colors text-sm">
                        <td class="px-6 py-4 font-mono"><?= esc($client['numero_telephone']) ?></td>
                        <td class="px-6 py-4 text-xs text-muted-foreground"><?= esc($client['date_creation']) ?></td>
                        <td class="px-6 py-4 text-right font-mono font-semibold"><?= number_format((float) $client['solde'], 2, ',', ' ') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="px-6 py-3 border-t border-border flex justify-between items-center text-xs text-muted-foreground">
            <span class="font-mono"><?= count($clients) ?> compte(s) affiché(s)</span>
            <span class="font-mono font-semibold text-foreground">Total : <?= number_format($totalSoldes, 2, ',', ' ') ?> Ar</span>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
