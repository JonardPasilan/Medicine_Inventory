<?php
require_once __DIR__ . '/db.php';

// Log Cleanup Logic
if (isset($_POST['delete_all_logs'])) {
    $conn->query("DELETE FROM logs");
    $deleted_count = $conn->affected_rows;
    header("Location: logs.php?cleaned=$deleted_count");
    exit();
}

if (isset($_POST['delete_selected_logs']) && !empty($_POST['log_ids'])) {
    $ids = array_map('intval', $_POST['log_ids']);
    if (count($ids) > 0) {
        $ids_str = implode(',', $ids);
        $conn->query("DELETE FROM logs WHERE id IN ($ids_str)");
        $deleted_count = $conn->affected_rows;
        header("Location: logs.php?deleted_selected=$deleted_count");
        exit();
    }
}

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $where_clause = "1=1";
    if (!empty($_GET['item_type'])) {
        $ftype = mysqli_real_escape_string($conn, $_GET['item_type']);
        $where_clause .= " AND m.type = '$ftype'";
    }
    if (!empty($_GET['action'])) {
        $faction = mysqli_real_escape_string($conn, $_GET['action']);
        $where_clause .= " AND l.action = '$faction'";
    }
    if (!empty($_GET['date_from'])) {
        $dfrom = mysqli_real_escape_string($conn, $_GET['date_from']);
        $where_clause .= " AND DATE(l.date) >= '$dfrom'";
    }
    if (!empty($_GET['date_to'])) {
        $dto = mysqli_real_escape_string($conn, $_GET['date_to']);
        $where_clause .= " AND DATE(l.date) <= '$dto'";
    }

    $query = "
        SELECT l.id, l.quantity, l.action, l.patient_name, l.prescriber_name, l.staff_name,
               l.edited_by, l.edited_at,
               DATE_FORMAT(l.date, '%M %d, %Y %H:%i') AS fmt_date,
               l.medicine_id, m.name, m.label, m.batch_number, m.expiration_date, m.type, m.date_acquired
        FROM logs l
        LEFT JOIN medicines m ON l.medicine_id = m.id
        WHERE $where_clause
        ORDER BY l.id DESC";

    $r = $conn->query($query);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=inventory_logs_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['#', 'Item Details', 'Expiry / Procured Date', 'Qty', 'Action', 'Details', 'Date', 'Edited By', 'Edited At']);

    if ($r && $r->num_rows > 0) {
        $row_num = 0;
        while ($row = $r->fetch_assoc()) {
            $row_num++;
            $mname = $row['name'] ? $row['name'] : 'Deleted Medicine';
            $mlabel = $row['label'] ? " ({$row['label']})" : '';
            $batch_label = ($row['type'] == 'dental' || $row['type'] == 'medical') ? "Equipment" : "Batch #{$row['batch_number']}";
            $item_details = $mname . $mlabel . " - " . $batch_label . " (ID: {$row['medicine_id']})";

            $bexp_disp = 'N/A';
            if ($row['type'] == 'dental' || $row['type'] == 'medical') {
                if (!empty($row['date_acquired']) && $row['date_acquired'] != '0000-00-00') {
                    $bexp_disp = date('M d, Y', strtotime($row['date_acquired']));
                }
            } else {
                if (!empty($row['expiration_date']) && $row['expiration_date'] != '0000-00-00') {
                    $bexp_disp = date('M d, Y', strtotime($row['expiration_date']));
                }
            }

            $details_arr = [];
            if (!empty($row['patient_name']))    $details_arr[] = "Patient: " . $row['patient_name'];
            if (!empty($row['prescriber_name'])) $details_arr[] = "Dr: "      . $row['prescriber_name'];
            if (!empty($row['staff_name']))      $details_arr[] = "Staff: "   . $row['staff_name'];
            $details_str = empty($details_arr) ? '—' : implode(' | ', $details_arr);

            fputcsv($output, [
                $row_num, $item_details, $bexp_disp,
                $row['quantity'] . " unit(s)", $row['action'], $details_str,
                $row['fmt_date'],
                $row['edited_by'] ?? '',
                $row['edited_at'] ?? '',
            ]);
        }
    }
    fclose($output);
    exit();
}

require_once __DIR__ . '/header.php';

