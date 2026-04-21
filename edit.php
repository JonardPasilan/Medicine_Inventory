<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/header.php';

// Validate batch ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id']);
$r  = $conn->query("SELECT * FROM medicines WHERE id = $id");

if (!$r || $r->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$row = $r->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Batch - Clinic Management System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            min-height: 100vh;
        }

        .nav {
            background: #2c3e50;
            padding: 8px 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky; top: 0; z-index: 999;
            display: flex; flex-wrap: wrap;
            justify-content: center; align-items: center;
            gap: 6px 10px;
        }
        .nav a {
            color: white; text-decoration: none;
            font-size: 13px; font-weight: 600;
            padding: 6px 12px; border-radius: 6px;
            transition: background 0.2s ease, transform 0.2s ease;
            display: inline-block; white-space: nowrap;
        }
        .nav a:hover { background: rgba(255,255,255,0.2); transform: translateY(-1px); }

        .container { max-width: 600px; margin: 40px auto; padding: 0 20px; }

        .form-card {
            background: white;
            border-radius: 15px;
            padding: 35px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .form-header { text-align: center; margin-bottom: 30px; }
        .form-header .icon { font-size: 50px; margin-bottom: 10px; }
        .form-header h2 { color: #2c3e50; font-size: 28px; margin-bottom: 8px; }
        .form-header p  { color: #7f8c8d; font-size: 14px; }

        /* Batch ID badge */
        .batch-id-badge {
            display: inline-block;
            background: #1f4f87;
            color: white;
            font-size: 12px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            margin-top: 6px;
        }

        /* Current info box */
        .medicine-preview {
            background: #eef2f7;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 25px;
            border: 1px solid #e0e0e0;
        }
        .preview-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
        }
        .preview-item:last-child { border-bottom: none; }
        .preview-label { font-weight: 600; color: #2c3e50; }
        .preview-value { color: #7f8c8d; }

        /* Warnings */
        .stock-warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px; border-radius: 8px;
            margin-bottom: 20px;
            display: flex; align-items: center; gap: 10px;
            font-size: 14px;
        }
        .expiry-warning {
            background: #ffeaea;
            border-left: 4px solid #e74c3c;
            padding: 12px; border-radius: 8px;
            margin-bottom: 20px;
            display: flex; align-items: center; gap: 10px;
            font-size: 14px;
        }

        .form-group { margin-bottom: 20px; }

        label {
            display: block; margin-bottom: 8px;
            color: #2c3e50; font-weight: 500; font-size: 14px;
        }
        label .required { color: #e74c3c; margin-left: 3px; }

        input, select, textarea {
            width: 100%; padding: 12px 15px;
            border: 2px solid #e0e0e0; border-radius: 8px;
            font-size: 14px; font-family: inherit;
            transition: all 0.3s ease;
        }
        input:focus, select:focus, textarea:focus {
            outline: none; border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
        }

        .button-group { display: flex; gap: 15px; margin-top: 10px; }

        .btn-update {
            flex: 1; padding: 14px;
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            border: none; color: white;
            font-size: 16px; font-weight: 600;
            border-radius: 8px; cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39,174,96,0.4);
        }

        .btn-cancel {
            flex: 1; padding: 14px;
            background: #95a5a6; border: none;
            color: white; font-size: 16px; font-weight: 600;
            border-radius: 8px; cursor: pointer;
            text-decoration: none; text-align: center;
            transition: all 0.3s ease;
        }
        .btn-cancel:hover { background: #7f8c8d; transform: translateY(-2px); }

        .alert {
            padding: 15px 20px; border-radius: 8px;
            margin-bottom: 20px;
            display: flex; align-items: center; gap: 10px;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { transform: translateX(-20px); opacity: 0; }
            to   { transform: translateX(0); opacity: 1; }
        }
        .alert-info {
            background: linear-gradient(135deg, #d1ecf1, #bee5eb);
            color: #0c5460; border-left: 4px solid #17a2b8;
        }

        @media (max-width: 768px) {
            .nav { padding: 6px 8px; gap: 4px 6px; }
            .nav a { font-size: 12px; padding: 5px 10px; }
            .container { margin: 20px auto; }
            .form-card { padding: 25px; }
            .button-group { flex-direction: column; }
            .form-header h2 { font-size: 24px; }
        }
    </style>
</head>
<body>

<div class="nav">
    <a href="index.php">🏠 Dashboard</a>
    <a href="add.php">➕ Add Medicine</a>
    <a href="dispense.php">💊 Dispense</a>
    <a href="logs.php">📋 Logs</a>
</div>

<div class="container">
    <div class="form-card">
        <div class="form-header">
            <div class="icon">✏️</div>
            <h2>Edit Batch</h2>
            <p>Update this specific batch entry</p>
            <span class="batch-id-badge">Batch ID #<?php echo (int)$row['id']; ?></span>
        </div>

        <?php
        if (isset($_GET['updated']) && $_GET['updated'] === 'success') {
            echo "<div class='alert alert-info' id='alertMessage'>
                    <span>✅</span>
                    <span>Batch updated successfully!</span>
                    <span style='margin-left:auto; cursor:pointer; font-size:20px;'
                          onclick='this.parentElement.style.display=\"none\"'>&times;</span>
                  </div>";
        }

        $today_s    = date("Y-m-d");
        $exp_date   = $row['expiration_date'];
        $quantity   = (int)$row['quantity'];

        if ($quantity <= 5) {
            echo "<div class='stock-warning'>
                    <span>⚠️</span>
                    <span><strong>Low Stock:</strong> This batch only has {$quantity} unit(s) remaining.</span>
                  </div>";
        }
        if ($exp_date && strtotime($exp_date) < strtotime($today_s)) {
            echo "<div class='expiry-warning'>
                    <span>⚠️</span>
                    <span><strong>Expired Batch!</strong> This batch expired on " . date('M d, Y', strtotime($exp_date)) . ". Consider deleting it.</span>
                  </div>";
        } elseif ($exp_date && strtotime($exp_date) < strtotime('+30 days')) {
            $days = ceil((strtotime($exp_date) - strtotime($today_s)) / 86400);
            echo "<div class='stock-warning'>
                    <span>📅</span>
                    <span><strong>Expiring Soon!</strong> This batch expires in {$days} day(s) on " . date('M d, Y', strtotime($exp_date)) . ".</span>
                  </div>";
        }
        ?>

        <div class="medicine-preview">
            <div class="preview-item">
                <span class="preview-label">Batch ID:</span>
                <span class="preview-value">#<?php echo (int)$row['id']; ?></span>
            </div>
            <div class="preview-item">
                <span class="preview-label">Date Added:</span>
                <span class="preview-value"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></span>
            </div>
            <div class="preview-item">
                <span class="preview-label">Current Stock:</span>
                <span class="preview-value"
                      style="color:<?php echo $quantity <= 5 ? '#e67e22' : '#2c3e50'; ?>; font-weight:bold;">
                    <?php echo $quantity; ?> units
                </span>
            </div>
            <div class="preview-item">
                <span class="preview-label">Current Expiry:</span>
                <span class="preview-value"
                      style="color:<?php echo ($exp_date && strtotime($exp_date) < strtotime($today_s)) ? '#e74c3c' : '#2c3e50'; ?>;">
                    <?php echo $exp_date ? date('M d, Y', strtotime($exp_date)) : 'N/A'; ?>
                </span>
            </div>
        </div>

        <form method="POST" action="update.php" id="editForm">
            <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">

            <div class="form-group">
                <label>Medicine Name <span class="required">*</span></label>
                <input type="text" name="name"
                       value="<?php echo htmlspecialchars($row['name']); ?>" required>
            </div>

            <div class="form-group">
                <label>Description <span class="required">*</span></label>
                <input type="text" name="label"
                       value="<?php echo htmlspecialchars((string)$row['label']); ?>" required>
            </div>

            <div class="form-group">
                <label>Quantity <span class="required">*</span></label>
                <input type="number" name="quantity" id="quantity"
                       value="<?php echo $quantity; ?>" min="0" required>
                <small style="color:#7f8c8d; margin-top:5px; display:block;">
                    Adjust the quantity for this specific batch only.
                </small>
            </div>

            <div class="form-group">
                <label>Expiration Date <span class="required">*</span></label>
                <input type="date" name="exp"
                       value="<?php echo htmlspecialchars((string)$row['expiration_date']); ?>" required>
                <small style="color:#7f8c8d; margin-top:5px; display:block;">
                    This updates the expiration date for this batch only.
                </small>
            </div>

            <div class="button-group">
                <button type="submit" name="update" class="btn-update">
                    💾 Update Batch
                </button>
                <a href="index.php" class="btn-cancel">❌ Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
    setTimeout(function() {
        const a = document.getElementById('alertMessage');
        if (a) a.style.display = 'none';
    }, 5000);

    const qtyInput = document.getElementById('quantity');
    if (qtyInput) {
        qtyInput.addEventListener('change', function() {
            if (this.value < 0) this.value = 0;
        });
    }

    let formChanged = false;
    const form   = document.getElementById('editForm');
    const inputs = form.querySelectorAll('input');
    inputs.forEach(inp => inp.addEventListener('change', () => { formChanged = true; }));
    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = 'You have unsaved changes. Leave anyway?';
        }
    });
    form.addEventListener('submit', () => { formChanged = false; });
</script>

</body>
</html>