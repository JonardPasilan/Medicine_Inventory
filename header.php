<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic Inventory System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
/* ============================================================
   DESIGN TOKENS — Edit here to restyle the entire product.
   ============================================================ */
:root {
  /* Brand (10% accent) */
  --color-brand:       hsl(245, 78%, 58%);   /* Primary CTA, active states */
  --color-brand-light: hsl(245, 78%, 96%);   /* Brand tint for backgrounds */
  --color-brand-dark:  hsl(245, 78%, 42%);   /* Brand hover / pressed states */

  /* Surfaces (60% canvas, 30% secondary) */
  --color-canvas:   hsl(40, 20%, 98%);       /* Page background (60%) */
  --color-surface:  hsl(0, 0%, 100%);        /* Card / panel (30%) */
  --color-overlay:  hsl(220, 14%, 97%);      /* Sidebar, input bg */

  /* Text */
  --color-text-primary:   hsl(220, 15%, 12%);
  --color-text-secondary: hsl(220, 10%, 44%);
  --color-text-muted:     hsl(220,  8%, 64%);

  /* Borders */
  --color-border:        hsl(220, 13%, 88%); /* Default border */
  --color-border-strong: hsl(220, 13%, 72%); /* Hover / emphasis border */

  /* Shadows (subtle only) */
  --shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.04);
  --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.05), 0 1px 2px rgba(0, 0, 0, 0.03);
  --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.07), 0 2px 4px rgba(0, 0, 0, 0.04);
  --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.09), 0 4px 8px rgba(0, 0, 0, 0.05);

  /* Border radius scale */
  --radius-xs:   4px;
  --radius-sm:   6px;
  --radius-md:   10px;
  --radius-lg:   14px;
  --radius-xl:   18px;
  --radius-full: 9999px;

  /* Transitions */
  --transition-fast: 100ms ease;
  --transition-base: 180ms ease;
  --transition-slow: 320ms ease;

  /* Type scale */
  --text-xs:   11px;
  --text-sm:   13px;
  --text-base: 15px;
  --text-md:   17px;
  --text-lg:   20px;
  --text-xl:   24px;
  --text-2xl:  30px;
  --text-3xl:  40px;
}

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--color-canvas);
            color: var(--color-text-primary);
            min-height: 100vh;
            font-size: var(--text-base);
        }

        button, a, [role="button"] {
            transition:
                color          var(--transition-base),
                background-color var(--transition-base),
                border-color   var(--transition-base),
                box-shadow     var(--transition-base),
                transform      var(--transition-base),
                opacity        var(--transition-base);
        }

        /* NAVIGATION BAR */
        .nav {
            background: var(--color-surface);
            padding: 10px;
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid var(--color-border);
        }
        .nav a {
            color: var(--color-text-secondary);
            text-decoration: none;
            font-size: var(--text-sm);
            font-weight: 500;
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .nav a:hover {
            background: var(--color-overlay);
            color: var(--color-text-primary);
            transform: translateY(-1px);
        }
        .nav a.active {
            background: var(--color-brand-light);
            color: var(--color-brand-dark);
            font-weight: 600;
        }

        /* Top Title Bar */
        .topbar {
            background: var(--color-brand);
            color: white;
            padding: 12px;
            text-align: center;
            font-size: var(--text-lg);
            font-weight: 600;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        @media (max-width: 600px) {
            .nav a {
                font-size: var(--text-xs);
                padding: 6px 10px;
            }
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            lucide.createIcons();
        });
    </script>
</head>
<body>

<div class="topbar">
    <i data-lucide="hospital" style="width: 20px; height: 20px;"></i> CLINIC MANAGEMENT SYSTEM
</div>

<div class="nav">
    <?php 
    $current_page = basename($_SERVER['PHP_SELF']); 
    ?>
    <a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>"><i data-lucide="layout-dashboard" style="width: 16px; height: 16px;"></i> Dashboard</a>
    <a href="add.php" class="<?php echo $current_page == 'add.php' ? 'active' : ''; ?>"><i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Add</a>
    <a href="import.php" class="<?php echo $current_page == 'import.php' ? 'active' : ''; ?>"><i data-lucide="download" style="width: 16px; height: 16px;"></i> Import</a>
    <a href="dispense.php" class="<?php echo $current_page == 'dispense.php' ? 'active' : ''; ?>"><i data-lucide="pill" style="width: 16px; height: 16px;"></i> Dispense</a>
    <a href="logs.php" class="<?php echo $current_page == 'logs.php' ? 'active' : ''; ?>"><i data-lucide="clipboard-list" style="width: 16px; height: 16px;"></i> Logs</a>
</div>
