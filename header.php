<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not authenticated
$current_page = basename($_SERVER['PHP_SELF']);
if (!isset($_SESSION['authenticated']) && $current_page !== 'access.php') {
    header("Location: access.php");
    exit();
}
?>
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

/* DARK MODE OVERRIDES */
:root[data-theme="dark"] {
  --color-brand-light: hsl(245, 60%, 20%);
  --color-canvas:   hsl(220, 20%, 10%);
  --color-surface:  hsl(220, 20%, 15%);
  --color-overlay:  hsl(220, 20%, 22%);
  --color-text-primary:   hsl(220, 15%, 95%);
  --color-text-secondary: hsl(220, 10%, 75%);
  --color-text-muted:     hsl(220,  8%, 60%);
  --color-border:        hsl(220, 15%, 25%);
  --color-border-strong: hsl(220, 15%, 35%);
}

[data-theme="dark"] body { background: var(--color-canvas) !important; color: var(--color-text-primary) !important; }
[data-theme="dark"] .form-card, 
[data-theme="dark"] .table-card, 
[data-theme="dark"] .header-card, 
[data-theme="dark"] .stat-card, 
[data-theme="dark"] .filter-section, 
[data-theme="dark"] .table-container,
[data-theme="dark"] .print-area,
[data-theme="dark"] .batch-card,
[data-theme="dark"] .medicine-preview {
    background: var(--color-surface) !important;
    border-color: var(--color-border) !important;
    box-shadow: 0 4px 15px rgba(0,0,0,0.5) !important;
}
[data-theme="dark"] input, [data-theme="dark"] select, [data-theme="dark"] textarea {
    background: var(--color-overlay) !important;
    border-color: var(--color-border) !important;
    color: var(--color-text-primary) !important;
}
[data-theme="dark"] th { background: var(--color-overlay) !important; color: var(--color-text-primary) !important; border-color: var(--color-border) !important; }
[data-theme="dark"] td { border-color: var(--color-border) !important; color: var(--color-text-secondary) !important; }
[data-theme="dark"] .medicine-name, [data-theme="dark"] h2, [data-theme="dark"] h3, [data-theme="dark"] label { color: var(--color-text-primary) !important; }
[data-theme="dark"] p { color: var(--color-text-secondary) !important; }
[data-theme="dark"] tr:hover { background: var(--color-overlay) !important; }
[data-theme="dark"] .topbar { background: hsl(220, 20%, 8%) !important; border-bottom: 1px solid var(--color-border); }

/* Dark Mode Neon Glow Effects for Buttons */
[data-theme="dark"] .nav a:hover,
[data-theme="dark"] .theme-toggle:hover,
[data-theme="dark"] .tab-btn:hover,
[data-theme="dark"] .btn:hover,
[data-theme="dark"] .modal-btn:hover,
[data-theme="dark"] .search-form button:hover {
    box-shadow: 0 0 8px var(--color-brand), 0 0 16px var(--color-brand) !important;
    border-color: var(--color-brand) !important;
    color: #ffffff !important;
    text-shadow: 0 0 3px rgba(255, 255, 255, 0.5) !important;
}

