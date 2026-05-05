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
            $import_type = $_POST['import_type'] ?? 'medicine';
            
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($import_type === 'medicine' || $import_type === 'consumable') {
                    // Supports both:
                    //   6-col (new): Name, Description, Category, Unit, Quantity, Expiration Date
                    //   7-col (old): Name, Description, Category, Unit, Type, Quantity, Expiration Date
                    $col_count = count($data);
                    if ($col_count >= 5) {
                        $name     = $conn->real_escape_string(trim($data[0]));
                        $label    = $conn->real_escape_string(trim($data[1]));
                        $category = $conn->real_escape_string(trim($data[2] ?? 'General'));
                        $unit     = $conn->real_escape_string(trim($data[3] ?? 'pcs'));

                        // Auto-detect: if col[4] is non-numeric (e.g. 'medicine','consumable'),
                        // treat it as the old Type column and shift qty/exp right by 1.
                        $col4 = trim($data[4] ?? '');
                        if (!is_numeric($col4)) {
                            // Old 7-column format — skip the Type column, force type from dropdown
                            $qty = intval($data[5] ?? 0);
                            $exp = trim($data[6] ?? '');
                        } else {
                            // New 6-column format
                            $qty = intval($col4);
                            $exp = trim($data[5] ?? '');
                        }

                        // Force the type from the dropdown — never trust the CSV column
                        $type = $import_type === 'consumable' ? 'consumable' : 'medicine';

                        // Validation
                        if (empty($name) || $qty <= 0) {
                            $error_count++;
                            continue;
                        }

                        $val_exp = (!empty($exp) && strtotime($exp)) ? "'" . $conn->real_escape_string($exp) . "'" : "NULL";

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
                } else if ($import_type === 'dental' || $import_type === 'medical') {
                    // Expected: Item Name, Unit, Brand/Serial, RIS/ICS/PAR, Color, Date Procured, Qty Serviceable, Qty Unserviceable, Qty Repair, Remarks
                    if (count($data) >= 7) {
                        $name     = $conn->real_escape_string(trim($data[0]));
                        $unit     = $conn->real_escape_string(trim($data[1] ?? 'Unit'));
                        $brand    = $conn->real_escape_string(trim($data[2] ?? ''));
                        $ris      = $conn->real_escape_string(trim($data[3] ?? ''));
                        $color    = $conn->real_escape_string(trim($data[4] ?? ''));
                        $date_acq = trim($data[5] ?? '');
                        $qsrv     = intval($data[6] ?? 0);
                        $qunsrv   = intval($data[7] ?? 0);
                        $qrep     = intval($data[8] ?? 0);
                        $remarks  = $conn->real_escape_string(trim($data[9] ?? ''));
                        
                        $qty = $qsrv + $qunsrv + $qrep;
                        
                        if (empty($name)) {
                            $error_count++;
                            continue;
                        }

                        $val_acq = (!empty($date_acq) && strtotime($date_acq)) ? "'" . $conn->real_escape_string($date_acq) . "'" : "NULL";

                        $sql = "INSERT INTO medicines 
                                (name, label, type, category, unit, batch_number, quantity, expiration_date, brand_serial, ris_id, color, date_acquired, qty_serviceable, qty_unserviceable, qty_repair, remarks, is_archived) 
                                VALUES 
                                ('$name', '', '$import_type', '', '$unit', 1, $qty, NULL, '$brand', '$ris', '$color', $val_acq, $qsrv, $qunsrv, $qrep, '$remarks', 0)";

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
        .container { max-width: 800px; margin: 40px auto; padding: 0 20px; }

        .form-card {
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-lg);
            padding: 35px;
            box-shadow: var(--shadow-md);
            animation: fadeInUp 0.4s ease;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .form-header { text-align: center; margin-bottom: 30px; }
        .form-header .icon { font-size: 48px; margin-bottom: 10px; }
        .form-header h2 { color: var(--color-text-primary); font-size: var(--text-2xl); margin-bottom: 6px; }
        .form-header p  { color: var(--color-text-secondary); font-size: var(--text-sm); }

        /* Type Selector Tabs */
        .type-tabs {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 24px;
        }
        .type-tab {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            padding: 14px 8px;
            border: 2px solid var(--color-border);
            border-radius: var(--radius-md);
            cursor: pointer;
            background: var(--color-overlay);
            color: var(--color-text-secondary);
            font-size: var(--text-xs);
            font-weight: 600;
            text-align: center;
            transition: all var(--transition-base);
            user-select: none;
        }
        .type-tab .tab-emoji { font-size: 24px; }
        .type-tab:hover {
            border-color: var(--color-brand);
            color: var(--color-brand);
        }
        .type-tab.active {
            border-color: var(--color-brand);
            background: var(--color-brand-light);
            color: var(--color-brand-dark);
        }
        [data-theme="dark"] .type-tab:hover,
        [data-theme="dark"] .type-tab.active {
            box-shadow: 0 0 8px var(--color-brand), 0 0 16px var(--color-brand);
        }

        /* Hidden real select */
        #importTypeSelect { display: none; }

        .upload-area {
            border: 2px dashed var(--color-border-strong);
            border-radius: var(--radius-md);
            padding: 40px 20px;
            text-align: center;
            background: var(--color-overlay);
            margin-bottom: 25px;
            transition: all var(--transition-base);
            cursor: pointer;
            position: relative;
        }
        .upload-area:hover, .upload-area.dragover {
            background: var(--color-brand-light);
            border-color: var(--color-brand);
        }
        .upload-icon { font-size: 36px; margin-bottom: 10px; }
        .upload-text { color: var(--color-text-primary); font-weight: 600; font-size: var(--text-base); margin-bottom: 4px; }
        .upload-subtext { color: var(--color-text-muted); font-size: var(--text-sm); }
        
        input[type="file"] {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            opacity: 0; cursor: pointer;
        }

        #fileName {
            display: block;
            margin-top: 12px;
            font-weight: 600;
            color: hsl(140, 70%, 40%);
            font-size: var(--text-sm);
        }

        .btn-submit {
            width: 100%; padding: 14px;
            background: var(--color-brand); border: none;
            color: white; font-size: var(--text-base); font-weight: 600;
            border-radius: var(--radius-md); cursor: pointer;
            font-family: 'Inter', sans-serif;
            transition: all var(--transition-base);
        }
        .btn-submit:hover {
            background: var(--color-brand-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(90, 72, 220, 0.4);
        }

        /* Instructions box */
        .instructions {
            margin-top: 24px;
            background: var(--color-overlay);
            padding: 20px;
            border-radius: var(--radius-md);
            border-left: 4px solid var(--color-brand);
            display: none;
        }
        .instructions.active { display: block; }
        .instructions h4 { color: var(--color-text-primary); margin-bottom: 10px; font-size: var(--text-sm); }
        .instructions ul { padding-left: 20px; color: var(--color-text-secondary); font-size: var(--text-sm); }
        .instructions li { margin-bottom: 5px; }
        
        .csv-format {
            background: hsl(220, 20%, 12%);
            color: hsl(140, 80%, 70%);
            padding: 12px 14px;
            border-radius: var(--radius-sm);
            font-family: monospace;
            font-size: 12px;
            margin-top: 10px;
            overflow-x: auto;
            line-height: 1.7;
        }

        .download-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 12px;
            color: var(--color-brand);
            font-weight: 600;
            font-size: var(--text-sm);
            text-decoration: none;
        }
        .download-link:hover { text-decoration: underline; }

        @media (max-width: 600px) {
            .type-tabs { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="form-card">
        <div class="form-header">
            <div class="icon">📥</div>
            <h2>Stock Upload</h2>
            <p>Upload a CSV file to add multiple items at once</p>
        </div>

        <form method="POST" action="import.php" enctype="multipart/form-data">
            <!-- Hidden select that gets submitted -->
            <select name="import_type" id="importTypeSelect">
                <option value="medicine">medicine</option>
                <option value="consumable">consumable</option>
                <option value="dental">dental</option>
                <option value="medical">medical</option>
            </select>

            <!-- Visual tab picker -->
            <div class="type-tabs">
                <div class="type-tab active" data-value="medicine">
                    <span class="tab-emoji">💊</span>
                    Medicine
                </div>
                <div class="type-tab" data-value="consumable">
                    <span class="tab-emoji">🧴</span>
                    Consumable
                </div>
                <div class="type-tab" data-value="dental">
                    <span class="tab-emoji">🦷</span>
                    Dental Device
                </div>
                <div class="type-tab" data-value="medical">
                    <span class="tab-emoji">🩺</span>
                    Medical Device
                </div>
            </div>

            <div class="upload-area" id="dropZone">
                <div class="upload-icon">📄</div>
                <div class="upload-text">Drag and drop your CSV file here</div>
                <div class="upload-subtext">or click to browse from your computer</div>
                <span id="fileName"></span>
                <input type="file" name="csv_file" id="csvFile" accept=".csv" required>
            </div>

            <button type="submit" name="import" class="btn-submit">🚀 Start Import</button>
        </form>

        <!-- Instructions: Medicine -->
        <div class="instructions active" id="instructionsMedicine">
            <h4>⚠️ CSV Format for <strong>Medicine</strong> Import:</h4>
            <ul>
                <li>The first row must be headers (it will be skipped).</li>
                <li>All rows will be tagged as <strong>type = medicine</strong> regardless of any column value.</li>
                <li><strong>Expiration Date</strong> format must be <code>YYYY-MM-DD</code>. Leave blank if none.</li>
            </ul>
            <div class="csv-format">
                Name, Description, Category, Unit, Quantity, Expiration Date<br>
                Paracetamol, 500mg, Tablet, pcs, 100, 2026-12-31<br>
                Amoxicillin, 250mg, Capsule, pcs, 50, 2025-06-30
            </div>
            <a href="Sample%20med.csv" download class="download-link">⬇️ Download Sample med CSV</a>
        </div>

        <!-- Instructions: Consumable -->
        <div class="instructions" id="instructionsConsumable">
            <h4>⚠️ CSV Format for <strong>Consumable</strong> Import:</h4>
            <ul>
                <li>The first row must be headers (it will be skipped).</li>
                <li>All rows will be tagged as <strong>type = consumable</strong> automatically — no need to add a Type column.</li>
                <li><strong>Expiration Date</strong> format must be <code>YYYY-MM-DD</code>. Leave blank if the item has no expiry.</li>
            </ul>
            <div class="csv-format">
                Name, Description, Category, Unit, Quantity, Expiration Date<br>
                Alcohol, 70% Isopropyl, General, bottle, 50,<br>
                Cotton Balls, Sterile, Supplies, pack, 30, 2027-01-01
            </div>
            <a href="sample%20consumable.csv" download class="download-link">⬇️ Download sample consumable CSV</a>
        </div>

        <!-- Instructions: Equipment -->
        <div class="instructions" id="instructionsEquipment">
            <h4>⚠️ CSV Format for <strong>Equipment</strong> Import:</h4>
            <ul>
                <li>The first row must be headers (it will be skipped).</li>
                <li><strong>Date Procured</strong> format must be <code>YYYY-MM-DD</code>. Leave blank if not applicable.</li>
                <li>Total quantity will be auto-computed from Serviceable + Unserviceable + Repair quantities.</li>
            </ul>
            <div class="csv-format">
                Item Name, Unit, Brand/Serial, RIS/ICS/PAR, Color, Date Procured, Qty Serviceable, Qty Unserviceable, Qty Repair, Remarks<br>
                Dental Chair, Unit, SN-1234, RIS-2023-01, White, 2023-01-15, 1, 0, 0, Good condition
            </div>
            <a href="sample%20dental.csv" id="downloadSampleBtn" download class="download-link">⬇️ Download sample dental CSV</a>
        </div>
    </div>
</div>

<script>
    const fileInput = document.getElementById('csvFile');
    const fileNameDisplay = document.getElementById('fileName');
    const dropZone = document.getElementById('dropZone');
    const importTypeSelect = document.getElementById('importTypeSelect');
    const typeTabs = document.querySelectorAll('.type-tab');

    // Instructions panels mapping
    const instructionPanels = {
        medicine:   document.getElementById('instructionsMedicine'),
        consumable: document.getElementById('instructionsConsumable'),
        dental:     document.getElementById('instructionsEquipment'),
        medical:    document.getElementById('instructionsEquipment'),
    };

    // Tab click handler
    typeTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            typeTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            const val = tab.dataset.value;
            importTypeSelect.value = val;

            // Show correct instructions
            Object.values(instructionPanels).forEach(p => p && p.classList.remove('active'));
            if (instructionPanels[val]) {
                instructionPanels[val].classList.add('active');
            }

            // Update equipment sample download link
            const dlBtn = document.getElementById('downloadSampleBtn');
            if (dlBtn) {
                if (val === 'dental') {
                    dlBtn.href = 'sample_dental.csv';
                    dlBtn.textContent = '⬇️ Download Dental Sample CSV';
                } else if (val === 'medical') {
                    dlBtn.href = 'sample_medical.csv';
                    dlBtn.textContent = '⬇️ Download Medical Sample CSV';
                }
            }
        });
    });

    // File picker
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            fileNameDisplay.textContent = 'Selected: ' + this.files[0].name;
        } else {
            fileNameDisplay.textContent = '';
        }
    });

    // Drag and drop
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, e => { e.preventDefault(); e.stopPropagation(); }, false);
    });
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover'), false);
    });
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover'), false);
    });
    dropZone.addEventListener('drop', e => {
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            fileNameDisplay.textContent = 'Selected: ' + files[0].name;
        }
    });

    <?php if ($message): ?>
        window.onload = () => {
            const title = "<?php echo ($msg_type === 'error' ? 'Import Failed' : ($msg_type === 'warning' ? 'Import Warning' : 'Import Successful')); ?>";
            showAlert(title, "<?php echo addslashes($message); ?>", "<?php echo $msg_type; ?>");
        };
    <?php endif; ?>
</script>

</body>
</html>
