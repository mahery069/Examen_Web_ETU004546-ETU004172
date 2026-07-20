<?php
    helper('icon');

    $currentPath = uri_string();
    $nav = [
        ['to' => 'client/tableau-de-bord', 'label' => 'Tableau de bord', 'icon' => 'layout-dashboard'],
        ['to' => 'client/solde',           'label' => 'Mon solde',       'icon' => 'wallet'],
        ['to' => 'client/depot',           'label' => 'Dépôt',           'icon' => 'arrow-down-right'],
        ['to' => 'client/retrait',         'label' => 'Retrait',         'icon' => 'arrow-up-right'],
        ['to' => 'client/transfert',       'label' => 'Transfert',       'icon' => 'arrow-left-right'],
        ['to' => 'client/envoi-multiple',  'label' => 'Envoi multiple',  'icon' => 'users'],
        ['to' => 'client/historique',      'label' => 'Historique',      'icon' => 'clock'],
    ];

    $titres = [
        'client/tableau-de-bord' => ['Tableau de bord', 'Vue d\'ensemble de votre compte.'],
        'client/solde'           => ['Mon solde', 'Solde actuel de votre compte mobile money.'],
        'client/depot'           => ['Effectuer un dépôt', 'Créditez votre compte instantanément.'],
        'client/retrait'         => ['Effectuer un retrait', 'Des frais sont appliqués selon le barème en vigueur.'],
        'client/transfert'       => ['Effectuer un transfert', 'Envoyez de l\'argent vers un autre client.'],
        'client/envoi-multiple'  => ['Envoi multiple', 'Envoyez de l\'argent à plusieurs destinataires en une fois.'],
        'client/historique'      => ['Historique des opérations', 'Toutes vos opérations, du plus récent au plus ancien.'],
    ];
    [$pageTitle, $pageSubtitle] = $titres[$currentPath] ?? ['FluxPay', ''];
    $this->setData(['title' => $pageTitle]);

    $numeroTelephone = session()->get('numero_telephone');
    $solde           = null;
    $compteId        = session()->get('compte_id');

    if ($compteId !== null) {
        $compteModel = new \App\Models\CompteModel();
        $compte      = $compteModel->find($compteId);
        $solde       = $compte['solde'] ?? null;
    }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<?= $this->include('admin/_theme_head') ?>
</head>
<body class="bg-background text-foreground antialiased">

<div class="min-h-screen flex bg-background text-foreground">
    <aside class="w-64 shrink-0 bg-sidebar text-sidebar-foreground flex flex-col">
        <div class="h-16 flex items-center gap-3 px-6 border-b border-sidebar-border">
            <div class="size-8 rounded-lg bg-primary grid place-items-center text-primary-foreground font-bold">F</div>
            <div>
                <div class="text-sm font-semibold">FluxPay</div>
                <div class="text-[10px] font-mono uppercase tracking-widest text-sidebar-foreground-60">Client</div>
            </div>
        </div>

        <nav class="flex-1 p-3 space-y-1">
            <?php foreach ($nav as $item): ?>
                <?php $active = $currentPath === $item['to']; ?>
                <a href="<?= site_url($item['to']) ?>"
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-sm transition-colors <?= $active
                        ? 'bg-sidebar-primary text-sidebar-primary-foreground font-medium'
                        : 'hover:bg-sidebar-accent text-sidebar-foreground-80' ?>">
                    <?= icon($item['icon'], 'size-4') ?>
                    <?= esc($item['label']) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="p-3 border-t border-sidebar-border space-y-1">
            <?php if ($numeroTelephone): ?>
                <div class="px-3 py-2 text-xs text-sidebar-foreground-60">
                    Connecté : <span class="font-medium font-mono text-sidebar-foreground-80"><?= esc($numeroTelephone) ?></span>
                </div>
            <?php endif; ?>
            <a href="<?= url_to('logout') ?>" class="w-full flex items-center gap-3 px-3 py-2 rounded-md text-sm text-sidebar-foreground-80 hover:bg-sidebar-accent transition-colors">
                <?= icon('log-out', 'size-4') ?> Déconnexion
            </a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
        <header class="h-16 border-b border-border bg-card flex items-center justify-between px-8">
            <div>
                <div class="text-[10px] font-mono uppercase tracking-widest text-primary">Client Mobile Money</div>
                <h1 class="text-lg font-semibold tracking-tight"><?= esc($pageTitle) ?></h1>
            </div>
            <?php if ($solde !== null): ?>
                <div class="flex items-center gap-2 pl-3">
                    <span class="text-xs text-muted-foreground">Solde</span>
                    <span class="text-sm font-mono font-semibold"><?= number_format((float) $solde, 2, ',', ' ') ?> Ar</span>
                </div>
            <?php endif; ?>
        </header>

        <main class="flex-1 p-8 overflow-y-auto">
            <?php if (! empty($pageSubtitle)): ?>
                <p class="text-sm text-muted-foreground mb-6 -mt-2"><?= esc($pageSubtitle) ?></p>
            <?php endif; ?>

            <?php if (session()->getFlashdata('succes')): ?>
                <div class="mb-6 px-4 py-3 rounded-lg bg-success-10 text-success text-sm font-medium">
                    <?= esc(session()->getFlashdata('succes')) ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('erreur')): ?>
                <div class="mb-6 px-4 py-3 rounded-lg bg-destructive-10 text-destructive text-sm">
                    <?= esc(session()->getFlashdata('erreur')) ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('erreurs')): ?>
                <div class="mb-6 px-4 py-3 rounded-lg bg-destructive-10 text-destructive text-sm">
                    <ul class="list-disc pl-4 space-y-0.5">
                        <?php foreach (session()->getFlashdata('erreurs') as $erreur): ?>
                            <li><?= esc($erreur) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </main>
    </div>
</div>
</body>
</html>
