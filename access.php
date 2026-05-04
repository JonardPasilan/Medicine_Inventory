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
    <style>
        :root {
            --primary: #1f4f87;
            --bg: #f4f6f9;
            --text: #2c3e50;
            --error: #e74c3c;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            color: var(--text);
        }
        .login-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .icon { font-size: 50px; margin-bottom: 20px; }
        h2 { margin-bottom: 10px; font-weight: 600; }
        p { color: #7f8c8d; font-size: 14px; margin-bottom: 30px; }
        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            margin-bottom: 20px;
            outline: none;
            transition: border-color 0.3s;
            text-align: center;
        }
        input:focus { border-color: var(--primary); }
        button {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.3s;
        }
        button:hover { opacity: 0.9; }
        .error-msg {
            color: var(--error);
            font-size: 13px;
            margin-bottom: 15px;
            background: #fdf2f2;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="icon">🔒</div>
        <h2>Access Required</h2>
        <p>Please enter the Access Key to continue.</p>

        <?php if ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="password" name="access_key" placeholder="Enter Access Key" autofocus required>
            <button type="submit">Unlock System</button>
        </form>
        
        <div style="margin-top: 20px; font-size: 12px; color: #bdc3c7;">
            Clinic Inventory Management System v2.0
        </div>
    </div>
</body>
</html>
