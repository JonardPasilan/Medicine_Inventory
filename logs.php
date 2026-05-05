<?php
require_once __DIR__ . '/db.php';

// Log Cleanup Logic
if (isset($_POST['clear_old_logs'])) {
    $six_months_ago = date('Y-m-d H:i:s', strtotime('-6 months'));
    $conn->query("DELETE FROM logs WHERE date < '$six_months_ago'");
    $deleted_count = $conn->affected_rows;
    header("Location: logs.php?cleaned=$deleted_count");
    exit();
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
        SELECT
            l.id,
            l.quantity,
            l.action,
            l.patient_name,
            l.prescriber_name,
            l.staff_name,
            DATE_FORMAT(l.date, '%M %d, %Y %h:%i %p') AS fmt_date,
            l.medicine_id,
            m.name,
            m.label,
            m.batch_number,
            m.expiration_date,
            m.type,
            m.date_acquired
        FROM logs l
        LEFT JOIN medicines m ON l.medicine_id = m.id
        WHERE $where_clause
        ORDER BY l.id DESC";

    $r = $conn->query($query);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=inventory_logs_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');

    // Headers
    fputcsv($output, ['#', 'Item Details', 'Expiry / Procured Date', 'Qty', 'Action', 'Details', 'Date & Time']);

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
            if (!empty($row['patient_name'])) $details_arr[] = "Patient: " . $row['patient_name'];
            if (!empty($row['prescriber_name'])) $details_arr[] = "Dr: " . $row['prescriber_name'];
            if (!empty($row['staff_name'])) $details_arr[] = "Staff: " . $row['staff_name'];
            $details_str = empty($details_arr) ? '—' : implode(' | ', $details_arr);

            fputcsv($output, [
                $row_num,
                $item_details,
                $bexp_disp,
                $row['quantity'] . " unit(s)",
                $row['action'],
                $details_str,
                $row['fmt_date']
            ]);
        }
    }
    fclose($output);
    exit();
}

