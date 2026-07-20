<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-card border border-border rounded-xl">
        <div class="px-6 py-4 border-b border-border">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground">Préfixes autorisés</h2>
        </div>

        <?php
        $prefixesInternes = array_filter($prefixes, fn($p) => (bool) $p['is_internal']);
        $prefixesExternes = array_filter($prefixes, fn($p) => !(bool) $p['is_internal']);
        ?>

        <?php if (empty($prefixes)): ?>
            <p class="px-6 py-8 text-sm text-muted-foreground text-center">Aucun préfixe configuré pour le moment.</p>
        <?php else: ?>
            <div class="divide-y divide-border">
                <?php if (!empty($prefixesInternes)): ?>
                    <div class="px-6 py-3 bg-primary-5 border-b border-border">
                        <h3 class="text-xs font-semibold text-primary uppercase tracking-wider">Préfixes internes (notre opérateur)</h3>
                    </div>
                    <?php foreach ($prefixesInternes as $p): ?>
                        <div class="px-6 py-4 flex items-center justify-between" id="view-row-<?= (int) $p['id'] ?>">
                            <div class="flex items-center gap-4">
                                <div class="size-10 rounded-lg bg-primary-10 text-primary grid place-items-center font-mono font-semibold">
                                    <?= esc($p['prefixe']) ?>
                                </div>
                                <div>
                                    <div class="font-medium"><?= esc($p['libelle'] ?: '—') ?></div>
                                    <div class="text-xs text-muted-foreground font-mono">Ajouté le <?= esc($p['date_creation']) ?></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2 py-1 rounded-full bg-primary-10 text-primary">
                                    Interne
                                </span>
                                <button type="button" onclick="toggleEdit(<?= (int) $p['id'] ?>)" class="size-8 rounded-md hover:bg-muted grid place-items-center text-muted-foreground hover:text-primary transition-colors">
                                    <?= icon('pencil', 'size-4') ?>
                                </button>
                                <form action="<?= site_url('admin/prefixes/' . (int) $p['id'] . '/delete') ?>" method="post" onsubmit="return confirm('Supprimer le préfixe <?= esc($p['prefixe'], 'js') ?> ?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="size-8 rounded-md hover:bg-destructive-10 grid place-items-center text-muted-foreground hover:text-destructive transition-colors">
                                        <?= icon('trash-2', 'size-4') ?>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div id="edit-row-<?= (int) $p['id'] ?>" class="hidden px-6 py-4 bg-secondary/60 border-t border-border">
                            <form class="flex flex-wrap items-end gap-3" action="<?= site_url('admin/prefixes/' . (int) $p['id'] . '/update') ?>" method="post">
                                <?= csrf_field() ?>
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs text-muted-foreground">Préfixe</label>
                                    <input type="text" name="prefixe" maxlength="3" pattern="\d{3}" value="<?= esc($p['prefixe']) ?>" required
                                           class="w-24 px-3 py-1.5 rounded-md border border-input bg-background text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ring">
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs text-muted-foreground">Libellé</label>
                                    <input type="text" name="libelle" maxlength="50" value="<?= esc($p['libelle']) ?>"
                                           class="w-48 px-3 py-1.5 rounded-md border border-input bg-background text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs text-muted-foreground">Type</label>
                                    <select name="is_internal" class="px-3 py-1.5 rounded-md border border-input bg-background text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                                        <option value="1" <?= (bool) $p['is_internal'] ? 'selected' : '' ?>>Interne</option>
                                        <option value="0" <?= !(bool) $p['is_internal'] ? 'selected' : '' ?>>Externe</option>
                                    </select>
                                </div>
                                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">
                                    <?= icon('save', 'size-4') ?> Enregistrer
                                </button>
                                <button type="button" onclick="toggleEdit(<?= (int) $p['id'] ?>)" class="px-4 py-2 rounded-lg border border-border text-sm hover:bg-muted transition-colors">
                                    Annuler
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!empty($prefixesExternes)): ?>
                    <div class="px-6 py-3 bg-secondary-5 border-b border-border">
                        <h3 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Préfixes externes (autres opérateurs)</h3>
                    </div>
                    <?php foreach ($prefixesExternes as $p): ?>
                        <div class="px-6 py-4 flex items-center justify-between" id="view-row-<?= (int) $p['id'] ?>">
                            <div class="flex items-center gap-4">
                                <div class="size-10 rounded-lg bg-secondary-10 text-muted-foreground grid place-items-center font-mono font-semibold">
                                    <?= esc($p['prefixe']) ?>
                                </div>
                                <div>
                                    <div class="font-medium"><?= esc($p['libelle'] ?: '—') ?></div>
                                    <div class="text-xs text-muted-foreground font-mono">Ajouté le <?= esc($p['date_creation']) ?></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2 py-1 rounded-full bg-secondary-10 text-muted-foreground">
                                    Externe
                                </span>
                                <button type="button" onclick="toggleEdit(<?= (int) $p['id'] ?>)" class="size-8 rounded-md hover:bg-muted grid place-items-center text-muted-foreground hover:text-primary transition-colors">
                                    <?= icon('pencil', 'size-4') ?>
                                </button>
                                <form action="<?= site_url('admin/prefixes/' . (int) $p['id'] . '/delete') ?>" method="post" onsubmit="return confirm('Supprimer le préfixe <?= esc($p['prefixe'], 'js') ?> ?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="size-8 rounded-md hover:bg-destructive-10 grid place-items-center text-muted-foreground hover:text-destructive transition-colors">
                                        <?= icon('trash-2', 'size-4') ?>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div id="edit-row-<?= (int) $p['id'] ?>" class="hidden px-6 py-4 bg-secondary/60 border-t border-border">
                            <form class="flex flex-wrap items-end gap-3" action="<?= site_url('admin/prefixes/' . (int) $p['id'] . '/update') ?>" method="post">
                                <?= csrf_field() ?>
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs text-muted-foreground">Préfixe</label>
                                    <input type="text" name="prefixe" maxlength="3" pattern="\d{3}" value="<?= esc($p['prefixe']) ?>" required
                                           class="w-24 px-3 py-1.5 rounded-md border border-input bg-background text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ring">
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs text-muted-foreground">Libellé</label>
                                    <input type="text" name="libelle" maxlength="50" value="<?= esc($p['libelle']) ?>"
                                           class="w-48 px-3 py-1.5 rounded-md border border-input bg-background text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs text-muted-foreground">Type</label>
                                    <select name="is_internal" class="px-3 py-1.5 rounded-md border border-input bg-background text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                                        <option value="1" <?= (bool) $p['is_internal'] ? 'selected' : '' ?>>Interne</option>
                                        <option value="0" <?= !(bool) $p['is_internal'] ? 'selected' : '' ?>>Externe</option>
                                    </select>
                                </div>
                                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">
                                    <?= icon('save', 'size-4') ?> Enregistrer
                                </button>
                                <button type="button" onclick="toggleEdit(<?= (int) $p['id'] ?>)" class="px-4 py-2 rounded-lg border border-border text-sm hover:bg-muted transition-colors">
                                    Annuler
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="px-6 py-4 border-t border-border bg-background/50 flex flex-wrap gap-3 items-end">
            <form class="flex flex-wrap gap-3 items-end flex-1" action="<?= site_url('admin/prefixes') ?>" method="post">
                <?= csrf_field() ?>
                <div class="flex-1 min-w-[140px] flex items-center gap-2 px-3 py-2 bg-card border border-border rounded-lg">
                    <span class="text-muted-foreground font-mono text-sm">+261</span>
                    <input type="text" name="prefixe" maxlength="3" pattern="\d{3}" placeholder="ex. 034" value="<?= esc(old('prefixe')) ?>" required
                           class="flex-1 bg-transparent outline-none text-sm font-mono placeholder:text-muted-foreground/50">
                </div>
                <div class="flex-1 min-w-[160px] px-3 py-2 bg-card border border-border rounded-lg">
                    <input type="text" name="libelle" maxlength="50" placeholder="Libellé (ex. Telma)" value="<?= esc(old('libelle')) ?>"
                           class="w-full bg-transparent outline-none text-sm placeholder:text-muted-foreground/50">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs text-muted-foreground">Type</label>
                    <select name="is_internal" class="px-3 py-1.5 rounded-md border border-input bg-background text-sm focus:outline-none focus:ring-2 focus:ring-ring">
                        <option value="1" selected>Interne</option>
                        <option value="0">Externe</option>
                    </select>
                </div>
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-primary-foreground text-sm font-medium hover:opacity-90 transition-opacity">
                    <?= icon('plus', 'size-4') ?> Ajouter
                </button>
            </form>
        </div>
    </div>

    <div class="bg-card border border-border rounded-xl p-6 space-y-5">
        <div>
            <h2 class="text-sm font-semibold uppercase tracking-wider text-muted-foreground mb-3">Règle de validation</h2>
            <p class="text-sm text-muted-foreground">
                Seuls les numéros commençant par un préfixe autorisé peuvent se connecter et effectuer des opérations.
            </p>
        </div>
        <div class="p-4 rounded-lg bg-background border border-border font-mono text-xs">
            <div class="text-muted-foreground mb-2">Exemple valide :</div>
            <div class="text-foreground">+261 <span class="text-primary font-semibold">034</span> 45 678 90</div>
        </div>
        <div class="pt-4 border-t border-border">
            <div class="text-xs text-muted-foreground mb-1">Format attendu</div>
            <div class="font-mono text-sm">3 chiffres, sans doublon</div>
        </div>
        <div class="pt-4 border-t border-border">
            <div class="text-xs text-muted-foreground mb-1">Préfixes internes</div>
            <div class="text-2xl font-bold text-primary"><?= count($prefixesInternes) ?></div>
        </div>
        <div class="pt-4 border-t border-border">
            <div class="text-xs text-muted-foreground mb-1">Préfixes externes</div>
            <div class="text-2xl font-bold text-muted-foreground"><?= count($prefixesExternes) ?></div>
        </div>
    </div>
</div>

<script>
    function toggleEdit(id) {
        document.getElementById('view-row-' + id).classList.toggle('hidden');
        document.getElementById('edit-row-' + id).classList.toggle('hidden');
    }
</script>
<?= $this->endSection() ?>
