<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/header.php';

$status_msg  = '';
$status_type = 'success';

if (isset($_POST['add_equipment'])) {
    $n      = mysqli_real_escape_string($conn, trim($_POST['name']          ?? ''));
    $t      = mysqli_real_escape_string($conn, trim($_POST['type']          ?? 'dental'));
    $u      = mysqli_real_escape_string($conn, trim($_POST['unit']          ?? 'Unit'));
    $brand  = mysqli_real_escape_string($conn, trim($_POST['brand_serial']  ?? ''));
    $ris    = mysqli_real_escape_string($conn, trim($_POST['ris_id']        ?? ''));
    $color  = mysqli_real_escape_string($conn, trim($_POST['color']         ?? ''));
    $date_acq = mysqli_real_escape_string($conn, trim($_POST['date_acquired'] ?? ''));
    $qsrv   = intval($_POST['qty_serviceable']   ?? 0);
    $qunsrv = intval($_POST['qty_unserviceable'] ?? 0);
    $qrep   = intval($_POST['qty_repair']        ?? 0);
    $rem    = mysqli_real_escape_string($conn, trim($_POST['remarks'] ?? ''));

    // Quantity is always the sum of the three statuses
    $q = $qsrv + $qunsrv + $qrep;

    $errors = [];
    if (empty($n)) $errors[] = "Item Name is required.";
    if (!in_array($t, ['dental', 'medical'])) $errors[] = "Invalid item type.";

    if (empty($errors)) {
        $val_acq = !empty($date_acq) ? "'$date_acq'" : "NULL";
        $sql = "INSERT INTO medicines
                    (name, label, type, category, unit, batch_number, quantity,
                     expiration_date, brand_serial, ris_id, color, date_acquired,
                     qty_serviceable, qty_unserviceable, qty_repair, remarks, is_archived)
                VALUES
                    ('$n', '', '$t', '', '$u', 1, $q,
                     NULL, '$brand', '$ris', '$color', $val_acq,
                     $qsrv, $qunsrv, $qrep, '$rem', 0)";

        if ($conn->query($sql)) {
            $new_id = $conn->insert_id;
            $conn->query("INSERT INTO logs (medicine_id, quantity, action) VALUES ($new_id, $q, 'New Batch Added')");
            $status_msg  = "$n added to inventory! Total Qty: $q.";
            $status_type = 'success';
        } else {
            $status_msg  = "Error: " . $conn->error;
            $status_type = 'error';
        }
    } else {
        $status_msg  = implode(" ", $errors);
        $status_type = 'error';
    }
}
?>

