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

    if (empty($med_name)) {
        $alert      = "Please select a medicine.";
        $alert_type = "error";

    } elseif ($qty_needed <= 0) {
        $alert      = "Please enter a valid quantity (minimum 1 unit).";
        $alert_type = "error";

    } else {
        // Total non-expired available stock
        $chk = $conn->query("
            SELECT SUM(quantity) AS avail
            FROM medicines
            WHERE name = '$med_name'
              AND expiration_date >= CURDATE()
              AND quantity > 0");
        $avail = (int)($chk ? $chk->fetch_assoc()['avail'] : 0);

        if ($avail < $qty_needed) {
            $alert      = "Insufficient non-expired stock! Only <strong>$avail</strong> unit(s) available for <strong>" . htmlspecialchars($med_name) . "</strong>.";
            $alert_type = "error";

        } else {
            // FIFO: pull from batches with earliest expiration date first
            $batches = $conn->query("
                SELECT *
                FROM medicines
                WHERE name = '$med_name'
                  AND expiration_date >= CURDATE()
                  AND quantity > 0
                ORDER BY expiration_date ASC");

            $remaining = $qty_needed;
            $details   = [];

            while ($remaining > 0 && ($b = $batches->fetch_assoc())) {
                $take    = min($remaining, (int)$b['quantity']);
                $new_qty = (int)$b['quantity'] - $take;

                $conn->query("UPDATE medicines SET quantity = $new_qty WHERE id = {$b['id']}");
                $conn->query("INSERT INTO logs (medicine_id, quantity, action)
                              VALUES ({$b['id']}, $take, 'Released to patient')");

                $exp_fmt = date('M d, Y', strtotime($b['expiration_date']));
                $details[] = "Batch #{$b['batch_number']} (Exp: {$exp_fmt}) — {$take} unit(s)";
                $remaining -= $take;
            }

            $detail_str = implode('<br>• ', $details);
            $new_total  = $avail - $qty_needed;
            $stock_note = $new_total <= 5
                ? "⚠️ Low stock alert: only <strong>{$new_total}</strong> unit(s) remaining."
                : "Remaining stock: <strong>{$new_total}</strong> unit(s).";

            $alert      = "✅ <strong>{$qty_needed} unit(s)</strong> of <strong>" . htmlspecialchars($med_name) . "</strong> dispensed to patient.<br>"
                        . "• {$detail_str}<br>{$stock_note}";
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
    GROUP BY name, label
    ORDER BY name ASC");
?>


    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            min-height: 100vh;
        }


        .container { max-width: 620px; margin: 40px auto; padding: 0 20px; }

        .form-card {
            background: white; border-radius: 15px; padding: 35px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .form-header { text-align: center; margin-bottom: 30px; }
        .form-header .icon { font-size: 50px; margin-bottom: 10px; }
        .form-header h2 { color: #2c3e50; font-size: 28px; margin-bottom: 10px; }
        .form-header p  { color: #7f8c8d; font-size: 14px; }

        .form-group { margin-bottom: 20px; }

        label {
            display: block; margin-bottom: 8px;
            color: #2c3e50; font-weight: 500; font-size: 14px;
        }
        label .required { color: #e74c3c; margin-left: 3px; }

        select, input {
            width: 100%; padding: 12px 15px;
            border: 2px solid #e0e0e0; border-radius: 8px;
            font-size: 14px; font-family: inherit;
            transition: all 0.3s ease; background: white;
        }
        select:focus, input:focus {
            outline: none; border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
        }

        /* Stock Info Panel */
        .stock-panel {
            border-radius: 10px; padding: 16px 18px;
            margin-bottom: 20px; display: none;
            border-left: 4px solid #3498db;
            background: #f0f8ff;
            font-size: 14px;
        }
        .stock-panel .sp-row { display: flex; justify-content: space-between; margin-bottom: 6px; }
        .stock-panel .sp-row:last-child { margin-bottom: 0; }
        .stock-panel .sp-label { color: #7f8c8d; }
        .stock-panel .sp-value { font-weight: 700; color: #2c3e50; }
        .stock-panel.warn  { border-left-color: #f39c12; background: #fff8e7; }
        .stock-panel.danger{ border-left-color: #e74c3c; background: #ffeaea; }

        /* Batch list inside panel */
        .batch-list { margin-top: 10px; }
        .batch-list-title { font-weight: 600; color: #2c3e50; margin-bottom: 6px; font-size: 13px; }
        .batch-item {
            display: flex; justify-content: space-between;
            padding: 5px 10px; border-radius: 5px;
            background: rgba(255,255,255,0.6);
            margin-bottom: 4px; font-size: 12px;
        }
        .batch-item.b-expired { background: #ffeaea; color: #e74c3c; }
        .batch-item.b-soon    { background: #fff3cd; color: #856404; }
        .batch-item.b-ok      { background: #d5f4e6; color: #1a7a4a; }

        /* Warnings */
        .warn-box {
            background: #fff3cd; border-left: 4px solid #ffc107;
            border-radius: 8px; padding: 12px 15px;
            margin-bottom: 16px; font-size: 13px; color: #856404;
            display: flex; align-items: flex-start; gap: 8px;
        }
        .warn-box.danger-box {
            background: #ffeaea; border-left-color: #e74c3c; color: #c0392b;
        }
        .fifo-note {
            background: #e8f4fd; border-left: 4px solid #3498db;
            border-radius: 8px; padding: 12px 15px;
            margin-bottom: 16px; font-size: 13px; color: #1a5276;
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
        }
        .btn-dispense:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231,76,60,0.4);
        }
        .btn-dispense:disabled {
            background: #95a5a6; cursor: not-allowed; transform: none;
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px; border-radius: 8px;
            margin-bottom: 20px;
            display: flex; align-items: flex-start;
            gap: 10px; animation: slideIn 0.3s ease;
            line-height: 1.6;
        }
        @keyframes slideIn {
            from { transform: translateX(-20px); opacity: 0; }
            to   { transform: translateX(0); opacity: 1; }
        }
        .alert-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724; border-left: 4px solid #28a745;
        }
        .alert-error {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24; border-left: 4px solid #dc3545;
        }
        .alert .close {
            margin-left: auto; cursor: pointer;
            font-size: 20px; font-weight: bold; flex-shrink: 0;
        }
        .alert .close:hover { opacity: 0.7; }

        .info-box {
            background: #f8f9fa; border-radius: 8px;
            padding: 15px; margin-top: 20px;
            text-align: center; border: 1px solid #e0e0e0;
        }
        .info-box p { color: #7f8c8d; font-size: 13px; }
        .info-box a { color: #3498db; text-decoration: none; font-weight: 500; }
        .info-box a:hover { text-decoration: underline; }

        input[type="number"] { appearance: textfield; -moz-appearance: textfield; }

        @media (max-width: 768px) {
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
            <div class="icon">💊</div>
            <h2>Dispense Medicine</h2>
            <p>Dispenses from the earliest-expiring batch first (FIFO)</p>
        </div>

        <?php if ($alert): ?>
        <div class="alert alert-<?php echo $alert_type; ?>" id="alertMessage">
            <div><?php echo $alert; ?></div>
            <span class="close" onclick="this.parentElement.style.display='none'">&times;</span>
        </div>
        <?php endif; ?>

        <div class="fifo-note">
            <span>ℹ️</span>
            <span><strong>FIFO Active:</strong> When you dispense, stock is taken from the batch with the earliest expiration date first.</span>
        </div>

        <form method="POST" id="dispenseForm">
            <div class="form-group">
                <label>Select Medicine <span class="required">*</span></label>
                <select name="med_name" id="medicineSelect" required onchange="updateStockPanel()">
                    <option value="">-- Select a medicine --</option>
                    <?php
                    /* Build option data. We'll store batch info as JSON in data-attr. */
                    $med_options = [];
                    if ($meds_query && $meds_query->num_rows > 0) {
                        while ($m = $meds_query->fetch_assoc()) {
                            $oname    = htmlspecialchars($m['name'],  ENT_QUOTES, 'UTF-8');
                            $olabel   = htmlspecialchars((string)$m['label'], ENT_QUOTES, 'UTF-8');
                            $avail    = (int)$m['avail_qty'];
                            $expired  = (int)$m['expired_qty'];
                            $next_exp = $m['next_exp'];

                            // Fetch individual batches for this medicine (for the panel)
                            $sname  = mysqli_real_escape_string($conn, $m['name']);
                            $slabel = mysqli_real_escape_string($conn, (string)$m['label']);
                            $bq     = $conn->query("
                                SELECT quantity, expiration_date
                                FROM medicines
                                WHERE name = '$sname' AND label = '$slabel' AND quantity > 0
                                ORDER BY expiration_date ASC");

                            $batches_info = [];
                            while ($brow = $bq->fetch_assoc()) {
                                $batches_info[] = [
                                    'qty' => (int)$brow['quantity'],
                                    'exp' => $brow['expiration_date'],
                                ];
                            }

                            $suffix = '';
                            if ($avail <= 0) {
                                $suffix = ' (ALL EXPIRED — cannot dispense)';
                            } elseif ($expired > 0) {
                                $suffix = ' (' . $expired . ' units expired, will be skipped)';
                            } elseif ($avail <= 5) {
                                $suffix = ' (LOW STOCK)';
                            }

                            $data = htmlspecialchars(json_encode([
                                'avail'    => $avail,
                                'expired'  => $expired,
                                'next_exp' => $next_exp,
                                'batches'  => $batches_info,
                                'label'    => $m['label'],
                            ]), ENT_QUOTES, 'UTF-8');

                            $disabled = ($avail <= 0) ? 'disabled' : '';
                            $style    = ($avail <= 0)
                                ? "color:#e74c3c;"
                                : ($avail <= 5 ? "color:#f39c12;" : "");

                            echo "<option value='{$oname}' data-info='{$data}' $disabled style='{$style}'>"
                               . "{$oname} ({$olabel}) — {$avail} avail{$suffix}"
                               . "</option>";
                        }
                    } else {
                        echo "<option value='' disabled>No medicines in inventory</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Dynamic stock panel -->
            <div id="stockPanel" class="stock-panel">
                <div class="sp-row">
                    <span class="sp-label">Available (non-expired):</span>
                    <span class="sp-value" id="panelAvail">—</span>
                </div>
                <div class="sp-row" id="panelExpiredRow" style="display:none;">
                    <span class="sp-label">⚠️ Expired (will be skipped):</span>
                    <span class="sp-value" style="color:#e74c3c;" id="panelExpired">—</span>
                </div>
                <div class="sp-row">
                    <span class="sp-label">Next expiry date (FIFO):</span>
                    <span class="sp-value" id="panelNextExp">—</span>
                </div>
                <div class="batch-list" id="batchList"></div>
            </div>

            <!-- Warnings injected by JS -->
            <div id="warnArea"></div>

            <div class="form-group">
                <label>Quantity to Dispense <span class="required">*</span></label>
                <input type="number" name="qty" id="qty" required min="1"
                       placeholder="Select a medicine first" disabled
                       oninput="validateQty()">
                <small id="qtyMsg" style="color:#e74c3c; display:none; margin-top:5px;"></small>
            </div>

            <button type="submit" name="use" id="dispenseBtn" class="btn-dispense" disabled>
                💊 Dispense Medicine
            </button>
        </form>

        <div class="info-box">
            <p>📋 <strong>Important:</strong> Always verify medicine and dosage before dispensing.</p>
            <p style="margin-top:10px;">📊 <a href="logs.php">View Dispensing Logs →</a></p>
            <p style="margin-top:5px;">🏥 <a href="index.php">Back to Inventory →</a></p>
        </div>
    </div>
</div>

<script>
const today = new Date().toISOString().split('T')[0];
const soon  = new Date(Date.now() + 30*24*60*60*1000).toISOString().split('T')[0];

function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    const d = new Date(dateStr + 'T00:00:00');
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function updateStockPanel() {
    const sel      = document.getElementById('medicineSelect');
    const panel    = document.getElementById('stockPanel');
    const qtyInput = document.getElementById('qty');
    const btn      = document.getElementById('dispenseBtn');
    const warnArea = document.getElementById('warnArea');
    const batchList= document.getElementById('batchList');

    warnArea.innerHTML = '';
    batchList.innerHTML = '';

    if (!sel.value) {
        panel.style.display = 'none';
        panel.className = 'stock-panel';
        qtyInput.disabled = true;
        qtyInput.value = '';
        qtyInput.placeholder = 'Select a medicine first';
        btn.disabled = true;
        return;
    }

    const opt  = sel.options[sel.selectedIndex];
    const info = JSON.parse(opt.dataset.info || '{}');
    const avail   = info.avail   || 0;
    const expired = info.expired || 0;
    const nextExp = info.next_exp || null;
    const batches = info.batches || [];

    // Panel values
    document.getElementById('panelAvail').textContent = avail + ' unit(s)';
    document.getElementById('panelExpired').textContent = expired + ' unit(s)';
    document.getElementById('panelExpiredRow').style.display = expired > 0 ? 'flex' : 'none';
    document.getElementById('panelNextExp').textContent = formatDate(nextExp);

    // Panel style
    panel.style.display = 'block';
    if (avail <= 0) {
        panel.className = 'stock-panel danger';
    } else if (avail <= 5 || (nextExp && nextExp <= soon)) {
        panel.className = 'stock-panel warn';
    } else {
        panel.className = 'stock-panel';
    }

    // Batch list
    if (batches.length > 0) {
        let html = '<div class="batch-list-title">📦 Batches (FIFO order — oldest expiry first):</div>';
        batches.forEach(function(b) {
            let cls = 'b-ok', tag = '✓ Valid';
            if (b.exp < today) { cls = 'b-expired'; tag = '⚠️ Expired (skip)'; }
            else if (b.exp < soon) { cls = 'b-soon'; tag = '📅 Expiring Soon'; }
            html += "<div class='batch-item " + cls + "'>"
                  + "<span>Exp: " + formatDate(b.exp) + "</span>"
                  + "<span>" + b.qty + " unit(s) &nbsp;|&nbsp; " + tag + "</span>"
                  + "</div>";
        });
        batchList.innerHTML = html;
    }

    // Warnings
    let warns = '';
    if (expired > 0) {
        warns += "<div class='warn-box'><span>⚠️</span><div><strong>Expired batches detected:</strong> "
               + expired + " unit(s) are expired and will be automatically skipped during dispensing.</div></div>";
    }
    if (nextExp && nextExp >= today && nextExp <= soon) {
        warns += "<div class='warn-box'><span>📅</span><div><strong>Expiring soon:</strong> The oldest available batch expires on "
               + formatDate(nextExp) + ".</div></div>";
    }
    warnArea.innerHTML = warns;

    // Enable/disable qty input and button
    if (avail <= 0) {
        qtyInput.disabled = true;
        qtyInput.placeholder = 'No non-expired stock available';
        btn.disabled = true;
    } else {
        qtyInput.disabled = false;
        qtyInput.max = avail;
        qtyInput.placeholder = 'Enter quantity (max ' + avail + ')';
        validateQty();
    }
}

function validateQty() {
    const sel     = document.getElementById('medicineSelect');
    const qty     = parseInt(document.getElementById('qty').value) || 0;
    const msg     = document.getElementById('qtyMsg');
    const btn     = document.getElementById('dispenseBtn');
    const opt     = sel.options[sel.selectedIndex];

    if (!sel.value || !opt) { btn.disabled = true; return; }

    const info  = JSON.parse(opt.dataset.info || '{}');
    const avail = info.avail || 0;

    if (qty <= 0) {
        msg.textContent = '⚠️ Please enter at least 1 unit.';
        msg.style.display = 'block';
        btn.disabled = true;
    } else if (qty > avail) {
        msg.textContent = '⚠️ Not enough non-expired stock! Only ' + avail + ' unit(s) available.';
        msg.style.display = 'block';
        btn.disabled = true;
    } else {
        msg.style.display = 'none';
        btn.disabled = false;
    }
}

// Auto-hide alert after 8 seconds
setTimeout(function() {
    const a = document.getElementById('alertMessage');
    if (a) a.style.display = 'none';
}, 8000);

// Reload page 3 seconds after successful dispense to refresh all data
<?php if ($alert_type === 'success'): ?>
setTimeout(function() { location.reload(); }, 3500);
<?php endif; ?>

document.addEventListener('DOMContentLoaded', updateStockPanel);
</script>

</body>
</html>