<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Configuration des préfixes opérateur</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; background: #f4f6f8; margin: 0; padding: 0; color: #222; }
        .container { max-width: 720px; margin: 40px auto; padding: 0 20px; }
        h1 { font-size: 22px; margin-bottom: 4px; }
        .subtitle { color: #666; margin-bottom: 24px; font-size: 14px; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.1); padding: 20px; margin-bottom: 24px; }
        .card h2 { font-size: 16px; margin-top: 0; margin-bottom: 16px; }
        .alert { padding: 10px 14px; border-radius: 6px; margin-bottom: 16px; font-size: 14px; }
        .alert-success { background: #e6f4ea; color: #1e7e34; border: 1px solid #b7dfc1; }
        .alert-error { background: #fdecea; color: #a12622; border: 1px solid #f5c6c2; }
        .alert ul { margin: 0; padding-left: 18px; }
        form.inline-form { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-start; }
        .field { display: flex; flex-direction: column; gap: 4px; }
        .field label { font-size: 12px; color: #555; }
        .field input { padding: 8px 10px; border: 1px solid #ccd0d5; border-radius: 5px; font-size: 14px; }
        .field input.prefixe-input { width: 90px; }
        .field input.libelle-input { width: 220px; }
        button, .btn { padding: 8px 16px; border: none; border-radius: 5px; font-size: 14px; cursor: pointer; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-secondary { background: #e5e7eb; color: #222; }
        .btn-secondary:hover { background: #d1d5db; }
        .btn-danger { background: #dc2626; color: #fff; }
        .btn-danger:hover { background: #b91c1c; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 10px 8px; border-bottom: 1px solid #eee; font-size: 14px; }
        th { color: #555; font-weight: 600; font-size: 12px; text-transform: uppercase; }
        td.actions { display: flex; gap: 8px; }
        .empty { color: #888; padding: 20px 0; text-align: center; }
        .edit-row { display: none; background: #f9fafb; }
        .edit-row.visible { display: table-row; }
        .view-row.hidden { display: none; }
        code.badge { background: #eef2ff; color: #3730a3; padding: 2px 8px; border-radius: 4px; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h1>Configuration des préfixes opérateur</h1>
    <p class="subtitle">Côté opérateur — préfixes valables pour l'identification des clients (ex: 033, 037).</p>

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
        <h2>Ajouter un préfixe</h2>
        <form class="inline-form" action="<?= site_url('admin/prefixes') ?>" method="post">
            <?= csrf_field() ?>
            <div class="field">
                <label for="prefixe">Préfixe (3 chiffres)</label>
                <input class="prefixe-input" type="text" id="prefixe" name="prefixe" maxlength="3" pattern="\d{3}" placeholder="033" value="<?= esc(old('prefixe')) ?>" required>
            </div>
            <div class="field">
                <label for="libelle">Libellé (optionnel)</label>
                <input class="libelle-input" type="text" id="libelle" name="libelle" maxlength="50" placeholder="Opérateur A" value="<?= esc(old('libelle')) ?>">
            </div>
            <div class="field">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary">Ajouter</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Préfixes existants</h2>

        <?php if (empty($prefixes)): ?>
            <p class="empty">Aucun préfixe configuré pour le moment.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Préfixe</th>
                        <th>Libellé</th>
                        <th>Créé le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($prefixes as $p): ?>
                        <tr class="view-row" id="view-<?= (int) $p['id'] ?>">
                            <td><code class="badge"><?= esc($p['prefixe']) ?></code></td>
                            <td><?= esc($p['libelle'] ?: '—') ?></td>
                            <td><?= esc($p['date_creation']) ?></td>
                            <td class="actions">
                                <button type="button" class="btn btn-secondary" onclick="toggleEdit(<?= (int) $p['id'] ?>)">Modifier</button>
                                <form action="<?= site_url('admin/prefixes/' . (int) $p['id'] . '/delete') ?>" method="post" onsubmit="return confirm('Supprimer le préfixe <?= esc($p['prefixe'], 'js') ?> ?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-danger">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                        <tr class="edit-row" id="edit-<?= (int) $p['id'] ?>">
                            <td colspan="4">
                                <form class="inline-form" action="<?= site_url('admin/prefixes/' . (int) $p['id'] . '/update') ?>" method="post">
                                    <?= csrf_field() ?>
                                    <div class="field">
                                        <label>Préfixe</label>
                                        <input class="prefixe-input" type="text" name="prefixe" maxlength="3" pattern="\d{3}" value="<?= esc($p['prefixe']) ?>" required>
                                    </div>
                                    <div class="field">
                                        <label>Libellé</label>
                                        <input class="libelle-input" type="text" name="libelle" maxlength="50" value="<?= esc($p['libelle']) ?>">
                                    </div>
                                    <div class="field">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                                    </div>
                                    <div class="field">
                                        <label>&nbsp;</label>
                                        <button type="button" class="btn btn-secondary" onclick="toggleEdit(<?= (int) $p['id'] ?>)">Annuler</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
    function toggleEdit(id) {
        var viewRow = document.getElementById('view-' + id);
        var editRow = document.getElementById('edit-' + id);
        viewRow.classList.toggle('hidden');
        editRow.classList.toggle('visible');
    }
</script>
</body>
</html>
