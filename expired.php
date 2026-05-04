<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/header.php';

$today_date = date('Y-m-d');
?>
<style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--color-canvas);
            min-height: 100vh;
        }
        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .header-card {
            background: var(--color-surface);
            border-radius: var(--radius-lg); padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--color-border);
            text-align: center;
        }
        .header-card .icon { font-size: 50px; margin-bottom: 10px; }
        .header-card h2 { color: hsl(0, 70%, 45%); font-size: 28px; margin-bottom: 10px; }
        .header-card p  { color: var(--color-text-secondary); font-size: 14px; }
        
        .action-bar {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 20px;
        }
        .btn {
            padding: 10px 20px; border: none; border-radius: var(--radius-sm);
            font-size: 14px; font-weight: 600; cursor: pointer;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s ease; text-decoration: none; display: inline-block;
        }
        .btn-back {
            background: var(--color-overlay); color: var(--color-text-secondary);
            border: 1px solid var(--color-border);
        }
        .btn-back:hover { background: var(--color-border); color: var(--color-text-primary); }
        .btn-clear { background: hsl(0, 65%, 45%); color: white; }
        .btn-clear:hover { background: hsl(0, 65%, 38%); transform: translateY(-2px); box-shadow: 0 4px 10px rgba(192,57,43,0.3); }

        .table-container {
            background: var(--color-surface);
            border-radius: var(--radius-md);
            overflow-x: auto;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--color-border);
        }
        table { width: 100%; border-collapse: collapse; }
        th {
            background: hsl(0, 65%, 45%); color: white; padding: 15px;
            text-align: left; font-weight: 600; font-size: 14px;
        }
        td { padding: 12px 15px; border-bottom: 1px solid var(--color-border); color: var(--color-text-primary); font-size: 14px; }
        tr:hover { background: var(--color-overlay); }
        
        .no-data { text-align: center; padding: 60px; color: var(--color-text-muted); }
        .no-data .icon { font-size: 48px; margin-bottom: 15px; }
        
        /* Modal */
        #loadingOverlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.4); z-index: 9999;
            align-items: center; justify-content: center; backdrop-filter: blur(2px);
        }
        .spinner {
            width: 40px; height: 40px;
            border: 4px solid var(--color-border);
            border-top: 4px solid hsl(0, 65%, 45%);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>

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
