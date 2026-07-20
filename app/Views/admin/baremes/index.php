<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="bg-card border border-border rounded-xl mb-6">
    <div class="px-6 py-4 border-b border-border">
        <h2 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground">Ajouter une tranche</h2>
    </div>
    <form class="px-6 py-4 flex flex-wrap gap-3 items-end" action="<?= site_url('admin/baremes') ?>" method="post">
        <?= csrf_field() ?>
        <div class="flex flex-col gap-1">
            <label class="text-xs text-muted-foreground">Type d'opération</label>
            <select name="type_operation_id" required class="w-40 px-3 py-1.5 rounded-md border border-input bg-background text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                <option value="">-- Choisir --</option>
                <?php foreach ($types as $type): ?>
                    <option value="<?= (int) $type['id'] ?>" <?= old('type_operation_id') == $type['id'] ? 'selected' : '' ?>><?= esc($type['libelle']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs text-muted-foreground">Montant min (Ar)</label>
            <input type="number" step="0.01" min="0" name="montant_min" value="<?= esc(old('montant_min')) ?>" required
                   class="w-32 px-3 py-1.5 rounded-md border border-input bg-background text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ring">
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs text-muted-foreground">Montant max (Ar)</label>
            <input type="number" step="0.01" min="0" name="montant_max" value="<?= esc(old('montant_max')) ?>" required
                   class="w-32 px-3 py-1.5 rounded-md border border-input bg-background text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ring">
        </div>
        <div class="flex flex-col gap-1">
            <label class="text-xs text-muted-foreground">Frais (Ar)</label>
            <input type="number" step="0.01" min="0" name="frais" value="<?= esc(old('frais')) ?>" required
                   class="w-28 px-3 py-1.5 rounded-md border border-input bg-background text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ring">
        </div>
        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">
            <?= icon('plus', 'size-4') ?> Ajouter
        </button>
    </form>
</div>

<div class="flex gap-2 mb-6 border-b border-border">
    <?php foreach ($groupes as $index => $groupe): ?>
        <button type="button" onclick="showTab(<?= (int) $groupe['type']['id'] ?>)" id="tab-btn-<?= (int) $groupe['type']['id'] ?>"
                class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors <?= $index === 0 ? 'border-primary text-foreground' : 'border-transparent text-muted-foreground hover:text-foreground' ?>">
            <?= esc($groupe['type']['libelle']) ?>
            <span class="ml-2 text-xs font-mono text-muted-foreground"><?= count($groupe['tranches']['tranches']) ?></span>
        </button>
    <?php endforeach; ?>
</div>

<?php foreach ($groupes as $index => $groupe): ?>
    <?php
        $type     = $groupe['type'];
        $tranches = $groupe['tranches']['tranches'];
        $gaps     = $groupe['tranches']['gaps'];
    ?>
    <div id="tab-panel-<?= (int) $type['id'] ?>" class="bg-card border border-border rounded-xl overflow-hidden <?= $index === 0 ? '' : 'hidden' ?>">
        <div class="px-6 py-4 border-b border-border">
            <h2 class="text-sm font-semibold">Barème : <?= esc($type['libelle']) ?></h2>
            <p class="text-xs text-muted-foreground mt-0.5">Chaque tranche définit un frais fixe selon le montant de l'opération.</p>
        </div>

        <?php if (empty($tranches)): ?>
            <p class="px-6 py-8 text-sm text-muted-foreground text-center">Aucune tranche configurée pour ce type d'opération.</p>
        <?php else: ?>
            <table class="w-full">
                <thead class="bg-background border-b border-border">
                    <tr class="text-[11px] font-mono uppercase text-muted-foreground">
                        <th class="px-6 py-3 text-left font-medium">Montant min (Ar)</th>
                        <th class="px-6 py-3 text-left font-medium">Montant max (Ar)</th>
                        <th class="px-6 py-3 text-left font-medium">Frais (Ar)</th>
                        <th class="px-6 py-3 w-24"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <?php foreach ($tranches as $tIndex => $t): ?>
                        <tr id="b-view-<?= (int) $t['id'] ?>" class="hover:bg-background/60 transition-colors">
                            <td class="px-6 py-3 font-mono text-sm"><?= esc($t['montant_min']) ?></td>
                            <td class="px-6 py-3 font-mono text-sm"><?= esc($t['montant_max']) ?></td>
                            <td class="px-6 py-3 font-mono text-sm"><?= esc($t['frais']) ?></td>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-1.5 justify-end">
                                    <button type="button" onclick="toggleBaremeEdit(<?= (int) $t['id'] ?>)" class="size-8 rounded-md hover:bg-muted grid place-items-center text-muted-foreground hover:text-primary transition-colors">
                                        <?= icon('pencil', 'size-4') ?>
                                    </button>
                                    <form action="<?= site_url('admin/baremes/' . (int) $t['id'] . '/delete') ?>" method="post" onsubmit="return confirm('Supprimer cette tranche ?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="size-8 rounded-md hover:bg-destructive-10 grid place-items-center text-muted-foreground hover:text-destructive transition-colors">
                                            <?= icon('trash-2', 'size-4') ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <tr id="b-edit-<?= (int) $t['id'] ?>" class="hidden bg-secondary/60">
                            <td colspan="4" class="px-6 py-3">
                                <form class="flex flex-wrap items-end gap-3" action="<?= site_url('admin/baremes/' . (int) $t['id'] . '/update') ?>" method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="type_operation_id" value="<?= (int) $t['type_operation_id'] ?>">
                                    <div class="flex flex-col gap-1">
                                        <label class="text-xs text-muted-foreground">Montant min</label>
                                        <input type="number" step="0.01" min="0" name="montant_min" value="<?= esc($t['montant_min']) ?>" required
                                               class="w-28 px-3 py-1.5 rounded-md border border-input bg-background text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ring">
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <label class="text-xs text-muted-foreground">Montant max</label>
                                        <input type="number" step="0.01" min="0" name="montant_max" value="<?= esc($t['montant_max']) ?>" required
                                               class="w-28 px-3 py-1.5 rounded-md border border-input bg-background text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ring">
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <label class="text-xs text-muted-foreground">Frais</label>
                                        <input type="number" step="0.01" min="0" name="frais" value="<?= esc($t['frais']) ?>" required
                                               class="w-28 px-3 py-1.5 rounded-md border border-input bg-background text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ring">
                                    </div>
                                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">
                                        <?= icon('save', 'size-4') ?> Enregistrer
                                    </button>
                                    <button type="button" onclick="toggleBaremeEdit(<?= (int) $t['id'] ?>)" class="px-4 py-2 rounded-lg border border-border text-sm hover:bg-muted transition-colors">
                                        Annuler
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php foreach ($gaps as $gap): ?>
                            <?php if ($gap['apres_tranche_index'] === $tIndex): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-2.5 bg-destructive-10 text-destructive text-xs font-medium">
                                        <span class="inline-flex items-center gap-1.5"><?= icon('alert-triangle', 'size-3.5') ?> Trou détecté : aucune tranche ne couvre <?= esc($gap['min']) ?> — <?= esc($gap['max']) ?> Ar</span>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<script>
    function showTab(id) {
        document.querySelectorAll('[id^="tab-panel-"]').forEach(function (el) { el.classList.add('hidden'); });
        document.querySelectorAll('[id^="tab-btn-"]').forEach(function (el) {
            el.classList.remove('border-primary', 'text-foreground');
            el.classList.add('border-transparent', 'text-muted-foreground');
        });
        document.getElementById('tab-panel-' + id).classList.remove('hidden');
        var btn = document.getElementById('tab-btn-' + id);
        btn.classList.remove('border-transparent', 'text-muted-foreground');
        btn.classList.add('border-primary', 'text-foreground');
    }

    function toggleBaremeEdit(id) {
        document.getElementById('b-view-' + id).classList.toggle('hidden');
        document.getElementById('b-edit-' + id).classList.toggle('hidden');
    }
</script>
<?= $this->endSection() ?>
