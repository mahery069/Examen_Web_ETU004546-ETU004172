<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="bg-foreground text-background rounded-xl p-6 mb-6">
    <div class="text-xs uppercase tracking-widest opacity-60 mb-2">
        Montant net total à reverser aux autres opérateurs
    </div>
    <div class="text-4xl font-bold tracking-tight">
        <?= number_format($totalMontantDu, 2, ',', ' ') ?> <span class="text-lg font-mono opacity-60">Ar</span>
    </div>
    <p class="text-xs opacity-60 mt-2">
        <?= (int) $totalTransferts ?> transfert(s) sortant(s) vers d'autres opérateurs — montant transféré, hors frais et commission gardés par notre opérateur.
    </p>
</div>

<div class="bg-card border border-border rounded-xl overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-border">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground">Regroupement par opérateur externe</h2>
        <p class="text-xs text-muted-foreground mt-0.5">Somme des montants dus à chaque opérateur (à reverser pour les fonds reçus par leurs clients).</p>
    </div>

    <?php if (empty($recap)): ?>
        <p class="px-6 py-8 text-sm text-muted-foreground text-center">Aucun transfert vers un autre opérateur pour le moment.</p>
    <?php else: ?>
        <table class="w-full">
            <thead class="bg-background border-b border-border">
                <tr class="text-[11px] font-mono uppercase text-muted-foreground">
                    <th class="px-6 py-3 text-left font-medium">Opérateur externe</th>
                    <th class="px-6 py-3 text-right font-medium">Nombre de transferts</th>
                    <th class="px-6 py-3 text-right font-medium">Commission retenue (Ar)</th>
                    <th class="px-6 py-3 text-right font-medium">Montant net dû (Ar)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                <?php foreach ($recap as $ligne): ?>
                    <tr class="hover:bg-background/60 transition-colors text-sm">
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center gap-2">
                                <span class="size-7 rounded-md bg-secondary-10 text-muted-foreground grid place-items-center font-mono text-xs font-semibold"><?= esc($ligne['prefixe']) ?></span>
                                <?= esc($ligne['libelle'] ?: '—') ?>
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-right font-mono text-muted-foreground"><?= (int) $ligne['nb_transferts'] ?></td>
                        <td class="px-6 py-3.5 text-right font-mono text-muted-foreground"><?= number_format((float) $ligne['total_commission'], 2, ',', ' ') ?></td>
                        <td class="px-6 py-3.5 text-right font-mono font-semibold"><?= number_format((float) $ligne['montant_du'], 2, ',', ' ') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="px-6 py-4 border-t border-border bg-background/50 flex justify-between items-center">
        <span class="text-sm font-medium">Total montant net dû (tous opérateurs confondus)</span>
        <span class="text-xl font-bold font-mono"><?= number_format($totalMontantDu, 2, ',', ' ') ?> Ar</span>
    </div>
</div>

<div class="bg-card border border-border rounded-xl mb-6">
    <div class="px-6 py-4 border-b border-border">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground">Filtrer le détail par opérateur externe</h2>
    </div>
    <form class="px-6 py-4 flex flex-wrap gap-3 items-end" action="<?= site_url('admin/reglements') ?>" method="get">
        <div class="flex flex-col gap-1">
            <label class="text-xs text-muted-foreground">Opérateur externe</label>
            <select name="prefixe_id" class="w-52 px-3 py-1.5 rounded-md border border-input bg-background text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                <option value="" <?= $prefixeExterneId === null ? 'selected' : '' ?>>Tous les opérateurs</option>
                <?php foreach ($operateursExternes as $operateur): ?>
                    <option value="<?= (int) $operateur['id'] ?>" <?= $prefixeExterneId === (int) $operateur['id'] ? 'selected' : '' ?>>
                        <?= esc($operateur['prefixe']) ?> — <?= esc($operateur['libelle'] ?: '—') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">
            <?= icon('filter', 'size-4') ?> Filtrer
        </button>
        <?php if ($prefixeExterneId !== null): ?>
            <a href="<?= site_url('admin/reglements') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-border text-sm hover:bg-muted transition-colors">
                <?= icon('rotate-ccw', 'size-4') ?> Réinitialiser
            </a>
        <?php endif; ?>
    </form>
</div>

<div class="bg-card border border-border rounded-xl overflow-hidden">
    <div class="px-6 py-4 border-b border-border">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground">Table de réconciliation — détail par transfert</h2>
        <p class="text-xs text-muted-foreground mt-0.5">Pour chaque transfert sortant, le montant net dû à l'opérateur externe correspondant.</p>
    </div>

    <?php if (empty($detail)): ?>
        <p class="px-6 py-8 text-sm text-muted-foreground text-center">Aucun transfert vers un autre opérateur pour le moment.</p>
    <?php else: ?>
        <table class="w-full">
            <thead class="bg-background border-b border-border">
                <tr class="text-[11px] font-mono uppercase text-muted-foreground">
                    <th class="px-6 py-3 text-left font-medium">Date</th>
                    <th class="px-6 py-3 text-left font-medium">Expéditeur</th>
                    <th class="px-6 py-3 text-left font-medium">Destinataire</th>
                    <th class="px-6 py-3 text-left font-medium">Opérateur</th>
                    <th class="px-6 py-3 text-right font-medium">Commission (Ar)</th>
                    <th class="px-6 py-3 text-right font-medium">Montant net dû (Ar)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                <?php foreach ($detail as $ligne): ?>
                    <tr class="hover:bg-background/60 transition-colors text-sm">
                        <td class="px-6 py-3.5 text-xs text-muted-foreground"><?= esc($ligne['date_operation']) ?></td>
                        <td class="px-6 py-3.5 font-mono text-xs"><?= esc($ligne['numero_expediteur']) ?></td>
                        <td class="px-6 py-3.5 font-mono text-xs"><?= esc($ligne['numero_destinataire']) ?></td>
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2 py-0.5 rounded-full bg-secondary-10 text-muted-foreground">
                                <?= esc($ligne['prefixe']) ?> — <?= esc($ligne['libelle'] ?: '—') ?>
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-right font-mono text-muted-foreground"><?= number_format((float) $ligne['commission'], 2, ',', ' ') ?></td>
                        <td class="px-6 py-3.5 text-right font-mono font-semibold"><?= number_format((float) $ligne['montant'], 2, ',', ' ') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
