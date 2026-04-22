<?php 
require_once __DIR__ . '/header.php';
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

        .search-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
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
        .search-form button:active { transform: translateY(0); }

        .table-container {
            background: white;
            border-radius: 10px;
            overflow-x: auto;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        table { width: 100%; border-collapse: collapse; }
        th {
            background: #1f4f87;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
        }
        td { padding: 12px 15px; border-bottom: 1px solid #e0e0e0; color: #2c3e50; transition: background 0.3s ease; }

        /* Group (main) rows */
        .group-row { cursor: pointer; transition: background 0.3s ease; }
        .group-row:hover { background: #eef6ff !important; }
        .group-row:hover td { background: #eef6ff; }

        /* Batch detail sub-rows */
        .batch-detail-row td {
            font-size: 13px;
            border-bottom: 1px dashed #d0e4f7;
            padding: 0; /* Changed for animation */
            overflow: hidden;
        }
        
        /* Sub-row animation wrapper */
        .batch-anim-wrapper {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1), padding 0.4s ease;
        }
        .batch-detail-row.expanded .batch-anim-wrapper {
            max-height: 100px; /* Adjust as needed */
            padding: 8px 15px;
        }

        .toggle-icon {
            font-size: 10px;
            color: #3498db;
            margin-left: 6px;
            display: inline-block;
            transition: transform 0.3s ease;
        }
        .rotated { transform: rotate(180deg); }

        .status-expired  { background: #fee; color: #e74c3c; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; display: inline-block; }
        .status-lowstock { background: #ffeaa7; color: #f39c12; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; display: inline-block; }
        .status-ok       { background: #d5f4e6; color: #27ae60; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; display: inline-block; }
        .status-soon     { background: #fff3cd; color: #e67e22; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; display: inline-block; }

        .action-buttons { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            position: relative;
            overflow: hidden;
        }
        .btn-edit   { background: #3498db; color: white; }
        .btn-edit:hover   { background: #2980b9; transform: translateY(-1px); box-shadow: 0 4px 8px rgba(52,152,219,0.3); }
        .btn-delete { background: #e74c3c; color: white; }
        .btn-delete:hover { background: #c0392b; transform: translateY(-1px); box-shadow: 0 4px 8px rgba(231,76,60,0.3); }
        .btn-stock  { background: #8e44ad; color: white; }
        .btn-stock:hover  { background: #7d3c98; transform: translateY(-1px); box-shadow: 0 4px 8px rgba(142,68,173,0.3); }
        .btn:active { transform: translateY(0); }

        .no-data { text-align: center; padding: 40px; color: #7f8c8d; font-size: 16px; }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card { background: white; padding: 20px; border-radius: 10px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: transform 0.3s ease; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card h3 { color: #7f8c8d; font-size: 14px; margin-bottom: 10px; }
        .stat-card .number { font-size: 32px; font-weight: bold; color: #2c3e50; }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            backdrop-filter: blur(3px);
        }
        .modal-overlay.active { display: flex; opacity: 1; }
        .modal-box {
            background: white;
            border-radius: 12px;
            padding: 30px;
            width: 100%;
            max-width: 400px;
            margin: 0 20px;
            text-align: center;
            transform: scale(0.7);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        .modal-overlay.active .modal-box { transform: scale(1); }
        .modal-box p { font-size: 16px; color: #2c3e50; margin-bottom: 10px; }
        .modal-box small { font-size: 13px; color: #7f8c8d; display: block; margin-bottom: 20px; }
        .modal-buttons { display: flex; gap: 12px; justify-content: center; }
        
        .btn-cancel {
            padding: 10px 25px;
            border: 1px solid #ddd;
            background: #f8f9fa;
            color: #2c3e50;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }
        .btn-cancel:hover { background: #e9ecef; }
        .btn-confirm {
            padding: 10px 25px;
            border: none;
            background: #e74c3c;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-confirm:hover { background: #c0392b; transform: scale(1.05); }

        /* Ripple Effect */
        .ripple {
            position: absolute;
            background: rgba(255, 255, 255, 0.4);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple-animation 0.6s linear;
            pointer-events: none;
        }
        @keyframes ripple-animation {
            to { transform: scale(4); opacity: 0; }
        }

        /* Loading Spinner */
        #loadingOverlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(255,255,255,0.7);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 15px;
            backdrop-filter: blur(2px);
        }
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* Toast Notification */
        #toastContainer {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 10001;
        }
        .toast {
            background: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 10px;
            transform: translateX(120%);
            transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            border-left: 5px solid #27ae60;
        }
        .toast.show { transform: translateX(0); }
        .toast.error { border-left-color: #e74c3c; }
        .toast-icon { font-size: 20px; }

        /* Row Delete Animation */
        .row-fade-out {
            opacity: 0;
            transform: translateX(50px);
            transition: all 0.5s ease;
        }

        @media (max-width: 768px) {
            th, td { padding: 8px 10px; font-size: 12px; }
            .action-buttons { flex-direction: column; }
            .btn { text-align: center; }
        }
    </style>
</head>
<body>

<div id="loadingOverlay">
    <div class="spinner"></div>
    <p style="color: #1f4f87; font-weight: 600;">Processing...</p>
</div>

<div id="toastContainer"></div>

<div class="container">
    <div class="header-card">
        <h2>🏥 Clinic Medicine Inventory System</h2>
        <p>Medicines grouped by name · Click a row to expand individual batches · FIFO dispensing enabled</p>
    </div>

    <?php
    require_once __DIR__ . '/db.php';

    $total_meds      = $conn->query("SELECT COUNT(DISTINCT CONCAT(name,'|',IFNULL(label,''))) AS c FROM medicines")->fetch_assoc()['c'];
    $expired_batches = $conn->query("SELECT COUNT(*) AS c FROM medicines WHERE expiration_date < CURDATE() AND quantity > 0")->fetch_assoc()['c'];
    $low_stock       = $conn->query("SELECT COUNT(*) AS c FROM (SELECT name, label, SUM(quantity) AS tq FROM medicines GROUP BY name, label HAVING tq <= 5) x")->fetch_assoc()['c'];
    ?>

    <div class="stats">
        <div class="stat-card">
            <h3>Total Medicines</h3>
            <div class="number"><?php echo (int)$total_meds; ?></div>
        </div>
        <div class="stat-card">
            <h3>Expired Batches</h3>
            <div class="number" style="color:#e74c3c;"><?php echo (int)$expired_batches; ?></div>
        </div>
        <div class="stat-card">
            <h3>Low Stock Items</h3>
            <div class="number" style="color:#f39c12;"><?php echo (int)$low_stock; ?></div>
        </div>
    </div>

    <div class="search-section">
        <form method="GET" class="search-form" onsubmit="showLoading()">
            <input type="text" name="search"
                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                placeholder="🔍 Search medicine by name or description...">
            <button type="submit">Search</button>
            <?php if(!empty($_GET['search'])): ?>
                <a href="index.php" class="btn" style="background:#95a5a6; color:white; padding: 12px 30px; border-radius: 8px;">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-container">
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
            <?php
            $today = date("Y-m-d");
            $soon  = date("Y-m-d", strtotime('+30 days'));

            if(!empty($_GET['search'])) {
                $s = mysqli_real_escape_string($conn, $_GET['search']);
                $groups = $conn->query("
                    SELECT name, label,
                           SUM(quantity)        AS total_qty,
                           MIN(expiration_date) AS earliest_exp,
                           COUNT(*)             AS batch_count
                    FROM medicines
                    WHERE name LIKE '%$s%' OR label LIKE '%$s%'
                    GROUP BY name, label
                    ORDER BY name ASC");
            } else {
                $groups = $conn->query("
                    SELECT name, label,
                           SUM(quantity)        AS total_qty,
                           MIN(expiration_date) AS earliest_exp,
                           COUNT(*)             AS batch_count
                    FROM medicines
                    GROUP BY name, label
                    ORDER BY name ASC");
            }

            if ($groups && $groups->num_rows > 0) {
                $gi = 0;
                while ($g = $groups->fetch_assoc()) {
                    $gi++;
                    $gid        = "grp_{$gi}";
                    $gname      = htmlspecialchars($g['name'],  ENT_QUOTES, 'UTF-8');
                    $glabel     = htmlspecialchars((string)$g['label'], ENT_QUOTES, 'UTF-8');
                    $total_qty  = (int)$g['total_qty'];
                    $earliest   = $g['earliest_exp'];
                    $batch_cnt  = (int)$g['batch_count'];

                    $is_expired = $earliest && strtotime($earliest) < strtotime($today);
                    $is_soon    = !$is_expired && $earliest && strtotime($earliest) < strtotime($soon);
                    $is_low     = $total_qty <= 5;

                    $status = '';
                    if ($is_expired) $status .= "<span class='status-expired'>⚠️ EXPIRED BATCH</span> ";
                    if ($is_soon)    $status .= "<span class='status-soon'>📅 EXPIRING SOON</span> ";
                    if ($is_low)     $status .= "<span class='status-lowstock'>📉 LOW STOCK</span>";
                    if (!$is_expired && !$is_soon && !$is_low) $status = "<span class='status-ok'>✓ OK</span>";

                    $row_bg    = $is_expired ? "background:#fff5f5;" : "";
                    $qty_color = $is_low ? "#e67e22" : "#2c3e50";
                    $exp_disp  = $earliest ? date('M d, Y', strtotime($earliest)) : 'N/A';

                    $name_enc  = urlencode($g['name']);
                    $label_enc = urlencode((string)$g['label']);

                    echo "<tr class='group-row' onclick='toggleBatch(\"{$gid}\")' style='{$row_bg}'>
                        <td>
                            <strong>{$gname}</strong>
                            <span class='toggle-icon' id='icon_{$gid}'>▼</span>
                            <br>
                            <small style='color:#7f8c8d; font-size:11px;'>{$batch_cnt} batch(es) · click to expand</small>
                        </td>
                        <td>{$glabel}</td>
                        <td><strong style='color:{$qty_color};'>{$total_qty}</strong></td>
                        <td>{$exp_disp}</td>
                        <td>{$status}</td>
                        <td class='action-buttons' onclick='event.stopPropagation()'>
                            <a href='add.php?name={$name_enc}&label={$label_enc}' class='btn btn-stock'>📦 New Batch</a>
                        </td>
                    </tr>";

                    // Individual batch sub-rows
                    $sname  = mysqli_real_escape_string($conn, $g['name']);
                    $slabel = mysqli_real_escape_string($conn, (string)$g['label']);
                    $batches = $conn->query("SELECT * FROM medicines WHERE name='{$sname}' AND label='{$slabel}' ORDER BY expiration_date ASC");

                    while ($b = $batches->fetch_assoc()) {
                        $bid       = $b['id'];
                        $bexp      = $b['expiration_date'];
                        $b_exp     = $bexp && strtotime($bexp) < strtotime($today);
                        $b_soon    = !$b_exp && $bexp && strtotime($bexp) < strtotime($soon);
                        $bexp_disp = $bexp ? date('M d, Y', strtotime($bexp)) : 'N/A';
                        $bexp_clr  = $b_exp ? '#e74c3c' : ($b_soon ? '#e67e22' : '#27ae60');
                        $bexp_ico  = $b_exp ? '⚠️' : ($b_soon ? '📅' : '✓');
                        $b_bg      = $b_exp ? 'background:#fff8f8;' : 'background:#f5faff;';
                        $bcname    = htmlspecialchars($b['name'], ENT_QUOTES, 'UTF-8');
                        $b_added   = date('M d, Y', strtotime($b['created_at']));

                        echo "<tr class='batch-detail-row' data-grp='{$gid}' id='row_{$bid}' style='display:none; {$b_bg}'>
                            <td colspan='6' style='padding:0;'>
                                <div class='batch-anim-wrapper'>
                                    <div style='display:flex; align-items:center; justify-content:space-between;'>
                                        <div style='flex:1;'>
                                            📦 <strong>Batch #{$b['batch_number']}</strong> &nbsp;·&nbsp; Added: {$b_added}
                                        </div>
                                        <div style='flex:1; text-align:center;'>
                                            <strong>{$b['quantity']}</strong> units
                                        </div>
                                        <div style='flex:1; text-align:center; color:{$bexp_clr}; font-weight:600;'>
                                            {$bexp_ico} {$bexp_disp}
                                        </div>
                                        <div class='action-buttons' style='flex:1; justify-content:flex-end;'>
                                            <a href='edit.php?id={$bid}' class='btn btn-edit'>✏️ Edit</a>
                                            <button type='button' class='btn btn-delete'
                                                onclick=\"openModal({$bid}, '{$bcname}')\">🗑️ Delete</button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>";
                    }
                }
            } else {
                echo "<tr><td colspan='6' class='no-data'>📭 No medicines found in inventory</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <p>Delete this batch of</p>
        <p><strong id="modalName"></strong>?</p>
        <small>This will remove this specific batch entry. This cannot be undone.</small>
        <div class="modal-buttons">
            <button class="btn-cancel" onclick="closeModal()">Cancel</button>
            <form id="deleteForm" method="POST" action="delete.php" style="margin:0;">
                <input type="hidden" name="id" id="modalId">
                <button type="submit" name="delete" class="btn-confirm" onclick="confirmDelete(event)">Yes, Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
    // Ripple Effect
    document.querySelectorAll('.btn, .search-form button').forEach(button => {
        button.addEventListener('click', function(e) {
            let x = e.clientX - e.target.getBoundingClientRect().left;
            let y = e.clientY - e.target.getBoundingClientRect().top;
            let ripples = document.createElement('span');
            ripples.className = 'ripple';
            ripples.style.left = x + 'px';
            ripples.style.top = y + 'px';
            this.appendChild(ripples);
            setTimeout(() => { ripples.remove() }, 600);
        });
    });

    function showLoading() {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }

    function showToast(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast ${type === 'error' ? 'error' : ''}`;
        toast.innerHTML = `
            <span class="toast-icon">${type === 'success' ? '✅' : '❌'}</span>
            <span>${message}</span>
        `;
        container.appendChild(toast);
        setTimeout(() => { toast.classList.add('show'); }, 100);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => { toast.remove(); }, 400);
        }, 4000);
    }

    function toggleBatch(id) {
        const rows = document.querySelectorAll('[data-grp="' + id + '"]');
        const icon = document.getElementById('icon_' + id);
        const isExpanding = rows[0].style.display === 'none';

        rows.forEach(r => {
            if (isExpanding) {
                r.style.display = 'table-row';
                setTimeout(() => r.classList.add('expanded'), 10);
            } else {
                r.classList.remove('expanded');
                setTimeout(() => { r.style.display = 'none'; }, 400);
            }
        });

        if (icon) {
            if (isExpanding) icon.classList.add('rotated');
            else icon.classList.remove('rotated');
        }
    }

    function openModal(id, name) {
        document.getElementById('modalId').value = id;
        document.getElementById('modalName').textContent = name;
        document.getElementById('deleteModal').classList.add('active');
    }

    function closeModal() {
        document.getElementById('deleteModal').classList.remove('active');
    }

    function confirmDelete(e) {
        e.preventDefault();
        const id = document.getElementById('modalId').value;
        const row = document.getElementById('row_' + id);
        
        closeModal();
        if (row) {
            row.classList.add('row-fade-out');
            setTimeout(() => {
                showLoading();
                document.getElementById('deleteForm').submit();
            }, 500);
        } else {
            showLoading();
            document.getElementById('deleteForm').submit();
        }
    }

    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    // Check for success/error in URL and show toast
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('msg')) {
        showToast(urlParams.get('msg'), urlParams.get('type') || 'success');
    }
</script>

</body>
</html>