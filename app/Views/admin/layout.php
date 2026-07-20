<!DOCTYPE html>
<html lang="fr">
<head>
<?= $this->include('admin/_theme_head') ?>
</head>
<body class="bg-background text-foreground antialiased">

<?php
    helper('icon');

    $currentPath = uri_string();
    $nav = [
        ['to' => 'admin',              'label' => 'Tableau de bord',    'icon' => 'layout-dashboard'],
        ['to' => 'admin/prefixes',      'label' => 'Préfixes réseau',    'icon' => 'radio'],
        ['to' => 'admin/baremes',       'label' => 'Barèmes de frais',   'icon' => 'receipt'],
        ['to' => 'admin/gains',         'label' => 'Situation des gains','icon' => 'trending-up'],
        ['to' => 'admin/comptes-clients', 'label' => 'Comptes clients', 'icon' => 'users'],
    ];
?>

<div class="min-h-screen flex bg-background text-foreground">
    <aside class="w-64 shrink-0 bg-sidebar text-sidebar-foreground flex flex-col">
        <div class="h-16 flex items-center gap-3 px-6 border-b border-sidebar-border">
            <div class="size-8 rounded-lg bg-primary grid place-items-center text-primary-foreground font-bold">F</div>
            <div>
                <div class="text-sm font-semibold"><?= esc('FluxPay') ?></div>
                <div class="text-[10px] font-mono uppercase tracking-widest text-sidebar-foreground-60">Opérateur</div>
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
            <?php if ($operateurLogin = session()->get('operateur_login')): ?>
                <div class="px-3 py-2 text-xs text-sidebar-foreground-60">
                    Connecté : <span class="font-medium text-sidebar-foreground-80"><?= esc($operateurLogin) ?></span>
                </div>
            <?php endif; ?>
            <button type="button" class="w-full flex items-center gap-3 px-3 py-2 rounded-md text-sm text-sidebar-foreground-80 hover:bg-sidebar-accent transition-colors">
                <?= icon('settings', 'size-4') ?> Paramètres
            </button>
            <form action="<?= site_url('admin/logout') ?>" method="post">
                <?= csrf_field() ?>
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-md text-sm text-sidebar-foreground-80 hover:bg-sidebar-accent transition-colors">
                    <?= icon('log-out', 'size-4') ?> Déconnexion
                </button>
            </form>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
        <header class="h-16 border-b border-border bg-card flex items-center justify-between px-8">
            <div>
                <div class="text-[10px] font-mono uppercase tracking-widest text-primary">Operator Terminal v1.0</div>
                <h1 class="text-lg font-semibold tracking-tight"><?= esc($title ?? '') ?></h1>
            </div>
            <div class="flex items-center gap-3">
                <?= $this->renderSection('actions') ?>
                <div class="flex items-center gap-2 pl-3 border-l border-border">
                    <div class="size-2 rounded-full bg-success"></div>
                    <span class="text-xs font-mono">RÉSEAU OK</span>
                </div>
            </div>
        </header>

        <main class="flex-1 p-8 overflow-y-auto">
            <?php if (! empty($subtitle)): ?>
                <p class="text-sm text-muted-foreground mb-6 -mt-2"><?= esc($subtitle) ?></p>
            <?php endif; ?>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="mb-6 px-4 py-3 rounded-lg bg-success-10 text-success text-sm font-medium">
                    <?= esc(session()->getFlashdata('success')) ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="mb-6 px-4 py-3 rounded-lg bg-destructive-10 text-destructive text-sm">
                    <ul class="list-disc pl-4 space-y-0.5">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
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
