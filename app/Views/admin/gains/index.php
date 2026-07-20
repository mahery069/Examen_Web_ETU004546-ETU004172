<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="bg-foreground text-background rounded-xl p-6">
        <div class="text-xs uppercase tracking-widest opacity-60 mb-2">
            Gains internes <?= $typeOperationId === null ? '(tous types)' : '(type sélectionné)' ?>
        </div>
        <div class="text-4xl font-bold tracking-tight">
            <?= number_format($totalInterne, 2, ',', ' ') ?> <span class="text-lg font-mono opacity-60">Ar</span>
        </div>
        <p class="text-xs opacity-60 mt-2">Frais habituels du barème (dépôts, retraits, transferts).</p>
    </div>

    <div class="bg-accent text-accent-foreground rounded-xl p-6">
        <div class="text-xs uppercase tracking-widest opacity-70 mb-2">
            Gains autres opérateurs
        </div>
        <div class="text-4xl font-bold tracking-tight">
            <?= number_format($totalExterne, 2, ',', ' ') ?> <span class="text-lg font-mono opacity-70">Ar</span>
        </div>
        <p class="text-xs opacity-70 mt-2">Commission inter-opérateur perçue sur les transferts sortants vers un autre opérateur.</p>
    </div>
</div>

<div class="bg-card border border-border rounded-xl mb-6">
    <div class="px-6 py-4 border-b border-border">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground">Filtrer les gains internes par type d'opération</h2>
    </div>
    <form class="px-6 py-4 flex flex-wrap gap-3 items-end" action="<?= site_url('admin/gains') ?>" method="get">
        <div class="flex flex-col gap-1">
            <label class="text-xs text-muted-foreground">Type d'opération</label>
            <select name="type_operation_id" class="w-52 px-3 py-1.5 rounded-md border border-input bg-background text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                <option value="" <?= $typeOperationId === null ? 'selected' : '' ?>>Tous les types</option>
                <?php foreach ($types as $type): ?>
                    <option value="<?= (int) $type['id'] ?>" <?= $typeOperationId === (int) $type['id'] ? 'selected' : '' ?>><?= esc($type['libelle']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">
            <?= icon('filter', 'size-4') ?> Filtrer
        </button>
        <?php if ($typeOperationId !== null): ?>
            <a href="<?= site_url('admin/gains') ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-border text-sm hover:bg-muted transition-colors">
                <?= icon('rotate-ccw', 'size-4') ?> Réinitialiser
            </a>
        <?php endif; ?>
        <p class="text-xs text-muted-foreground ml-auto">Ce filtre ne s'applique qu'au bloc "Gains internes" ci-dessous ; la commission externe concerne uniquement les transferts.</p>
    </form>
</div>

<div class="bg-card border border-border rounded-xl overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-border">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground">Gains internes — frais perçus par type d'opération</h2>
    </div>

    <?php if (empty($recapInterne)): ?>
        <p class="px-6 py-8 text-sm text-muted-foreground text-center">Aucune donnée disponible.</p>
    <?php else: ?>
        <table class="w-full">
            <thead class="bg-background border-b border-border">
                <tr class="text-[11px] font-mono uppercase text-muted-foreground">
                    <th class="px-6 py-3 text-left font-medium">Type d'opération</th>
                    <th class="px-6 py-3 text-right font-medium">Nombre d'opérations</th>
                    <th class="px-6 py-3 text-right font-medium">Total des frais perçus (Ar)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                <?php foreach ($recapInterne as $ligne): ?>
                    <tr class="hover:bg-background/60 transition-colors text-sm">
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2 py-0.5 rounded-full <?=
                                $ligne['code'] === 'depot' ? 'bg-success-10 text-success' :
                                ($ligne['code'] === 'retrait' ? 'bg-primary-10 text-primary' : 'bg-accent text-accent-foreground')
                            ?>"><?= esc($ligne['libelle']) ?></span>
                        </td>
                        <td class="px-6 py-3.5 text-right font-mono text-muted-foreground"><?= (int) $ligne['nb_operations'] ?></td>
                        <td class="px-6 py-3.5 text-right font-mono font-semibold"><?= number_format((float) $ligne['total_frais'], 2, ',', ' ') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="px-6 py-4 border-t border-border bg-background/50 flex justify-between items-center">
        <span class="text-sm font-medium"><?= $typeOperationId === null ? 'Total gains internes (tous types confondus)' : 'Total gains internes pour le type sélectionné' ?></span>
        <span class="text-xl font-bold font-mono"><?= number_format($totalInterne, 2, ',', ' ') ?> Ar</span>
    </div>
</div>

<div class="bg-card border border-border rounded-xl overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-border">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground">Gains autres opérateurs — commission par opérateur externe</h2>
        <p class="text-xs text-muted-foreground mt-0.5">Uniquement les transferts sortants vers un numéro dont le préfixe appartient à un autre opérateur.</p>
    </div>

    <?php if (empty($recapExterne)): ?>
        <p class="px-6 py-8 text-sm text-muted-foreground text-center">Aucun transfert vers un autre opérateur pour le moment.</p>
    <?php else: ?>
        <table class="w-full">
            <thead class="bg-background border-b border-border">
                <tr class="text-[11px] font-mono uppercase text-muted-foreground">
                    <th class="px-6 py-3 text-left font-medium">Opérateur externe</th>
                    <th class="px-6 py-3 text-right font-medium">Nombre de transferts</th>
                    <th class="px-6 py-3 text-right font-medium">Commission perçue (Ar)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                <?php foreach ($recapExterne as $ligne): ?>
                    <tr class="hover:bg-background/60 transition-colors text-sm">
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center gap-2">
                                <span class="size-7 rounded-md bg-secondary-10 text-muted-foreground grid place-items-center font-mono text-xs font-semibold"><?= esc($ligne['prefixe']) ?></span>
                                <?= esc($ligne['libelle'] ?: '—') ?>
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-right font-mono text-muted-foreground"><?= (int) $ligne['nb_transferts'] ?></td>
                        <td class="px-6 py-3.5 text-right font-mono font-semibold"><?= number_format((float) $ligne['total_commission'], 2, ',', ' ') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="px-6 py-4 border-t border-border bg-background/50 flex justify-between items-center">
        <span class="text-sm font-medium">Total commission autres opérateurs</span>
        <span class="text-xl font-bold font-mono"><?= number_format($totalExterne, 2, ',', ' ') ?> Ar</span>
    </div>
</div>

<div class="bg-card border border-border rounded-xl px-6 py-4 flex justify-between items-center">
    <span class="text-sm font-medium">Total global (gains internes + commission autres opérateurs)</span>
    <span class="text-xl font-bold font-mono"><?= number_format($totalGlobal, 2, ',', ' ') ?> Ar</span>
</div>
<?= $this->endSection() ?>
