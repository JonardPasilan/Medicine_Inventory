<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            min-height: 100vh;
        }

        /* NAVIGATION BAR */
        .nav {
            background: #1f4f87;
            padding: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        .nav a {
            color: white;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .nav a:hover {
            background: rgba(255,255,255,0.15);
            transform: translateY(-1px);
        }
        .nav a.active {
            background: rgba(255,255,255,0.25);
        }

        /* Top Title Bar */
        .topbar {
            background: #153e6b;
            color: white;
            padding: 12px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        @media (max-width: 600px) {
            .nav a {
                font-size: 12px;
                padding: 6px 10px;
            }
        }
    </style>
</head>
<body>

<div class="topbar">
    🏥 CLINIC MANAGEMENT SYSTEM
</div>

<div class="nav">
    <?php 
    $current_page = basename($_SERVER['PHP_SELF']); 
    ?>
    <a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">🏠 Dashboard</a>
    <a href="add.php" class="<?php echo $current_page == 'add.php' ? 'active' : ''; ?>">➕ Add</a>
    <a href="dispense.php" class="<?php echo $current_page == 'dispense.php' ? 'active' : ''; ?>">💊 Dispense</a>
    <a href="logs.php" class="<?php echo $current_page == 'logs.php' ? 'active' : ''; ?>">📋 Logs</a>
</div>
