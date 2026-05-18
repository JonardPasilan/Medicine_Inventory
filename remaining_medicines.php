<?php
require_once __DIR__ . '/db.php';

// ─── CSV Export ───────────────────────────────────────────────────────────────
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $type_filter = '';
    if (!empty($_GET['rem_type'])) {
        $rt = mysqli_real_escape_string($conn, $_GET['rem_type']);
        $type_filter = " AND type = '$rt'";
    }

    $q = $conn->query("
        SELECT name, label, type,
               SUM(quantity)        AS total_remaining,
               MIN(expiration_date) AS earliest_exp
        FROM medicines
        WHERE is_archived = 0
          AND type IN ('medicine','consumable')
          $type_filter
        GROUP BY name, label, type
        ORDER BY name ASC, label ASC");

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=remaining_medicines_' . date('Y-m') . '.csv');
    $out = fopen('php://output', 'w');

    // Title rows
    fputcsv($out, ['REMAINING MEDICINES REPORT - ' . date('F Y')]);
    fputcsv($out, ['Generated on: ' . date('F d, Y h:i A')]);
    fputcsv($out, []);
    // Only 3 columns as requested
    fputcsv($out, ['Medicine Name', 'Expiration Date', 'Remaining']);

    if ($q && $q->num_rows > 0) {
        while ($row = $q->fetch_assoc()) {
            // Combine name + label (dosage) into one full medicine name
            $full_name = trim($row['name'] . ($row['label'] ? ' ' . $row['label'] : ''));

            $exp_str = (!empty($row['earliest_exp']) && $row['earliest_exp'] != '0000-00-00')
                ? date('F d, Y', strtotime($row['earliest_exp'])) : 'N/A';

            fputcsv($out, [
                $full_name,
                $exp_str,
                $row['total_remaining']
            ]);
        }
    }
    fclose($out);
    exit();
}

// ─── Query ────────────────────────────────────────────────────────────────────
$rem_type_param = isset($_GET['rem_type']) ? $_GET['rem_type'] : 'medicine';
$type_filter_sql = '';
if (!empty($rem_type_param)) {
    $rt_safe = mysqli_real_escape_string($conn, $rem_type_param);
    $type_filter_sql = " AND type = '$rt_safe'";
}

