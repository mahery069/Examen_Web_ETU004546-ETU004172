<?= $this->extend('client/layout') ?>

<?= $this->section('content') ?>
<div class="max-w-2xl">
    <div class="bg-card border border-border rounded-xl overflow-hidden mb-6">
        <table class="w-full">
            <thead class="bg-background border-b border-border">
                <tr class="text-[11px] font-mono uppercase text-muted-foreground">
                    <th class="px-6 py-3 text-left font-medium">Destinataire</th>
                    <th class="px-6 py-3 text-right font-medium">Montant</th>
                    <th class="px-6 py-3 text-right font-medium">Frais transfert</th>
                    <?php if ($inclure_frais_retrait): ?>
                        <th class="px-6 py-3 text-right font-medium">Frais retrait pris en charge</th>
                    <?php endif; ?>
                    <th class="px-6 py-3 text-right font-medium">Sous-total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                <?php foreach ($lignes as $ligne): ?>
                    <tr class="text-sm">
                        <td class="px-6 py-3.5 font-mono"><?= esc($ligne['numero']) ?></td>
                        <td class="px-6 py-3.5 text-right font-mono"><?= number_format($ligne['montant'], 2, ',', ' ') ?> Ar</td>
                        <td class="px-6 py-3.5 text-right font-mono text-muted-foreground"><?= number_format($ligne['frais_transfert'], 2, ',', ' ') ?> Ar</td>
                        <?php if ($inclure_frais_retrait): ?>
                            <td class="px-6 py-3.5 text-right font-mono text-muted-foreground"><?= number_format($ligne['frais_retrait_estime'], 2, ',', ' ') ?> Ar</td>
                        <?php endif; ?>
                        <td class="px-6 py-3.5 text-right font-mono font-semibold"><?= number_format($ligne['sous_total'], 2, ',', ' ') ?> Ar</td>
                        <input type="hidden" name="numero_destinataire[]" value="<?= esc($ligne['numero'], 'attr') ?>" form="form-confirmer-envoi">
                        <input type="hidden" name="montant[]" value="<?= esc((string) $ligne['montant'], 'attr') ?>" form="form-confirmer-envoi">
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="bg-card border border-border rounded-xl p-6 mb-6">
        <dl class="space-y-2 text-sm">
            <div class="flex items-center justify-between">
                <dt class="text-muted-foreground">Nombre de destinataires</dt>
                <dd class="font-medium"><?= count($lignes) ?></dd>
            </div>
            <div class="flex items-center justify-between">
                <dt class="text-muted-foreground">Total des montants envoyés</dt>
                <dd class="font-mono"><?= number_format($total_montant, 2, ',', ' ') ?> Ar</dd>
            </div>
            <div class="flex items-center justify-between">
                <dt class="text-muted-foreground">Total des frais</dt>
                <dd class="font-mono"><?= number_format($total_frais, 2, ',', ' ') ?> Ar</dd>
            </div>
            <div class="flex items-center justify-between pt-2 border-t border-border">
                <dt class="font-medium">Total débité de votre compte</dt>
                <dd class="font-mono font-semibold text-destructive"><?= number_format($total_debit, 2, ',', ' ') ?> Ar</dd>
            </div>
        </dl>
    </div>

    <div class="flex gap-3">
        <a href="<?= url_to('envoi_multiple') ?>" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-border text-sm font-medium hover:bg-background transition-colors">
            Modifier
        </a>
        <form id="form-confirmer-envoi" action="<?= url_to('envoi_multiple_confirmer') ?>" method="post" class="flex-1">
            <input type="hidden" name="inclure_frais_retrait" value="<?= $inclure_frais_retrait ? '1' : '0' ?>">
            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">
                <?= icon('check', 'size-4') ?> Confirmer l'envoi
            </button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
