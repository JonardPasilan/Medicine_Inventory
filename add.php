<?php 
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/header.php';

// Pre-fill values when coming from "New Batch" button
$prefill_name  = isset($_GET['name'])  ? htmlspecialchars($_GET['name'],  ENT_QUOTES, 'UTF-8') : '';
$prefill_label = isset($_GET['label']) ? htmlspecialchars($_GET['label'], ENT_QUOTES, 'UTF-8') : '';
$prefill_type  = isset($_GET['type'])  ? htmlspecialchars($_GET['type'],  ENT_QUOTES, 'UTF-8') : 'medicine';
$prefill_cat   = isset($_GET['cat'])   ? htmlspecialchars($_GET['cat'],   ENT_QUOTES, 'UTF-8') : 'General';
$prefill_unit  = isset($_GET['unit'])  ? htmlspecialchars($_GET['unit'],  ENT_QUOTES, 'UTF-8') : 'pcs';
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
            animation: fadeIn 0.6s cubic-bezier(0.23, 1, 0.32, 1);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px) scale(0.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .form-header h2 { color: #2c3e50; font-size: 28px; margin-bottom: 10px; }
        .form-header p  { color: #7f8c8d; font-size: 14px; }
        .form-header .icon { font-size: 50px; margin-bottom: 10px; transition: transform 0.5s ease; }
        .form-card:hover .icon { transform: rotate(15deg) scale(1.1); }

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
            animation: slideInLeft 0.5s ease forwards;
        }
        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

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
            transform: translateY(-1px);
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
            position: relative;
            overflow: hidden;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(31,79,135,0.4);
        }
        .btn-submit:active { transform: translateY(0); }

        /* Ripple Effect */
        .ripple {
            position: absolute;
            background: rgba(255, 255, 255, 0.4);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple-animation 0.6s linear;
            pointer-events: none;
        }
        @keyframes ripple-animation {
            to { transform: scale(4); opacity: 0; }
        }

        /* Loading Spinner */
        #loadingOverlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(255,255,255,0.7);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 15px;
            backdrop-filter: blur(2px);
        }
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* Toast Notification */
        #toastContainer {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 10001;
        }
        .toast {
            background: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 10px;
            transform: translateX(120%);
            transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            border-left: 5px solid #27ae60;
        }
        .toast.show { transform: translateX(0); }
        .toast.error { border-left-color: #e74c3c; }
        .toast-icon { font-size: 20px; }

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
            .container { margin: 20px auto; }
            .form-card { padding: 25px; }
            .form-header h2 { font-size: 24px; }
        }
    </style>
</head>
<body>

<div id="loadingOverlay">
    <div class="spinner"></div>
    <p style="color: #1f4f87; font-weight: 600;">Saving item...</p>
</div>

<div id="toastContainer"></div>