<!-- Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; min-height: 100vh; }

    .container { max-width: 650px; margin: 40px auto; padding: 0 20px; }

    .form-card {
        background: white; border-radius: 15px; padding: 35px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        animation: fadeIn 0.5s ease;
    }
    @keyframes fadeIn { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }

    .form-header { text-align: center; margin-bottom: 30px; }
    .form-header .icon { font-size: 50px; margin-bottom: 10px; display:block; }
    .form-header h2 { color: #2c3e50; font-size: 26px; margin-bottom: 8px; }
    .form-header p { color: #7f8c8d; font-size: 14px; }

    .form-group { margin-bottom: 18px; }
    label { display: block; margin-bottom: 7px; color: #2c3e50; font-weight: 500; font-size: 14px; }
    label .required { color: #e74c3c; margin-left: 3px; }

    input, select, textarea {
        width: 100%; padding: 11px 14px;
        border: 2px solid #e0e0e0; border-radius: 8px;
        font-size: 14px; font-family: inherit;
        transition: all 0.3s ease;
    }
    input:focus, select:focus, textarea:focus {
        outline: none; border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
    }
    input[readonly] {
        background: #eef2f7; color: #7f8c8d; cursor: not-allowed;
    }

    .section-divider {
        border: none; border-top: 2px dashed #e0e0e0;
        margin: 24px 0;
    }
    .section-label {
        font-weight: 700; font-size: 13px; color: #7f8c8d;
        text-transform: uppercase; letter-spacing: 0.05em;
        margin-bottom: 14px;
    }

    .qty-grid { display: flex; gap: 12px; }
    .qty-grid > div { flex: 1; }
    .qty-summary {
        background: #eef2f7; border-radius: 8px;
        padding: 12px 15px; margin-top: 10px;
        font-size: 14px; color: #2c3e50; font-weight: 600;
        text-align: center;
    }

    .btn-submit {
        width: 100%; padding: 14px;
        background: #1f4f87; border: none; color: white;
        font-size: 16px; font-weight: 600; border-radius: 8px;
        cursor: pointer; transition: all 0.3s ease;
        margin-top: 10px;
    }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(31,79,135,0.4); }

    .info-box {
        background: #f8f9fa; border-radius: 8px; padding: 15px;
        margin-top: 20px; text-align: center; border: 1px solid #e0e0e0;
    }
    .info-box a { color: #3498db; text-decoration: none; font-weight: 500; }

    #loadingOverlay {
        display: none; position: fixed; inset: 0;
        background: rgba(255,255,255,0.7); z-index: 10000;
        align-items: center; justify-content: center;
        flex-direction: column; gap: 15px; backdrop-filter: blur(2px);
    }
    .spinner {
        width: 50px; height: 50px;
        border: 5px solid #f3f3f3; border-top: 5px solid #3498db;
        border-radius: 50%; animation: spin 1s linear infinite;
    }
    @keyframes spin { 0% { transform:rotate(0deg); } 100% { transform:rotate(360deg); } }

    #toastContainer { position: fixed; bottom: 30px; right: 30px; z-index: 10001; }
    .toast {
        background: white; padding: 15px 25px; border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        display: flex; align-items: center; gap: 12px; margin-top: 10px;
        transform: translateX(120%);
        transition: transform 0.4s cubic-bezier(0.68,-0.55,0.265,1.55);
        border-left: 5px solid #27ae60;
    }
    .toast.show { transform: translateX(0); }
    .toast.error { border-left-color: #e74c3c; }
</style>

<div id="loadingOverlay"><div class="spinner"></div><p style="color:#1f4f87;font-weight:600;">Saving item...</p></div>
<div id="toastContainer"></div>

<div class="container">
    <div class="form-card">
        <div class="form-header">
            <span class="icon">🦷🩺</span>
            <h2>Add Device & Equipment</h2>
            <p>For Dental and Medical equipment only. Quantity is auto-computed.</p>
        </div>

        <form method="POST" id="eqForm" onsubmit="document.getElementById('loadingOverlay').style.display='flex';">

            <!-- Item Type -->
            <div class="form-group">
                <label>Item Type <span class="required">*</span></label>
                <select name="type" required>
                    <option value="dental">🦷 Dental Device & Equipment</option>
                    <option value="medical">🩺 Medical Device & Equipment</option>
                </select>
            </div>

            <!-- Item Name -->
            <div class="form-group">
                <label>Item Name / Description <span class="required">*</span></label>
                <input type="text" name="name" placeholder="e.g., Dental Chair, Blood Pressure Monitor" required>
            </div>

            <!-- Unit -->
            <div class="form-group">
                <label>Unit <span class="required">*</span></label>
                <input type="text" name="unit" list="unitList" value="Unit" placeholder="e.g., Unit, SET, PCS" required>
                <datalist id="unitList">
                    <option value="Unit">
                    <option value="SET">
                    <option value="PCS">
                    <option value="pcs">
                </datalist>
            </div>

            <hr class="section-divider">
            <p class="section-label">📋 Equipment Details</p>

            <div style="display:flex; gap:15px;">
                <div class="form-group" style="flex:1;">
                    <label>Brand / Serial #</label>
                    <input type="text" name="brand_serial" placeholder="e.g., SN-12345">
                </div>
                <div class="form-group" style="flex:1;">
                    <label>RIS # / ICS # / PAR #</label>
                    <input type="text" name="ris_id" placeholder="e.g., RIS No. 21-6-136">
                </div>
            </div>

            <div style="display:flex; gap:15px;">
                <div class="form-group" style="flex:1;">
                    <label>Color</label>
                    <input type="text" name="color" placeholder="e.g., White, Blue">
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Date Procured</label>
                    <input type="date" name="date_acquired">
                    <small style="color:#7f8c8d; display:block; margin-top:4px;">(Leave blank for N/A)</small>
                </div>
            </div>

            <hr class="section-divider">
            <p class="section-label">📦 Quantity (Auto-Computed)</p>

            <div class="qty-grid">
                <div class="form-group">
                    <label>✅ Serviceable</label>
                    <input type="number" name="qty_serviceable" id="svcInput" min="0" value="0" oninput="syncQty()">
                </div>
                <div class="form-group">
                    <label>❌ Unserviceable</label>
                    <input type="number" name="qty_unserviceable" id="unsvcInput" min="0" value="0" oninput="syncQty()">
                </div>
                <div class="form-group">
                    <label>🔧 For Repair</label>
                    <input type="number" name="qty_repair" id="repInput" min="0" value="0" oninput="syncQty()">
                </div>
            </div>

            <!-- Hidden quantity field (submitted to DB) -->
            <input type="hidden" name="quantity" id="totalQty" value="0">
            <div class="qty-summary" id="qtySummary">Total Quantity: <strong>0</strong></div>

            <hr class="section-divider">

            <div class="form-group">
                <label>Remarks / Notes</label>
                <textarea name="remarks" rows="2" placeholder="Optional notes about this equipment..."></textarea>
            </div>

            <button type="submit" name="add_equipment" class="btn-submit">➕ Add Equipment to Inventory</button>
        </form>

        <div class="info-box">
            <p>📊 <a href="index.php">← Back to Inventory</a> &nbsp;|&nbsp; <a href="add.php">💊 Add Medicine/Consumable →</a></p>
        </div>
    </div>
</div>

<script>
    function syncQty() {
        const svc   = parseInt(document.getElementById('svcInput').value)   || 0;
        const unsvc = parseInt(document.getElementById('unsvcInput').value) || 0;
        const rep   = parseInt(document.getElementById('repInput').value)   || 0;
        const total = svc + unsvc + rep;
        document.getElementById('totalQty').value     = total;
        document.getElementById('qtySummary').innerHTML = `Total Quantity: <strong>${total}</strong>`;
    }

    function showToast(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast ${type === 'error' ? 'error' : ''}`;
        toast.innerHTML = `<span>${type === 'success' ? '✅' : '❌'}</span><span>${message}</span>`;
        container.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 100);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 400);
        }, 5000);
    }

    document.addEventListener('DOMContentLoaded', () => {
        flatpickr("input[type=date]", {
            altInput: true, altFormat: "m/d/Y",
            dateFormat: "Y-m-d", allowInput: true
        });

        <?php if ($status_msg): ?>
        document.getElementById('loadingOverlay').style.display = 'none';
        showToast("<?php echo addslashes($status_msg); ?>", "<?php echo $status_type; ?>");
        <?php if ($status_type === 'success'): ?>
        setTimeout(() => document.getElementById('eqForm').reset(), 500);
        <?php endif; ?>
        <?php endif; ?>
    });
</script>
</body>
</html>
