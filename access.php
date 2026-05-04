<?php
session_start();

// Define your Access Key here
$ACCESS_KEY = "admin123"; 

$error = "";

if (isset($_POST['access_key'])) {
    if ($_POST['access_key'] === $ACCESS_KEY) {
        $_SESSION['authenticated'] = true;
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid Access Key. Please try again.";
    }
}

// Logout logic
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: access.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Required - Medicine Inventory</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            /* Design Tokens (Same as header.php) */
            --color-canvas: #f4f6f9;
            --color-surface: #ffffff;
            --color-overlay: #f8f9fa;
            --color-border: #e0e0e0;
            --color-text-primary: #2c3e50;
            --color-text-secondary: #7f8c8d;
            --color-brand: #1f4f87;
            --color-brand-light: rgba(31, 79, 135, 0.1);
            --shadow-md: 0 10px 25px rgba(0,0,0,0.05);
        }

        [data-theme="dark"] {
            --color-canvas: #0f172a;
            --color-surface: #1e293b;
            --color-overlay: #334155;
            --color-border: #334155;
            --color-text-primary: #f8fafc;
            --color-text-secondary: #94a3b8;
            --color-brand: #38bdf8;
            --color-brand-light: rgba(56, 189, 248, 0.1);
            --shadow-md: 0 10px 30px rgba(0,0,0,0.3);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--color-canvas);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            color: var(--color-text-primary);
            transition: background 0.3s ease, color 0.3s ease;
        }
        .login-card {
            background: var(--color-surface);
            padding: 40px;
            border-radius: 15px;
            box-shadow: var(--shadow-md);
            width: 100%;
            max-width: 400px;
            text-align: center;
            animation: fadeIn 0.5s ease;
            border: 1px solid var(--color-border);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .icon { font-size: 50px; margin-bottom: 20px; }
        h2 { margin-bottom: 10px; font-weight: 600; color: var(--color-text-primary); }
        p { color: var(--color-text-secondary); font-size: 14px; margin-bottom: 30px; }
        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--color-border);
            background: var(--color-overlay);
            color: var(--color-text-primary);
            border-radius: 8px;
            font-size: 16px;
            margin-bottom: 20px;
            outline: none;
            transition: all 0.3s;
            text-align: center;
        }
        input:focus { border-color: var(--color-brand); }
        button.btn-login {
            width: 100%;
            padding: 14px;
            background: var(--color-brand);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.3s;
        }
        button.btn-login:hover { opacity: 0.9; }

        /* Theme Toggle Button (Login Version) */
        .theme-toggle-login {
            position: fixed; top: 20px; right: 20px;
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            color: var(--color-text-primary);
            padding: 10px 15px;
            border-radius: 30px;
            cursor: pointer;
            display: flex; align-items: center; gap: 8px;
            font-size: 14px; font-weight: 500;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
        }
        .theme-toggle-login:hover { transform: translateY(-2px); }

        .error-msg {
            color: #ef4444; font-size: 13px; margin-bottom: 15px;
            background: rgba(239, 68, 68, 0.1); padding: 10px; border-radius: 5px;
        }
    </style>
</head>
<body>
    <!-- Theme Toggler -->
    <button class="theme-toggle-login" onclick="toggleTheme()" title="Toggle Dark Mode">
        <i id="themeIcon" data-lucide="moon" style="width: 18px; height: 18px;"></i> <span>Dark Mode</span>
    </button>

    <div class="login-card">
        <div class="icon">🔒</div>
        <h2>Access Required</h2>
        <p>Please enter the Access Key to continue.</p>

        <?php if ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="password" name="access_key" placeholder="Enter Access Key" autofocus required>
            <button type="submit" class="btn-login">Unlock System</button>
        </form>
        
        <div style="margin-top: 20px; font-size: 12px; color: var(--color-text-secondary);">
            Clinic Inventory Management System v2.0
        </div>
    </div>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        // Theme Logic
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        }

        function updateThemeIcon(theme) {
            const icon = document.getElementById('themeIcon');
            if (theme === 'dark') {
                icon.setAttribute('data-lucide', 'sun');
            } else {
                icon.setAttribute('data-lucide', 'moon');
            }
            lucide.createIcons();
        }

        // Apply theme on load
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            updateThemeIcon(savedTheme);
        })();
    </script>
</body>
</html>
