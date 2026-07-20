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
