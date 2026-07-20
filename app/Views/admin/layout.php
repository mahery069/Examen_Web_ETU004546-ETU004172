<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? esc($title) . ' — FluxPay Opérateur' : 'FluxPay Opérateur' ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        background: 'var(--background)',
                        foreground: 'var(--foreground)',
                        card: 'var(--card)',
                        'card-foreground': 'var(--card-foreground)',
                        primary: 'var(--primary)',
                        'primary-foreground': 'var(--primary-foreground)',
                        secondary: 'var(--secondary)',
                        'secondary-foreground': 'var(--secondary-foreground)',
                        muted: 'var(--muted)',
                        'muted-foreground': 'var(--muted-foreground)',
                        accent: 'var(--accent)',
                        'accent-foreground': 'var(--accent-foreground)',
                        destructive: 'var(--destructive)',
                        'destructive-foreground': 'var(--destructive-foreground)',
                        success: 'var(--success)',
                        'success-foreground': 'var(--success-foreground)',
                        border: 'var(--border)',
                        input: 'var(--input)',
                        ring: 'var(--ring)',
                        sidebar: 'var(--sidebar)',
                        'sidebar-foreground': 'var(--sidebar-foreground)',
                        'sidebar-primary': 'var(--sidebar-primary)',
                        'sidebar-primary-foreground': 'var(--sidebar-primary-foreground)',
                        'sidebar-accent': 'var(--sidebar-accent)',
                        'sidebar-accent-foreground': 'var(--sidebar-accent-foreground)',
                        'sidebar-border': 'var(--sidebar-border)',
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                        mono: ['JetBrains Mono', 'ui-monospace', 'monospace'],
                    },
                    borderRadius: {
                        lg: 'var(--radius)',
                        xl: 'calc(var(--radius) + 4px)',
                    },
                },
            },
        };
    </script>

    <style>
        /* Ocean Banking — thème bleu, repris du design pocket-money-manager-main */
        :root {
            --radius: 0.625rem;
            --background: oklch(1 0 0);
            --foreground: oklch(0.129 0.042 264.695);
            --card: oklch(1 0 0);
            --card-foreground: oklch(0.129 0.042 264.695);
            --primary: oklch(0.208 0.042 265.755);
            --primary-foreground: oklch(0.984 0.003 247.858);
            --secondary: oklch(0.968 0.007 247.896);
            --secondary-foreground: oklch(0.208 0.042 265.755);
            --muted: oklch(0.968 0.007 247.896);
            --muted-foreground: oklch(0.554 0.046 257.417);
            --accent: oklch(0.968 0.007 247.896);
            --accent-foreground: oklch(0.208 0.042 265.755);
            --destructive: oklch(0.577 0.245 27.325);
            --destructive-foreground: oklch(0.984 0.003 247.858);
            --success: oklch(0.62 0.14 155);
            --success-foreground: oklch(0.99 0 0);
            --border: oklch(0.929 0.013 255.508);
            --input: oklch(0.929 0.013 255.508);
            --ring: oklch(0.704 0.04 256.788);
            --sidebar: oklch(0.984 0.003 247.858);
            --sidebar-foreground: oklch(0.129 0.042 264.695);
            --sidebar-primary: oklch(0.208 0.042 265.755);
            --sidebar-primary-foreground: oklch(0.984 0.003 247.858);
            --sidebar-accent: oklch(0.968 0.007 247.896);
            --sidebar-accent-foreground: oklch(0.208 0.042 265.755);
            --sidebar-border: oklch(0.929 0.013 255.508);
        }

        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }

        /* Variantes translucides (équivalent des modificateurs Tailwind bg-x/10) */
        .bg-primary-10  { background-color: color-mix(in oklch, var(--primary) 10%, transparent); }
        .bg-success-10  { background-color: color-mix(in oklch, var(--success) 10%, transparent); }
        .bg-destructive-10 { background-color: color-mix(in oklch, var(--destructive) 10%, transparent); }
        .border-sidebar-foreground-10 { border-color: color-mix(in oklch, var(--sidebar-foreground) 10%, transparent); }
        .text-sidebar-foreground-60 { color: color-mix(in oklch, var(--sidebar-foreground) 60%, transparent); }
        .text-sidebar-foreground-80 { color: color-mix(in oklch, var(--sidebar-foreground) 80%, transparent); }
    </style>
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
            <button type="button" class="w-full flex items-center gap-3 px-3 py-2 rounded-md text-sm text-sidebar-foreground-80 hover:bg-sidebar-accent transition-colors">
                <?= icon('settings', 'size-4') ?> Paramètres
            </button>
            <button type="button" class="w-full flex items-center gap-3 px-3 py-2 rounded-md text-sm text-sidebar-foreground-80 hover:bg-sidebar-accent transition-colors">
                <?= icon('log-out', 'size-4') ?> Déconnexion
            </button>
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
