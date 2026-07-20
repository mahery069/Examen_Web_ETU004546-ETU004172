<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Barèmes de frais par tranche de montant</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; background: #f4f6f8; margin: 0; padding: 0; color: #222; }
        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        h1 { font-size: 22px; margin-bottom: 4px; }
        h2 { font-size: 16px; margin-top: 0; margin-bottom: 16px; }
        h3 { font-size: 15px; margin: 0; }
        .subtitle { color: #666; margin-bottom: 24px; font-size: 14px; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.1); padding: 20px; margin-bottom: 24px; }
        .alert { padding: 10px 14px; border-radius: 6px; margin-bottom: 16px; font-size: 14px; }
        .alert-success { background: #e6f4ea; color: #1e7e34; border: 1px solid #b7dfc1; }
        .alert-error { background: #fdecea; color: #a12622; border: 1px solid #f5c6c2; }
        .alert ul { margin: 0; padding-left: 18px; }
        form.inline-form { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-start; }
        .field { display: flex; flex-direction: column; gap: 4px; }
        .field label { font-size: 12px; color: #555; }
        .field input, .field select { padding: 8px 10px; border: 1px solid #ccd0d5; border-radius: 5px; font-size: 14px; }
        .field input.montant-input { width: 130px; }
        .field select.type-select { width: 160px; }
        button, .btn { padding: 8px 16px; border: none; border-radius: 5px; font-size: 14px; cursor: pointer; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-secondary { background: #e5e7eb; color: #222; }
        .btn-secondary:hover { background: #d1d5db; }
        .btn-danger { background: #dc2626; color: #fff; }
        .btn-danger:hover { background: #b91c1c; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #eee; font-size: 14px; }
        th { color: #555; font-weight: 600; font-size: 12px; text-transform: uppercase; }
        td.actions { display: flex; gap: 8px; }
        .empty { color: #888; padding: 12px 0; font-size: 14px; }
        .edit-row { display: none; background: #f9fafb; }
        .edit-row.visible { display: table-row; }
        .view-row.hidden { display: none; }
        .type-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
        .type-badge { background: #eef2ff; color: #3730a3; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .gap-row td { background: #fff7ed; color: #9a5b0f; font-size: 13px; font-style: italic; border-bottom: 1px solid #fde8cd; }
    </style>
</head>
<body>
<div class="container">
    <h1>Barèmes de frais par tranche de montant</h1>
    <p class="subtitle">Côté opérateur — frais appliqués selon le montant, pour chaque type d'opération (dépôt / retrait / transfert).</p>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>Ajouter une tranche</h2>
        <form class="inline-form" action="<?= site_url('admin/baremes') ?>" method="post">
            <?= csrf_field() ?>
            <div class="field">
                <label for="type_operation_id">Type d'opération</label>
                <select class="type-select" id="type_operation_id" name="type_operation_id" required>
                    <option value="">-- Choisir --</option>
                    <?php foreach ($types as $type): ?>
                        <option value="<?= (int) $type['id'] ?>" <?= old('type_operation_id') == $type['id'] ? 'selected' : '' ?>>
                            <?= esc($type['libelle']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="montant_min">Montant min (Ar)</label>
                <input class="montant-input" type="number" step="0.01" min="0" id="montant_min" name="montant_min" value="<?= esc(old('montant_min')) ?>" required>
            </div>
            <div class="field">
                <label for="montant_max">Montant max (Ar)</label>
                <input class="montant-input" type="number" step="0.01" min="0" id="montant_max" name="montant_max" value="<?= esc(old('montant_max')) ?>" required>
            </div>
            <div class="field">
                <label for="frais">Frais (Ar)</label>
                <input class="montant-input" type="number" step="0.01" min="0" id="frais" name="frais" value="<?= esc(old('frais')) ?>" required>
            </div>
            <div class="field">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </div>
        </form>
    </div>

    <?php foreach ($groupes as $groupe): ?>
        <?php
            $type      = $groupe['type'];
            $tranches  = $groupe['tranches']['tranches'];
            $gaps      = $groupe['tranches']['gaps'];
        ?>
        <div class="card">
            <div class="type-header">
                <h3><?= esc($type['libelle']) ?></h3>
                <span class="type-badge"><?= esc($type['code']) ?></span>
            </div>

            <?php if (empty($tranches)): ?>
                <p class="empty">Aucune tranche configurée pour ce type d'opération.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Montant min</th>
                            <th>Montant max</th>
                            <th>Frais</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tranches as $index => $t): ?>
                            <tr class="view-row" id="view-b-<?= (int) $t['id'] ?>">
                                <td><?= esc($t['montant_min']) ?></td>
                                <td><?= esc($t['montant_max']) ?></td>
                                <td><?= esc($t['frais']) ?></td>
                                <td class="actions">
                                    <button type="button" class="btn btn-secondary" onclick="toggleEdit(<?= (int) $t['id'] ?>)">Modifier</button>
                                    <form action="<?= site_url('admin/baremes/' . (int) $t['id'] . '/delete') ?>" method="post" onsubmit="return confirm('Supprimer cette tranche ?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-danger">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                            <tr class="edit-row" id="edit-b-<?= (int) $t['id'] ?>">
                                <td colspan="4">
                                    <form class="inline-form" action="<?= site_url('admin/baremes/' . (int) $t['id'] . '/update') ?>" method="post">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="type_operation_id" value="<?= (int) $t['type_operation_id'] ?>">
                                        <div class="field">
                                            <label>Montant min</label>
                                            <input class="montant-input" type="number" step="0.01" min="0" name="montant_min" value="<?= esc($t['montant_min']) ?>" required>
                                        </div>
                                        <div class="field">
                                            <label>Montant max</label>
                                            <input class="montant-input" type="number" step="0.01" min="0" name="montant_max" value="<?= esc($t['montant_max']) ?>" required>
                                        </div>
                                        <div class="field">
                                            <label>Frais</label>
                                            <input class="montant-input" type="number" step="0.01" min="0" name="frais" value="<?= esc($t['frais']) ?>" required>
                                        </div>
                                        <div class="field">
                                            <label>&nbsp;</label>
                                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                                        </div>
                                        <div class="field">
                                            <label>&nbsp;</label>
                                            <button type="button" class="btn btn-secondary" onclick="toggleEdit(<?= (int) $t['id'] ?>)">Annuler</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                            <?php foreach ($gaps as $gap): ?>
                                <?php if ($gap['apres_tranche_index'] === $index): ?>
                                    <tr class="gap-row">
                                        <td colspan="4">⚠ Trou détecté : aucune tranche ne couvre <?= esc($gap['min']) ?> — <?= esc($gap['max']) ?> Ar</td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<script>
    function toggleEdit(id) {
        var viewRow = document.getElementById('view-b-' + id);
        var editRow = document.getElementById('edit-b-' + id);
        viewRow.classList.toggle('hidden');
        editRow.classList.toggle('visible');
    }
</script>
</body>
</html>
