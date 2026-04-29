<?php 
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/db.php';

// Handle search query
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Fetch Alerts Data
$today_date = date('Y-m-d');
$soon_date  = date('Y-m-d', strtotime('+30 days'));

$low_stock_q = $conn->query("SELECT COUNT(*) as c FROM (SELECT name, label, SUM(quantity) as tq FROM medicines WHERE is_archived = 0 GROUP BY name, label HAVING tq <= 5) as sub");
$low_stock_count = $low_stock_q ? $low_stock_q->fetch_assoc()['c'] : 0;

$expired_q = $conn->query("SELECT COUNT(*) as c FROM medicines WHERE is_archived = 1 AND expiration_date < '$today_date'");
$expired_count = $expired_q ? $expired_q->fetch_assoc()['c'] : 0;

$expiring_soon_q = $conn->query("SELECT COUNT(*) as c FROM medicines WHERE is_archived = 0 AND expiration_date >= '$today_date' AND expiration_date <= '$soon_date'");
$expiring_soon_count = $expiring_soon_q ? $expiring_soon_q->fetch_assoc()['c'] : 0;
?>

    <style>
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .header-card {
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-lg);
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--shadow-sm);
            text-align: center;
        }
        .header-card h2 { color: var(--color-text-primary); font-size: var(--text-2xl); margin-bottom: 8px; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .header-card p  { color: var(--color-text-secondary); font-size: var(--text-sm); }

        /* Dashboard Alerts */
        .alerts-dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .alert-card {
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: var(--shadow-sm);
        }
        .alert-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
        
        .alert-card.warning { border-left: 4px solid hsl(40, 90%, 50%); }
        .alert-card.danger { border-left: 4px solid hsl(0, 80%, 50%); }
        .alert-card.info { border-left: 4px solid hsl(210, 90%, 50%); }
        
        .alert-icon { font-size: 30px; color: var(--color-text-muted); }
        .alert-card.warning .alert-icon { color: hsl(40, 90%, 50%); }
        .alert-card.danger .alert-icon { color: hsl(0, 80%, 50%); }
        .alert-card.info .alert-icon { color: hsl(210, 90%, 50%); }

        .alert-details h3 { font-size: var(--text-xl); color: var(--color-text-primary); margin-bottom: 2px; }
        .alert-details p { font-size: var(--text-xs); color: var(--color-text-secondary); margin: 0; }

        /* Search Section */
        .search-section {
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
        }
        .search-form { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
        .search-form input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid var(--color-border);
            background: var(--color-overlay);
            border-radius: var(--radius-sm);
            font-size: var(--text-sm);
            color: var(--color-text-primary);
        }
        .search-form input:focus {
            outline: none;
            border-color: var(--color-brand);
            box-shadow: 0 0 0 2px var(--color-brand-light);
        }
        .search-form button {
            padding: 10px 20px;
            background: var(--color-brand);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: var(--text-sm);
            font-weight: 500;
        }
        .search-form button:hover {
            background: var(--color-brand-dark);
            box-shadow: var(--shadow-xs);
        }

        /* Tabs Section */
        .tabs-container {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            justify-content: center;
        }
        .tab-btn {
            padding: 10px 20px;
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 500;
            font-size: var(--text-sm);
            color: var(--color-text-secondary);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .tab-btn:hover { background: var(--color-overlay); color: var(--color-text-primary); }
        .tab-btn.active {
            background: var(--color-brand-light);
            border-color: var(--color-brand-dark);
            color: var(--color-brand-dark);
            font-weight: 600;
        }

        .table-container {
            background: var(--color-surface);
            border: 1px solid var(--color-border);
            border-radius: var(--radius-md);
            overflow-x: auto;
            box-shadow: var(--shadow-sm);
            display: none; 
        }
        .table-container.active { display: block; }

        table { width: 100%; border-collapse: collapse; }
        th {
            background: var(--color-overlay);
            color: var(--color-text-secondary);
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            font-size: var(--text-xs);
            border-bottom: 1px solid var(--color-border);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        td { padding: 12px 15px; border-bottom: 1px solid var(--color-border); color: var(--color-text-primary); font-size: var(--text-sm); }

        /* Group Rows */
        .group-row { cursor: pointer; }
        .group-row:hover { background: var(--color-overlay) !important; }

        /* Batch details */
        .batch-detail-row td {
            font-size: var(--text-xs);
            border-bottom: 1px dashed var(--color-border);
            padding: 0;
            overflow: hidden;
            background: var(--color-overlay);
        }
        .batch-anim-wrapper {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease, padding 0.4s ease;
        }
        .batch-detail-row.expanded .batch-anim-wrapper {
            max-height: 200px;
            padding: 10px 15px 10px 40px;
        }

        .toggle-icon {
            color: var(--color-text-muted);
            margin-left: 6px;
            display: inline-flex;
            align-items: center;
        }

        /* Status Badges */
        .status-expired  { background: hsl(0, 100%, 97%); color: hsl(0, 70%, 40%); border: 1px solid hsl(0, 70%, 80%); padding: 2px 8px; border-radius: var(--radius-full); font-size: var(--text-xs); font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
        .status-lowstock { background: hsl(40, 100%, 96%); color: hsl(40, 70%, 40%); border: 1px solid hsl(40, 70%, 80%); padding: 2px 8px; border-radius: var(--radius-full); font-size: var(--text-xs); font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
        .status-ok       { background: hsl(140, 100%, 96%); color: hsl(140, 70%, 35%); border: 1px solid hsl(140, 70%, 80%); padding: 2px 8px; border-radius: var(--radius-full); font-size: var(--text-xs); font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }

        .btn {
            padding: 6px 12px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: var(--text-xs);
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            justify-content: center;
        }
        .btn-edit   { background: var(--color-surface); border: 1px solid var(--color-border); color: var(--color-text-secondary); }
        .btn-edit:hover { background: var(--color-overlay); color: var(--color-text-primary); border-color: var(--color-border-strong); box-shadow: var(--shadow-xs); }
        
        .btn-delete { background: hsl(0, 100%, 97%); border: 1px solid hsl(0, 70%, 80%); color: hsl(0, 70%, 40%); }
        .btn-delete:hover { background: hsl(0, 100%, 95%); color: hsl(0, 70%, 35%); border-color: hsl(0, 70%, 70%); box-shadow: var(--shadow-xs); }
        
        .btn-stock  { background: var(--color-brand); color: white; border: 1px solid var(--color-brand-dark); }
        .btn-stock:hover { background: var(--color-brand-dark); box-shadow: var(--shadow-xs); }

        #loadingOverlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(255,255,255,0.7);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(2px);
        }
        .spinner {
            width: 40px; height: 40px;
            border: 4px solid var(--color-border);
            border-top: 4px solid var(--color-brand);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        .no-data { text-align: center; padding: 40px; color: var(--color-text-muted); font-size: var(--text-sm); }

        /* Custom Delete Modal */
        #deleteModal {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.4); z-index: 10000;
            align-items: center; justify-content: center;
            backdrop-filter: blur(2px); opacity: 0;
            transition: opacity 0.3s ease;
        }
        #deleteModal.show { opacity: 1; }
        .modal-content {
            background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 30px;
            width: 90%; max-width: 400px; text-align: center;
            box-shadow: var(--shadow-lg);
            transform: scale(0.95); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        #deleteModal.show .modal-content { transform: scale(1); }
        .modal-icon { font-size: 40px; color: hsl(0, 70%, 50%); margin-bottom: 15px; display: inline-block; }
        .modal-title { font-size: var(--text-lg); color: var(--color-text-primary); margin-bottom: 8px; font-weight: 600; }
        .modal-text { color: var(--color-text-secondary); font-size: var(--text-sm); margin-bottom: 25px; line-height: 1.5; }
        .modal-actions { display: flex; gap: 15px; justify-content: center; }
        .btn-modal {
            padding: 10px 20px; border: 1px solid transparent; border-radius: var(--radius-sm);
            font-size: var(--text-sm); font-weight: 500; cursor: pointer;
        }
        .btn-modal-cancel { background: var(--color-surface); color: var(--color-text-secondary); border-color: var(--color-border); }
        .btn-modal-cancel:hover { background: var(--color-overlay); color: var(--color-text-primary); border-color: var(--color-border-strong); }
        .btn-modal-confirm { background: hsl(0, 70%, 50%); color: white; border-color: hsl(0, 70%, 40%); }
        .btn-modal-confirm:hover { background: hsl(0, 70%, 45%); box-shadow: var(--shadow-sm); }

        @media (max-width: 768px) {
            th, td { padding: 10px; font-size: var(--text-xs); }
            .tab-btn { padding: 8px 12px; font-size: var(--text-xs); }
        }
    </style>
</head>
<body>

<div id="loadingOverlay">
    <div class="spinner"></div>
</div>

<div class="container">
    <div class="header-card">
        <h2><i data-lucide="hospital"></i> Clinic Inventory Management</h2>
        <p>Manage medicines and consumables in one place</p>
    </div>

    <!-- Alerts Dashboard -->
    <div class="alerts-dashboard">
        <div class="alert-card warning">
            <div class="alert-icon"><i data-lucide="trending-down"></i></div>
            <div class="alert-details">
                <h3><?php echo $low_stock_count; ?></h3>
                <p>Low Stock Items (≤ 5)</p>
            </div>
        </div>
        <div class="alert-card danger" style="position: relative; cursor: pointer;" onclick="window.location.href='expired.php'">
            <div class="alert-icon"><i data-lucide="alert-triangle"></i></div>
            <div class="alert-details">
                <h3><?php echo $expired_count; ?></h3>
                <p>Expired Batches (Archived)</p>
            </div>
            <?php if ($expired_count > 0): ?>
            <button onclick="event.stopPropagation(); clearExpiredBatches();" class="btn btn-delete" style="position:absolute; right:15px; top:50%; transform:translateY(-50%); z-index:10;">
                <i data-lucide="trash-2" style="width:14px;height:14px;"></i> Clear All
            </button>
            <?php endif; ?>
        </div>
        <div class="alert-card info">
            <div class="alert-icon"><i data-lucide="calendar"></i></div>
            <div class="alert-details">
                <h3><?php echo $expiring_soon_count; ?></h3>
                <p>Expiring Soon (30 Days)</p>
            </div>
        </div>
    </div>

    <!-- Search Section -->
    <div class="search-section">
        <form method="GET" class="search-form" onsubmit="showLoading()">
            <div style="position: relative; flex: 1;">
                <i data-lucide="search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--color-text-muted);"></i>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by name or description..." style="padding-left: 36px; width: 100%;">
            </div>
            <button type="submit">Search</button>
            <?php if ($search): ?>
                <a href="index.php" class="btn btn-edit">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Tabs -->
    <div class="tabs-container">
        <button class="tab-btn active" onclick="switchTab('medicine')"><i data-lucide="pill" style="width:16px;height:16px;"></i> Medicines</button>
        <button class="tab-btn" onclick="switchTab('consumable')"><i data-lucide="droplet" style="width:16px;height:16px;"></i> Consumable Supplies</button>
        <button class="tab-btn" onclick="switchTab('dental')"><i data-lucide="activity" style="width:16px;height:16px;"></i> Dental Device & Equipment</button>
        <button class="tab-btn" onclick="switchTab('medical')"><i data-lucide="stethoscope" style="width:16px;height:16px;"></i> Medical Device & Equipment</button>
    </div>

    <!-- MEDICINES VIEW -->
    <div id="medicineView" class="table-container active">
        <table>
            <thead>
                <tr>
                    <th>Medicine Name</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Total Qty</th>
                    <th>Earliest Expiry</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php renderInventoryTable($conn, 'medicine', $search); ?>
            </tbody>
        </table>
    </div>

    <!-- CONSUMABLES VIEW -->
    <div id="consumableView" class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Total Qty</th>
                    <th>Earliest Expiry</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php renderInventoryTable($conn, 'consumable', $search); ?>
            </tbody>
        </table>
    </div>

    <!-- DENTAL VIEW -->
    <div id="dentalView" class="table-container">
        <table class="equipment-table">
            <thead>
                <tr>
                    <th rowspan="2">No.</th>
                    <th rowspan="2">Item Description</th>
                    <th rowspan="2">Stock Qty/Unit</th>
                    <th rowspan="2">Brand/Serial #</th>
                    <th rowspan="2">RIS #/ICS #/PAR #</th>
                    <th rowspan="2">Color</th>
                    <th rowspan="2">Date Acquired</th>
                    <th colspan="3" style="text-align: center; border-bottom: 1px solid var(--color-border);">Condition</th>
                    <th rowspan="2">Remarks/Notes</th>
                    <th rowspan="2">Actions</th>
                </tr>
                <tr>
                    <th>Serviceable</th>
                    <th>Unserviceable</th>
                    <th>For Repair</th>
                </tr>
            </thead>
            <tbody>
                <?php renderEquipmentTable($conn, 'dental', $search); ?>
            </tbody>
        </table>
    </div>

    <!-- MEDICAL VIEW -->
    <div id="medicalView" class="table-container">
        <table class="equipment-table">
            <thead>
                <tr>
                    <th rowspan="2">No.</th>
                    <th rowspan="2">Item Description</th>
                    <th rowspan="2">Stock Qty/Unit</th>
                    <th rowspan="2">Brand/Serial #</th>
                    <th rowspan="2">RIS #/ICS #/PAR #</th>
                    <th rowspan="2">Color</th>
                    <th rowspan="2">Date Acquired</th>
                    <th colspan="3" style="text-align: center; border-bottom: 1px solid var(--color-border);">Condition</th>
                    <th rowspan="2">Remarks/Notes</th>
                    <th rowspan="2">Actions</th>
                </tr>
                <tr>
                    <th>Serviceable</th>
                    <th>Unserviceable</th>
                    <th>For Repair</th>
                </tr>
            </thead>
            <tbody>
                <?php renderEquipmentTable($conn, 'medical', $search); ?>
            </tbody>
        </table>
    </div>
</div>

<?php
/**
 * Helper to render the equipment table (Dental and Medical)
 */
function renderEquipmentTable($conn, $type, $search) {
    $where = "WHERE type = '$type' AND is_archived = 0";
    if ($search) {
        $where .= " AND (name LIKE '%$search%' OR label LIKE '%$search%' OR brand_serial LIKE '%$search%' OR ris_id LIKE '%$search%')";
    }

    $res = $conn->query("SELECT * FROM medicines $where ORDER BY name ASC");
    if ($res && $res->num_rows > 0) {
        $i = 1;
        while ($row = $res->fetch_assoc()) {
            $id = $row['id'];
            $name_label = htmlspecialchars($row['name']);
            if(!empty($row['label'])) $name_label .= " - " . htmlspecialchars((string)$row['label']);
            $qty = (int)$row['quantity'];
            $unit = htmlspecialchars($row['unit'] ?? 'unit');
            $brand = htmlspecialchars((string)$row['brand_serial']);
            $ris = htmlspecialchars((string)$row['ris_id']);
            $color = htmlspecialchars((string)$row['color']);
            $acq = $row['date_acquired'] ? date('m/d/Y', strtotime($row['date_acquired'])) : 'N/A';
            $srv = (int)$row['qty_serviceable'];
            $unsrv = (int)$row['qty_unserviceable'];
            $rep = (int)$row['qty_repair'];
            $rem = htmlspecialchars((string)$row['remarks']);

            echo "<tr>
                    <td>$i</td>
                    <td><strong style='color:var(--color-text-primary);'>$name_label</strong></td>
                    <td>$qty <small style='color:var(--color-text-muted);'>$unit</small></td>
                    <td>$brand</td>
                    <td>$ris</td>
                    <td>$color</td>
                    <td>$acq</td>
                    <td style='text-align:center;'>$srv</td>
                    <td style='text-align:center;'>$unsrv</td>
                    <td style='text-align:center;'>$rep</td>
                    <td>$rem</td>
                    <td>
                        <div style='display:flex; gap:8px;'>
                            <a href='edit.php?id=$id' class='btn btn-edit'><i data-lucide='edit-2' style='width:14px;height:14px;'></i> Edit</a>
                            <button class='btn btn-delete' onclick='deleteBatch(event, $id)'><i data-lucide='trash-2' style='width:14px;height:14px;'></i> Delete</button>
                        </div>
                    </td>
                  </tr>";
            $i++;
        }
    } else {
        echo "<tr><td colspan='12' class='no-data'>No records found.</td></tr>";
    }
}
?>

<?php
/**
 * Helper to render the grouped inventory table rows
 */
function renderInventoryTable($conn, $type, $search) {
    $today = date("Y-m-d");
    $soon  = date("Y-m-d", strtotime('+30 days'));

    $where = "WHERE type = '$type' AND is_archived = 0";
    if ($search) {
        $where .= " AND (name LIKE '%$search%' OR label LIKE '%$search%')";
    }

    $groups = $conn->query("
        SELECT name, label, MAX(category) as category, MAX(unit) as unit,
               SUM(quantity) AS total_qty, 
               MIN(expiration_date) AS earliest_exp,
               COUNT(*) AS batch_count
        FROM medicines
        $where
        GROUP BY name, label
        ORDER BY name ASC");

    if ($groups && $groups->num_rows > 0) {
        $i = 0;
        while ($g = $groups->fetch_assoc()) {
            $i++;
            $gid = "{$type}_grp_{$i}";
            $gname = htmlspecialchars($g['name']);
            $glabel = htmlspecialchars((string)$g['label']);
            $total_qty = (int)$g['total_qty'];
            $earliest = $g['earliest_exp'];
            $batch_cnt = (int)$g['batch_count'];

            $gcat = htmlspecialchars($g['category'] ?? 'General');
            $gunit = htmlspecialchars($g['unit'] ?? 'pcs');

            $is_exp = $earliest && strtotime($earliest) < strtotime($today);
            $is_soon = !$is_exp && $earliest && strtotime($earliest) < strtotime($soon);
            $is_low = $total_qty <= 5;

            $status = '';
            if ($is_exp) $status .= "<span class='status-expired'><i data-lucide='alert-triangle' style='width:12px;height:12px;'></i> EXPIRED</span> ";
            if ($is_soon) $status .= "<span class='status-lowstock'><i data-lucide='calendar' style='width:12px;height:12px;'></i> SOON</span> ";
            if ($is_low) $status .= "<span class='status-lowstock'><i data-lucide='trending-down' style='width:12px;height:12px;'></i> LOW</span> ";
            if (!$is_exp && !$is_soon && !$is_low) $status = "<span class='status-ok'><i data-lucide='check-circle-2' style='width:12px;height:12px;'></i> OK</span>";

            $row_style = $is_exp ? "background:hsl(0, 100%, 99%);" : "";
            
            echo "<tr class='group-row' onclick='toggleBatch(\"$gid\")' style='$row_style'>
                    <td>
                        <strong style='color:var(--color-text-primary);'>$gname</strong> <span class='toggle-icon' id='icon_$gid'><i data-lucide='chevron-down' style='width:14px;height:14px;'></i></span><br>
                        <small style='color:var(--color-text-muted); font-size:var(--text-xs);'>$batch_cnt batch(es)</small>
                    </td>
                    <td><span style='background:var(--color-brand-light); color:var(--color-brand-dark); padding:2px 8px; border-radius:var(--radius-full); font-size:var(--text-xs); font-weight:600;'>$gcat</span></td>
                    <td style='color:var(--color-text-secondary);'>$glabel</td>
                    <td><strong style='color:var(--color-text-primary);'>$total_qty</strong> <small style='color:var(--color-text-muted);'>$gunit</small></td>
                    <td>" . ($earliest ? date('M d, Y', strtotime($earliest)) : 'N/A') . "</td>
                    <td>$status</td>
                    <td onclick='event.stopPropagation()'>
                        <a href='add.php?name=".urlencode($g['name'])."&label=".urlencode((string)$g['label'])."&type=$type&cat=".urlencode($gcat)."&unit=".urlencode($gunit)."' class='btn btn-stock'><i data-lucide='package-plus' style='width:14px;height:14px;'></i> New Batch</a>
                    </td>
                  </tr>";

            // Sub-rows for batches
            $sname = mysqli_real_escape_string($conn, $g['name']);
            $slabel = mysqli_real_escape_string($conn, (string)$g['label']);
            $batches = $conn->query("SELECT * FROM medicines WHERE name='$sname' AND label='$slabel' AND type='$type' AND is_archived = 0 ORDER BY expiration_date ASC");

            while ($b = $batches->fetch_assoc()) {
                $bid = $b['id'];
                $b_exp = $b['expiration_date'] && strtotime($b['expiration_date']) < strtotime($today);
                $b_bg = $b_exp ? "background:hsl(0, 100%, 98%);" : "background:var(--color-overlay);";
                
                $bunit = htmlspecialchars($b['unit'] ?? 'pcs');
                echo "<tr class='batch-detail-row' data-grp='$gid' id='row_$bid' style='display:none; $b_bg'>
                        <td colspan='7' style='padding:0;'>
                            <div class='batch-anim-wrapper'>
                                <div style='display:flex; justify-content:space-between; align-items:center;'>
                                    <div style='display:flex; align-items:center; gap:6px;'><i data-lucide='package' style='width:14px;height:14px;color:var(--color-text-muted);'></i> Batch #{$b['batch_number']} <small style='color:var(--color-text-muted); margin-left:10px;'>Exp: " . ($b['expiration_date'] ? date('M d, Y', strtotime($b['expiration_date'])) : 'N/A') . "</small></div>
                                    <div><strong style='color:var(--color-text-primary);'>{$b['quantity']}</strong> <small style='color:var(--color-text-muted);'>$bunit</small></div>
                                    <div style='display:flex; gap:8px;'>
                                        <a href='edit.php?id=$bid' class='btn btn-edit'><i data-lucide='edit-2' style='width:14px;height:14px;'></i> Edit</a>
                                        <button class='btn btn-delete' onclick='deleteBatch(event, $bid)'><i data-lucide='trash-2' style='width:14px;height:14px;'></i> Delete</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                      </tr>";
            }
        }
    } else {
        echo "<tr><td colspan='7' class='no-data'>No records found.</td></tr>";
    }
}
?>

<!-- Custom Delete Confirmation Modal -->
<div id="deleteModal">
    <div class="modal-content">
        <div class="modal-icon"><i data-lucide="alert-circle" style="width: 48px; height: 48px; stroke-width: 1.5;"></i></div>
        <h3 class="modal-title">Delete Batch?</h3>
        <p class="modal-text">Are you sure you want to permanently delete this batch? This action cannot be undone.</p>
        <div class="modal-actions">
            <button class="btn-modal btn-modal-cancel" onclick="closeDeleteModal()">Cancel</button>
            <button class="btn-modal btn-modal-confirm" id="confirmDeleteBtn">Yes, Delete</button>
        </div>
    </div>
</div>

<script>
    function switchTab(type) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.table-container').forEach(c => c.classList.remove('active'));

        if (type === 'medicine') {
            document.querySelector('.tab-btn:nth-child(1)').classList.add('active');
            document.getElementById('medicineView').classList.add('active');
            localStorage.setItem('activeInventoryTab', 'medicine');
        } else if (type === 'consumable') {
            document.querySelector('.tab-btn:nth-child(2)').classList.add('active');
            document.getElementById('consumableView').classList.add('active');
            localStorage.setItem('activeInventoryTab', 'consumable');
        } else if (type === 'dental') {
            document.querySelector('.tab-btn:nth-child(3)').classList.add('active');
            document.getElementById('dentalView').classList.add('active');
            localStorage.setItem('activeInventoryTab', 'dental');
        } else if (type === 'medical') {
            document.querySelector('.tab-btn:nth-child(4)').classList.add('active');
            document.getElementById('medicalView').classList.add('active');
            localStorage.setItem('activeInventoryTab', 'medical');
        }
    }

    function toggleBatch(gid) {
        const rows = document.querySelectorAll(`[data-grp="${gid}"]`);
        const icon = document.getElementById(`icon_${gid}`);
        const isOpening = rows[0].style.display === 'none';

        rows.forEach(r => {
            if (isOpening) {
                r.style.display = 'table-row';
                setTimeout(() => r.classList.add('expanded'), 10);
            } else {
                r.classList.remove('expanded');
                setTimeout(() => r.style.display = 'none', 400);
            }
        });
        
        // update lucide icon rotation
        if (icon) {
            const svg = icon.querySelector('svg');
            if (svg) {
                svg.style.transition = 'transform 0.3s';
                svg.style.transform = isOpening ? 'rotate(180deg)' : 'rotate(0deg)';
            }
        }
    }

    function showLoading() {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }

    let deleteTargetId = null;

    function deleteBatch(event, id) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        deleteTargetId = id;
        const modal = document.getElementById('deleteModal');
        modal.style.display = 'flex';
        // Small delay to allow display:flex to apply before adding opacity class
        setTimeout(() => modal.classList.add('show'), 10);
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.remove('show');
        setTimeout(() => { modal.style.display = 'none'; deleteTargetId = null; }, 300);
    }

    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (deleteTargetId) {
            closeDeleteModal();
            showLoading();
            
            // Create a hidden form to submit via POST
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'delete.php';
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = deleteTargetId;
            
            const deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'delete';
            deleteInput.value = '1';
            
            form.appendChild(idInput);
            form.appendChild(deleteInput);
            document.body.appendChild(form);
            
            form.submit();
        }
    });

    function clearExpiredBatches() {
        if (confirm("Are you sure you want to PERMANENTLY DELETE all expired batches from the database? This cannot be undone.")) {
            showLoading();
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'delete_expired.php';
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Restore last active tab on load
    window.onload = function() {
        const savedTab = localStorage.getItem('activeInventoryTab');
        if (savedTab) switchTab(savedTab);
    };

    // Ripple effect
    document.querySelectorAll('.btn, .tab-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            let x = e.clientX - e.target.getBoundingClientRect().left;
            let y = e.clientY - e.target.getBoundingClientRect().top;
            let ripples = document.createElement('span');
            ripples.className = 'ripple';
            ripples.style.left = x + 'px';
            ripples.style.top = y + 'px';
            this.appendChild(ripples);
            setTimeout(() => ripples.remove(), 600);
        });
    });
</script>

</body>
</html>