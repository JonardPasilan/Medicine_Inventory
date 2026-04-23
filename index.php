<?php 
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/db.php';

// Handle search query
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
?>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .header-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
        }
        .header-card h2 { color: #2c3e50; font-size: 28px; margin-bottom: 8px; }
        .header-card p  { color: #7f8c8d; font-size: 14px; }

        /* Search Section */
        .search-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .search-form { display: flex; gap: 10px; flex-wrap: wrap; }
        .search-form input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .search-form input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
        }
        .search-form button {
            padding: 12px 30px;
            background: #1f4f87;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .search-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(31,79,135,0.4);
        }

        /* Tabs Section */
        .tabs-container {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            justify-content: center;
        }
        .tab-btn {
            padding: 12px 25px;
            background: white;
            border: 2px solid transparent;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            color: #7f8c8d;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .tab-btn:hover { background: #f8f9fa; color: #1f4f87; }
        .tab-btn.active {
            background: #1f4f87;
            color: white;
            box-shadow: 0 4px 10px rgba(31,79,135,0.3);
        }

        .table-container {
            background: white;
            border-radius: 10px;
            overflow-x: auto;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            display: none; /* Hidden by default, JS will show active */
        }
        .table-container.active { display: block; }

        table { width: 100%; border-collapse: collapse; }
        th {
            background: #1f4f87;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }
        td { padding: 12px 15px; border-bottom: 1px solid #e0e0e0; color: #2c3e50; }

        /* Group Rows */
        .group-row { cursor: pointer; transition: background 0.2s; }
        .group-row:hover { background: #f0f7ff !important; }

        /* Batch details */
        .batch-detail-row td {
            font-size: 13px;
            border-bottom: 1px dashed #d0e4f7;
            padding: 0;
            overflow: hidden;
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
            font-size: 10px;
            color: #3498db;
            margin-left: 6px;
            display: inline-block;
            transition: transform 0.3s;
        }
        .toggle-icon.rotated { transform: rotate(180deg); }

        /* Status Badges */
        .status-expired  { background: #fee; color: #e74c3c; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; }
        .status-lowstock { background: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; }
        .status-ok       { background: #d5f4e6; color: #27ae60; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
            position: relative;
            overflow: hidden;
        }
        .btn-edit   { background: #3498db; color: white; min-width: 90px; text-align: center; }
        .btn-delete { background: #e74c3c; color: white; min-width: 90px; text-align: center; }
        .btn-stock  { background: #8e44ad; color: white; }
        .btn:hover { opacity: 0.9; transform: translateY(-1px); }

        /* Ripple Effect */
        .ripple {
            position: absolute;
            background: rgba(255,255,255,0.4);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple-animation 0.6s linear;
            pointer-events: none;
        }
        @keyframes ripple-animation { to { transform: scale(4); opacity: 0; } }

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
            border: 4px solid #f3f3f3;
            border-top: 4px solid #1f4f87;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        .no-data { text-align: center; padding: 40px; color: #7f8c8d; font-size: 15px; }

        @media (max-width: 768px) {
            th, td { padding: 10px; font-size: 12px; }
            .tab-btn { padding: 10px 15px; font-size: 13px; }
        }
    </style>
</head>
<body>

<div id="loadingOverlay">
    <div class="spinner"></div>
</div>

<div class="container">
    <div class="header-card">
        <h2>🏥 Clinic Inventory Management</h2>
        <p>Manage medicines and consumables in one place</p>
    </div>

    <!-- Search Section -->
    <div class="search-section">
        <form method="GET" class="search-form" onsubmit="showLoading()">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="🔍 Search by name or description...">
            <button type="submit">Search</button>
            <?php if ($search): ?>
                <a href="index.php" class="btn" style="background:#95a5a6; color:white; padding:12px 20px; border-radius:8px;">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Tabs -->
    <div class="tabs-container">
        <button class="tab-btn active" onclick="switchTab('medicine')">💊 Medicines</button>
        <button class="tab-btn" onclick="switchTab('consumable')">🧴 Consumables</button>
    </div>

    <!-- MEDICINES VIEW -->
    <div id="medicineView" class="table-container active">
        <table>
            <thead>
                <tr>
                    <th>Medicine Name</th>
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
</div>

<?php
/**
 * Helper to render the grouped inventory table rows
 */
function renderInventoryTable($conn, $type, $search) {
    $today = date("Y-m-d");
    $soon  = date("Y-m-d", strtotime('+30 days'));

    $where = "WHERE type = '$type'";
    if ($search) {
        $where .= " AND (name LIKE '%$search%' OR label LIKE '%$search%')";
    }

    $groups = $conn->query("
        SELECT name, label, 
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

            $is_exp = $earliest && strtotime($earliest) < strtotime($today);
            $is_soon = !$is_exp && $earliest && strtotime($earliest) < strtotime($soon);
            $is_low = $total_qty <= 5;

            $status = '';
            if ($is_exp) $status .= "<span class='status-expired'>⚠️ EXPIRED</span> ";
            if ($is_soon) $status .= "<span class='status-lowstock'>📅 SOON</span> ";
            if ($is_low) $status .= "<span class='status-lowstock'>📉 LOW</span> ";
            if (!$is_exp && !$is_soon && !$is_low) $status = "<span class='status-ok'>✓ OK</span>";

            $row_style = $is_exp ? "background:#fff5f5;" : "";
            
            echo "<tr class='group-row' onclick='toggleBatch(\"$gid\")' style='$row_style'>
                    <td>
                        <strong>$gname</strong> <span class='toggle-icon' id='icon_$gid'>▼</span><br>
                        <small style='color:#7f8c8d; font-size:11px;'>$batch_cnt batch(es)</small>
                    </td>
                    <td>$glabel</td>
                    <td><strong>$total_qty</strong></td>
                    <td>" . ($earliest ? date('M d, Y', strtotime($earliest)) : 'N/A') . "</td>
                    <td>$status</td>
                    <td onclick='event.stopPropagation()'>
                        <a href='add.php?name=".urlencode($g['name'])."&label=".urlencode((string)$g['label'])."&type=$type' class='btn btn-stock'>📦 New Batch</a>
                    </td>
                  </tr>";

            // Sub-rows for batches
            $sname = mysqli_real_escape_string($conn, $g['name']);
            $slabel = mysqli_real_escape_string($conn, (string)$g['label']);
            $batches = $conn->query("SELECT * FROM medicines WHERE name='$sname' AND label='$slabel' AND type='$type' ORDER BY expiration_date ASC");

            while ($b = $batches->fetch_assoc()) {
                $bid = $b['id'];
                $b_exp = $b['expiration_date'] && strtotime($b['expiration_date']) < strtotime($today);
                $b_bg = $b_exp ? "background:#fffafa;" : "background:#f9f9f9;";
                
                echo "<tr class='batch-detail-row' data-grp='$gid' id='row_$bid' style='display:none; $b_bg'>
                        <td colspan='6' style='padding:0;'>
                            <div class='batch-anim-wrapper'>
                                <div style='display:flex; justify-content:space-between; align-items:center;'>
                                    <div>📦 Batch #{$b['batch_number']} <small style='color:#7f8c8d; margin-left:10px;'>Exp: " . ($b['expiration_date'] ? date('M d, Y', strtotime($b['expiration_date'])) : 'N/A') . "</small></div>
                                    <div><strong>{$b['quantity']}</strong> units</div>
                                    <div style='display:flex; gap:8px;'>
                                        <a href='edit.php?id=$bid' class='btn btn-edit'>✏️ Edit</a>
                                        <button class='btn btn-delete' onclick='deleteBatch($bid)'>🗑️ Delete</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                      </tr>";
            }
        }
    } else {
        echo "<tr><td colspan='6' class='no-data'>No records found for " . ($type == 'medicine' ? 'medicines' : 'consumables') . ".</td></tr>";
    }
}
?>

<script>
    function switchTab(type) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.table-container').forEach(c => c.classList.remove('active'));

        if (type === 'medicine') {
            document.querySelector('.tab-btn:nth-child(1)').classList.add('active');
            document.getElementById('medicineView').classList.add('active');
            localStorage.setItem('activeInventoryTab', 'medicine');
        } else {
            document.querySelector('.tab-btn:nth-child(2)').classList.add('active');
            document.getElementById('consumableView').classList.add('active');
            localStorage.setItem('activeInventoryTab', 'consumable');
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
        icon.classList.toggle('rotated', isOpening);
    }

    function showLoading() {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }

    function deleteBatch(id) {
        if (confirm("Are you sure you want to delete this batch?")) {
            showLoading();
            // In a real app, you'd use AJAX here or a form submit. 
            // For now, redirecting to delete.php with id
            window.location.href = `delete.php?id=${id}`;
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