/* ── Build medicine list for Edit modal ── */
$edit_meds_query = $conn->query("
    SELECT name, SUM(CASE WHEN expiration_date >= CURDATE() OR expiration_date IS NULL THEN quantity ELSE 0 END) AS avail_qty
    FROM medicines WHERE quantity > 0 AND is_archived = 0 AND type IN ('medicine','consumable')
    GROUP BY name ORDER BY name ASC");
$edit_med_list = [];
while ($em = $edit_meds_query->fetch_assoc()) {
    $edit_med_list[] = ['name' => $em['name'], 'avail' => (int)$em['avail_qty']];
}
?>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', sans-serif; background: var(--color-canvas); min-height: 100vh; }
    .container { max-width: 95%; margin: 40px auto; padding: 0 20px; }

    .header-card {
        background: var(--color-surface); border-radius: var(--radius-lg); padding: 30px;
        margin-bottom: 30px; box-shadow: var(--shadow-md);
        border: 1px solid var(--color-border); animation: fadeIn 0.5s ease;
    }
    @keyframes fadeIn { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
    .header-content { text-align: center; }
    .header-content .icon { font-size: 50px; margin-bottom: 10px; }
    .header-content h2 { color: var(--color-text-primary); font-size: 28px; margin-bottom: 10px; }
    .header-content p  { color: var(--color-text-secondary); font-size: 14px; }

    .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card {
        background: var(--color-surface); padding: 20px;
        border-radius: var(--radius-md); text-align: center;
        box-shadow: var(--shadow-sm); border: 1px solid var(--color-border);
        transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
        cursor: pointer; text-decoration: none; display: block; color: inherit;
    }
    .stat-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-md); border-color: var(--color-brand); }
    .stat-card.active-card { border-color: var(--color-brand); background: var(--color-brand-light); }
    .stat-card h3 { color: var(--color-text-secondary); font-size: 14px; margin-bottom: 10px; }
    .stat-card .number { font-size: 32px; font-weight: bold; color: var(--color-text-primary); }

    .filter-section {
        background: var(--color-surface); border-radius: var(--radius-md); padding: 20px;
        margin-bottom: 30px; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border);
    }
    .filter-form { display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; }
    .filter-group { flex: 1; min-width: 150px; }
    .filter-group label { display: block; margin-bottom: 8px; color: var(--color-text-primary); font-weight: 500; font-size: 13px; }
    .filter-group select, .filter-group input {
        width: 100%; padding: 10px; border: 2px solid var(--color-border); border-radius: var(--radius-sm);
        background: var(--color-overlay); color: var(--color-text-primary); font-size: 14px; transition: all 0.3s ease;
    }
    .filter-group select:focus, .filter-group input:focus {
        outline: none; border-color: var(--color-brand); box-shadow: 0 0 0 3px var(--color-brand-light);
    }
    .filter-buttons { display: flex; gap: 10px; }
    .btn-filter {
        padding: 10px 25px; background: var(--color-brand); color: white;
        border: none; border-radius: var(--radius-sm); cursor: pointer;
        font-size: 14px; font-weight: 500; font-family: 'Inter', sans-serif; transition: all 0.3s ease;
    }
    .btn-filter:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(90,72,220,0.35); }
    .btn-reset {
        padding: 10px 25px; background: var(--color-overlay); color: var(--color-text-secondary);
        border: 1px solid var(--color-border); border-radius: var(--radius-sm);
        cursor: pointer; font-size: 14px; font-weight: 500; font-family: 'Inter', sans-serif;
        transition: all 0.3s ease; text-decoration: none; display: inline-block; text-align: center;
    }
    .btn-reset:hover { background: var(--color-border); color: var(--color-text-primary); transform: translateY(-2px); }

    .table-container {
        background: var(--color-surface); border-radius: var(--radius-md);
        overflow-x: auto; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border);
    }
    table { width: 100%; border-collapse: collapse; }
    th {
        background: var(--color-brand); color: white;
        padding: 14px 12px; text-align: left; font-weight: 600; font-size: 13px;
    }
    td { padding: 11px 12px; border-bottom: 1px solid var(--color-border); color: var(--color-text-primary); }
    tr:hover { background: var(--color-overlay); }

    .medicine-name { font-weight: 600; color: var(--color-text-primary); }
    .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    .badge-dispense  { background: hsl(140,60%,94%); color: hsl(140,60%,28%); }
    .badge-new-batch { background: hsl(210,80%,94%); color: hsl(210,70%,35%); }
    .badge-edited    { background: hsl(270,60%,94%); color: hsl(270,60%,35%); }
    .badge-other     { background: hsl(40,80%,94%);  color: hsl(40,70%,35%); }

    .qty-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-weight: 600; font-size: 13px; }
    .qty-normal { background: hsl(210,80%,94%); color: hsl(210,70%,35%); }
    .qty-low    { background: hsl(30,90%,94%);  color: hsl(30,80%,35%); }

    .date-cell { font-family: monospace; font-size: 12px; color: var(--color-text-muted); }
    .batch-info { font-size: 11px; color: var(--color-text-muted); margin-top: 2px; }
    .no-data { text-align: center; padding: 60px; color: var(--color-text-muted); }
    .no-data .icon { font-size: 48px; margin-bottom: 15px; }

    .export-section { margin-bottom: 20px; text-align: right; }
    .btn-export {
        padding: 10px 20px; background: hsl(140,60%,40%); color: white;
        border: none; border-radius: var(--radius-sm); cursor: pointer;
        font-size: 14px; font-weight: 500; font-family: 'Inter', sans-serif; transition: all 0.3s ease;
    }
    .btn-export:hover { background: hsl(140,60%,33%); transform: translateY(-2px); }
    .btn-remaining {
        padding: 10px 20px; background: hsl(210,70%,40%); color: white;
        border: none; border-radius: var(--radius-sm); cursor: pointer;
        font-size: 14px; font-weight: 500; font-family: 'Inter', sans-serif;
        text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.3s ease;
    }
    .btn-remaining:hover { background: hsl(210,70%,33%); transform: translateY(-2px); }
    .btn-clear-logs {
        padding: 10px 20px; background: transparent; color: hsl(0,70%,50%);
        border: 1px solid hsl(0,70%,50%); border-radius: var(--radius-sm);
        cursor: pointer; font-size: 14px; font-weight: 500; font-family: 'Inter', sans-serif; transition: all 0.3s ease;
    }
    .btn-clear-logs:hover { background: hsl(0,70%,50%); color: white; transform: translateY(-2px); }
    .btn-delete-selected {
        padding: 10px 20px; background: transparent; color: #e67e22;
        border: 1px solid #e67e22; border-radius: var(--radius-sm);
        cursor: pointer; font-size: 14px; font-weight: 500; font-family: 'Inter', sans-serif; transition: all 0.3s ease;
    }
    .btn-delete-selected:hover { background: #e67e22; color: white; transform: translateY(-2px); }

    /* Edit button */
    .btn-edit-log {
        padding: 5px 12px; font-size: 12px; font-weight: 600; border: none;
        background: hsl(210,80%,94%); color: hsl(210,70%,35%);
        border-radius: var(--radius-sm); cursor: pointer; font-family: inherit;
        transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 4px;
    }
    .btn-edit-log:hover { background: var(--color-brand); color: white; transform: translateY(-1px); }

    /* Edited badge */
    .edited-tag {
        display: inline-block; font-size: 10px; font-weight: 700;
        background: hsl(270,60%,94%); color: hsl(270,60%,35%);
        border-radius: 99px; padding: 2px 7px; margin-left: 4px; vertical-align: middle;
    }

    /* ── Edit Modal ── */
    #editLogModal {
        position: fixed; inset: 0; background: rgba(15,23,42,0.65);
        display: none; align-items: center; justify-content: center;
        z-index: 10050; backdrop-filter: blur(4px);
        opacity: 0; transition: opacity 0.2s ease;
    }
    #editLogModal.show { display: flex; opacity: 1; }
    .edit-modal-card {
        background: var(--color-surface); border-radius: var(--radius-lg);
        padding: 30px; width: 94%; max-width: 560px; max-height: 90vh; overflow-y: auto;
        box-shadow: var(--shadow-lg); border: 1px solid var(--color-border);
        transform: translateY(20px); transition: transform 0.25s ease;
        animation: modalSlideIn 0.3s ease forwards;
    }
    @keyframes modalSlideIn { from { opacity:0; transform:translateY(30px); } to { opacity:1; transform:translateY(0); } }
    .edit-modal-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid var(--color-border);
    }
    .edit-modal-header h3 { font-size: 18px; color: var(--color-text-primary); display: flex; align-items: center; gap: 8px; }
    .edit-modal-close {
        background: none; border: none; font-size: 22px; cursor: pointer;
        color: var(--color-text-muted); line-height: 1; padding: 2px 6px; border-radius: 4px;
        transition: background 0.2s ease;
    }
    .edit-modal-close:hover { background: var(--color-overlay); }
    .edit-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    @media (max-width: 540px) { .edit-grid { grid-template-columns: 1fr; } }
    .edit-form-group { margin-bottom: 0; }
    .edit-form-group.full { grid-column: 1 / -1; }
    .edit-form-group label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: var(--color-text-secondary); }
    .edit-form-group input, .edit-form-group select {
        width: 100%; padding: 10px 12px; border: 2px solid var(--color-border);
        border-radius: var(--radius-sm); font-size: 14px; font-family: inherit;
        background: var(--color-overlay); color: var(--color-text-primary); transition: all 0.2s ease;
    }
    .edit-form-group input:focus, .edit-form-group select:focus {
        outline: none; border-color: var(--color-brand); box-shadow: 0 0 0 3px var(--color-brand-light);
    }
    .edit-modal-footer { margin-top: 22px; display: flex; gap: 12px; justify-content: flex-end; }
    .btn-edit-save {
        padding: 11px 24px; background: var(--color-brand); color: white;
        border: none; border-radius: var(--radius-md); font-weight: 600;
        font-size: 14px; cursor: pointer; font-family: inherit; transition: all 0.2s ease;
    }
    .btn-edit-save:hover { background: var(--color-brand-dark); transform: translateY(-1px); }
    .btn-edit-cancel {
        padding: 11px 24px; background: var(--color-overlay); color: var(--color-text-secondary);
        border: 1px solid var(--color-border); border-radius: var(--radius-md); font-weight: 600;
        font-size: 14px; cursor: pointer; font-family: inherit; transition: all 0.2s ease;
    }
    .btn-edit-cancel:hover { background: var(--color-border); }

    /* Non-editable note */
    .non-edit-note {
        background: hsl(40,80%,94%); border-left: 3px solid hsl(40,80%,50%);
        border-radius: var(--radius-sm); padding: 10px 13px;
        font-size: 12px; color: hsl(40,70%,35%); margin-bottom: 16px;
    }

    /* Searchable dropdown for edit modal */
    .edit-med-wrap { position: relative; }
    .edit-med-drop {
        display: none; position: absolute; top: calc(100% + 3px); left: 0; right: 0;
        background: var(--color-surface); border: 2px solid var(--color-brand);
        border-radius: var(--radius-sm); z-index: 10100;
        max-height: 200px; overflow-y: auto; box-shadow: var(--shadow-lg);
    }
    .edit-med-wrap.open .edit-med-drop { display: block; }
    .edit-med-opt {
        padding: 9px 13px; cursor: pointer; font-size: 13px;
        border-bottom: 1px solid var(--color-border); color: var(--color-text-primary);
        display: flex; justify-content: space-between; transition: background 0.15s;
    }
    .edit-med-opt:last-child { border-bottom: none; }
    .edit-med-opt:hover { background: var(--color-brand-light); }

    .inv-note { font-size: 11px; color: var(--color-text-muted); margin-top: 4px; display: none; }

    @media (max-width: 768px) {
        .container { margin: 20px auto; }
        .filter-group { min-width: 100%; }
        .filter-buttons { width: 100%; }
        .btn-filter, .btn-reset { flex: 1; }
        th, td { padding: 10px 8px; font-size: 12px; }
    }
