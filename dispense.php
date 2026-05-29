<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/header.php';

$today = date('Y-m-d');
$soon  = date('Y-m-d', strtotime('+30 days'));

/* ─── Multi-Medicine Dispense Handler ───────────────────────────── */
$alert      = '';
$alert_type = '';

if (isset($_POST['use_multi'])) {
    $med_names     = $_POST['med_names']    ?? [];
    $qtys          = $_POST['qtys']         ?? [];
    $dispense_units= $_POST['dispense_units']?? [];
    $patient       = mysqli_real_escape_string($conn, trim($_POST['patient_name']   ?? ''));
    $prescriber    = mysqli_real_escape_string($conn, trim($_POST['prescriber_name']?? ''));
    $staff_name    = mysqli_real_escape_string($conn, trim($_POST['staff_name']     ?? ''));

    $dispense_date_raw = trim($_POST['dispense_date'] ?? '');
    if (empty($dispense_date_raw)) {
        $dispense_date = date('Y-m-d H:i:s');
    } else {
        $dispense_date = $dispense_date_raw . ' ' . date('H:i:s');
    }
    $dispense_date = mysqli_real_escape_string($conn, $dispense_date);

    /* — Validate rows — */
    $rows = [];
    $errors = [];
    $seen_meds = [];

    for ($i = 0; $i < count($med_names); $i++) {
        $mname        = mysqli_real_escape_string($conn, trim($med_names[$i]   ?? ''));
        $qty_input    = floatval($qtys[$i] ?? 0);
        $dunit        = mysqli_real_escape_string($conn, trim($dispense_units[$i] ?? 'boxes'));

        if (empty($mname)) { $errors[] = "Row " . ($i+1) . ": No medicine selected."; continue; }
        if ($qty_input <= 0){ $errors[] = "Row " . ($i+1) . ": Invalid quantity for <strong>$mname</strong>."; continue; }

        /* Stock check */
        $chk = $conn->query("
            SELECT SUM(quantity) AS avail, MAX(unit) as unit, MAX(pcs_per_box) as pcs_per_box
            FROM medicines
            WHERE name = '$mname'
              AND (expiration_date >= CURDATE() OR expiration_date IS NULL)
              AND quantity > 0
              AND type IN ('medicine','consumable')");
        $cd   = $chk ? $chk->fetch_assoc() : null;
        $avail= (float)($cd['avail'] ?? 0);
        $dbu  = strtolower(trim($cd['unit'] ?? 'pcs'));
        $ppb  = (int)($cd['pcs_per_box'] ?? 1);

        $qty_needed = $qty_input;
        if ($dunit === 'pieces' && ($dbu === 'box' || $dbu === 'boxes')) {
            $qty_needed = $qty_input / max(1, $ppb);
        }

        if ($avail < $qty_needed) {
            $errors[] = "Insufficient stock for <strong>$mname</strong>! Only $avail unit(s) available.";
            continue;
        }

        $rows[] = compact('mname', 'qty_input', 'qty_needed', 'dunit', 'dbu', 'ppb', 'avail');
    }

    if (!empty($errors)) {
        $alert      = implode('<br>', $errors);
        $alert_type = 'error';
    } elseif (empty($rows)) {
        $alert      = 'Please add at least one medicine.';
        $alert_type = 'error';
    } else {
        /* — Begin transaction — */
        $conn->begin_transaction();
        $all_details = [];

        try {
            foreach ($rows as $row) {
                $mname      = $row['mname'];
                $qty_needed = $row['qty_needed'];
                $qty_input  = $row['qty_input'];
                $dunit      = $row['dunit'];

                $batches = $conn->query("
                    SELECT * FROM medicines
                    WHERE name = '$mname'
                      AND (expiration_date >= CURDATE() OR expiration_date IS NULL)
                      AND quantity > 0
                      AND type IN ('medicine','consumable')
                    ORDER BY expiration_date ASC");

                $remaining = $qty_needed;
                $batch_details = [];

                while ($remaining > 0 && ($b = $batches->fetch_assoc())) {
                    $take    = min($remaining, (float)$b['quantity']);
                    $new_qty = (float)$b['quantity'] - $take;
                    $conn->query("UPDATE medicines SET quantity = $new_qty WHERE id = {$b['id']}");

                    $p_val = !empty($patient)    ? "'$patient'"    : "NULL";
                    $d_val = !empty($prescriber)  ? "'$prescriber'" : "NULL";
                    $s_val = !empty($staff_name)  ? "'$staff_name'" : "NULL";

                    $conn->query("INSERT INTO logs (medicine_id, quantity, action, patient_name, prescriber_name, staff_name, date)
                                  VALUES ({$b['id']}, $take, 'Released to patient', $p_val, $d_val, $s_val, '$dispense_date')");

                    $exp_fmt = $b['expiration_date'] ? date('M d, Y', strtotime($b['expiration_date'])) : 'N/A';
                    $batch_details[] = "Batch #{$b['batch_number']} (Exp: {$exp_fmt}) — {$take} unit(s)";
                    $remaining -= $take;
                }

                $dispensed_label = ($dunit === 'pieces') ? "{$qty_input} piece(s)" : "{$qty_needed} unit(s)";
                $all_details[] = [
                    'name'    => htmlspecialchars($mname),
                    'label'   => $dispensed_label,
                    'batches' => $batch_details,
                ];
            }

            $conn->commit();

            /* Build success message */
            $msg = '<strong>' . count($all_details) . ' medicine(s) dispensed successfully!</strong><br><br>';
            foreach ($all_details as $d) {
                $msg .= "💊 <strong>{$d['name']}</strong> — {$d['label']}<br>";
                if (!empty($d['batches'])) {
                    $msg .= '&nbsp;&nbsp;• ' . implode('<br>&nbsp;&nbsp;• ', $d['batches']) . '<br>';
                }
                $msg .= '<br>';
            }
            $alert      = rtrim($msg);
            $alert_type = 'success';

        } catch (Exception $e) {
            $conn->rollback();
            $alert      = 'Transaction failed. Please try again.';
            $alert_type = 'error';
        }
    }
}

/* ─── Build medicine dropdown data ──────────────────────────────── */
$meds_query = $conn->query("
    SELECT
        name, label,
        SUM(CASE WHEN expiration_date >= CURDATE() OR expiration_date IS NULL THEN quantity ELSE 0 END) AS avail_qty,
        SUM(CASE WHEN expiration_date <  CURDATE() THEN quantity ELSE 0 END) AS expired_qty,
        MIN(CASE WHEN (expiration_date >= CURDATE() OR expiration_date IS NULL) AND quantity > 0
                 THEN expiration_date ELSE NULL END) AS next_exp,
        MAX(unit) as unit,
        MAX(pcs_per_box) as pcs_per_box
    FROM medicines
    WHERE quantity > 0 AND is_archived = 0 AND type IN ('medicine','consumable')
    GROUP BY name, label
    ORDER BY name ASC");

$med_data_map = [];
if ($meds_query && $meds_query->num_rows > 0) {
    while ($m = $meds_query->fetch_assoc()) {
        $sname  = mysqli_real_escape_string($conn, $m['name']);
        $slabel = mysqli_real_escape_string($conn, (string)$m['label']);
        $bq     = $conn->query("SELECT quantity, expiration_date FROM medicines WHERE name='$sname' AND label='$slabel' AND quantity>0 ORDER BY expiration_date ASC");
        $batches_info = [];
        while ($brow = $bq->fetch_assoc()) {
            $batches_info[] = ['qty' => (int)$brow['quantity'], 'exp' => $brow['expiration_date']];
        }
        $med_data_map[] = [
            'name'        => $m['name'],
            'label'       => (string)$m['label'],
            'avail'       => (int)$m['avail_qty'],
            'expired'     => (int)$m['expired_qty'],
            'next_exp'    => $m['next_exp'],
            'unit'        => $m['unit'],
            'pcs_per_box' => $m['pcs_per_box'],
            'batches'     => $batches_info,
        ];
    }
}
?>

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', sans-serif; background: var(--color-canvas); min-height: 100vh; }

    .container { max-width: 760px; margin: 40px auto; padding: 0 20px; }

    .form-card {
        background: var(--color-surface); border-radius: var(--radius-lg); padding: 35px;
        box-shadow: var(--shadow-md); border: 1px solid var(--color-border);
        animation: fadeIn 0.6s cubic-bezier(0.23, 1, 0.32, 1);
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(30px) scale(0.98); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    .form-header { text-align: center; margin-bottom: 28px; }
    .form-header .icon { font-size: 50px; margin-bottom: 10px; transition: transform 0.5s ease; }
    .form-card:hover .icon { transform: rotate(-15deg) scale(1.1); }
    .form-header h2 { color: var(--color-text-primary); font-size: 28px; margin-bottom: 8px; }
    .form-header p  { color: var(--color-text-secondary); font-size: 14px; }

    .form-group { margin-bottom: 20px; }
    label {
        display: block; margin-bottom: 8px;
        color: var(--color-text-primary); font-weight: 500; font-size: 14px;
    }
    label .required { color: #e74c3c; margin-left: 3px; }

    input, select {
        width: 100%; padding: 11px 14px;
        border: 2px solid var(--color-border); border-radius: var(--radius-sm);
        font-size: 14px; font-family: inherit;
        transition: all 0.3s ease; background: var(--color-overlay); color: var(--color-text-primary);
    }
    input:focus, select:focus {
        outline: none; border-color: var(--color-brand);
        box-shadow: 0 0 0 3px var(--color-brand-light); transform: translateY(-1px);
    }

    /* ── Section divider ── */
    .section-label {
        font-size: 11px; font-weight: 700; letter-spacing: 0.08em;
        text-transform: uppercase; color: var(--color-text-muted);
        margin-bottom: 14px; padding-bottom: 8px;
        border-bottom: 1px solid var(--color-border);
    }

    /* ── Patient info row ── */
    .patient-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    @media (max-width: 600px) { .patient-grid { grid-template-columns: 1fr; } }

    /* ── FIFO Note ── */
    .fifo-note {
        background: var(--color-brand-light); border-left: 4px solid var(--color-brand);
        border-radius: var(--radius-sm); padding: 11px 14px;
        margin-bottom: 22px; font-size: 13px; color: var(--color-text-primary);
        display: flex; align-items: center; gap: 8px;
    }

    /* ── Medicine list ── */
    #medList { display: flex; flex-direction: column; gap: 14px; margin-bottom: 16px; }

    .med-row {
        background: var(--color-overlay); border: 1px solid var(--color-border);
        border-radius: var(--radius-md); padding: 16px;
        position: relative; animation: rowSlideIn 0.3s ease;
        transition: border-color 0.2s ease;
    }
    .med-row:hover { border-color: var(--color-brand); }
    @keyframes rowSlideIn {
        from { opacity: 0; transform: translateY(-8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .med-row-header {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 12px;
    }
    .med-row-num {
        font-size: 12px; font-weight: 700; color: var(--color-brand);
        background: var(--color-brand-light); padding: 3px 10px; border-radius: 99px;
    }
    .med-row-remove {
        background: none; border: 1px solid #e74c3c; color: #e74c3c;
        border-radius: var(--radius-sm); padding: 4px 10px; font-size: 12px;
        cursor: pointer; font-weight: 600; transition: all 0.2s ease;
    }
    .med-row-remove:hover { background: #e74c3c; color: white; }
    .med-row-remove:disabled { opacity: 0.3; cursor: not-allowed; }

    .med-row-body { display: grid; grid-template-columns: 2fr 1fr; gap: 12px; align-items: start; }
    @media (max-width: 560px) { .med-row-body { grid-template-columns: 1fr; } }

    /* ── Searchable Dropdown ── */
    .med-search-wrap { position: relative; }
    .med-search-input {
        width: 100%; padding: 11px 36px 11px 14px;
        border: 2px solid var(--color-border); border-radius: var(--radius-sm);
        font-size: 14px; font-family: inherit;
        transition: all 0.3s ease; background: var(--color-surface); color: var(--color-text-primary);
        cursor: pointer;
    }
    .med-search-input:focus {
        outline: none; border-color: var(--color-brand);
        box-shadow: 0 0 0 3px var(--color-brand-light); transform: translateY(-1px);
    }
    .med-search-wrap .search-arrow {
        position: absolute; right: 13px; top: 50%; transform: translateY(-50%);
        pointer-events: none; color: var(--color-text-muted); font-size: 12px;
        transition: transform 0.2s ease;
    }
    .med-search-wrap.open .search-arrow { transform: translateY(-50%) rotate(180deg); }
    .med-dropdown {
        display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0;
        background: var(--color-surface); border: 2px solid var(--color-brand);
        border-radius: var(--radius-sm); z-index: 999;
        max-height: 240px; overflow-y: auto; box-shadow: var(--shadow-lg);
        animation: dropFadeIn 0.2s ease;
    }
    @keyframes dropFadeIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }
    .med-search-wrap.open .med-dropdown { display: block; }
    .med-option {
        padding: 10px 14px; cursor: pointer; font-size: 13px;
        border-bottom: 1px solid var(--color-border);
        color: var(--color-text-primary);
        display: flex; justify-content: space-between; align-items: center;
        transition: background 0.15s ease;
    }
    .med-option:last-child { border-bottom: none; }
    .med-option:hover, .med-option.highlighted { background: var(--color-brand-light); }
    .med-option.disabled { color: var(--color-text-muted); cursor: not-allowed; opacity: 0.6; }
    .med-option.disabled:hover { background: transparent; }
    .med-option .opt-name { font-weight: 600; }
    .med-option .opt-meta { font-size: 11px; color: var(--color-text-muted); margin-top: 1px; }
    .med-option .opt-badge {
        font-size: 11px; font-weight: 700; padding: 2px 8px;
        border-radius: 99px; white-space: nowrap;
    }
    .opt-badge.ok   { background: #d5f4e6; color: #1a7a4a; }
    .opt-badge.low  { background: #fff3cd; color: #856404; }
    .opt-badge.none { background: #ffeaea; color: #c0392b; }
    .med-no-results { padding: 16px; text-align: center; color: var(--color-text-muted); font-size: 13px; }

    /* Stock badge inline */
    .stock-badge {
        display: none; font-size: 12px; margin-top: 6px; padding: 5px 10px;
        border-radius: var(--radius-sm); font-weight: 600;
        border-left: 3px solid;
    }
    .stock-badge.ok     { background: #d5f4e6; color: #1a7a4a; border-color: #1a7a4a; }
    .stock-badge.low    { background: #fff3cd; color: #856404; border-color: #f39c12; }
    .stock-badge.danger { background: #ffeaea; color: #c0392b; border-color: #e74c3c; }

    /* Qty row */
    .qty-wrap { display: flex; gap: 8px; align-items: flex-start; flex-direction: column; }
    .qty-inner { display: flex; gap: 8px; width: 100%; }
    .qty-inner input { flex: 2; }
    .qty-inner select { flex: 1; min-width: 80px; }
    .qty-error { font-size: 12px; color: #e74c3c; display: none; margin-top: 2px; }

    /* Duplicate warning */
    #dupWarning {
        background: #fff3cd; border-left: 4px solid #ffc107;
        border-radius: 8px; padding: 10px 14px; margin-bottom: 14px;
        font-size: 13px; color: #856404; display: none;
    }

    /* Add Medicine button */
    .btn-add-med {
        width: 100%; padding: 12px; border: 2px dashed var(--color-brand);
        background: var(--color-brand-light); color: var(--color-brand);
        font-size: 14px; font-weight: 600; border-radius: var(--radius-sm);
        cursor: pointer; transition: all 0.2s ease; margin-bottom: 22px;
        font-family: inherit;
    }
    .btn-add-med:hover { background: var(--color-brand); color: white; transform: translateY(-1px); }

    /* Dispense button */
    .btn-dispense {
        width: 100%; padding: 14px;
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        border: none; color: white; font-size: 16px; font-weight: 600;
        border-radius: 8px; cursor: pointer; transition: all 0.3s ease;
        position: relative; overflow: hidden; font-family: inherit;
    }
    .btn-dispense:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(231,76,60,0.4); }
    .btn-dispense:active { transform: translateY(0); }
    .btn-dispense:disabled { background: var(--color-overlay); color: var(--color-text-muted); cursor: not-allowed; transform: none; box-shadow: none; }

    /* Ripple */
    .ripple {
        position: absolute; background: rgba(255,255,255,0.4);
        border-radius: 50%; transform: scale(0);
        animation: ripple-anim 0.6s linear; pointer-events: none;
    }
    @keyframes ripple-anim { to { transform: scale(4); opacity: 0; } }

    /* Loading overlay */
    #loadingOverlay {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,0.35); z-index: 10000;
        align-items: center; justify-content: center;
        flex-direction: column; gap: 15px; backdrop-filter: blur(2px);
    }
    .spinner {
        width: 50px; height: 50px;
        border: 5px solid var(--color-border); border-top: 5px solid var(--color-brand);
        border-radius: 50%; animation: spin 1s linear infinite;
    }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

    .info-box {
        background: var(--color-overlay); border-radius: var(--radius-sm);
        padding: 15px; margin-top: 20px;
        text-align: center; border: 1px solid var(--color-border);
    }
    .info-box a { color: var(--color-brand); text-decoration: none; font-weight: 500; }

    @media (max-width: 768px) {
        .container { margin: 20px auto; }
        .form-card { padding: 22px; }
        .form-header h2 { font-size: 22px; }
    }
</style>

<div id="loadingOverlay">
    <div class="spinner"></div>
    <p style="color: #1f4f87; font-weight: 600;">Processing dispense...</p>
</div>

<div class="container">
    <div class="form-card">
        <div class="form-header">
            <div class="icon">💊</div>
            <h2>Dispense Medicine</h2>
            <p>Dispenses from the earliest-expiring batch first (FIFO) — supports multiple medicines per patient</p>
        </div>

        <div class="fifo-note">
            <span>ℹ️</span>
            <span><strong>FIFO Active:</strong> Stock is taken from the oldest expiration batch first. You can add multiple medicines per dispense slip.</span>
        </div>

        <form method="POST" id="dispenseForm" onsubmit="handleSubmit(event)">

            <!-- ── Patient Info ── -->
            <div class="section-label">Patient & Transaction Info</div>
            <div class="patient-grid" style="margin-bottom: 20px;">
                <div>
                    <label>Patient Name <small style="color:#7f8c8d;">(Optional)</small></label>
                    <input type="text" name="patient_name" id="patientName" placeholder="Enter patient name">
                </div>
                <div>
                    <label>Prescriber Name <small style="color:#7f8c8d;">(Optional)</small></label>
                    <input type="text" name="prescriber_name" placeholder="Dr. Name">
                </div>
                <div>
                    <label>Dispensed By (Staff) <span class="required">*</span></label>
                    <input type="text" name="staff_name" id="staffNameInput" placeholder="Your name" required>
                </div>
                <div>
                    <label>Dispense Date <small style="color:#7f8c8d;">(Defaults to today)</small></label>
                    <input type="date" name="dispense_date" max="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>

            <!-- ── Medicine List ── -->
            <div class="section-label">Medicines to Dispense</div>

            <div id="dupWarning">⚠️ <strong>Duplicate medicine detected!</strong> Each medicine should appear only once per dispense slip.</div>

            <div id="medList">
                <!-- rows injected by JS -->
            </div>

            <button type="button" class="btn-add-med" id="addMedBtn" onclick="addMedRow()">
                ＋ Add Another Medicine
            </button>

            <button type="submit" name="use_multi" id="dispenseBtn" class="btn-dispense">
                💊 Dispense All Medicines
            </button>
        </form>

        <div class="info-box">
            <p>📋 <strong>Important:</strong> Verify medicine and dosage before dispensing.</p>
            <p style="margin-top:10px;">📊 <a href="index.php">Back to Inventory →</a> &nbsp; | &nbsp; <a href="logs.php">View Logs →</a></p>
        </div>
    </div>
</div>

<!-- Medicine data from PHP -->
<script>
const MED_DATA = <?php echo json_encode($med_data_map); ?>;
const today = new Date().toISOString().split('T')[0];
const soon  = new Date(Date.now() + 30*24*60*60*1000).toISOString().split('T')[0];

function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    const d = new Date(dateStr + 'T00:00:00');
    return d.toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' });
}

/* ── Row counter (unique IDs) ── */
let rowCounter = 0;

function addMedRow() {
    rowCounter++;
    const idx = rowCounter;
    const list = document.getElementById('medList');
    const div = document.createElement('div');
    div.className = 'med-row';
    div.id = 'medRow_' + idx;
    div.innerHTML = buildRowHTML(idx);
    list.appendChild(div);
    updateRowNumbers();
    updateRemoveBtnState();
    // init dropdown for this row
    initRowDropdown(idx);
}

function buildRowHTML(idx) {
    return `
    <div class="med-row-header">
        <span class="med-row-num" id="rowNum_${idx}">Medicine #${idx}</span>
        <button type="button" class="med-row-remove" onclick="removeRow(${idx})" title="Remove this medicine">✕ Remove</button>
    </div>
    <input type="hidden" name="med_names[]" id="medHidden_${idx}" value="">
    <div class="med-row-body">
        <div>
            <label style="font-size:13px; margin-bottom:6px;">Medicine <span class="required">*</span></label>
            <div class="med-search-wrap" id="wrap_${idx}">
                <input type="text" class="med-search-input" id="medSearch_${idx}"
                       placeholder="🔍 Type to search medicine..."
                       autocomplete="off" readonly onclick="openDrop(${idx})">
                <span class="search-arrow">▼</span>
                <div class="med-dropdown" id="drop_${idx}"></div>
            </div>
            <div class="stock-badge" id="stockBadge_${idx}"></div>
        </div>
        <div>
            <label style="font-size:13px; margin-bottom:6px;">Quantity <span class="required">*</span></label>
            <div class="qty-wrap">
                <div class="qty-inner">
                    <input type="number" step="0.01" min="0.01" name="qtys[]" id="qty_${idx}"
                           placeholder="Qty" disabled oninput="validateRow(${idx})">
                    <select name="dispense_units[]" id="unit_${idx}" style="display:none;" onchange="validateRow(${idx})">
                        <option value="pieces">Pcs</option>
                        <option value="boxes">Boxes</option>
                    </select>
                </div>
                <div class="qty-error" id="qtyErr_${idx}"></div>
            </div>
        </div>
    </div>`;
}

function removeRow(idx) {
    const row = document.getElementById('medRow_' + idx);
    if (row) {
        row.style.animation = 'none';
        row.style.opacity = '0';
        row.style.transform = 'translateY(-8px)';
        row.style.transition = 'all 0.25s ease';
        setTimeout(() => { row.remove(); updateRowNumbers(); updateRemoveBtnState(); checkDuplicates(); }, 250);
    }
}

function updateRowNumbers() {
    const rows = document.querySelectorAll('.med-row');
    rows.forEach((r, i) => {
        const numEl = r.querySelector('.med-row-num');
        if (numEl) numEl.textContent = 'Medicine #' + (i + 1);
    });
}

function updateRemoveBtnState() {
    const btns = document.querySelectorAll('.med-row-remove');
    btns.forEach(b => { b.disabled = btns.length <= 1; });
}

/* ── Per-row dropdown logic ── */
let rowMedInfo = {}; // rowMedInfo[idx] = MED_DATA item

function initRowDropdown(idx) {
    const searchInput = document.getElementById('medSearch_' + idx);
    if (!searchInput) return;

    searchInput.addEventListener('input', function() {
        renderDrop(idx, this.value);
    });
}

function renderDrop(idx, filter) {
    const drop = document.getElementById('drop_' + idx);
    if (!drop) return;
    const q = (filter || '').toLowerCase().trim();
    const items = q ? MED_DATA.filter(m =>
        m.name.toLowerCase().includes(q) || m.label.toLowerCase().includes(q)
    ) : MED_DATA;

    if (items.length === 0) {
        drop.innerHTML = `<div class="med-no-results">😕 No medicines found for "${filter}"</div>`;
        return;
    }
    drop.innerHTML = items.map(m => {
        const disabled = m.avail <= 0;
        let badge, badgeCls;
        if (disabled)         { badge = '0 avail'; badgeCls = 'none'; }
        else if (m.avail <= 5){ badge = m.avail + ' avail ⚠️'; badgeCls = 'low'; }
        else                  { badge = m.avail + ' avail'; badgeCls = 'ok'; }
        const meta = m.label ? m.label : '&nbsp;';
        const globalIdx = MED_DATA.indexOf(m);
        return `<div class="med-option${disabled ? ' disabled' : ''}" data-global="${globalIdx}"
                     onclick="${disabled ? '' : 'selectMedForRow(' + idx + ', ' + globalIdx + ')'}">
                    <div>
                        <div class="opt-name">${m.name}</div>
                        <div class="opt-meta">${meta}</div>
                    </div>
                    <span class="opt-badge ${badgeCls}">${badge}</span>
                </div>`;
    }).join('');
}

function openDrop(idx) {
    const wrap = document.getElementById('wrap_' + idx);
    const input = document.getElementById('medSearch_' + idx);
    wrap.classList.add('open');
    input.removeAttribute('readonly');
    input.select();
    renderDrop(idx, input.value);

    // Close other open dropdowns
    document.querySelectorAll('.med-search-wrap.open').forEach(w => {
        if (w.id !== 'wrap_' + idx) closeDrop(w.id.replace('wrap_', ''));
    });
}

function closeDrop(idx) {
    const wrap = document.getElementById('wrap_' + idx);
    const input = document.getElementById('medSearch_' + idx);
    if (wrap) wrap.classList.remove('open');
    if (input) input.setAttribute('readonly', true);
}

function selectMedForRow(rowIdx, globalMedIdx) {
    const m = MED_DATA[globalMedIdx];
    rowMedInfo[rowIdx] = m;

    document.getElementById('medHidden_' + rowIdx).value = m.name;
    const displayText = m.name + (m.label ? ' (' + m.label + ')' : '');
    document.getElementById('medSearch_' + rowIdx).value = displayText;
    document.getElementById('medSearch_' + rowIdx).style.borderColor = '';
    closeDrop(rowIdx);

    // Show stock badge
    const badge = document.getElementById('stockBadge_' + rowIdx);
    const avail = m.avail || 0;
    badge.textContent = '📦 Stock: ' + avail + ' unit(s) available';
    badge.className = 'stock-badge ' + (avail <= 0 ? 'danger' : (avail <= 5 ? 'low' : 'ok'));
    badge.style.display = 'block';

    // Enable qty
    const qtyEl = document.getElementById('qty_' + rowIdx);
    qtyEl.disabled = (avail <= 0);
    qtyEl.value = '';

    // Show/hide unit selector
    const dbu = (m.unit || '').toLowerCase().trim();
    const unitSel = document.getElementById('unit_' + rowIdx);
    if (dbu === 'box' || dbu === 'boxes') {
        unitSel.style.display = 'block';
        unitSel.value = 'pieces';
    } else {
        unitSel.style.display = 'none';
    }

    validateRow(rowIdx);
    checkDuplicates();
}

function validateRow(idx) {
    const m = rowMedInfo[idx];
    if (!m) return;
    const qtyEl  = document.getElementById('qty_' + idx);
    const errEl  = document.getElementById('qtyErr_' + idx);
    const unitEl = document.getElementById('unit_' + idx);
    const qtyVal = parseFloat(qtyEl.value) || 0;
    const avail  = m.avail || 0;
    const dbu    = (m.unit || '').toLowerCase().trim();
    const dunit  = unitEl.style.display !== 'none' ? unitEl.value : 'boxes';

    let qtyNeeded = qtyVal;
    if (dunit === 'pieces' && (dbu === 'box' || dbu === 'boxes')) {
        qtyNeeded = qtyVal / Math.max(1, m.pcs_per_box || 1);
    }

    errEl.style.display = 'none';
    if (qtyVal <= 0) {
        errEl.textContent = '⚠️ Enter a valid amount.';
        errEl.style.display = 'block';
    } else if (qtyNeeded > avail) {
        errEl.textContent = '⚠️ Max available: ' + avail + ' unit(s).';
        errEl.style.display = 'block';
    }
}

/* ── Duplicate check ── */
function checkDuplicates() {
    const hiddens = document.querySelectorAll('input[name="med_names[]"]');
    const names = [];
    let hasDup = false;
    hiddens.forEach(h => {
        if (h.value) {
            if (names.includes(h.value)) hasDup = true;
            names.push(h.value);
        }
    });
    document.getElementById('dupWarning').style.display = hasDup ? 'block' : 'none';
}

/* ── Close dropdown on outside click ── */
document.addEventListener('click', function(e) {
    if (!e.target.closest('.med-search-wrap')) {
        document.querySelectorAll('.med-search-wrap.open').forEach(w => {
            closeDrop(w.id.replace('wrap_', ''));
        });
    }
});

/* ── Form submit validation ── */
function handleSubmit(e) {
    const hiddens = document.querySelectorAll('input[name="med_names[]"]');
    let hasErrors = false;
    let names = [];

    // Check each row
    hiddens.forEach(h => {
        if (!h.value) { hasErrors = true; }
        if (names.includes(h.value)) { hasErrors = true; }
        if (h.value) names.push(h.value);
    });

    // Check qty errors
    document.querySelectorAll('.qty-error').forEach(el => {
        if (el.style.display !== 'none' && el.textContent) hasErrors = true;
    });

    // At least one medicine
    if (hiddens.length === 0 || (hiddens.length === 1 && !hiddens[0].value)) {
        hasErrors = true;
    }

    if (hasErrors) {
        e.preventDefault();
        showAlert('Validation Error', 'Please fix all errors before dispensing. Make sure all medicines are selected, quantities are valid, and there are no duplicates.', 'error');
        return;
    }

    document.getElementById('loadingOverlay').style.display = 'flex';
}

/* ── Ripple effect ── */
document.getElementById('dispenseBtn').addEventListener('click', function(e) {
    let x = e.clientX - e.target.getBoundingClientRect().left;
    let y = e.clientY - e.target.getBoundingClientRect().top;
    let r = document.createElement('span');
    r.className = 'ripple';
    r.style.left = x + 'px'; r.style.top = y + 'px';
    this.appendChild(r);
    setTimeout(() => r.remove(), 600);
});

/* ── Init: add first row on load ── */
window.addEventListener('DOMContentLoaded', () => {
    addMedRow();
});

<?php if ($alert): ?>
    window.addEventListener('load', () => {
        const title = "<?php echo ($alert_type === 'error' ? 'Dispense Failed' : 'Dispense Successful'); ?>";
        showAlert(title, "<?php echo addslashes($alert); ?>", "<?php echo $alert_type; ?>");
    });
<?php endif; ?>
</script>

</body>
</html>