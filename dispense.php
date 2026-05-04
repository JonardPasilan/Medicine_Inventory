<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/header.php';

$today = date('Y-m-d');
$soon  = date('Y-m-d', strtotime('+30 days'));

/* ─── FIFO Dispense Handler ─────────────────────────────────────── */
$alert       = '';
$alert_type  = '';

if (isset($_POST['use'])) {
    $med_name   = mysqli_real_escape_string($conn, trim($_POST['med_name'] ?? ''));
    $qty_needed = intval($_POST['qty'] ?? 0);
    $patient    = mysqli_real_escape_string($conn, trim($_POST['patient_name'] ?? ''));
    $prescriber = mysqli_real_escape_string($conn, trim($_POST['prescriber_name'] ?? ''));
    $staff_name = mysqli_real_escape_string($conn, trim($_POST['staff_name'] ?? ''));

    if (empty($med_name)) {
        $alert      = "Please select a medicine.";
        $alert_type = "error";
    } elseif ($qty_needed <= 0) {
        $alert      = "Please enter a valid quantity (minimum 1 unit).";
        $alert_type = "error";
    } else {
        $chk = $conn->query("
            SELECT SUM(quantity) AS avail
            FROM medicines
            WHERE name = '$med_name'
              AND expiration_date >= CURDATE()
              AND quantity > 0
              AND type IN ('medicine', 'consumable')");
        $avail = (int)($chk ? $chk->fetch_assoc()['avail'] : 0);

        if ($avail < $qty_needed) {
            $alert      = "Insufficient non-expired stock! Only $avail unit(s) available.";
            $alert_type = "error";
        } else {
            $batches = $conn->query("
                SELECT *
                FROM medicines
                WHERE name = '$med_name'
                  AND expiration_date >= CURDATE()
                  AND quantity > 0
                  AND type IN ('medicine', 'consumable')
                ORDER BY expiration_date ASC");

            $remaining = $qty_needed;
            $details   = [];

            while ($remaining > 0 && ($b = $batches->fetch_assoc())) {
                $take    = min($remaining, (int)$b['quantity']);
                $new_qty = (int)$b['quantity'] - $take;

                $conn->query("UPDATE medicines SET quantity = $new_qty WHERE id = {$b['id']}");
                
                $p_val = !empty($patient) ? "'$patient'" : "NULL";
                $d_val = !empty($prescriber) ? "'$prescriber'" : "NULL";
                $s_val = !empty($staff_name) ? "'$staff_name'" : "NULL";
                
                $conn->query("INSERT INTO logs (medicine_id, quantity, action, patient_name, prescriber_name, staff_name)
                              VALUES ({$b['id']}, $take, 'Released to patient', $p_val, $d_val, $s_val)");

                $exp_fmt = date('M d, Y', strtotime($b['expiration_date']));
                $details[] = "Batch #{$b['batch_number']} (Exp: {$exp_fmt}) — {$take} unit(s)";
                $remaining -= $take;
            }

            $detail_str = implode('<br>• ', $details);
            $alert      = "<strong>{$qty_needed} unit(s)</strong> of " . htmlspecialchars($med_name) . " dispensed.<br>• {$detail_str}";
            $alert_type = "success";
        }
    }
}

/* ─── Build medicine dropdown data ──────────────────────────────── */
$meds_query = $conn->query("
    SELECT
        name,
        label,
        SUM(CASE WHEN expiration_date >= CURDATE() THEN quantity ELSE 0 END) AS avail_qty,
        SUM(CASE WHEN expiration_date <  CURDATE() THEN quantity ELSE 0 END) AS expired_qty,
        MIN(CASE WHEN expiration_date >= CURDATE() AND quantity > 0
                 THEN expiration_date ELSE NULL END)                          AS next_exp
    FROM medicines
    WHERE quantity > 0
      AND is_archived = 0
      AND type IN ('medicine', 'consumable')
    GROUP BY name, label
    ORDER BY name ASC");
?>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--color-canvas);
            min-height: 100vh;
        }


        .container { max-width: 620px; margin: 40px auto; padding: 0 20px; }

        .form-card {
            background: var(--color-surface); border-radius: var(--radius-lg); padding: 35px;
            box-shadow: var(--shadow-md); border: 1px solid var(--color-border);
            animation: fadeIn 0.6s cubic-bezier(0.23, 1, 0.32, 1);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px) scale(0.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .form-header { text-align: center; margin-bottom: 30px; }
        .form-header .icon { font-size: 50px; margin-bottom: 10px; transition: transform 0.5s ease; }
        .form-card:hover .icon { transform: rotate(-15deg) scale(1.1); }
        .form-header h2 { color: var(--color-text-primary); font-size: 28px; margin-bottom: 10px; }
        .form-header p  { color: var(--color-text-secondary); font-size: 14px; }

        .form-group { margin-bottom: 20px; }

        label {
            display: block; margin-bottom: 8px;
            color: var(--color-text-primary); font-weight: 500; font-size: 14px;
        }
        label .required { color: #e74c3c; margin-left: 3px; }

        select, input {
            width: 100%; padding: 12px 15px;
            border: 2px solid var(--color-border); border-radius: var(--radius-sm);
            font-size: 14px; font-family: inherit;
            transition: all 0.3s ease; background: var(--color-overlay); color: var(--color-text-primary);
        }
        select:focus, input:focus {
            outline: none; border-color: var(--color-brand);
            box-shadow: 0 0 0 3px var(--color-brand-light);
            transform: translateY(-1px);
        }

        /* Stock Info Panel */
        .stock-panel {
            border-radius: var(--radius-md); padding: 16px 18px;
            margin-bottom: 20px; display: none;
            border-left: 4px solid var(--color-brand);
            background: var(--color-brand-light);
            font-size: 14px;
            animation: slideDown 0.4s ease;
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .stock-panel .sp-row { display: flex; justify-content: space-between; margin-bottom: 6px; }
        .stock-panel .sp-value { font-weight: 700; color: var(--color-text-primary); }
        .stock-panel.warn  { border-left-color: #f39c12; background: #fff8e7; }
        .stock-panel.danger{ border-left-color: #e74c3c; background: #ffeaea; }

        /* Batch list inside panel */
        .batch-list { margin-top: 10px; }
        .batch-list-title { font-weight: 600; color: var(--color-text-primary); margin-bottom: 6px; font-size: 13px; }
        .batch-item {
            display: flex; justify-content: space-between;
            padding: 5px 10px; border-radius: 5px;
            background: var(--color-overlay);
            margin-bottom: 4px; font-size: 12px;
            transition: transform 0.2s ease;
        }
        .batch-item:hover { transform: scale(1.02); }
        .batch-item.b-expired { background: #ffeaea; color: #e74c3c; }
        .batch-item.b-soon    { background: #fff3cd; color: #856404; }
        .batch-item.b-ok      { background: #d5f4e6; color: #1a7a4a; }

        /* Warnings */
        .warn-box {
            background: #fff3cd; border-left: 4px solid #ffc107;
            border-radius: 8px; padding: 12px 15px;
            margin-bottom: 16px; font-size: 13px; color: #856404;
            display: flex; align-items: flex-start; gap: 8px;
            animation: fadeIn 0.3s ease;
        }
        .warn-box.danger-box { background: #ffeaea; border-left-color: #e74c3c; color: #c0392b; }
        .fifo-note {
            background: var(--color-brand-light); border-left: 4px solid var(--color-brand);
            border-radius: var(--radius-sm); padding: 12px 15px;
            margin-bottom: 16px; font-size: 13px; color: var(--color-text-primary);
            display: flex; align-items: center; gap: 8px;
        }

        /* Dispense Button */
        .btn-dispense {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            border: none; color: white;
            font-size: 16px; font-weight: 600;
            border-radius: 8px; cursor: pointer;
            transition: all 0.3s ease; margin-top: 10px;
            position: relative; overflow: hidden;
        }
        .btn-dispense:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(231,76,60,0.4);
        }
        .btn-dispense:active { transform: translateY(0); }
        .btn-dispense:disabled { background: var(--color-overlay); color: var(--color-text-muted); cursor: not-allowed; transform: none; box-shadow: none; }

        /* Ripple Effect */
        .ripple {
            position: absolute;
            background: rgba(255, 255, 255, 0.4);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple-animation 0.6s linear;
            pointer-events: none;
        }
        @keyframes ripple-animation { to { transform: scale(4); opacity: 0; } }

        /* Loading Spinner */
        #loadingOverlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.35);
            z-index: 10000; align-items: center; justify-content: center;
            flex-direction: column; gap: 15px; backdrop-filter: blur(2px);
        }
        .spinner {
            width: 50px; height: 50px;
            border: 5px solid var(--color-border); border-top: 5px solid var(--color-brand);
            border-radius: 50%; animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* Toast Notification */
        #toastContainer { position: fixed; bottom: 30px; right: 30px; z-index: 10001; }
        .toast {
            background: var(--color-surface); padding: 15px 25px; border-radius: var(--radius-md);
            box-shadow: var(--shadow-lg); border: 1px solid var(--color-border);
            display: flex; align-items: center; gap: 12px; margin-top: 10px;
            transform: translateX(120%); transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            border-left: 5px solid hsl(140, 60%, 45%);
        }
        .toast.show { transform: translateX(0); }
        .toast.error { border-left-color: hsl(0, 70%, 50%); }

        .info-box {
            background: var(--color-overlay); border-radius: var(--radius-sm);
            padding: 15px; margin-top: 20px;
            text-align: center; border: 1px solid var(--color-border);
        }
        .info-box a { color: var(--color-brand); text-decoration: none; font-weight: 500; }

        @media (max-width: 768px) {
            .container { margin: 20px auto; }
            .form-card { padding: 25px; }
            .form-header h2 { font-size: 24px; }
        }
    </style>

<div id="loadingOverlay">
    <div class="spinner"></div>
    <p style="color: #1f4f87; font-weight: 600;">Processing dispensing...</p>
</div>

<div id="toastContainer"></div>

<div class="container">
    <div class="form-card">
        <div class="form-header">
            <div class="icon">💊</div>
            <h2>Dispense Medicine</h2>
            <p>Dispenses from the earliest-expiring batch first (FIFO)</p>
        </div>

        <div class="fifo-note">
            <span>ℹ️</span>
            <span><strong>FIFO Active:</strong> Stock is taken from the oldest expiration batch first.</span>
        </div>

        <form method="POST" id="dispenseForm" onsubmit="handleSubmit(event)">
            <div class="form-group">
                <label>Select Medicine <span class="required">*</span></label>
                <select name="med_name" id="medicineSelect" required onchange="updateStockPanel()">
                    <option value="">-- Select a medicine --</option>
                    <?php
                    if ($meds_query && $meds_query->num_rows > 0) {
                        while ($m = $meds_query->fetch_assoc()) {
                            $oname    = htmlspecialchars($m['name'],  ENT_QUOTES, 'UTF-8');
                            $olabel   = htmlspecialchars((string)$m['label'], ENT_QUOTES, 'UTF-8');
                            $avail    = (int)$m['avail_qty'];
                            $expired  = (int)$m['expired_qty'];
                            $next_exp = $m['next_exp'];

                            $sname  = mysqli_real_escape_string($conn, $m['name']);
                            $slabel = mysqli_real_escape_string($conn, (string)$m['label']);
                            $bq     = $conn->query("SELECT quantity, expiration_date FROM medicines WHERE name = '$sname' AND label = '$slabel' AND quantity > 0 ORDER BY expiration_date ASC");

                            $batches_info = [];
                            while ($brow = $bq->fetch_assoc()) {
                                $batches_info[] = ['qty' => (int)$brow['quantity'], 'exp' => $brow['expiration_date']];
                            }

                            $data = htmlspecialchars(json_encode([
                                'avail' => $avail, 'expired' => $expired, 'next_exp' => $next_exp, 'batches' => $batches_info
                            ]), ENT_QUOTES, 'UTF-8');

                            $disabled = ($avail <= 0) ? 'disabled' : '';
                            $style = ($avail <= 0) ? "color:#e74c3c;" : ($avail <= 5 ? "color:#f39c12;" : "");

                            echo "<option value='{$oname}' data-info='{$data}' $disabled style='{$style}'>{$oname} ({$olabel}) — {$avail} avail</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div id="stockPanel" class="stock-panel">
                <div class="sp-row">
                    <span>Available (non-expired):</span>
                    <span class="sp-value" id="panelAvail">—</span>
                </div>
                <div class="sp-row" id="panelExpiredRow" style="display:none;">
                    <span style="color:#e74c3c;">⚠️ Expired (skipped):</span>
                    <span class="sp-value" style="color:#e74c3c;" id="panelExpired">—</span>
                </div>
                <div class="sp-row">
                    <span>Next expiry (FIFO):</span>
                    <span class="sp-value" id="panelNextExp">—</span>
                </div>
                <div class="batch-list" id="batchList"></div>
            </div>

            <div id="warnArea"></div>

            <div class="form-group" style="display:flex; gap:15px; flex-wrap:wrap;">
                <div style="flex:1; min-width: 150px;">
                    <label>Patient Name <small style="color:#7f8c8d;">(Optional)</small></label>
                    <input type="text" name="patient_name" placeholder="Enter patient name">
                </div>
                <div style="flex:1; min-width: 150px;">
                    <label>Prescriber Name <small style="color:#7f8c8d;">(Optional)</small></label>
                    <input type="text" name="prescriber_name" placeholder="Dr. Name">
                </div>
                <div style="flex:1; min-width: 150px;">
                    <label>Dispensed By (Staff) <span class="required">*</span></label>
                    <input type="text" name="staff_name" placeholder="Your name" required>
                </div>
            </div>

            <div class="form-group">
                <label>Quantity to Dispense <span class="required">*</span></label>
                <input type="number" name="qty" id="qty" required min="1" placeholder="Select medicine first" disabled oninput="validateQty()">
                <small id="qtyMsg" style="color:#e74c3c; display:none; margin-top:5px;"></small>
            </div>

            <button type="submit" name="use" id="dispenseBtn" class="btn-dispense" disabled>
                💊 Dispense Medicine
            </button>
        </form>

        <div class="info-box">
            <p>📋 <strong>Important:</strong> Verify medicine and dosage before dispensing.</p>
            <p style="margin-top:10px;">📊 <a href="index.php">Back to Inventory →</a></p>
        </div>
    </div>
</div>

<script>
    // Ripple effect
    document.querySelectorAll('.btn-dispense').forEach(button => {
        button.addEventListener('click', function(e) {
            let x = e.clientX - e.target.getBoundingClientRect().left;
            let y = e.clientY - e.target.getBoundingClientRect().top;
            let ripples = document.createElement('span');
            ripples.className = 'ripple';
            ripples.style.left = x + 'px'; ripples.style.top = y + 'px';
            this.appendChild(ripples);
            setTimeout(() => { ripples.remove() }, 600);
        });
    });

    function showLoading() { document.getElementById('loadingOverlay').style.display = 'flex'; }

    function handleSubmit() { showLoading(); }

    const today = new Date().toISOString().split('T')[0];
    const soon  = new Date(Date.now() + 30*24*60*60*1000).toISOString().split('T')[0];

    function formatDate(dateStr) {
        if (!dateStr) return 'N/A';
        const d = new Date(dateStr + 'T00:00:00');
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function updateStockPanel() {
        const sel = document.getElementById('medicineSelect');
        const panel = document.getElementById('stockPanel');
        const qtyInput = document.getElementById('qty');
        const btn = document.getElementById('dispenseBtn');
        const warnArea = document.getElementById('warnArea');
        const batchList = document.getElementById('batchList');

        warnArea.innerHTML = ''; batchList.innerHTML = '';

        if (!sel.value) {
            panel.style.display = 'none'; qtyInput.disabled = true; qtyInput.value = ''; btn.disabled = true; return;
        }

        const info = JSON.parse(sel.options[sel.selectedIndex].dataset.info || '{}');
        const avail = info.avail || 0;
        const expired = info.expired || 0;
        const nextExp = info.next_exp || null;
        const batches = info.batches || [];

        document.getElementById('panelAvail').textContent = avail + ' unit(s)';
        document.getElementById('panelExpired').textContent = expired + ' unit(s)';
        document.getElementById('panelExpiredRow').style.display = expired > 0 ? 'flex' : 'none';
        document.getElementById('panelNextExp').textContent = formatDate(nextExp);

        panel.style.display = 'block';
        panel.className = 'stock-panel' + (avail <= 0 ? ' danger' : (avail <= 5 ? ' warn' : ''));

        if (batches.length > 0) {
            let html = '<div class="batch-list-title">📦 Batches (FIFO Order):</div>';
            batches.forEach(b => {
                let cls = 'b-ok', tag = '✓ Valid';
                if (b.exp < today) { cls = 'b-expired'; tag = '⚠️ Expired'; }
                else if (b.exp < soon) { cls = 'b-soon'; tag = '📅 Expiring Soon'; }
                html += `<div class='batch-item ${cls}'><span>Exp: ${formatDate(b.exp)}</span><span>${b.qty} unit(s)</span></div>`;
            });
            batchList.innerHTML = html;
        }

        qtyInput.disabled = avail <= 0;
        if (!qtyInput.disabled) { qtyInput.max = avail; validateQty(); }
    }

    function validateQty() {
        const qty = parseInt(document.getElementById('qty').value) || 0;
        const sel = document.getElementById('medicineSelect');
        const info = JSON.parse(sel.options[sel.selectedIndex].dataset.info || '{}');
        const avail = info.avail || 0;
        const msg = document.getElementById('qtyMsg');
        const btn = document.getElementById('dispenseBtn');

        if (qty <= 0) { msg.textContent = '⚠️ Enter at least 1 unit.'; msg.style.display = 'block'; btn.disabled = true; }
        else if (qty > avail) { msg.textContent = '⚠️ Max available is ' + avail; msg.style.display = 'block'; btn.disabled = true; }
        else { msg.style.display = 'none'; btn.disabled = false; }
    }

    <?php if ($alert): ?>
        window.onload = () => {
            const title = "<?php echo ($alert_type === 'error' ? 'Dispense Failed' : 'Dispense Successful'); ?>";
            showAlert(title, "<?php echo addslashes($alert); ?>", "<?php echo $alert_type; ?>");
        };
    <?php endif; ?>
</script>

</body>
</html>