</style>

<div class="container">

    <div class="header-card">
        <div class="header-content">
            <div class="icon">📋</div>
            <h2>Inventory Activity Logs</h2>
            <p>Dispense records, batch additions, and item updates</p>
        </div>
    </div>

    <?php
    $total_dispensed    = $conn->query("SELECT SUM(quantity) AS t FROM logs WHERE action = 'Released to patient'")->fetch_assoc()['t'];
    $total_transactions = $conn->query("SELECT COUNT(*) AS t FROM logs")->fetch_assoc()['t'];
    $today_dispensed    = $conn->query("SELECT SUM(quantity) AS t FROM logs WHERE action = 'Released to patient' AND DATE(date) = CURDATE()")->fetch_assoc()['t'];
    $batches_added      = $conn->query("SELECT COUNT(*) AS t FROM logs WHERE action = 'New Batch Added'")->fetch_assoc()['t'];

    $cur_filter_action = isset($_GET['action']) ? $_GET['action'] : '';
    $cur_filter_from   = isset($_GET['date_from']) ? $_GET['date_from'] : '';
    $today_str = date('Y-m-d');
    ?>

    <div class="stats">
        <?php $c1_active = ($cur_filter_action === 'Released to patient' && $cur_filter_from === '') ? 'active-card' : ''; ?>
        <a href="logs.php?action=Released+to+patient&filter=1" class="stat-card <?php echo $c1_active; ?>">
            <h3>Total Units Dispensed</h3>
            <div class="number"><?php echo (int)$total_dispensed; ?></div>
        </a>
        <a href="logs.php" class="stat-card">
            <h3>Total Transactions</h3>
            <div class="number"><?php echo (int)$total_transactions; ?></div>
        </a>
        <?php $c3_active = ($cur_filter_action === 'Released to patient' && $cur_filter_from === $today_str) ? 'active-card' : ''; ?>
        <a href="logs.php?action=Released+to+patient&date_from=<?php echo $today_str; ?>&date_to=<?php echo $today_str; ?>&filter=1" class="stat-card <?php echo $c3_active; ?>">
            <h3>Today's Dispensed</h3>
            <div class="number" style="color:#27ae60;"><?php echo (int)$today_dispensed; ?></div>
        </a>
        <?php $c4_active = ($cur_filter_action === 'New Batch Added') ? 'active-card' : ''; ?>
        <a href="logs.php?action=New+Batch+Added&filter=1" class="stat-card <?php echo $c4_active; ?>">
            <h3>New Batches Added</h3>
            <div class="number" style="color:#1f4f87;"><?php echo (int)$batches_added; ?></div>
        </a>
    </div>

    <div class="filter-section">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label>🔍 Filter by Type</label>
                <select name="item_type">
                    <option value="">All Types</option>
                    <option value="medicine"   <?php echo (isset($_GET['item_type']) && $_GET['item_type'] === 'medicine')   ? 'selected' : ''; ?>>Medicines</option>
                    <option value="consumable" <?php echo (isset($_GET['item_type']) && $_GET['item_type'] === 'consumable') ? 'selected' : ''; ?>>Consumable Supplies</option>
                    <option value="dental"     <?php echo (isset($_GET['item_type']) && $_GET['item_type'] === 'dental')     ? 'selected' : ''; ?>>Dental Device &amp; Equipment</option>
                    <option value="medical"    <?php echo (isset($_GET['item_type']) && $_GET['item_type'] === 'medical')    ? 'selected' : ''; ?>>Medical Device &amp; Equipment</option>
                </select>
            </div>
            <div class="filter-group">
                <label>🏷️ Filter by Action</label>
                <select name="action">
                    <option value="">All Actions</option>
                    <?php
                    $cur_action = isset($_GET['action']) ? $_GET['action'] : '';
                    $actions = ['Released to patient', 'New Batch Added', 'Item Updated', 'Log Edited'];
                    foreach ($actions as $a) {
                        $sel = ($cur_action === $a) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($a, ENT_QUOTES) . "' $sel>" . htmlspecialchars($a) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="filter-group">
                <label>📅 From Date</label>
                <input type="date" name="date_from" value="<?php echo isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : ''; ?>">
            </div>
            <div class="filter-group">
                <label>📅 To Date</label>
                <input type="date" name="date_to"   value="<?php echo isset($_GET['date_to'])   ? htmlspecialchars($_GET['date_to'])   : ''; ?>">
            </div>
            <div class="filter-buttons" style="width:100%; display:flex; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <button type="submit" name="filter" value="1" class="btn-filter">Apply Filter</button>
                    <a href="logs.php" class="btn-reset" style="text-decoration:none; display:inline-block; text-align:center;">Reset</a>
                    <a href="remaining_medicines.php" class="btn-remaining" style="text-decoration:none;">💊 Remaining Medicines</a>
                </div>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <button type="submit" name="export" value="csv" class="btn-export">📊 Export to CSV</button>
                    <button type="button" onclick="confirmDeleteSelected()" class="btn-delete-selected" id="btnDeleteSelected" style="display:none;">🗑️ Delete Selected</button>
                    <button type="button" onclick="confirmLogCleanup()" class="btn-clear-logs">🗑️ Delete All Logs</button>
                </div>
            </div>
        </form>

        <?php if (isset($_GET['cleaned'])): ?>
            <div style="background:var(--color-brand-light); color:var(--color-brand); padding:12px; border-radius:8px; margin-top:15px; font-size:14px; text-align:center; border:1px solid var(--color-brand);">
                ✨ Successfully deleted <strong><?php echo (int)$_GET['cleaned']; ?></strong> log entries.
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted_selected'])): ?>
            <div style="background:var(--color-brand-light); color:var(--color-brand); padding:12px; border-radius:8px; margin-top:15px; font-size:14px; text-align:center; border:1px solid var(--color-brand);">
                ✨ Successfully deleted <strong><?php echo (int)$_GET['deleted_selected']; ?></strong> selected log entries.
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['edited'])): ?>
            <div style="background:#d5f4e6; color:#1a7a4a; padding:12px; border-radius:8px; margin-top:15px; font-size:14px; text-align:center; border:1px solid #1a7a4a;">
                ✅ Log entry #<?php echo (int)($_GET['log_id'] ?? 0); ?> was successfully updated. Inventory has been adjusted.
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['edit_error'])): ?>
            <?php
            $err_msgs = [
                'invalid'     => 'Invalid data submitted. Please try again.',
                'notfound'    => 'Log entry not found.',
                'insufficient'=> 'Insufficient stock for the new medicine/quantity selected. Available: ' . (int)($_GET['avail'] ?? 0) . ', needed: ' . (int)($_GET['need'] ?? 0) . '.',
                'exception'   => 'A database error occurred. Please try again.',
            ];
            $err_msg = $err_msgs[$_GET['edit_error']] ?? 'An error occurred.';
            ?>
            <div style="background:#ffeaea; color:#c0392b; padding:12px; border-radius:8px; margin-top:15px; font-size:14px; text-align:center; border:1px solid #e74c3c;">
                ❌ <?php echo htmlspecialchars($err_msg); ?>
            </div>
        <?php endif; ?>
    </div>

    <form method="POST" id="deleteForm">
        <input type="hidden" name="delete_selected_logs" value="1">
        <div class="table-container">
            <table id="logTable">
                <thead>
                    <tr>
                        <th style="width:40px; text-align:center;"><input type="checkbox" id="selectAll" onclick="toggleSelectAll()"></th>
                        <th>#</th>
                        <th>Item Details</th>
                        <th>Expiry / Procured Date</th>
                        <th>Qty</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>Date</th>
                        <th style="text-align:center;">Edit</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $limit  = 50;
                $page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $page   = max($page, 1);
                $offset = ($page - 1) * $limit;

                $where_clause = "1=1";
                if (!empty($_GET['item_type'])) {
                    $ftype = mysqli_real_escape_string($conn, $_GET['item_type']);
                    $where_clause .= " AND m.type = '$ftype'";
                }
                if (!empty($_GET['action'])) {
                    $faction = mysqli_real_escape_string($conn, $_GET['action']);
                    $where_clause .= " AND l.action = '$faction'";
                }
                if (!empty($_GET['date_from'])) {
                    $dfrom = mysqli_real_escape_string($conn, $_GET['date_from']);
                    $where_clause .= " AND DATE(l.date) >= '$dfrom'";
                }
                if (!empty($_GET['date_to'])) {
                    $dto = mysqli_real_escape_string($conn, $_GET['date_to']);
                    $where_clause .= " AND DATE(l.date) <= '$dto'";
                }

                $count_query    = "SELECT COUNT(*) as total FROM logs l LEFT JOIN medicines m ON l.medicine_id = m.id WHERE $where_clause";
                $total_records  = $conn->query($count_query)->fetch_assoc()['total'];
                $total_pages    = ceil($total_records / $limit);

                $query = "
                    SELECT l.id, l.quantity, l.action, l.patient_name, l.prescriber_name, l.staff_name,
                           l.edited_by, l.edited_at,
                           DATE_FORMAT(l.date, '%M %d, %Y') AS fmt_date,
                           DATE_FORMAT(l.date, '%Y-%m-%d') AS iso_date,
                           l.medicine_id, m.name, m.label, m.batch_number, m.expiration_date, m.type, m.date_acquired
                    FROM logs l
                    LEFT JOIN medicines m ON l.medicine_id = m.id
                    WHERE $where_clause
                    ORDER BY l.id DESC
                    LIMIT $limit OFFSET $offset";

                $r = $conn->query($query);

                if ($r && $r->num_rows > 0) {
                    $row_num = $offset;
                    while ($row = $r->fetch_assoc()) {
                        $row_num++;
                        $qty = (float)$row['quantity'];

                        if ($row['name']) {
                            $mname      = htmlspecialchars($row['name'],  ENT_QUOTES, 'UTF-8');
                            $mlabel     = htmlspecialchars((string)$row['label'], ENT_QUOTES, 'UTF-8');
                            $type       = $row['type'];
                            $batch_label = ($type == 'dental' || $type == 'medical') ? "Equipment" : "Batch #{$row['batch_number']}";
                            $med_display = "<div class='medicine-name'>{$mname}</div>
                                           <small style='color:#7f8c8d;'>{$mlabel}</small>
                                           <div class='batch-info'>{$batch_label} (ID: {$row['medicine_id']})</div>";
                        } else {
                            $med_display = "<div class='medicine-name' style='color:#95a5a6;'>Deleted Medicine</div>
                                           <div class='batch-info'>Item (ID: {$row['medicine_id']})</div>";
                            $mname = '';
                        }

                        // Expiry / Acquired date
                        $bexp  = $row['expiration_date'];
                        $acq   = $row['date_acquired'];
                        $type  = $row['type'];
                        if ($type == 'dental' || $type == 'medical') {
                            $bexp_disp = (!empty($acq) && $acq != '0000-00-00')
                                ? "<span style='color:#1f4f87; font-size:13px;'>" . date('M d, Y', strtotime($acq)) . "</span>"
                                : "<span style='color:#95a5a6;'>N/A</span>";
                        } else {
                            if ($bexp && $bexp != '0000-00-00') {
                                $today_s  = date('Y-m-d');
                                $bexp_clr = (strtotime($bexp) < strtotime($today_s)) ? '#e74c3c' : '#27ae60';
                                $bexp_disp = "<span style='color:{$bexp_clr}; font-size:13px;'>" . date('M d, Y', strtotime($bexp)) . "</span>";
                            } else {
                                $bexp_disp = "<span style='color:#95a5a6;'>N/A</span>";
                            }
                        }

                        // Action badge
                        $act = htmlspecialchars((string)$row['action'], ENT_QUOTES, 'UTF-8');
                        if ($row['action'] === 'Released to patient')  { $badge_class = 'badge-dispense';  $badge_icon = '💊'; }
                        elseif ($row['action'] === 'New Batch Added')  { $badge_class = 'badge-new-batch'; $badge_icon = '📦'; }
                        elseif ($row['action'] === 'Log Edited')       { $badge_class = 'badge-edited';    $badge_icon = '✏️'; }
                        elseif ($row['action'] === 'Item Updated')     { $badge_class = 'badge-other';     $badge_icon = '🔄'; }
                        else                                           { $badge_class = 'badge-other';     $badge_icon = '📝'; }

                        $qty_class = $qty <= 5 ? 'qty-low' : 'qty-normal';
                        $qty_disp  = number_format($qty, 2, '.', '') + 0; // remove trailing zeros
                        $fmt_date  = htmlspecialchars((string)$row['fmt_date'], ENT_QUOTES, 'UTF-8');

                        // Details
                        $p_name = !empty($row['patient_name'])    ? htmlspecialchars($row['patient_name'])    : 'N/A';
                        $d_name = !empty($row['prescriber_name']) ? htmlspecialchars($row['prescriber_name']) : 'N/A';
                        $s_name = !empty($row['staff_name'])      ? htmlspecialchars($row['staff_name'])      : 'N/A';

                        if ($row['action'] === 'Released to patient') {
                            $details_html = "<div style='font-size:12px; line-height:1.6;'>";
                            if ($p_name !== 'N/A') $details_html .= "<div><strong>Patient:</strong> {$p_name}</div>";
                            if ($d_name !== 'N/A') $details_html .= "<div><strong>Dr.:</strong> {$d_name}</div>";
                            if ($s_name !== 'N/A') $details_html .= "<div><strong>Staff:</strong> <span style='color:#2980b9'>{$s_name}</span></div>";
                            $details_html .= "</div>";
                            if ($details_html === "<div style='font-size:12px; line-height:1.6;'></div>") {
                                $details_html = "<span style='color:#95a5a6;'>—</span>";
                            }
                        } elseif ($row['action'] === 'Log Edited') {
                            $details_html = "<div style='font-size:12px; line-height:1.6;'>";
                            if ($s_name !== 'N/A') $details_html .= "<div><strong>Edited by:</strong> <span style='color:#9b59b6'>{$s_name}</span></div>";
                            $details_html .= "</div>";
                        } elseif ($s_name !== 'N/A') {
                            $details_html = "<div style='font-size:12px; line-height:1.6;'><div><strong>Staff:</strong> <span style='color:#2980b9'>{$s_name}</span></div></div>";
                        } else {
                            $details_html = "<span style='color:#95a5a6;'>—</span>";
                        }

                        // Edited indicator
                        $edited_tag = (!empty($row['edited_by']) && $row['action'] !== 'Log Edited')
                            ? "<span class='edited-tag' title='Edited by {$row['edited_by']} on {$row['edited_at']}'>✏️ edited</span>"
                            : '';

                        // Prepare JSON encoded params for the JS function
                        $js_name       = htmlspecialchars(json_encode($row['name'] ?? ''), ENT_QUOTES, 'UTF-8');
                        $js_patient    = htmlspecialchars(json_encode($row['patient_name'] ?? ''), ENT_QUOTES, 'UTF-8');
                        $js_prescriber = htmlspecialchars(json_encode($row['prescriber_name'] ?? ''), ENT_QUOTES, 'UTF-8');
                        $js_staff      = htmlspecialchars(json_encode($row['staff_name'] ?? ''), ENT_QUOTES, 'UTF-8');
                        $js_date       = htmlspecialchars(json_encode($row['iso_date'] ?? ''), ENT_QUOTES, 'UTF-8');

                        // Edit button — only for dispense logs
                        $can_edit = ($row['action'] === 'Released to patient');
                        $edit_btn = $can_edit
                            ? "<button type='button' class='btn-edit-log' onclick='openEditModal({$row['id']}, {$js_name}, {$qty_disp}, {$js_patient}, {$js_prescriber}, {$js_staff}, {$js_date})'>✏️ Edit</button>"
                            : "<span style='color:var(--color-text-muted); font-size:12px;'>—</span>";

                        $log_id = $row['id'];
                        echo "<tr>
                            <td style='text-align:center;'><input type='checkbox' name='log_ids[]' value='{$log_id}' class='log-checkbox' onclick='updateDeleteBtn()'></td>
                            <td style='color:#aaa; font-size:12px;'>{$row_num}</td>
                            <td>{$med_display}</td>
                            <td>{$bexp_disp}</td>
                            <td><span class='qty-badge {$qty_class}'>{$qty_disp} unit(s)</span></td>
                            <td><span class='badge {$badge_class}'>{$badge_icon} {$act}</span>{$edited_tag}</td>
                            <td>{$details_html}</td>
                            <td class='date-cell'>📅 {$fmt_date}</td>
                            <td style='text-align:center;'>{$edit_btn}</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='9' class='no-data'>
                             <div class='icon'>📭</div>
                             <div>No log records found</div>
                             <small style='margin-top:10px; display:block;'>Try adjusting your filters</small>
                          </td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </form>

    <?php if ($total_pages > 1): ?>
        <div style="margin-top:20px; text-align:center;">
            <?php
            $params = $_GET;
            for ($i = 1; $i <= $total_pages; $i++) {
                $params['page'] = $i;
                $qs = http_build_query($params);
                $active = ($i == $page) ? 'background:#1f4f87; color:white;' : 'background:white; color:#1f4f87; border:1px solid #1f4f87;';
                echo "<a href='logs.php?{$qs}' style='display:inline-block; padding:8px 12px; margin:0 4px; border-radius:5px; text-decoration:none; font-size:14px; transition:0.3s; {$active}'>{$i}</a>";
            }
            ?>
        </div>
    <?php endif; ?>

</div>

<!-- ═══════════════════════════════════════════════════════════════ -->
<!-- Edit Log Modal                                                   -->
<!-- ═══════════════════════════════════════════════════════════════ -->
<div id="editLogModal">
    <div class="edit-modal-card">
        <div class="edit-modal-header">
            <h3>✏️ Edit Log Entry <span id="editLogIdLabel" style="font-size:14px; color:var(--color-text-muted);"></span></h3>
            <button class="edit-modal-close" onclick="closeEditModal()" title="Close">✕</button>
        </div>

        <div class="non-edit-note">
            ⚠️ Editing the <strong>Medicine</strong> or <strong>Quantity</strong> will automatically adjust inventory stock levels. An audit record will be saved.
        </div>

        <form method="POST" action="edit_log.php" id="editLogForm" onsubmit="return confirmEdit(event)">
            <input type="hidden" name="log_id" id="editLogId">

            <div class="edit-grid">
                <!-- Medicine -->
                <div class="edit-form-group full">
                    <label>Medicine <span style="color:#e74c3c;">*</span></label>
                    <input type="hidden" name="new_med_name" id="editMedHidden">
                    <div class="edit-med-wrap" id="editMedWrap">
                        <input type="text" id="editMedSearch" class="edit-form-group input"
                               style="width:100%; padding:10px 12px; border:2px solid var(--color-border); border-radius:var(--radius-sm); font-size:14px; font-family:inherit; background:var(--color-overlay); color:var(--color-text-primary);"
                               placeholder="🔍 Search medicine..." autocomplete="off"
                               oninput="filterEditMeds()" onclick="openEditMedDrop()">
                        <div class="edit-med-drop" id="editMedDrop"></div>
                    </div>
                    <div class="inv-note" id="editInvNote"></div>
                </div>

                <!-- Quantity -->
                <div class="edit-form-group">
                    <label>Quantity <span style="color:#e74c3c;">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="new_qty" id="editQty" placeholder="Enter quantity" required>
                </div>

                <!-- Patient -->
                <div class="edit-form-group">
                    <label>Patient Name</label>
                    <input type="text" name="new_patient" id="editPatient" placeholder="Patient name">
                </div>

                <!-- Prescriber -->
                <div class="edit-form-group">
                    <label>Prescriber (Doctor)</label>
                    <input type="text" name="new_prescrib" id="editPrescrib" placeholder="Dr. Name">
                </div>

                <!-- Staff -->
                <div class="edit-form-group">
                    <label>Dispensed By (Staff)</label>
                    <input type="text" name="new_staff" id="editStaff" placeholder="Staff name">
                </div>

                <!-- Date -->
                <div class="edit-form-group">
                    <label>Date</label>
                    <input type="date" name="new_date" id="editDate"
                           max="<?php echo date('Y-m-d'); ?>">
                </div>

                <!-- Edited by -->
                <div class="edit-form-group full">
                    <label>Your Name (Editing Staff) <span style="color:#e74c3c;">*</span></label>
                    <input type="text" name="edited_by" id="editEditorName" placeholder="Who is making this edit?" required>
                </div>
            </div>

            <div class="edit-modal-footer">
                <button type="button" class="btn-edit-cancel" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-edit-save">💾 Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
/* ── Medicine data for edit modal ── */
const EDIT_MED_DATA = <?php echo json_encode($edit_med_list); ?>;
let editSelectedMed = null;

function openEditModal(logId, medName, qty, patient, prescrib, staff, isoDate) {
    document.getElementById('editLogId').value          = logId;
    document.getElementById('editLogIdLabel').textContent = '(#' + logId + ')';
    document.getElementById('editMedHidden').value       = medName;
    document.getElementById('editMedSearch').value       = medName;
    document.getElementById('editQty').value             = qty;
    document.getElementById('editPatient').value         = patient || '';
    document.getElementById('editPrescrib').value        = prescrib || '';
    document.getElementById('editStaff').value           = staff || '';
    document.getElementById('editDate').value            = isoDate || '';
    document.getElementById('editEditorName').value      = '';

    // Find med stock info
    editSelectedMed = EDIT_MED_DATA.find(m => m.name === medName) || null;
    updateEditInvNote();

    const modal = document.getElementById('editLogModal');
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('show'), 10);
}

function closeEditModal() {
    const modal = document.getElementById('editLogModal');
    modal.classList.remove('show');
    setTimeout(() => { modal.style.display = 'none'; }, 250);
    document.getElementById('editMedWrap').classList.remove('open');
}

function updateEditInvNote() {
    const note = document.getElementById('editInvNote');
    if (editSelectedMed) {
        note.textContent = '📦 Available stock: ' + editSelectedMed.avail + ' unit(s)';
        note.style.display = 'block';
        note.style.color = editSelectedMed.avail <= 5 ? '#856404' : '#1a7a4a';
    } else {
        note.style.display = 'none';
    }
}

function filterEditMeds() {
    const q = document.getElementById('editMedSearch').value.toLowerCase();
    renderEditMedDrop(q);
}

function openEditMedDrop() {
    document.getElementById('editMedWrap').classList.add('open');
    renderEditMedDrop(document.getElementById('editMedSearch').value.toLowerCase());
}

function renderEditMedDrop(q) {
    const drop = document.getElementById('editMedDrop');
    const items = q ? EDIT_MED_DATA.filter(m => m.name.toLowerCase().includes(q)) : EDIT_MED_DATA;
    if (items.length === 0) {
        drop.innerHTML = '<div style="padding:12px; color:var(--color-text-muted); font-size:13px;">No medicines found</div>';
        return;
    }
    drop.innerHTML = items.map(m =>
        `<div class="edit-med-opt" onclick="selectEditMed(${JSON.stringify(m.name)}, ${m.avail})">
            <span>${m.name}</span>
            <span style="font-size:11px; color:${m.avail<=5?'#856404':'#1a7a4a'}; font-weight:600;">${m.avail} avail</span>
        </div>`
    ).join('');
}

function selectEditMed(name, avail) {
    document.getElementById('editMedHidden').value = name;
    document.getElementById('editMedSearch').value = name;
    editSelectedMed = { name, avail };
    document.getElementById('editMedWrap').classList.remove('open');
    updateEditInvNote();
}

// Close edit med dropdown on outside click
document.addEventListener('click', function(e) {
    if (!e.target.closest('#editMedWrap')) {
        document.getElementById('editMedWrap').classList.remove('open');
    }
});

// Close modal on backdrop click
document.getElementById('editLogModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});

async function confirmEdit(e) {
    e.preventDefault();
    const medName = document.getElementById('editMedHidden').value;
    const qty     = parseFloat(document.getElementById('editQty').value) || 0;
    const editor  = document.getElementById('editEditorName').value.trim();

    if (!medName) { showAlert('Validation Error', 'Please select a medicine.', 'error'); return false; }
    if (qty <= 0)  { showAlert('Validation Error', 'Please enter a valid quantity (> 0).', 'error'); return false; }
    if (!editor)   { showAlert('Validation Error', 'Please enter your name (editing staff).', 'error'); return false; }

    const logId = document.getElementById('editLogId').value;
    const confirmed = await showConfirm(
        'Save Changes?',
        `Are you sure you want to update Log #${logId}?<br><br>
        <strong>Medicine:</strong> ${medName}<br>
        <strong>New Qty:</strong> ${qty} unit(s)<br><br>
        <em>Inventory will be automatically adjusted.</em>`
    );
    if (confirmed) {
        document.getElementById('editLogForm').submit();
    }
    return false;
}

/* ── General log management ── */
async function confirmLogCleanup() {
    const confirmed = await showConfirm("Delete All Logs?", "This will permanently delete <strong>all log entries</strong>. This action cannot be undone. Proceed?");
    if (confirmed) {
        const f = document.createElement('form');
        f.method = 'POST'; f.style.display = 'none';
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'delete_all_logs'; inp.value = '1';
        f.appendChild(inp); document.body.appendChild(f); f.submit();
    }
}

async function confirmDeleteSelected() {
    const checkboxes = document.querySelectorAll('.log-checkbox:checked');
    if (checkboxes.length === 0) return;
    const confirmed = await showConfirm("Delete Selected Logs?", `Are you sure you want to delete ${checkboxes.length} selected log(s)? This action cannot be undone.`);
    if (confirmed) { document.getElementById('deleteForm').submit(); }
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    document.querySelectorAll('.log-checkbox').forEach(cb => cb.checked = selectAll.checked);
    updateDeleteBtn();
}

function updateDeleteBtn() {
    const cnt = document.querySelectorAll('.log-checkbox:checked').length;
    const btn = document.getElementById('btnDeleteSelected');
    if (cnt > 0) { btn.style.display = 'inline-block'; btn.innerHTML = `🗑️ Delete Selected (${cnt})`; }
    else         { btn.style.display = 'none'; }
}
</script>

</body>
</html>