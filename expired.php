<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/header.php';

$today_date = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expired Batches Archive</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            min-height: 100vh;
        }
        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .header-card {
            background: white; border-radius: 15px; padding: 30px;
            margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }
        .header-card .icon { font-size: 50px; margin-bottom: 10px; }
        .header-card h2 { color: #e74c3c; font-size: 28px; margin-bottom: 10px; }
        .header-card p  { color: #7f8c8d; font-size: 14px; }
        
        .action-bar {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 20px;
        }
        .btn {
            padding: 10px 20px; border: none; border-radius: 8px;
            font-size: 14px; font-weight: 600; cursor: pointer;
            transition: all 0.2s ease; text-decoration: none; display: inline-block;
        }
        .btn-back { background: #95a5a6; color: white; }
        .btn-back:hover { background: #7f8c8d; }
        .btn-clear { background: #c0392b; color: white; }
        .btn-clear:hover { background: #a93226; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(192,57,43,0.3); }

        .table-container {
            background: white; border-radius: 10px;
            overflow-x: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        table { width: 100%; border-collapse: collapse; }
        th {
            background: #e74c3c; color: white; padding: 15px;
            text-align: left; font-weight: 600; font-size: 14px;
        }
        td { padding: 12px 15px; border-bottom: 1px solid #e0e0e0; color: #2c3e50; font-size: 14px; }
        tr:hover { background: #fdf2f2; }
        
        .no-data { text-align: center; padding: 60px; color: #7f8c8d; }
        .no-data .icon { font-size: 48px; margin-bottom: 15px; }
        
        /* Modal */
        #loadingOverlay {
            display: none; position: fixed; inset: 0;
            background: rgba(255,255,255,0.7); z-index: 9999;
            align-items: center; justify-content: center; backdrop-filter: blur(2px);
        }
        .spinner {
            width: 40px; height: 40px; border: 4px solid #f3f3f3;
            border-top: 4px solid #e74c3c; border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>

<div id="loadingOverlay"><div class="spinner"></div></div>

<div class="container">
    <div class="header-card">
        <div class="icon">⚠️</div>
        <h2>Archived Expired Batches</h2>
        <p>These items have been automatically hidden from the main inventory because their expiration date has passed.</p>
    </div>

    <div class="action-bar">
        <a href="index.php" class="btn btn-back">⬅️ Back</a>
        <?php
        $check_q = $conn->query("SELECT COUNT(*) as c FROM medicines WHERE is_archived = 1 AND expiration_date < '$today_date'");
        if ($check_q && $check_q->fetch_assoc()['c'] > 0): 
        ?>
            <button onclick="clearExpiredBatches()" class="btn btn-clear">🗑️ Clear All Permanently</button>
        <?php endif; ?>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Batch #</th>
                    <th>Quantity</th>
                    <th>Expired On</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $q = $conn->query("SELECT * FROM medicines WHERE is_archived = 1 AND expiration_date < '$today_date' ORDER BY expiration_date DESC");
                if ($q && $q->num_rows > 0) {
                    while ($row = $q->fetch_assoc()) {
                        $name = htmlspecialchars($row['name']);
                        $cat = htmlspecialchars($row['category']);
                        $desc = htmlspecialchars((string)$row['label']);
                        $bn = $row['batch_number'];
                        $qty = $row['quantity'] . ' ' . htmlspecialchars($row['unit']);
                        $exp = date('M d, Y', strtotime($row['expiration_date']));
                        $id = $row['id'];
                        
                        echo "<tr>
                                <td><strong>$name</strong></td>
                                <td>$cat</td>
                                <td>$desc</td>
                                <td>#$bn</td>
                                <td>$qty</td>
                                <td style='color:#c0392b; font-weight:bold;'>$exp</td>
                                <td>
                                    <form method='POST' action='delete_expired.php' style='display:inline;' class='delete-single-form'>
                                        <input type='hidden' name='single_id' value='$id'>
                                        <button type='button' class='delete-btn' style='background:none;border:none;color:#e74c3c;cursor:pointer;font-size:16px;' title='Delete Item'>🗑️</button>
                                    </form>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='no-data'>
                            <div class='icon'>✨</div>
                            <div>No expired batches found!</div>
                          </td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    async function clearExpiredBatches() {
        const confirmed = await showConfirm("Delete All Expired?", "Are you sure you want to PERMANENTLY DELETE all expired batches from the database? This cannot be undone.");
        if (confirmed) {
            document.getElementById('loadingOverlay').style.display = 'flex';
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'delete_expired.php';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'clear_all';
            input.value = '1';
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    }

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const confirmed = await showConfirm("Confirm Action", "Permanently delete this specific batch?");
            if (confirmed) {
                this.closest('form').submit();
            }
        });
    });
</script>

</body>
</html>
