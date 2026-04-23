<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/header.php';

$message = '';
$msg_type = '';

if (isset($_POST['import']) && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    
    if (empty($file)) {
        $message = "Please select a file to upload.";
        $msg_type = "error";
    } else {
        $handle = fopen($file, "r");
        if ($handle !== FALSE) {
            // Skip the header row
            fgetcsv($handle, 1000, ",");
            
            $success_count = 0;
            $error_count = 0;
            
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Expected CSV format: Name, Description, Category, Unit, Type(medicine/consumable), Quantity, Expiration Date(YYYY-MM-DD)
                if (count($data) >= 6) {
                    $name     = $conn->real_escape_string(trim($data[0]));
                    $label    = $conn->real_escape_string(trim($data[1]));
                    $category = $conn->real_escape_string(trim($data[2] ?? 'General'));
                    $unit     = $conn->real_escape_string(trim($data[3] ?? 'pcs'));
                    $type     = $conn->real_escape_string(trim(strtolower($data[4] ?? 'medicine')));
                    $qty      = intval($data[5]);
                    $exp      = trim($data[6] ?? '');

                    // Validation
                    if (empty($name) || empty($label) || $qty < 0) {
                        $error_count++;
                        continue;
                    }
                    if ($type !== 'medicine' && $type !== 'consumable') {
                        $type = 'medicine';
                    }
                    $val_exp = (!empty($exp) && strtotime($exp)) ? "'" . $conn->real_escape_string($exp) . "'" : "NULL";
                    
                    if ($type === 'medicine' && $val_exp === "NULL") {
                        $error_count++; // skip medicines without expiry
                        continue;
                    }

                    // Determine batch number
                    $bn_res = $conn->query("SELECT MAX(batch_number) AS max_bn FROM medicines WHERE name = '$name' AND label = '$label' AND type = '$type'");
                    $next_bn = 1;
                    if ($bn_res && $row = $bn_res->fetch_assoc()) {
                        $next_bn = intval($row['max_bn']) + 1;
                    }

                    $sql = "INSERT INTO medicines (name, label, type, category, unit, batch_number, quantity, expiration_date) 
                            VALUES ('$name', '$label', '$type', '$category', '$unit', $next_bn, $qty, $val_exp)";
                    
                    if ($conn->query($sql)) {
                        $new_id = $conn->insert_id;
                        $conn->query("INSERT INTO logs (medicine_id, quantity, action) VALUES ($new_id, $qty, 'Imported via CSV')");
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                } else {
                    $error_count++;
                }
            }
            fclose($handle);
            
            $message = "Import complete. $success_count items added successfully.";
            if ($error_count > 0) {
                $message .= " $error_count rows failed (check formatting or missing required fields).";
            }
            $msg_type = $error_count > 0 ? "warning" : "success";
        } else {
            $message = "Error opening the uploaded file.";
            $msg_type = "error";
        }
    }
}
?>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            min-height: 100vh;
        }

        .container { max-width: 800px; margin: 40px auto; padding: 0 20px; }

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

        .upload-area {
            border: 2px dashed #3498db;
            border-radius: 10px;
            padding: 40px 20px;
            text-align: center;
            background: #f0f8ff;
            margin-bottom: 25px;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }
        .upload-area:hover, .upload-area.dragover {
            background: #e1f0fa;
            border-color: #2980b9;
        }
        .upload-icon { font-size: 40px; color: #3498db; margin-bottom: 10px; }
        .upload-text { color: #2c3e50; font-weight: 600; font-size: 16px; margin-bottom: 5px; }
        .upload-subtext { color: #7f8c8d; font-size: 13px; }
        
        input[type="file"] {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            opacity: 0; cursor: pointer;
        }

        #fileName {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #27ae60;
        }

        .btn-submit {
            width: 100%; padding: 14px;
            background: #1f4f87; border: none;
            color: white; font-size: 16px; font-weight: 600;
            border-radius: 8px; cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(31,79,135,0.4); }

        .instructions {
            margin-top: 30px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #f39c12;
        }
        .instructions h4 { color: #2c3e50; margin-bottom: 10px; }
        .instructions ul { padding-left: 20px; color: #7f8c8d; font-size: 14px; }
        .instructions li { margin-bottom: 5px; }
        
        .csv-format {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 13px;
            margin-top: 10px;
            overflow-x: auto;
        }

        /* Toast */
        #toastContainer { position: fixed; bottom: 30px; right: 30px; z-index: 10001; }
        .toast {
            background: white; padding: 15px 25px; border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            display: flex; align-items: center; gap: 12px; margin-top: 10px;
            transform: translateX(120%); transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            border-left: 5px solid #27ae60;
        }
        .toast.show { transform: translateX(0); }
        .toast.error { border-left-color: #e74c3c; }
        .toast.warning { border-left-color: #f39c12; }

    </style>
</head>
<body>

<div id="toastContainer"></div>

<div class="container">
    <div class="form-card">
        <div class="form-header">
            <div class="icon">📥</div>
            <h2>Stock Upload</h2>
            <p>Upload a CSV file to add multiple items at once</p>
        </div>

        <form method="POST" action="import.php" enctype="multipart/form-data">
            <div class="upload-area" id="dropZone">
                <div class="upload-icon">📄</div>
                <div class="upload-text">Drag and drop your CSV file here</div>
                <div class="upload-subtext">or click to browse from your computer</div>
                <span id="fileName"></span>
                <input type="file" name="csv_file" id="csvFile" accept=".csv" required>
            </div>

            <button type="submit" name="import" class="btn-submit">🚀 Start Import</button>
        </form>

        <div class="instructions">
            <h4>⚠️ Important CSV Format Rules:</h4>
            <ul>
                <li>The first row must be headers (it will be skipped).</li>
                <li>Ensure the columns are in this exact order.</li>
                <li><strong>Expiration Date</strong> format must be <code>YYYY-MM-DD</code> (e.g., 2026-12-31).</li>
                <li>Leave expiration date empty for consumables without expiry.</li>
            </ul>
            <div class="csv-format">
                Name, Description, Category, Unit, Type, Quantity, Expiration Date<br>
                Paracetamol, 500mg, Tablet, pcs, medicine, 100, 2026-12-31<br>
                Alcohol, 70% Isopropyl, General, bottle, consumable, 50,<br>
            </div>
            <a href="sample.csv" download style="display:inline-block; margin-top:10px; color:#3498db; font-weight:bold; text-decoration:none;">⬇️ Download Sample CSV</a>
        </div>
    </div>
</div>

<script>
    const fileInput = document.getElementById('csvFile');
    const fileNameDisplay = document.getElementById('fileName');
    const dropZone = document.getElementById('dropZone');

    fileInput.addEventListener('change', function() {
        if(this.files && this.files[0]) {
            fileNameDisplay.textContent = 'Selected: ' + this.files[0].name;
        } else {
            fileNameDisplay.textContent = '';
        }
    });

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover'), false);
    });

    function showToast(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        let icon = '✅';
        if(type === 'error') icon = '❌';
        if(type === 'warning') icon = '⚠️';
        
        toast.className = `toast ${type}`;
        toast.innerHTML = `<span>${icon}</span><span>${message}</span>`;
        container.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 100);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 400);
        }, 5000);
    }

    <?php if ($message): ?>
        window.onload = () => showToast("<?php echo addslashes($message); ?>", "<?php echo $msg_type; ?>");
    <?php endif; ?>
</script>

</body>
</html>
