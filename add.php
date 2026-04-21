<?php 
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/header.php';

// Pre-fill values when coming from "New Batch" button
$prefill_name  = isset($_GET['name'])  ? htmlspecialchars($_GET['name'],  ENT_QUOTES, 'UTF-8') : '';
$prefill_label = isset($_GET['label']) ? htmlspecialchars($_GET['label'], ENT_QUOTES, 'UTF-8') : '';
$is_new_batch  = ($prefill_name !== '');
?>


    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            min-height: 100vh;
        }


        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 0 20px;
        }

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

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .form-header h2 { color: #2c3e50; font-size: 28px; margin-bottom: 10px; }
        .form-header p  { color: #7f8c8d; font-size: 14px; }
        .form-header .icon { font-size: 50px; margin-bottom: 10px; }

        /* "Adding new batch for" banner */
        .batch-banner {
            background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
            border-left: 4px solid #27ae60;
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #1e8449;
        }
        .batch-banner strong { font-size: 15px; }

        .form-group { margin-bottom: 20px; }

        label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
            font-size: 14px;
        }
        label .required { color: #e74c3c; margin-left: 3px; }

        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s ease;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: #1f4f87;
            border: none;
            color: white;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(31,79,135,0.4);
        }
        .btn-submit:active { transform: translateY(0); }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from { transform: translateX(-20px); opacity: 0; }
            to   { transform: translateX(0); opacity: 1; }
        }
        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .alert-error {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .alert .close {
            margin-left: auto;
            cursor: pointer;
            font-size: 20px;
            font-weight: bold;
        }
        .alert .close:hover { opacity: 0.7; }

        .info-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            text-align: center;
            border: 1px solid #e0e0e0;
        }
        .info-box p { color: #7f8c8d; font-size: 13px; }
        .info-box a { color: #3498db; text-decoration: none; font-weight: 500; }
        .info-box a:hover { text-decoration: underline; }

        @media (max-width: 768px) {
            .nav { padding: 6px 8px; gap: 4px 6px; }
            .nav a { font-size: 12px; padding: 5px 10px; }
            .container { margin: 20px auto; }
            .form-card { padding: 25px; }
            .form-header h2 { font-size: 24px; }
        }
    </style>
</head>
<body>


<div class="container">
    <div class="form-card">
        <div class="form-header">
            <div class="icon"><?php echo $is_new_batch ? '📦' : '💊'; ?></div>
            <h2><?php echo $is_new_batch ? 'Add New Batch' : 'Add New Medicine'; ?></h2>
            <p><?php echo $is_new_batch
                ? 'Creating a new batch entry for this medicine'
                : 'Enter the details of the medicine to add to inventory'; ?></p>
        </div>

        <?php
        if ($is_new_batch): ?>
        <div class="batch-banner">
            <span>📦</span>
            <div>
                <strong>New Batch For: <?php echo $prefill_name; ?></strong><br>
                <small>A separate batch record will be created with its own quantity and expiration date.</small>
            </div>
        </div>
        <?php endif; ?>

        <?php
        if (isset($_POST['add'])) {
            $n = mysqli_real_escape_string($conn, trim($_POST['name']       ?? ''));
            $l = mysqli_real_escape_string($conn, trim($_POST['Description'] ?? ''));
            $q = intval($_POST['quantity'] ?? 0);
            $e = mysqli_real_escape_string($conn, trim($_POST['exp']        ?? ''));

            $errors = [];
            if (empty($n)) $errors[] = "Medicine name is required.";
            if (empty($l)) $errors[] = "Description is required.";
            if ($q < 0)   $errors[] = "Quantity cannot be negative.";
            if (empty($e)) $errors[] = "Expiration date is required.";

            if (empty($errors)) {
                $sql = "INSERT INTO medicines (name, label, quantity, expiration_date)
                        VALUES ('$n', '$l', '$q', '$e')";

                if ($conn->query($sql)) {
                    $new_id = $conn->insert_id;
                    // Log the new batch addition
                    $conn->query("INSERT INTO logs (medicine_id, quantity, action)
                                  VALUES ($new_id, $q, 'New Batch Added')");

                    echo "<div class='alert alert-success' id='alertMessage'>
                            <span>✅</span>
                            <span>" . ($is_new_batch ? "New batch added for <strong>$n</strong>" : "Medicine <strong>$n</strong> added") . "! Qty: <strong>$q</strong> units.</span>
                            <span class='close' onclick='this.parentElement.style.display=\"none\"'>&times;</span>
                          </div>";
                    echo "<script>setTimeout(function(){document.getElementById('addForm').reset();},800);</script>";
                } else {
                    echo "<div class='alert alert-error' id='alertMessage'>
                            <span>❌</span>
                            <span>Error: " . htmlspecialchars($conn->error) . "</span>
                            <span class='close' onclick='this.parentElement.style.display=\"none\"'>&times;</span>
                          </div>";
                }
            } else {
                foreach ($errors as $err) {
                    echo "<div class='alert alert-error'>
                            <span>⚠️</span>
                            <span>$err</span>
                          </div>";
                }
            }
        }
        ?>

        <form method="POST" id="addForm">
            <div class="form-group">
                <label>Medicine Name <span class="required">*</span></label>
                <input type="text" name="name"
                       value="<?php echo $prefill_name; ?>"
                       placeholder="e.g., Paracetamol, Amoxicillin" required>
            </div>

            <div class="form-group">
                <label>Description <span class="required">*</span></label>
                <input type="text" name="Description"
                       value="<?php echo $prefill_label; ?>"
                       placeholder="e.g., 500mg tablet, syrup" required>
            </div>

            <div class="form-group">
                <label>Quantity <span class="required">*</span></label>
                <input type="number" name="quantity" min="0"
                       placeholder="Enter number of units" required>
            </div>

            <div class="form-group">
                <label>Expiration Date <span class="required">*</span></label>
                <input type="date" name="exp" id="expDate" required>
                <small style="color:#7f8c8d; display:block; margin-top:5px;">
                    <?php echo $is_new_batch ? 'Set the expiration date for this new batch.' : 'Make sure to enter the correct expiration date.'; ?>
                </small>
            </div>

            <button type="submit" name="add" class="btn-submit">
                <?php echo $is_new_batch ? '📦 Add New Batch to Inventory' : '➕ Add Medicine to Inventory'; ?>
            </button>
        </form>

        <div class="info-box">
            <p>💡 <strong>Tip:</strong> Each batch can have a different quantity and expiration date. Dispensing follows <strong>FIFO</strong> (oldest expiry first).</p>
            <p style="margin-top: 10px;">📊 <a href="index.php">← Back to Inventory</a></p>
        </div>
    </div>
</div>

<script>
    setTimeout(function() {
        document.querySelectorAll('.alert').forEach(function(a) { a.style.display = 'none'; });
    }, 5000);
</script>

</body>
</html>