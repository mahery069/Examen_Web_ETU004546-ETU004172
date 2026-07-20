<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Situation des gains</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; background: #f4f6f8; margin: 0; padding: 0; color: #222; }
        .container { max-width: 720px; margin: 40px auto; padding: 0 20px; }
        h1 { font-size: 22px; margin-bottom: 4px; }
        h2 { font-size: 16px; margin-top: 0; margin-bottom: 16px; }
        .subtitle { color: #666; margin-bottom: 24px; font-size: 14px; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.1); padding: 20px; margin-bottom: 24px; }
        form.inline-form { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; }
        .field { display: flex; flex-direction: column; gap: 4px; }
        .field label { font-size: 12px; color: #555; }
        .field select { padding: 8px 10px; border: 1px solid #ccd0d5; border-radius: 5px; font-size: 14px; width: 200px; }
        button, .btn { padding: 8px 16px; border: none; border-radius: 5px; font-size: 14px; cursor: pointer; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-secondary { background: #e5e7eb; color: #222; text-decoration: none; display: inline-flex; align-items: center; }
        .btn-secondary:hover { background: #d1d5db; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 10px 8px; border-bottom: 1px solid #eee; font-size: 14px; }
        th { color: #555; font-weight: 600; font-size: 12px; text-transform: uppercase; }
        td.num, th.num { text-align: right; }
        tfoot td { font-weight: bold; border-top: 2px solid #ddd; border-bottom: none; }
        .total-global { margin-top: 16px; background: #eef2ff; border-radius: 8px; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; }
        .total-global .label { font-size: 14px; color: #3730a3; }
        .total-global .value { font-size: 24px; font-weight: bold; color: #3730a3; }
    </style>
</head>
<body>
<div class="container">
    <h1>Situation des gains</h1>
    <p class="subtitle">Côté opérateur — total des frais perçus sur les retraits et transferts (et dépôts si applicable).</p>

    <div class="card">
        <h2>Filtrer par type d'opération</h2>
        <form class="inline-form" action="<?= site_url('admin/gains') ?>" method="get">
            <div class="field">
                <label for="type_operation_id">Type d'opération</label>
                <select id="type_operation_id" name="type_operation_id">
                    <option value="" <?= $typeOperationId === null ? 'selected' : '' ?>>Tous les types</option>
                    <?php foreach ($types as $type): ?>
                        <option value="<?= (int) $type['id'] ?>" <?= $typeOperationId === (int) $type['id'] ? 'selected' : '' ?>>
                            <?= esc($type['libelle']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <button type="submit" class="btn btn-primary">Filtrer</button>
            </div>
            <?php if ($typeOperationId !== null): ?>
                <div class="field">
                    <a class="btn btn-secondary" href="<?= site_url('admin/gains') ?>">Réinitialiser</a>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <h2>Tableau récapitulatif des frais perçus</h2>

        <?php if (empty($recap)): ?>
            <p>Aucune donnée disponible.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Type d'opération</th>
                        <th class="num">Nombre d'opérations</th>
                        <th class="num">Total des frais perçus (Ar)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recap as $ligne): ?>
                        <tr>
                            <td><?= esc($ligne['libelle']) ?></td>
                            <td class="num"><?= (int) $ligne['nb_operations'] ?></td>
                            <td class="num"><?= number_format((float) $ligne['total_frais'], 2, ',', ' ') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="total-global">
            <span class="label"><?= $typeOperationId === null ? 'Total global (tous types confondus)' : 'Total pour le type sélectionné' ?></span>
            <span class="value"><?= number_format($totalGlobal, 2, ',', ' ') ?> Ar</span>
        </div>
    </div>
</div>
</body>
</html>