require_once __DIR__ . '/header.php';
?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--color-canvas);
            min-height: 100vh;
        }

        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }

        .header-card {
            background: var(--color-surface);
            border-radius: var(--radius-lg); padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--color-border);
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .header-content { text-align: center; }
        .header-content .icon { font-size: 50px; margin-bottom: 10px; }
        .header-content h2 { color: var(--color-text-primary); font-size: 28px; margin-bottom: 10px; }
        .header-content p  { color: var(--color-text-secondary); font-size: 14px; }

        /* Stats */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px; margin-bottom: 30px;
        }
        .stat-card {
            background: var(--color-surface); padding: 20px;
            border-radius: var(--radius-md); text-align: center;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--color-border);
            transition: transform 0.3s ease;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card h3 { color: var(--color-text-secondary); font-size: 14px; margin-bottom: 10px; }
        .stat-card .number { font-size: 32px; font-weight: bold; color: var(--color-text-primary); }

        /* Filter */
        .filter-section {
            background: var(--color-surface);
            border-radius: var(--radius-md); padding: 20px;
            margin-bottom: 30px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--color-border);
        }
        .filter-form { display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; }
        .filter-group { flex: 1; min-width: 150px; }
        .filter-group label {
            display: block; margin-bottom: 8px;
            color: var(--color-text-primary); font-weight: 500; font-size: 13px;
        }
        .filter-group select, .filter-group input {
            width: 100%; padding: 10px;
            border: 2px solid var(--color-border); border-radius: var(--radius-sm);
            background: var(--color-overlay); color: var(--color-text-primary);
            font-size: 14px; transition: all 0.3s ease;
        }
        .filter-group select:focus, .filter-group input:focus {
            outline: none; border-color: var(--color-brand);
            box-shadow: 0 0 0 3px var(--color-brand-light);
        }
        .filter-buttons { display: flex; gap: 10px; }
        .btn-filter {
            padding: 10px 25px; background: var(--color-brand);
            color: white; border: none; border-radius: var(--radius-sm);
            cursor: pointer; font-size: 14px; font-weight: 500;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }
        .btn-filter:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(90,72,220,0.35); }
        .btn-reset {
            padding: 10px 25px;
            background: var(--color-overlay); color: var(--color-text-secondary);
            border: 1px solid var(--color-border); border-radius: var(--radius-sm);
            cursor: pointer; font-size: 14px; font-weight: 500;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease; text-decoration: none; display: inline-block; text-align: center;
        }
        .btn-reset:hover { background: var(--color-border); color: var(--color-text-primary); transform: translateY(-2px); }

        /* Table */
        .table-container {
            background: var(--color-surface);
            border-radius: var(--radius-md);
            overflow-x: auto;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--color-border);
        }
        table { width: 100%; border-collapse: collapse; }
        th {
            background: var(--color-brand); color: white;
            padding: 15px; text-align: left;
            font-weight: 600; font-size: 14px;
        }
        td { padding: 12px 15px; border-bottom: 1px solid var(--color-border); color: var(--color-text-primary); }
        tr:hover { background: var(--color-overlay); }

        .medicine-name { font-weight: 600; color: var(--color-text-primary); }

        /* Action badges */
        .badge {
            display: inline-block; padding: 4px 10px;
            border-radius: 20px; font-size: 12px; font-weight: 600;
        }
        .badge-dispense { background: hsl(140, 60%, 94%); color: hsl(140, 60%, 28%); }
        .badge-new-batch { background: hsl(210, 80%, 94%); color: hsl(210, 70%, 35%); }
        .badge-other     { background: hsl(270, 60%, 94%); color: hsl(270, 60%, 35%); }

        /* Quantity badges */
        .qty-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-weight: 600; font-size: 13px; }
        .qty-normal { background: hsl(210, 80%, 94%); color: hsl(210, 70%, 35%); }
        .qty-low    { background: hsl(30, 90%, 94%); color: hsl(30, 80%, 35%); }

        .date-cell { font-family: monospace; font-size: 13px; color: var(--color-text-muted); }
        .batch-info { font-size: 11px; color: var(--color-text-muted); margin-top: 2px; }

        .no-data { text-align: center; padding: 60px; color: var(--color-text-muted); }
        .no-data .icon { font-size: 48px; margin-bottom: 15px; }

        .export-section { margin-bottom: 20px; text-align: right; }
        .btn-export {
            padding: 10px 20px; background: hsl(140, 60%, 40%);
            color: white; border: none; border-radius: var(--radius-sm);
            cursor: pointer; font-size: 14px; font-weight: 500;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }
        .btn-export:hover { background: hsl(140, 60%, 33%); transform: translateY(-2px); }

        .btn-clear-logs {
            padding: 10px 20px; background: transparent;
            color: hsl(0, 70%, 50%); border: 1px solid hsl(0, 70%, 50%);
            border-radius: var(--radius-sm);
            cursor: pointer; font-size: 14px; font-weight: 500;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }
        .btn-clear-logs:hover { background: hsl(0, 70%, 50%); color: white; transform: translateY(-2px); }

        @media (max-width: 768px) {
            .container { margin: 20px auto; }
            .filter-group { min-width: 100%; }
            .filter-buttons { width: 100%; }
            .btn-filter, .btn-reset { flex: 1; }
            th, td { padding: 10px; font-size: 12px; }
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

    ?>

    <div class="stats">
        <div class="stat-card">
            <h3>Total Units Dispensed</h3>
            <div class="number"><?php echo (int)$total_dispensed; ?></div>
        </div>
        <div class="stat-card">
            <h3>Total Transactions</h3>
            <div class="number"><?php echo (int)$total_transactions; ?></div>
        </div>
        <div class="stat-card">
            <h3>Today's Dispensed</h3>
            <div class="number" style="color:#27ae60;"><?php echo (int)$today_dispensed; ?></div>
        </div>
        <div class="stat-card">
            <h3>New Batches Added</h3>
            <div class="number" style="color:#1f4f87;"><?php echo (int)$batches_added; ?></div>
        </div>
    </div>

    <div class="filter-section">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label>🔍 Filter by Type</label>
                <select name="item_type">
                    <option value="">All Types</option>
                    <option value="medicine" <?php echo (isset($_GET['item_type']) && $_GET['item_type'] === 'medicine') ? 'selected' : ''; ?>>Medicines</option>
                    <option value="consumable" <?php echo (isset($_GET['item_type']) && $_GET['item_type'] === 'consumable') ? 'selected' : ''; ?>>Consumables Supplies</option>
                    <option value="dental" <?php echo (isset($_GET['item_type']) && $_GET['item_type'] === 'dental') ? 'selected' : ''; ?>>Dental Device & Equipment</option>
                    <option value="medical" <?php echo (isset($_GET['item_type']) && $_GET['item_type'] === 'medical') ? 'selected' : ''; ?>>Medical Device & Equipment</option>
                </select>
            </div>
            <div class="filter-group">
                <label>🏷️ Filter by Action</label>
                <select name="action">
                    <option value="">All Actions</option>
                    <?php
                    $cur_action = isset($_GET['action']) ? $_GET['action'] : '';
                    $actions = ['Released to patient', 'New Batch Added', 'Item Updated'];
                    foreach ($actions as $a) {
                        $sel = ($cur_action === $a) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($a, ENT_QUOTES) . "' $sel>" . htmlspecialchars($a) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="filter-group">
                <label>📅 From Date</label>
                <input type="date" name="date_from"
                       value="<?php echo isset($_GET['date_from']) ? htmlspecialchars($_GET['date_from']) : ''; ?>">
            </div>
            <div class="filter-group">
                <label>📅 To Date</label>
                <input type="date" name="date_to"
                       value="<?php echo isset($_GET['date_to']) ? htmlspecialchars($_GET['date_to']) : ''; ?>">
            </div>
            <div class="filter-buttons" style="width:100%; display:flex; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                <div style="display:flex; gap:10px;">
                    <button type="submit" name="filter" value="1" class="btn-filter">Apply Filter</button>
                    <a href="logs.php" class="btn-reset" style="text-decoration:none; display:inline-block; text-align:center;">Reset</a>
                </div>
                <div style="display:flex; gap:10px;">
                    <button type="submit" name="export" value="csv" class="btn-export">📊 Export to CSV</button>
                    <button type="button" onclick="confirmLogCleanup()" class="btn-clear-logs">🧹 Clean Up (6mo+)</button>
                </div>
            </div>
        </form>

        <?php if (isset($_GET['cleaned'])): ?>
            <div style="background: var(--color-brand-light); color: var(--color-brand); padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; text-align: center; border: 1px solid var(--color-brand);">
                ✨ Successfully cleaned up <strong><?php echo (int)$_GET['cleaned']; ?></strong> old log entries.
            </div>
        <?php endif; ?>
    </div>

        <div class="table-container">
            <table id="logTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item Details</th>
                        <th>Expiry / Procured Date</th>
                        <th>Qty</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>Date &amp; Time</th>
                    </tr>
            </thead>
            <tbody>
            <?php
            // Pagination settings
            $limit = 50; 
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $page = max($page, 1);
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

            // Get total for pagination
            $count_query = "SELECT COUNT(*) as total FROM logs l LEFT JOIN medicines m ON l.medicine_id = m.id WHERE $where_clause";
            $total_records = $conn->query($count_query)->fetch_assoc()['total'];
            $total_pages = ceil($total_records / $limit);

            $query = "
                SELECT
                    l.id,
                    l.quantity,
                    l.action,
                    l.patient_name,
                    l.prescriber_name,
                    l.staff_name,
                    DATE_FORMAT(l.date, '%M %d, %Y %h:%i %p') AS fmt_date,
                    l.medicine_id,
                    m.name,
                    m.label,
                    m.batch_number,
                    m.expiration_date,
                    m.type,
                    m.date_acquired
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
                    $qty = (int)$row['quantity'];

                    // Medicine name (handle deleted batches)
                    if ($row['name']) {
                        $mname  = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
                        $mlabel = htmlspecialchars((string)$row['label'], ENT_QUOTES, 'UTF-8');
                        $type = $row['type'];
                        $batch_label = ($type == 'dental' || $type == 'medical') ? "Equipment" : "Batch #{$row['batch_number']}";
                        $med_display = "<div class='medicine-name'>{$mname}</div>
                                         <small style='color:#7f8c8d;'>{$mlabel}</small>
                                         <div class='batch-info'>{$batch_label} (ID: {$row['medicine_id']})</div>";
                    } else {
                        $med_display = "<div class='medicine-name' style='color:#95a5a6;'>Deleted Medicine</div>
                                         <div class='batch-info'>Item (ID: {$row['medicine_id']})</div>";
                    }

                    // Expiry date or Date Acquired for the batch/item
                    $bexp = $row['expiration_date'];
                    $acq = $row['date_acquired'];
                    $type = $row['type'];

                    if ($type == 'dental' || $type == 'medical') {
                        if (!empty($acq) && $acq != '0000-00-00') {
                            $bexp_disp = "<span style='color:#1f4f87; font-size:13px;'>" . date('M d, Y', strtotime($acq)) . "</span>";
                        } else {
                            $bexp_disp = "<span style='color:#95a5a6;'>N/A</span>";
                        }
                    } else {
                        if ($bexp && $bexp != '0000-00-00') {
                            $today_s = date('Y-m-d');
                            $bexp_clr = (strtotime($bexp) < strtotime($today_s)) ? '#e74c3c' : '#27ae60';
                            $bexp_disp = "<span style='color:{$bexp_clr}; font-size:13px;'>"
                                       . date('M d, Y', strtotime($bexp)) . "</span>";
                        } else {
                            $bexp_disp = "<span style='color:#95a5a6;'>N/A</span>";
                        }
                    }

                    // Action badge
                    $act = htmlspecialchars((string)$row['action'], ENT_QUOTES, 'UTF-8');
                    if ($row['action'] === 'Released to patient') {
                        $badge_class = 'badge-dispense';
                        $badge_icon  = '💊';
                    } elseif ($row['action'] === 'New Batch Added') {
                        $badge_class = 'badge-new-batch';
                        $badge_icon  = '📦';
                    } elseif ($row['action'] === 'Item Updated') {
                        $badge_class = 'badge-other';
                        $badge_icon  = '🔄';
                    } else {
                        $badge_class = 'badge-other';
                        $badge_icon  = '📝';
                    }

                    $qty_class = $qty <= 5 ? 'qty-low' : 'qty-normal';
                    $fmt_date  = htmlspecialchars((string)$row['fmt_date'], ENT_QUOTES, 'UTF-8');
                    
                    // Patient/Prescriber/Staff Details
                    $details_html = "<span style='color:#95a5a6;'>—</span>";
                    $p_name = !empty($row['patient_name']) ? htmlspecialchars($row['patient_name']) : 'N/A';
                    $d_name = !empty($row['prescriber_name']) ? htmlspecialchars($row['prescriber_name']) : 'N/A';
                    $s_name = !empty($row['staff_name']) ? htmlspecialchars($row['staff_name']) : 'N/A';

                    if ($row['action'] === 'Released to patient') {
                        $details_html = "<div style='font-size:12px; line-height:1.4;'>
                            " . ($p_name !== 'N/A' ? "<div><strong>Patient:</strong> {$p_name}</div>" : "") . "
                            " . ($d_name !== 'N/A' ? "<div><strong>Dr.:</strong> {$d_name}</div>" : "") . "
                            " . ($s_name !== 'N/A' ? "<div><strong>Staff:</strong> <span style='color:#2980b9'>{$s_name}</span></div>" : "") . "
                        </div>";
                        if ($details_html === "<div style='font-size:12px; line-height:1.4;'>\n                            \n                            \n                            \n                        </div>") {
                            $details_html = "<span style='color:#95a5a6;'>—</span>";
                        }
                    } elseif ($s_name !== 'N/A') {
                         $details_html = "<div style='font-size:12px; line-height:1.4;'>
                            <div><strong>Staff:</strong> <span style='color:#2980b9'>{$s_name}</span></div>
                        </div>";
                    }

                    echo "<tr>
                        <td style='color:#aaa; font-size:12px;'>{$row_num}</td>
                        <td>{$med_display}</td>
                        <td>{$bexp_disp}</td>
                        <td><span class='qty-badge {$qty_class}'>{$qty} unit(s)</span></td>
                        <td><span class='badge {$badge_class}'>{$badge_icon} {$act}</span></td>
                        <td>{$details_html}</td>
                        <td class='date-cell'>📅 {$fmt_date}</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='7' class='no-data'>
                         <div class='icon'>📭</div>
                         <div>No log records found</div>
                         <small style='margin-top:10px; display:block;'>Try adjusting your filters</small>
                      </td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_pages > 1): ?>
        <div style="margin-top: 20px; text-align: center;">
            <?php
            // Build current query params to keep filters in pagination links
            $params = $_GET;
            for ($i = 1; $i <= $total_pages; $i++) {
                $params['page'] = $i;
                $qs = http_build_query($params);
                $active = ($i == $page) ? 'background:#1f4f87; color:white;' : 'background:white; color:#1f4f87; border: 1px solid #1f4f87;';
                echo "<a href='logs.php?{$qs}' style='display:inline-block; padding:8px 12px; margin: 0 4px; border-radius:5px; text-decoration:none; font-size:14px; transition:0.3s; {$active}'>{$i}</a>";
            }
            ?>
        </div>
    <?php endif; ?>

</div>

<script>
    async function confirmLogCleanup() {
        const confirmed = await showConfirm(
            "Clean Up Old Logs?",
            "This will permanently delete all log entries <strong>older than 6 months</strong>. This action cannot be undone. Proceed?"
        );

        if (confirmed) {
            const cleanupForm = document.createElement('form');
            cleanupForm.method = 'POST';
            cleanupForm.style.display = 'none';
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'clear_old_logs';
            input.value = '1';
            cleanupForm.appendChild(input);
            document.body.appendChild(cleanupForm);
            cleanupForm.submit();
        }
    }
</script>
</body>
</html>