$result = $conn->query("
    SELECT name, label, type,
           SUM(quantity)        AS total_remaining,
           MIN(expiration_date) AS earliest_exp
    FROM medicines
    WHERE is_archived = 0
      AND type IN ('medicine','consumable')
      $type_filter_sql
    GROUP BY name, label, type
    ORDER BY name ASC, label ASC");

$rows        = [];
$total_items = 0;
$total_units = 0;
$low_count   = 0;
if ($result && $result->num_rows > 0) {
    while ($r = $result->fetch_assoc()) {
        $rows[]      = $r;
        $total_items++;
        $total_units += (int)$r['total_remaining'];
        if ((int)$r['total_remaining'] <= 5) $low_count++;
    }
}

$today = date('Y-m-d');
$soon  = date('Y-m-d', strtotime('+30 days'));

$export_url = 'remaining_medicines.php?export=csv';
if (!empty($rem_type_param)) $export_url .= '&rem_type=' . urlencode($rem_type_param);

require_once __DIR__ . '/header.php';
?>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', sans-serif; background: var(--color-canvas); min-height: 100vh; }

    .container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }

    /* ── Page Header ── */
    .page-hero {
        background: linear-gradient(135deg, hsl(210, 70%, 30%) 0%, hsl(230, 65%, 42%) 100%);
        border-radius: var(--radius-lg);
        padding: 36px 40px;
        margin-bottom: 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 20px;
        box-shadow: 0 8px 32px rgba(30, 60, 140, 0.22);
        animation: fadeUp 0.5s ease;
    }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(18px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .page-hero-left { display: flex; align-items: center; gap: 20px; }
    .page-hero-icon {
        width: 64px; height: 64px;
        background: rgba(255,255,255,0.18);
        border-radius: var(--radius-md);
        display: flex; align-items: center; justify-content: center;
        font-size: 32px;
        backdrop-filter: blur(6px);
        border: 1px solid rgba(255,255,255,0.25);
    }
    .page-hero-text h1 { color: white; font-size: 24px; font-weight: 700; margin-bottom: 4px; }
    .page-hero-text p  { color: rgba(255,255,255,0.8); font-size: 13px; }
    .page-hero-actions { display: flex; gap: 10px; flex-wrap: wrap; }

    .btn-back {
        padding: 10px 20px;
        background: rgba(255,255,255,0.15);
        color: white;
        border: 1px solid rgba(255,255,255,0.35);
        border-radius: var(--radius-sm);
        font-size: 14px; font-weight: 500;
        cursor: pointer; text-decoration: none;
        display: inline-flex; align-items: center; gap: 6px;
        font-family: 'Inter', sans-serif;
        transition: all 0.3s ease;
        backdrop-filter: blur(4px);
    }
    .btn-back:hover { background: rgba(255,255,255,0.25); transform: translateY(-2px); }

    .btn-export-csv {
        padding: 10px 22px;
        background: hsl(140, 60%, 38%);
        color: white;
        border: none;
        border-radius: var(--radius-sm);
        font-size: 14px; font-weight: 600;
        cursor: pointer; text-decoration: none;
        display: inline-flex; align-items: center; gap: 6px;
        font-family: 'Inter', sans-serif;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(34, 139, 34, 0.3);
    }
    .btn-export-csv:hover { background: hsl(140, 60%, 31%); transform: translateY(-2px); box-shadow: 0 6px 18px rgba(34,139,34,0.35); }

    /* ── Summary Cards ── */
    .summary-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .sum-card {
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        padding: 20px;
        text-align: center;
        box-shadow: var(--shadow-sm);
        transition: transform 0.25s ease;
    }
    .sum-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
    .sum-card .sc-num { font-size: 30px; font-weight: 800; color: var(--color-text-primary); }
    .sum-card .sc-lbl { font-size: 12px; color: var(--color-text-secondary); margin-top: 4px; font-weight: 500; }

    /* ── Filter Bar ── */
    .filter-bar {
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        padding: 16px 20px;
        margin-bottom: 20px;
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
        box-shadow: var(--shadow-sm);
    }
    .filter-bar label { font-size: 13px; font-weight: 600; color: var(--color-text-secondary); }
    .filter-bar select {
        padding: 8px 14px;
        border: 2px solid var(--color-border);
        border-radius: var(--radius-sm);
        background: var(--color-overlay);
        color: var(--color-text-primary);
        font-size: 14px;
        font-family: 'Inter', sans-serif;
        cursor: pointer;
        transition: border-color 0.2s;
    }
    .filter-bar select:focus { outline: none; border-color: var(--color-brand); }
    .filter-bar .btn-apply {
        padding: 8px 20px;
        background: var(--color-brand);
        color: white;
        border: none;
        border-radius: var(--radius-sm);
        font-size: 14px; font-weight: 500;
        cursor: pointer;
        font-family: 'Inter', sans-serif;
        transition: all 0.25s ease;
    }
    .filter-bar .btn-apply:hover { background: var(--color-brand-dark); transform: translateY(-1px); }
    .filter-bar .btn-reset {
        padding: 8px 16px;
        background: var(--color-overlay);
        color: var(--color-text-secondary);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-sm);
        font-size: 14px; font-weight: 500;
        cursor: pointer; text-decoration: none;
        font-family: 'Inter', sans-serif;
        transition: all 0.25s ease;
        display: inline-flex; align-items: center;
    }
    .filter-bar .btn-reset:hover { background: var(--color-border); color: var(--color-text-primary); }

    /* ── Table ── */
    .table-wrap {
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        overflow-x: auto;
        box-shadow: var(--shadow-sm);
        animation: fadeUp 0.5s ease 0.1s both;
    }
    table { width: 100%; border-collapse: collapse; }
    thead th {
        background: var(--color-brand);
        color: white;
        padding: 14px 16px;
        text-align: left;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.03em;
    }
    tbody td {
        padding: 13px 16px;
        border-bottom: 1px solid var(--color-border);
        color: var(--color-text-primary);
        font-size: 14px;
    }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover td { background: var(--color-overlay); }

    /* Qty badges */
    .qty-badge {
        display: inline-block; padding: 4px 14px;
        border-radius: 20px; font-weight: 700; font-size: 14px;
    }
    .qty-ok   { background: hsl(140, 60%, 93%); color: hsl(140, 60%, 25%); }
    .qty-low  { background: hsl(30, 90%, 93%);  color: hsl(30, 80%, 30%);  }
    .qty-zero { background: hsl(0, 80%, 94%);   color: hsl(0, 70%, 35%);   }

    /* Expiry colors */
    .exp-ok   { color: hsl(140, 60%, 30%); font-weight: 600; }
    .exp-soon { color: hsl(30, 80%, 33%);  font-weight: 600; }
    .exp-past { color: hsl(0, 70%, 38%);   font-weight: 600; }

    /* Type pills */
    .type-pill {
        display: inline-block; padding: 3px 10px;
        border-radius: 20px; font-size: 11px; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.05em;
    }
    .type-medicine   { background: hsl(210, 80%, 93%); color: hsl(210, 70%, 30%); }
    .type-consumable { background: hsl(270, 60%, 93%); color: hsl(270, 60%, 30%); }

    /* Footer summary bar */
    .foot-bar {
        display: flex; gap: 24px; padding: 14px 20px;
        background: var(--color-overlay);
        border-top: 1px solid var(--color-border);
        font-size: 13px; flex-wrap: wrap; align-items: center;
    }
    .foot-bar span { color: var(--color-text-secondary); }
    .foot-bar strong { color: var(--color-text-primary); }

    .no-data { text-align: center; padding: 60px; color: var(--color-text-muted); }
    .no-data .icon { font-size: 48px; margin-bottom: 14px; }

    @media (max-width: 768px) {
        .page-hero { padding: 24px 20px; }
        .page-hero-left { gap: 12px; }
        .page-hero-icon { width: 50px; height: 50px; font-size: 24px; }
        .page-hero-text h1 { font-size: 18px; }
        thead th, tbody td { padding: 10px 12px; font-size: 12px; }
    }
