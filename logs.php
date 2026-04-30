<?php
require_once __DIR__ . '/db.php';

// Handle Delete Logs (MUST BE BEFORE header.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_logs'])) {
    if (!empty($_POST['log_ids']) && is_array($_POST['log_ids'])) {
        $ids = array_map('intval', $_POST['log_ids']);
        $ids_str = implode(',', $ids);
        $conn->query("DELETE FROM logs WHERE id IN ($ids_str)");
        header("Location: logs.php?deleted=" . count($ids));
        exit();
    }
}

require_once __DIR__ . '/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispense Logs - Clinic Management System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            min-height: 100vh;
        }


        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }

        .header-card {
            background: white; border-radius: 15px; padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .header-content { text-align: center; }
        .header-content .icon { font-size: 50px; margin-bottom: 10px; }
        .header-content h2 { color: #2c3e50; font-size: 28px; margin-bottom: 10px; }
        .header-content p  { color: #7f8c8d; font-size: 14px; }

        /* Stats */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px; margin-bottom: 30px;
        }
        .stat-card {
            background: white; padding: 20px;
            border-radius: 10px; text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card h3 { color: #7f8c8d; font-size: 14px; margin-bottom: 10px; }
        .stat-card .number { font-size: 32px; font-weight: bold; color: #2c3e50; }

        /* Filter */
        .filter-section {
            background: white; border-radius: 10px; padding: 20px;
            margin-bottom: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .filter-form { display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; }
        .filter-group { flex: 1; min-width: 150px; }
        .filter-group label {
            display: block; margin-bottom: 8px;
            color: #2c3e50; font-weight: 500; font-size: 13px;
        }
        .filter-group select, .filter-group input {
            width: 100%; padding: 10px;
            border: 2px solid #e0e0e0; border-radius: 8px;
            font-size: 14px; transition: all 0.3s ease;
        }
        .filter-group select:focus, .filter-group input:focus {
            outline: none; border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
        }
        .filter-buttons { display: flex; gap: 10px; }
        .btn-filter {
            padding: 10px 25px; background: #1f4f87;
            color: white; border: none; border-radius: 8px;
            cursor: pointer; font-size: 14px; font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-filter:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(31,79,135,0.4); }
        .btn-reset {
            padding: 10px 25px; background: #95a5a6;
            color: white; border: none; border-radius: 8px;
            cursor: pointer; font-size: 14px; font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-reset:hover { background: #7f8c8d; transform: translateY(-2px); }

        /* Table */
        .table-container {
            background: white; border-radius: 10px;
            overflow-x: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        table { width: 100%; border-collapse: collapse; }
        th {
            background: #1f4f87; color: white;
            padding: 15px; text-align: left;
            font-weight: 600; font-size: 14px;
        }
        td { padding: 12px 15px; border-bottom: 1px solid #e0e0e0; color: #2c3e50; }
        tr:hover { background: #f8f9fa; }

        .medicine-name { font-weight: 600; color: #2c3e50; }

        /* Action badges */
        .badge {
            display: inline-block; padding: 4px 10px;
            border-radius: 20px; font-size: 12px; font-weight: 600;
        }
        .badge-dispense { background: #e8f5e9; color: #2e7d32; }
        .badge-new-batch { background: #e3f2fd; color: #1565c0; }
        .badge-other     { background: #f3e5f5; color: #6a1b9a; }

        /* Quantity badges */
        .qty-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-weight: 600; font-size: 13px; }
        .qty-normal { background: #e3f2fd; color: #1976d2; }
        .qty-low    { background: #fff3e0; color: #f57c00; }

        .date-cell { font-family: monospace; font-size: 13px; color: #7f8c8d; }
        .batch-info { font-size: 11px; color: #95a5a6; margin-top: 2px; }

        .no-data { text-align: center; padding: 60px; color: #7f8c8d; }
        .no-data .icon { font-size: 48px; margin-bottom: 15px; }

        .export-section { margin-bottom: 20px; text-align: right; }
        .btn-export {
            padding: 10px 20px; background: #27ae60;
            color: white; border: none; border-radius: 8px;
            cursor: pointer; font-size: 14px; font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-export:hover { background: #229954; transform: translateY(-2px); }

        @media (max-width: 768px) {
            .nav { padding: 6px 8px; gap: 4px 6px; }
            .nav a { font-size: 12px; padding: 5px 10px; }
            .container { margin: 20px auto; }
            .filter-group { min-width: 100%; }
            .filter-buttons { width: 100%; }
            .btn-filter, .btn-reset { flex: 1; }
            th, td { padding: 10px; font-size: 12px; }
        }
    </style>
</head>
<body>


<div class="container">
    <?php if (isset($_GET['deleted'])): ?>
        <div style="background: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 500;">
            ✅ Successfully deleted <?php echo intval($_GET['deleted']); ?> log record(s).
        </div>
    <?php endif; ?>

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
            <div class="filter-buttons">
                <button type="submit" name="filter" class="btn-filter">Apply Filter</button>
                <a href="logs.php" class="btn-reset"
                   style="text-decoration:none; display:inline-block; text-align:center;">Reset</a>
            </div>
        </form>
    </div>

    <form method="POST" id="logsForm">
        <div class="export-section" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <button type="submit" name="delete_logs" class="btn-delete" onclick="return confirm('Are you sure you want to delete selected logs?');" style="background:#e74c3c; color:white; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-weight:500;">🗑️ Delete Selected</button>
            <button type="button" onclick="exportToCSV()" class="btn-export">📊 Export to CSV</button>
        </div>

        <div class="table-container">
            <table id="logTable">
                <thead>
                    <tr>
                        <th style="width: 40px;"><input type="checkbox" id="selectAll" onclick="toggleAll(this)"></th>
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
            // Build query — LEFT JOIN so deleted batches still show
            $query = "
                SELECT
                    l.id,
                    l.quantity,
                    l.action,
                    l.patient_name,
                    l.prescriber_name,
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
                WHERE 1=1";

            if (!empty($_GET['item_type'])) {
                $ftype = mysqli_real_escape_string($conn, $_GET['item_type']);
                $query .= " AND m.type = '$ftype'";
            }
            if (!empty($_GET['action'])) {
                $faction = mysqli_real_escape_string($conn, $_GET['action']);
                $query .= " AND l.action = '$faction'";
            }
            if (!empty($_GET['date_from'])) {
                $dfrom = mysqli_real_escape_string($conn, $_GET['date_from']);
                $query .= " AND DATE(l.date) >= '$dfrom'";
            }
            if (!empty($_GET['date_to'])) {
                $dto = mysqli_real_escape_string($conn, $_GET['date_to']);
                $query .= " AND DATE(l.date) <= '$dto'";
            }
            $query .= " ORDER BY l.id DESC";

            $r = $conn->query($query);

            if ($r && $r->num_rows > 0) {
                $row_num = 0;
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
                    
                    // Patient/Prescriber Details
                    $details_html = "<span style='color:#95a5a6;'>—</span>";
                    if ($row['action'] === 'Released to patient') {
                        $p_name = !empty($row['patient_name']) ? htmlspecialchars($row['patient_name']) : 'N/A';
                        $d_name = !empty($row['prescriber_name']) ? htmlspecialchars($row['prescriber_name']) : 'N/A';
                        if ($p_name !== 'N/A' || $d_name !== 'N/A') {
                            $details_html = "<div style='font-size:12px; line-height:1.4;'>
                                <div><strong>Patient:</strong> {$p_name}</div>
                                <div><strong>Dr.:</strong> {$d_name}</div>
                            </div>";
                        }
                    }

                    echo "<tr>
                        <td><input type='checkbox' name='log_ids[]' value='{$row['id']}' class='log-checkbox'></td>
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
                echo "<tr><td colspan='8' class='no-data'>
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
</div>

<script>
    function toggleAll(source) {
        let checkboxes = document.querySelectorAll('.log-checkbox');
        for(let i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = source.checked;
        }
    }

    function exportToCSV() {
        const table = document.getElementById('logTable');
        const csv   = [];

        // Headers
        const headers = [];
        table.querySelectorAll('thead th').forEach(th => headers.push('"' + th.innerText + '"'));
        csv.push(headers.join(','));

        // Rows
        table.querySelectorAll('tbody tr').forEach(function(row) {
            const rowData = [];
            row.querySelectorAll('td').forEach(function(cell) {
                let text = cell.innerText.replace(/\n/g, ' ').replace(/\s+/g, ' ').trim();
                rowData.push('"' + text.replace(/"/g, '""') + '"');
            });
            csv.push(rowData.join(','));
        });

        const blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
        const url  = window.URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href     = url;
        a.download = 'medicine_logs_' + new Date().toISOString().slice(0, 10) + '.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }
</script>

</body>
</html>