/* Neon Red for Delete / Cancel Actions */
[data-theme="dark"] .btn-delete:hover,
[data-theme="dark"] .modal-btn-cancel:hover {
    box-shadow: 0 0 8px hsl(0, 80%, 50%), 0 0 16px hsl(0, 80%, 50%) !important;
    border-color: hsl(0, 80%, 50%) !important;
    color: #ffffff !important;
    text-shadow: 0 0 3px rgba(255, 255, 255, 0.5) !important;
}

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--color-canvas);
            color: var(--color-text-primary);
            min-height: 100vh;
            font-size: var(--text-base);
            transition: background-color var(--transition-base), color var(--transition-base);
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
            position: relative; /* For absolute positioning of toggle */
        }
        .nav a, .theme-toggle {
            color: var(--color-text-secondary);
            text-decoration: none;
            font-size: var(--text-sm);
            font-weight: 500;
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            background: transparent;
            border: none;
            font-family: 'Inter', sans-serif;
        }
        .nav a:hover {
            background: var(--color-overlay);
            color: var(--color-text-primary);
            transform: translateY(-1px);
        }
        .theme-toggle:hover {
            background: var(--color-border);
            color: var(--color-text-primary);
        }
        .nav a.active {
            background: var(--color-brand-light);
            color: var(--color-brand-dark);
            font-weight: 600;
        }
        .theme-toggle {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--color-overlay);
            z-index: 10;
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

        @media (max-width: 900px) {
            .theme-toggle {
                position: static;
                transform: none;
                margin-left: 0;
                margin: 0 auto; /* Center on small screens */
            }
        }
        
        @media (max-width: 600px) {
            .nav a, .theme-toggle {
                font-size: var(--text-xs);
                padding: 6px 10px;
            }
        }

        /* CUSTOM MODAL STYLES */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10001;
            backdrop-filter: blur(4px);
            opacity: 0;
            transition: opacity var(--transition-base);
        }
        .modal-overlay.show { display: flex; opacity: 1; }
        
        .modal-card {
            background: var(--color-surface);
            border-radius: var(--radius-lg);
            padding: 28px;
            width: 90%;
            max-width: 400px;
            box-shadow: var(--shadow-lg);
            transform: translateY(20px);
            transition: transform var(--transition-base);
            text-align: center;
            border: 1px solid var(--color-border);
        }
        .modal-overlay.show .modal-card { transform: translateY(0); }
        
        .modal-header {
            margin-bottom: 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }
        .modal-header i {
            color: var(--color-brand);
            width: 48px;
            height: 48px;
        }
        .modal-header h3 {
            font-size: var(--text-lg);
            color: var(--color-text-primary);
            font-weight: 700;
        }
        
        .modal-body {
            margin-bottom: 24px;
            color: var(--color-text-secondary);
            font-size: var(--text-base);
            line-height: 1.5;
        }
        
        .modal-footer {
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        
        .modal-btn {
            padding: 10px 20px;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: var(--text-sm);
            cursor: pointer;
            border: none;
            flex: 1;
        }
        .modal-btn-confirm {
            background: var(--color-brand);
            color: white;
        }
        .modal-btn-confirm:hover { background: var(--color-brand-dark); }
        
        .modal-btn-cancel {
            background: var(--color-overlay);
            color: var(--color-text-secondary);
            border: 1px solid var(--color-border);
        }
        .modal-btn-cancel:hover { background: var(--color-border); color: var(--color-text-primary); }

        .modal-btn-alert {
            background: var(--color-brand);
            color: white;
            max-width: 120px;
        }
    </style>
    <script>
        // Theme initialization before rendering to prevent flash
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);

        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            // Update icon
            const themeIcon = document.getElementById('themeIcon');
            if(themeIcon) {
                themeIcon.setAttribute('data-lucide', newTheme === 'dark' ? 'sun' : 'moon');
                lucide.createIcons();
            }
        }

        // Custom Modal Logic
        let modalResolve = null;

        function showConfirm(title, message) {
            return new Promise((resolve) => {
                const modal = document.getElementById('customModal');
                const titleEl = document.getElementById('modalTitle');
                const messageEl = document.getElementById('modalMessage');
                const cancelBtn = document.getElementById('modalCancel');
                const confirmBtn = document.getElementById('modalConfirm');
                const iconEl = document.getElementById('modalIcon');

                titleEl.innerText = title || "Confirm Action";
                messageEl.innerHTML = message || "Are you sure you want to proceed?";
                cancelBtn.style.display = "block";
                confirmBtn.className = "modal-btn modal-btn-confirm";
                confirmBtn.innerText = "Confirm";
                iconEl.setAttribute('data-lucide', 'help-circle');
                iconEl.style.color = 'var(--color-brand)';
                lucide.createIcons();

                modal.classList.add('show');
                modalResolve = resolve;
            });
        }

        function showAlert(title, message, type = 'info') {
            return new Promise((resolve) => {
                const modal = document.getElementById('customModal');
                const titleEl = document.getElementById('modalTitle');
                const messageEl = document.getElementById('modalMessage');
                const cancelBtn = document.getElementById('modalCancel');
                const confirmBtn = document.getElementById('modalConfirm');
                const iconEl = document.getElementById('modalIcon');

                titleEl.innerText = title || "Notification";
                messageEl.innerHTML = message;
                cancelBtn.style.display = "none";
                confirmBtn.className = "modal-btn modal-btn-alert";
                confirmBtn.innerText = "OK";
                
                if(type === 'error') {
                    iconEl.setAttribute('data-lucide', 'x-circle');
                    iconEl.style.color = '#e53935';
                } else if(type === 'success') {
                    iconEl.setAttribute('data-lucide', 'check-circle');
                    iconEl.style.color = '#2e7d32';
                } else {
                    iconEl.setAttribute('data-lucide', 'info');
                    iconEl.style.color = 'var(--color-brand)';
                }
                lucide.createIcons();

                modal.classList.add('show');
                modalResolve = resolve;
            });
        }

        function closeModal(result) {
            const modal = document.getElementById('customModal');
            modal.classList.remove('show');
            if (modalResolve) {
                modalResolve(result);
                modalResolve = null;
            }
        }

        document.addEventListener("DOMContentLoaded", () => {
            // Set initial theme icon
            const themeIcon = document.getElementById('themeIcon');
            if(themeIcon) {
                themeIcon.setAttribute('data-lucide', savedTheme === 'dark' ? 'sun' : 'moon');
            }

            lucide.createIcons();
            
            // Modal button events
            document.getElementById('modalCancel').addEventListener('click', () => closeModal(false));
            document.getElementById('modalConfirm').addEventListener('click', () => closeModal(true));
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
    <a href="add.php" class="<?php echo $current_page == 'add.php' ? 'active' : ''; ?>"><i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Add Medicine</a>
    <a href="add_equipment.php" class="<?php echo $current_page == 'add_equipment.php' ? 'active' : ''; ?>"><i data-lucide="stethoscope" style="width: 16px; height: 16px;"></i> Add Equipment</a>
    <a href="import.php" class="<?php echo $current_page == 'import.php' ? 'active' : ''; ?>"><i data-lucide="download" style="width: 16px; height: 16px;"></i> Import</a>
    <a href="dispense.php" class="<?php echo $current_page == 'dispense.php' ? 'active' : ''; ?>"><i data-lucide="pill" style="width: 16px; height: 16px;"></i> Dispense</a>
    <a href="borrowers_slip.php" class="<?php echo $current_page == 'borrowers_slip.php' ? 'active' : ''; ?>"><i data-lucide="shopping-cart" style="width: 16px; height: 16px;"></i> Borrower's Slip</a>
    <a href="logs.php" class="<?php echo $current_page == 'logs.php' ? 'active' : ''; ?>"><i data-lucide="clipboard-list" style="width: 16px; height: 16px;"></i> Logs</a>
    
    <a href="access.php?logout=1" style="color: #e74c3c;"><i data-lucide="log-out" style="width: 16px; height: 16px;"></i> Logout</a>

    <!-- Theme Toggler -->
    <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Dark Mode">
        <i id="themeIcon" data-lucide="moon" style="width: 18px; height: 18px;"></i> <span class="hide-mobile">Dark Mode</span>
    </button>
</div>

<!-- Custom Modal HTML -->
<div id="customModal" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header">
            <i id="modalIcon" data-lucide="help-circle"></i>
            <h3 id="modalTitle">Confirm Action</h3>
        </div>
        <div class="modal-body">
            <p id="modalMessage">Are you sure you want to proceed?</p>
        </div>
        <div class="modal-footer">
            <button id="modalCancel" class="modal-btn modal-btn-cancel">Cancel</button>
            <button id="modalConfirm" class="modal-btn modal-btn-confirm">Confirm</button>
        </div>
    </div>
</div>
