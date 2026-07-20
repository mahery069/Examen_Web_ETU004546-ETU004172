<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
    <div class="bg-card border border-border rounded-xl p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Gains totaux</span>
            <?= icon('wallet', 'size-4 text-muted-foreground') ?>
        </div>
        <div class="flex items-baseline gap-1.5">
            <span class="text-2xl font-bold tracking-tight"><?= number_format($totalGains, 2, ',', ' ') ?></span>
            <span class="text-sm text-muted-foreground font-mono">Ar</span>
        </div>
    </div>
    <div class="bg-card border border-border rounded-xl p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Total des soldes clients</span>
            <?= icon('arrow-left-right', 'size-4 text-muted-foreground') ?>
        </div>
        <div class="flex items-baseline gap-1.5">
            <span class="text-2xl font-bold tracking-tight"><?= number_format($totalSoldes, 2, ',', ' ') ?></span>
            <span class="text-sm text-muted-foreground font-mono">Ar</span>
        </div>
    </div>
    <div class="bg-card border border-border rounded-xl p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Opérations enregistrées</span>
            <?= icon('trending-up', 'size-4 text-muted-foreground') ?>
        </div>
        <div class="flex items-baseline gap-1.5">
            <span class="text-2xl font-bold tracking-tight"><?= $totalOperations ?></span>
        </div>
    </div>
    <div class="bg-card border border-border rounded-xl p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-medium uppercase tracking-wider text-muted-foreground">Comptes clients</span>
            <?= icon('users', 'size-4 text-muted-foreground') ?>
        </div>
        <div class="flex items-baseline gap-1.5">
            <span class="text-2xl font-bold tracking-tight"><?= $totalClients ?></span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2 bg-card border border-border rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-border flex justify-between items-center">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground">Opérations récentes</h2>
            <a href="<?= site_url('admin/gains') ?>" class="text-xs font-medium text-primary hover:underline">Voir les gains</a>
        </div>

        <?php if (empty($recentOperations)): ?>
            <p class="px-6 py-10 text-sm text-muted-foreground text-center">
                Aucune opération enregistrée pour le moment (le côté client n'a pas encore été utilisé).
            </p>
        <?php else: ?>
            <table class="w-full">
                <thead class="bg-background border-b border-border">
                    <tr class="text-[11px] font-mono uppercase text-muted-foreground">
                        <th class="px-6 py-3 text-left font-medium">Type</th>
                        <th class="px-6 py-3 text-left font-medium">Client</th>
                        <th class="px-6 py-3 text-right font-medium">Montant</th>
                        <th class="px-6 py-3 text-right font-medium">Frais</th>
                        <th class="px-6 py-3 text-right font-medium">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <?php foreach ($recentOperations as $op): ?>
                        <tr class="hover:bg-background transition-colors text-sm">
                            <td class="px-6 py-3.5">
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2 py-0.5 rounded-full <?=
                                    $op['code'] === 'depot' ? 'bg-success-10 text-success' :
                                    ($op['code'] === 'retrait' ? 'bg-primary-10 text-primary' : 'bg-accent text-accent-foreground')
                                ?>"><?= esc($op['libelle']) ?></span>
                            </td>
                            <td class="px-6 py-3.5 font-mono text-xs"><?= esc($op['numero_telephone']) ?></td>
                            <td class="px-6 py-3.5 text-right font-mono font-medium"><?= number_format((float) $op['montant'], 2, ',', ' ') ?> Ar</td>
                            <td class="px-6 py-3.5 text-right font-mono text-xs text-muted-foreground"><?= number_format((float) $op['frais'], 2, ',', ' ') ?> Ar</td>
                            <td class="px-6 py-3.5 text-right text-xs text-muted-foreground"><?= esc($op['date_operation']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="bg-card border border-border rounded-xl p-6">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground mb-6">Répartition des gains</h2>
        <div class="divide-y divide-border">
            <?php foreach ($recap as $ligne): ?>
                <div class="flex justify-between items-center py-3 first:pt-0">
                    <span class="text-sm font-medium"><?= esc($ligne['libelle']) ?></span>
                    <span class="font-mono text-sm text-muted-foreground"><?= number_format((float) $ligne['total_frais'], 2, ',', ' ') ?> Ar</span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-6 pt-6 border-t border-border">
            <div class="text-xs text-muted-foreground mb-1">Total collecté</div>
            <div class="text-3xl font-bold tracking-tight"><?= number_format($totalGains, 2, ',', ' ') ?> <span class="text-sm font-mono text-muted-foreground">Ar</span></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
