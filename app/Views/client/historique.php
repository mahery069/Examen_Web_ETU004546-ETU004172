<?= $this->extend('client/layout') ?>

<?= $this->section('content') ?>
<div class="bg-card border border-border rounded-xl overflow-hidden">
    <?php if (empty($lignes)): ?>
        <p class="px-6 py-10 text-sm text-muted-foreground text-center">Aucune opération pour le moment.</p>
    <?php else: ?>
        <table class="w-full">
            <thead class="bg-background border-b border-border">
                <tr class="text-[11px] font-mono uppercase text-muted-foreground">
                    <th class="px-6 py-3 text-left font-medium">Type</th>
                    <th class="px-6 py-3 text-left font-medium">Contact</th>
                    <th class="px-6 py-3 text-right font-medium">Montant</th>
                    <th class="px-6 py-3 text-right font-medium">Frais</th>
                    <th class="px-6 py-3 text-right font-medium">Commission</th>
                    <th class="px-6 py-3 text-right font-medium">Effet sur le solde</th>
                    <th class="px-6 py-3 text-right font-medium">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                <?php foreach ($lignes as $ligne): ?>
                    <tr class="hover:bg-background/60 transition-colors text-sm">
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2 py-0.5 rounded-full <?=
                                str_contains($ligne['libelle'], 'Dépôt') ? 'bg-success-10 text-success' :
                                (str_contains($ligne['libelle'], 'Retrait') ? 'bg-destructive-10 text-destructive' : 'bg-accent text-accent-foreground')
                            ?>"><?= esc($ligne['libelle']) ?></span>
                        </td>
                        <td class="px-6 py-3.5 font-mono text-xs"><?= $ligne['contrepartie'] !== null ? esc($ligne['contrepartie']) : '—' ?></td>
                        <td class="px-6 py-3.5 text-right font-mono"><?= number_format($ligne['montant'], 2, ',', ' ') ?> Ar</td>
                        <td class="px-6 py-3.5 text-right font-mono text-xs text-muted-foreground"><?= number_format($ligne['frais'], 2, ',', ' ') ?> Ar</td>
                        <td class="px-6 py-3.5 text-right font-mono text-xs text-muted-foreground"><?= $ligne['commission'] > 0 ? number_format($ligne['commission'], 2, ',', ' ') . ' Ar' : '—' ?></td>
                        <td class="px-6 py-3.5 text-right font-mono font-semibold <?= $ligne['montant_signe'] >= 0 ? 'text-success' : 'text-destructive' ?>">
                            <?= $ligne['montant_signe'] >= 0 ? '+' : '' ?><?= number_format($ligne['montant_signe'], 2, ',', ' ') ?> Ar
                        </td>
                        <td class="px-6 py-3.5 text-right text-xs text-muted-foreground"><?= esc($ligne['date']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