<div class="container">
    <div class="form-card">
        <div class="form-header">
            <div class="icon"><?php 
                if ($prefill_type == 'medicine') echo '💊';
                elseif ($prefill_type == 'consumable') echo '🧴';
                elseif ($prefill_type == 'dental') echo '🦷';
                else echo '🩺'; 
            ?></div>
            <h2><?php echo $is_new_batch ? 'Add New Batch' : 'Add New Item'; ?></h2>
            <p><?php echo $is_new_batch
                ? 'Creating a new batch entry for this ' . $prefill_type
                : 'Enter the details of the ' . $prefill_type . ' to add to inventory'; ?></p>
        </div>

        <?php if ($is_new_batch): ?>
        <div class="batch-banner">
            <span>📦</span>
            <div>
                <strong>New Batch For: <?php echo $prefill_name; ?></strong><br>
                <small>A separate batch record will be created with its own quantity and expiration date.</small>
            </div>
        </div>
        <?php endif; ?>

        <?php
        $status_msg = '';
        $status_type = 'success';
        if (isset($_POST['add'])) {
            $n = mysqli_real_escape_string($conn, trim($_POST['name']       ?? ''));
            $l = mysqli_real_escape_string($conn, trim($_POST['Description'] ?? ''));
            $t = mysqli_real_escape_string($conn, trim($_POST['type']        ?? 'medicine'));
            $c = mysqli_real_escape_string($conn, trim($_POST['category']    ?? 'General'));
            $u = mysqli_real_escape_string($conn, trim($_POST['unit']        ?? 'pcs'));
            $q = intval($_POST['quantity'] ?? 0);
            $e = mysqli_real_escape_string($conn, trim($_POST['exp']        ?? ''));
            
            // Equipment fields
            $brand = mysqli_real_escape_string($conn, trim($_POST['brand_serial'] ?? ''));
            $ris = mysqli_real_escape_string($conn, trim($_POST['ris_id'] ?? ''));
            $color = mysqli_real_escape_string($conn, trim($_POST['color'] ?? ''));
            $date_acq = mysqli_real_escape_string($conn, trim($_POST['date_acquired'] ?? ''));
            $qsrv = intval($_POST['qty_serviceable'] ?? 0);
            $qunsrv = intval($_POST['qty_unserviceable'] ?? 0);
            $qrep = intval($_POST['qty_repair'] ?? 0);
            $rem = mysqli_real_escape_string($conn, trim($_POST['remarks'] ?? ''));

            $errors = [];
            if (empty($n)) $errors[] = "Name is required.";
            if (empty($l)) $errors[] = "Description is required.";
            if ($q < 0)   $errors[] = "Quantity cannot be negative.";
            
            // Only required for medicines
            if ($t === 'medicine' && empty($e)) {
                $errors[] = "Expiration date is required for medicines.";
            }

            if (empty($errors)) {
                $bn_res = $conn->query("SELECT MAX(batch_number) AS max_bn FROM medicines WHERE name = '$n' AND label = '$l' AND type = '$t'");
                $next_bn = 1;
                if ($bn_res && $row = $bn_res->fetch_assoc()) {
                    $next_bn = intval($row['max_bn']) + 1;
                }

                $val_exp = !empty($e) ? "'$e'" : "NULL";
                $val_acq = !empty($date_acq) ? "'$date_acq'" : "NULL";
                
                $sql = "INSERT INTO medicines (name, label, type, category, unit, batch_number, quantity, expiration_date, brand_serial, ris_id, color, date_acquired, qty_serviceable, qty_unserviceable, qty_repair, remarks)
                        VALUES ('$n', '$l', '$t', '$c', '$u', $next_bn, '$q', $val_exp, '$brand', '$ris', '$color', $val_acq, $qsrv, $qunsrv, $qrep, '$rem')";

                if ($conn->query($sql)) {
                    $new_id = $conn->insert_id;
                    $conn->query("INSERT INTO logs (medicine_id, quantity, action)
                                  VALUES ($new_id, $q, 'New Batch Added')");
                    $status_msg = ($is_new_batch ? "New batch added for $n" : "$n added") . "! Qty: $q units.";
                    $status_type = 'success';
                } else {
                    $status_msg = "Error: " . $conn->error;
                    $status_type = 'error';
                }
            } else {
                $status_msg = implode(" ", $errors);
                $status_type = 'error';
            }
        }
        ?>

        <form method="POST" id="addForm" onsubmit="handleSubmit(event)">
            <div class="form-group">
                <label>Item Type <span class="required">*</span></label>
                <select name="type" id="typeSelect" required <?php echo $is_new_batch ? 'disabled' : ''; ?> onchange="updateRequiredFields()">
                    <option value="medicine" <?php echo $prefill_type == 'medicine' ? 'selected' : ''; ?>>💊 Medicine</option>
                    <option value="consumable" <?php echo $prefill_type == 'consumable' ? 'selected' : ''; ?>>🧴 Consumable</option>
                    <option value="dental" <?php echo $prefill_type == 'dental' ? 'selected' : ''; ?>>🦷 Dental Device & Equipment</option>
                    <option value="medical" <?php echo $prefill_type == 'medical' ? 'selected' : ''; ?>>🩺 Medical Device & Equipment</option>
                </select>
                <?php if($is_new_batch): ?>
                    <input type="hidden" name="type" value="<?php echo $prefill_type; ?>">
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Item Name <span class="required">*</span></label>
                <input type="text" name="name"
                       value="<?php echo $prefill_name; ?>"
                       placeholder="e.g., Paracetamol, Gauze, Alcohol" required>
            </div>

            <div class="form-group">
                <label>Description <span class="required">*</span></label>
                <input type="text" name="Description"
                       value="<?php echo $prefill_label; ?>"
                       placeholder="e.g., 500mg tablet, 100pcs/box" required>
            </div>

            <div class="form-group" style="display:flex; gap:15px;">
                <div style="flex:1;" id="categoryGroup">
                    <label>Category <span class="required">*</span></label>
                    <input type="text" name="category" list="categoryList"
                           value="<?php echo $prefill_cat; ?>"
                           placeholder="e.g., Tablet, Syrup, Injectable" required <?php echo $is_new_batch ? 'readonly' : ''; ?>>
                    <datalist id="categoryList">
                        <option value="Tablet">
                        <option value="Syrup">
                        <option value="Capsule">
                        <option value="Injectable">
                        <option value="Topical">
                        <option value="Drops">
                        <option value="General">
                        <option value="Consumable">
                    </datalist>
                </div>
                <div style="flex:1;" id="unitGroup">
                    <label>Unit <span class="required">*</span></label>
                    <input type="text" name="unit" list="unitList"
                           value="<?php echo $prefill_unit; ?>"
                           placeholder="e.g., pcs, box, ml" required <?php echo $is_new_batch ? 'readonly' : ''; ?>>
                    <datalist id="unitList">
                        <option value="pcs">
                        <option value="PCS">
                        <option value="SET">
                        <option value="Unit">
                        <option value="box">
                        <option value="ml">
                        <option value="mg">
                        <option value="vial">
                        <option value="bottle">
                        <option value="pack">
                    </datalist>
                </div>
            </div>

            <div class="form-group">
                <label>Quantity <span class="required">*</span></label>
                <input type="number" name="quantity" min="0"
                       placeholder="Enter number of units" required>
            </div>

            <div class="form-group" id="expGroup">
                <label>Expiration Date <span class="required" id="expReq" style="<?php echo $prefill_type == 'medicine' ? '' : 'display:none;'; ?>">*</span></label>
                <input type="date" name="exp" id="expDate" <?php echo $prefill_type == 'medicine' ? 'required' : ''; ?>>
                <small style="color:#7f8c8d; display:block; margin-top:5px;">
                    Set the expiration date for this batch. (Optional for consumables)
                </small>
            </div>

            <!-- Equipment Specific Fields -->
            <div id="equipmentFields" style="display:none; background: #eef2f7; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <h4 style="margin-bottom: 15px; color: #2c3e50;">Equipment Details</h4>
                <div class="form-group" style="display:flex; gap:15px;">
                    <div style="flex:1;">
                        <label>Brand/Serial #</label>
                        <input type="text" name="brand_serial" placeholder="e.g., SN-12345">
                    </div>
                    <div style="flex:1;">
                        <label>RIS # / ICS # / PAR #</label>
                        <input type="text" name="ris_id" placeholder="e.g., RIS No. 21-6-136">
                    </div>
                </div>
                <div class="form-group" style="display:flex; gap:15px;">
                    <div style="flex:1;">
                        <label>Color</label>
                        <input type="text" name="color" placeholder="e.g., Blue, Orange">
                    </div>
                    <div style="flex:1;">
                        <label>DATE PROCURED</label>
                        <input type="date" name="date_acquired">
                    </div>
                </div>
                <div class="form-group" style="display:flex; gap:10px;">
                    <div style="flex:1;">
                        <label>Serviceable</label>
                        <input type="number" name="qty_serviceable" min="0" value="0">
                    </div>
                    <div style="flex:1;">
                        <label>Unserviceable</label>
                        <input type="number" name="qty_unserviceable" min="0" value="0">
                    </div>
                    <div style="flex:1;">
                        <label>For Repair</label>
                        <input type="number" name="qty_repair" min="0" value="0">
                    </div>
                </div>
                <div class="form-group">
                    <label>Remarks / Notes</label>
                    <textarea name="remarks" rows="2" placeholder=""></textarea>
                </div>
            </div>

            <button type="submit" name="add" class="btn-submit">
                <?php echo $is_new_batch ? '📦 Add New Batch to Inventory' : '➕ Add to Inventory'; ?>
            </button>
        </form>

        <div class="info-box">
            <p>📊 <a href="index.php">← Back to Inventory</a></p>
        </div>
    </div>
</div>

<script>
    // Ripple Effect
    document.querySelectorAll('.btn-submit').forEach(button => {
        button.addEventListener('click', function(e) {
            let x = e.clientX - e.target.getBoundingClientRect().left;
            let y = e.clientY - e.target.getBoundingClientRect().top;
            let ripples = document.createElement('span');
            ripples.className = 'ripple';
            ripples.style.left = x + 'px';
            ripples.style.top = y + 'px';
            this.appendChild(ripples);
            setTimeout(() => { ripples.remove() }, 600);
        });
    });

    function showLoading() {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }

    function showToast(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast ${type === 'error' ? 'error' : ''}`;
        toast.innerHTML = `
            <span class="toast-icon">${type === 'success' ? '✅' : '❌'}</span>
            <span>${message}</span>
        `;
        container.appendChild(toast);
        setTimeout(() => { toast.classList.add('show'); }, 100);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => { toast.remove(); }, 400);
        }, 5000);
    }

    function handleSubmit(e) {
        showLoading();
    }

    // PHP passed status
    <?php if ($status_msg): ?>
        window.onload = function() {
            showToast("<?php echo addslashes($status_msg); ?>", "<?php echo $status_type; ?>");
            <?php if ($status_type === 'success'): ?>
                setTimeout(() => { document.getElementById('addForm').reset(); updateRequiredFields(); }, 500);
            <?php endif; ?>
        };
    <?php endif; ?>

    function updateRequiredFields() {
        const type = document.getElementById('typeSelect').value;
        const expInput = document.getElementById('expDate');
        const expStar = document.getElementById('expReq');
        const eqFields = document.getElementById('equipmentFields');
        const categoryGroup = document.getElementById('categoryGroup');
        const expGroup = document.getElementById('expGroup');
        const catInput = document.querySelector('input[name="category"]');
        
        if (type === 'medicine') {
            expInput.required = true;
            expStar.style.display = 'inline';
        } else {
            expInput.required = false;
            expStar.style.display = 'none';
        }

        if (type === 'dental' || type === 'medical') {
            eqFields.style.display = 'block';
            categoryGroup.style.display = 'none';
            expGroup.style.display = 'none';
            catInput.required = false;
        } else {
            eqFields.style.display = 'none';
            categoryGroup.style.display = 'block';
            expGroup.style.display = 'block';
            catInput.required = true;
        }
    }

    // Initial check
    document.addEventListener('DOMContentLoaded', updateRequiredFields);
</script>

</body>
</html>