</style>

<div class="container">

    <!-- Page Hero -->
    <div class="page-hero">
        <div class="page-hero-left">
            <div class="page-hero-icon">💊</div>
            <div class="page-hero-text">
                <h1>Remaining Medicines Report</h1>
               
            </div>
        </div>
        <div class="page-hero-actions">
            <a href="logs.php" class="btn-back">← Back to Logs</a>
            <a href="<?php echo htmlspecialchars($export_url); ?>" class="btn-export-csv">⬇️ Export CSV</a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="sum-card">
            <div class="sc-num"><?php echo $total_items; ?></div>
            <div class="sc-lbl">📦 Total Items</div>
        </div>
        <div class="sum-card">
            <div class="sc-num"><?php echo number_format($total_units); ?></div>
            <div class="sc-lbl">🔢 Total Units</div>
        </div>
        <div class="sum-card">
            <div class="sc-num" style="color:hsl(30,80%,35%);"><?php echo $low_count; ?></div>
            <div class="sc-lbl">⚠️ Low Stock (≤5)</div>
        </div>
        <div class="sum-card">
            <div class="sc-num" style="color:hsl(0,70%,40%);"><?php echo array_sum(array_map(fn($r) => (int)$r['total_remaining'] === 0 ? 1 : 0, $rows)); ?></div>
            <div class="sc-lbl">📭 Zero Stock</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="GET" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            <label>Filter by Type:</label>
            <select name="rem_type">
                <option value="medicine"    <?php echo $rem_type_param === 'medicine'   ? 'selected' : ''; ?>>Medicines Only</option>
                <option value="consumable"  <?php echo $rem_type_param === 'consumable' ? 'selected' : ''; ?>>Consumables Only</option>
                <option value=""            <?php echo $rem_type_param === ''           ? 'selected' : ''; ?>>All (Medicines + Consumables)</option>
            </select>
            <button type="submit" class="btn-apply">Apply</button>
            <?php if ($rem_type_param !== 'medicine'): ?>
                <a href="remaining_medicines.php" class="btn-reset">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Table -->
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Medicine Name</th>
                    <th>Expiration Date</th>
                    <th>Remaining</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($rows)): ?>
                <?php foreach ($rows as $i => $r): ?>
                <?php
                    $qty   = (int)$r['total_remaining'];
                    $exp   = $r['earliest_exp'];
                    // Combine name + label (dosage) exactly like dashboard displays
                    $full_name = htmlspecialchars(trim($r['name'] . ($r['label'] ? ' ' . $r['label'] : '')));

                    // Qty badge
                    if ($qty === 0)     $qcls = 'qty-zero';
                    elseif ($qty <= 5)  $qcls = 'qty-low';
                    else                $qcls = 'qty-ok';

                    // Expiry
                    if (!empty($exp) && $exp !== '0000-00-00') {
                        $ts  = strtotime($exp);
                        $fmt = date('F d, Y', $ts);
                        if ($ts < strtotime($today)) {
                            $ecls = 'exp-past'; $exp_str = '⚠️ ' . $fmt . ' (Expired)';
                        } elseif ($ts <= strtotime($soon)) {
                            $ecls = 'exp-soon'; $exp_str = '⏳ ' . $fmt . ' (Expiring Soon)';
                        } else {
                            $ecls = 'exp-ok'; $exp_str = $fmt;
                        }
                    } else {
                        $ecls = ''; $exp_str = 'N/A';
                    }
                ?>
                <tr>
                    <td style="color:#aaa; font-size:12px;"><?php echo $i + 1; ?></td>
                    <td><strong><?php echo $full_name; ?></strong></td>
                    <td class="<?php echo $ecls; ?>"><?php echo $exp_str; ?></td>
                    <td><span class="qty-badge <?php echo $qcls; ?>"><?php echo $qty; ?> unit(s)</span></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="no-data">
                        <div class="icon">📭</div>
                        <div>No medicines found</div>
                        <small style="margin-top:8px; display:block;">All medicines may be archived or none exist yet.</small>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

        <div class="foot-bar">
            <span>📦 Total Items: <strong><?php echo $total_items; ?></strong></span>
            <span>🔢 Total Units: <strong><?php echo number_format($total_units); ?></strong></span>
            <span>⚠️ Low Stock (≤5): <strong style="color:hsl(30,80%,35%)"><?php echo $low_count; ?></strong></span>
            <span style="margin-left:auto; font-size:12px; color:var(--color-text-muted);">
                Generated: <?php echo date('F d, Y h:i A'); ?>
            </span>
        </div>
    </div>

</div>
</body>
</html>
