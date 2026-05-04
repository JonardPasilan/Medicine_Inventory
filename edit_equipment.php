<?php
require_once __DIR__ . '/db.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) { header("Location: index.php"); exit(); }

$row = $conn->query("SELECT * FROM medicines WHERE id = $id")->fetch_assoc();
if (!$row || !in_array($row['type'], ['dental', 'medical'])) {
    header("Location: index.php"); exit();
}

// Handle update
if (isset($_POST['update_equipment'])) {
    $n      = $conn->real_escape_string(trim($_POST['name']          ?? ''));
    $t      = $conn->real_escape_string(trim($_POST['type']          ?? 'dental'));
    $u      = $conn->real_escape_string(trim($_POST['unit']          ?? 'Unit'));
    $brand  = $conn->real_escape_string(trim($_POST['brand_serial']  ?? ''));
    $ris    = $conn->real_escape_string(trim($_POST['ris_id']        ?? ''));
    $color  = $conn->real_escape_string(trim($_POST['color']         ?? ''));
    $date_acq = $conn->real_escape_string(trim($_POST['date_acquired'] ?? ''));
    $qsrv   = intval($_POST['qty_serviceable']   ?? 0);
    $qunsrv = intval($_POST['qty_unserviceable'] ?? 0);
    $qrep   = intval($_POST['qty_repair']        ?? 0);
    $rem    = $conn->real_escape_string(trim($_POST['remarks'] ?? ''));
    $q      = $qsrv + $qunsrv + $qrep; // Auto-computed

    $val_acq = !empty($date_acq) ? "'$date_acq'" : "NULL";

    $conn->query("UPDATE medicines SET
        name='$n', type='$t', unit='$u',
        quantity=$q,
        brand_serial='$brand', ris_id='$ris', color='$color',
        date_acquired=$val_acq,
        qty_serviceable=$qsrv, qty_unserviceable=$qunsrv, qty_repair=$qrep,
        remarks='$rem', is_archived=0
        WHERE id=$id");

    $conn->query("INSERT INTO logs (medicine_id, quantity, action) VALUES ($id, $q, 'Item Updated')");
    header("Location: index.php");
    exit();
}

require_once __DIR__ . '/header.php';
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
        font-size: 14px; font-family: inherit; transition: all 0.3s ease;
    }
    input:focus, select:focus, textarea:focus {
        outline: none; border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
    }

    .section-divider { border: none; border-top: 2px dashed #e0e0e0; margin: 24px 0; }
    .section-label { font-weight: 700; font-size: 13px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 14px; }

    .qty-grid { display: flex; gap: 12px; }
    .qty-grid > div { flex: 1; }
    .qty-summary {
        background: #eef2f7; border-radius: 8px;
        padding: 12px 15px; margin-top: 10px;
        font-size: 14px; color: #2c3e50; font-weight: 600;
        text-align: center;
    }

    .button-group { display: flex; gap: 15px; margin-top: 20px; }
    .btn-update {
        flex: 1; padding: 13px; background: #1f4f87;
        border: none; color: white; font-size: 15px; font-weight: 600;
        border-radius: 8px; cursor: pointer; transition: all 0.3s ease;
    }
    .btn-update:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(31,79,135,0.4); }
    .btn-cancel {
        padding: 13px 20px; background: #95a5a6; color: white;
        border: none; border-radius: 8px; text-decoration: none;
        font-size: 15px; font-weight: 600; display: inline-flex;
        align-items: center; justify-content: center; transition: all 0.3s ease;
    }
    .btn-cancel:hover { background: #7f8c8d; }
</style>

<div class="container">
    <div class="form-card">
        <div class="form-header">
            <span class="icon"><?php echo $row['type'] == 'dental' ? '🦷' : '🩺'; ?></span>
            <h2>Edit Equipment</h2>
            <p>Editing: <strong><?php echo htmlspecialchars($row['name']); ?></strong></p>
        </div>

        <form method="POST" id="editEqForm">

            <!-- Item Type -->
            <div class="form-group">
                <label>Item Type <span class="required">*</span></label>
                <select name="type" required>
                    <option value="dental"  <?php echo $row['type'] == 'dental'  ? 'selected' : ''; ?>>🦷 Dental Device & Equipment</option>
                    <option value="medical" <?php echo $row['type'] == 'medical' ? 'selected' : ''; ?>>🩺 Medical Device & Equipment</option>
                </select>
            </div>

            <!-- Item Name -->
            <div class="form-group">
                <label>Item Name / Description <span class="required">*</span></label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required>
            </div>

            <!-- Unit -->
            <div class="form-group">
                <label>Unit <span class="required">*</span></label>
                <input type="text" name="unit" list="unitList" value="<?php echo htmlspecialchars($row['unit'] ?? 'Unit'); ?>" required>
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
                    <input type="text" name="brand_serial" value="<?php echo htmlspecialchars((string)$row['brand_serial']); ?>">
                </div>
                <div class="form-group" style="flex:1;">
                    <label>RIS # / ICS # / PAR #</label>
                    <input type="text" name="ris_id" value="<?php echo htmlspecialchars((string)$row['ris_id']); ?>">
                </div>
            </div>

            <div style="display:flex; gap:15px;">
                <div class="form-group" style="flex:1;">
                    <label>Color</label>
                    <input type="text" name="color" value="<?php echo htmlspecialchars((string)$row['color']); ?>">
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Date Procured</label>
                    <input type="date" name="date_acquired" value="<?php echo htmlspecialchars((string)($row['date_acquired'] ?? '')); ?>">
                    <small style="color:#7f8c8d; display:block; margin-top:4px;">(Leave blank for N/A)</small>
                </div>
            </div>

            <hr class="section-divider">
            <p class="section-label">📦 Quantity (Auto-Computed)</p>

            <div class="qty-grid">
                <div class="form-group">
                    <label>✅ Serviceable</label>
                    <input type="number" name="qty_serviceable" id="svcInput" min="0"
                           value="<?php echo (int)($row['qty_serviceable'] ?? 0); ?>" oninput="syncQty()">
                </div>
                <div class="form-group">
                    <label>❌ Unserviceable</label>
                    <input type="number" name="qty_unserviceable" id="unsvcInput" min="0"
                           value="<?php echo (int)($row['qty_unserviceable'] ?? 0); ?>" oninput="syncQty()">
                </div>
                <div class="form-group">
                    <label>🔧 For Repair</label>
                    <input type="number" name="qty_repair" id="repInput" min="0"
                           value="<?php echo (int)($row['qty_repair'] ?? 0); ?>" oninput="syncQty()">
                </div>
            </div>

            <input type="hidden" name="quantity" id="totalQty" value="<?php echo (int)$row['quantity']; ?>">
            <div class="qty-summary" id="qtySummary">
                Total Quantity: <strong><?php echo (int)$row['quantity']; ?></strong>
            </div>

            <hr class="section-divider">

            <div class="form-group">
                <label>Remarks / Notes</label>
                <textarea name="remarks" rows="2"><?php echo htmlspecialchars((string)($row['remarks'] ?? '')); ?></textarea>
            </div>

            <div class="button-group">
                <button type="submit" name="update_equipment" class="btn-update">💾 Update Equipment</button>
                <a href="index.php" class="btn-cancel">❌ Cancel</a>
            </div>
        </form>
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

    document.addEventListener('DOMContentLoaded', () => {
        flatpickr("input[type=date]", {
            altInput: true, altFormat: "m/d/Y",
            dateFormat: "Y-m-d", allowInput: true
        });
        // Warn on unsaved changes
        let changed = false;
        document.getElementById('editEqForm').querySelectorAll('input, select, textarea')
            .forEach(el => el.addEventListener('change', () => changed = true));
        window.addEventListener('beforeunload', e => {
            if (changed) { e.preventDefault(); e.returnValue = ''; }
        });
        document.getElementById('editEqForm').addEventListener('submit', () => changed = false);
    });
</script>
</body>
</html>
