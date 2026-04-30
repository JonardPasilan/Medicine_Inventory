<?php 
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/db.php';

if (!isset($_GET['id'])) {
    header("Location: borrowers_slip.php");
    exit();
}

$id = intval($_GET['id']);

// Handle item return
if (isset($_POST['return_item_id'])) {
    $item_id = intval($_POST['return_item_id']);
    $now = date('Y-m-d H:i:s');
    
    // Get item info
    $item_q = $conn->query("SELECT * FROM borrower_slip_items WHERE id = $item_id");
    if ($item_q && $item_q->num_rows > 0) {
        $item_data = $item_q->fetch_assoc();
        $qty = $item_data['quantity'];
        $desc = mysqli_real_escape_string($conn, $item_data['item_description']);
        
        // Update item returned date
        $conn->query("UPDATE borrower_slip_items SET date_returned = '$now' WHERE id = $item_id");
        
        // Find equipment by name/description to restore quantity
        $conn->query("UPDATE medicines SET 
                      qty_serviceable = qty_serviceable + $qty, 
                      quantity = quantity + $qty 
                      WHERE name = '$desc' AND type IN ('dental', 'medical') 
                      LIMIT 1");
                      
        header("Location: view_borrower_slip.php?id=$id&returned=1");
        exit();
    }
}

$res = $conn->query("SELECT * FROM borrowers_slips WHERE id = $id");
if (!$res || $res->num_rows == 0) {
    header("Location: borrowers_slip.php");
    exit();
}
$slip = $res->fetch_assoc();

$items_res = $conn->query("SELECT * FROM borrower_slip_items WHERE slip_id = $id");
?>

<style>
    .container {
        max-width: 900px;
        margin: 30px auto;
        padding: 0 20px;
    }
    
    .print-area {
        background: white;
        padding: 50px;
        border: 1px solid var(--color-border);
        box-shadow: var(--shadow-md);
        position: relative;
        color: black;
    }
    
    .slip-header {
        text-align: center;
        margin-bottom: 30px;
    }
    .slip-header h1 {
        font-size: 20px;
        margin-bottom: 5px;
        text-transform: uppercase;
    }
    .slip-header p {
        font-size: 14px;
        color: #555;
    }
    
    .slip-title {
        text-align: center;
        background: #f0f0f0;
        padding: 10px;
        font-weight: bold;
        margin: 20px 0;
        border: 1px solid #ccc;
        font-size: 18px;
    }
    
    .meta-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
    }
    .meta-item {
        border-bottom: 1px solid #ddd;
        padding: 5px 0;
        display: flex;
        justify-content: space-between;
    }
    .meta-label {
        font-weight: bold;
        color: #444;
    }
    
    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 50px;
    }
    .items-table th, .items-table td {
        border: 1px solid #000;
        padding: 10px;
        text-align: left;
        font-size: 13px;
    }
    .items-table th {
        background: #f5f5f5;
    }
    
    .signatures {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 100px;
        margin-top: 50px;
    }
    .sig-box {
        text-align: center;
    }
    .sig-line {
        border-top: 1px solid black;
        margin-top: 40px;
        padding-top: 5px;
        font-weight: bold;
    }
    
    .actions {
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
    }
    .btn {
        padding: 10px 20px;
        border-radius: var(--radius-sm);
        cursor: pointer;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }
    .btn-print {
        background: #333;
        color: white;
    }
    .btn-back {
        background: var(--color-overlay);
        color: var(--color-text-primary);
        border: 1px solid var(--color-border);
    }
    
    @media print {
        body * {
            visibility: hidden;
        }
        .print-area, .print-area * {
            visibility: visible;
        }
        .print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            border: none;
            box-shadow: none;
            padding: 20px;
        }
        .actions {
            display: none;
        }
        .nav, .topbar {
            display: none !important;
        }
    }
</style>

<div class="container">
    <div class="actions">
        <a href="borrowers_slip.php" class="btn btn-back">
            <i data-lucide="arrow-left" style="width: 18px; height: 18px;"></i> Back to List
        </a>
        <button onclick="window.print()" class="btn btn-print">
            <i data-lucide="printer" style="width: 18px; height: 18px;"></i> Print Slip
        </button>
    </div>

    <div class="print-area">
        <div class="slip-header">
            <h1>Republic of the Philippines</h1>
            <p>Department of Education</p>
            <p>Region Office</p>
            <p>Division Office</p>
        </div>
        
        <div class="slip-title">BORROWER'S SLIP FOR MEDICAL EQUIPMENT</div>
        
        <div class="meta-grid">
            <div>
                <div class="meta-item">
                    <span class="meta-label">Borrower Name:</span>
                    <span><?php echo htmlspecialchars($slip['borrower_name']); ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Category:</span>
                    <span><?php echo htmlspecialchars($slip['category']); ?></span>
                </div>
            </div>
            <div>
                <div class="meta-item">
                    <span class="meta-label">Date Created:</span>
                    <span><?php echo date('M d, Y', strtotime($slip['created_at'])); ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Availability:</span>
                    <span><?php echo htmlspecialchars($slip['availability']); ?></span>
                </div>
            </div>
        </div>
        
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50px;">No.</th>
                    <th style="width: 80px;">Qty</th>
                    <th>Item Description</th>
                    <th>Date/Time Released</th>
                    <th>Date/Time Returned</th>
                    <th>Remarks/Purpose</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $i = 1;
                while ($item = $items_res->fetch_assoc()) {
                    $released = $item['date_released'] ? date('m/d/Y H:i', strtotime($item['date_released'])) : '-';
                    $returned = $item['date_returned'] ? date('m/d/Y H:i', strtotime($item['date_returned'])) : '-';
                    $btn_html = "";
                    if (!$item['date_returned']) {
                        $btn_html = "<form method='POST' style='display:inline;' class='return-form'>
                                        <input type='hidden' name='return_item_id' value='{$item['id']}'>
                                        <button type='button' class='btn-return return-btn' style='padding:2px 6px; font-size:10px; cursor:pointer; background:#27ae60; color:white; border:none; border-radius:3px;'>Return</button>
                                     </form>";
                    }
                    echo "<tr>
                        <td>" . htmlspecialchars($item['item_no'] ?: $i) . "</td>
                        <td>{$item['quantity']}</td>
                        <td>" . htmlspecialchars($item['item_description']) . "</td>
                        <td>$released</td>
                        <td>$returned $btn_html</td>
                        <td>" . htmlspecialchars($item['remarks_purpose']) . "</td>
                    </tr>";
                    $i++;
                }
                ?>
            </tbody>
        </table>
        
        <div class="signatures">
            <div class="sig-box">
                <div class="sig-line">Borrower's Signature Over Printed Name</div>
            </div>
            <div class="sig-box">
                <div class="sig-line">Approved By: (Clinic In-Charge)</div>
            </div>
        </div>
        
        <div style="margin-top: 50px; font-size: 11px; color: #777; text-align: right;">
            Printed on <?php echo date('M d, Y h:i A'); ?>
        </div>
    </div>
</div>

<script>
    lucide.createIcons();

    document.querySelectorAll('.return-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const confirmed = await showConfirm("Return Item?", "Are you sure you want to mark this item as returned? This will restore the quantity to inventory.");
            if (confirmed) {
                this.closest('form').submit();
            }
        });
    });
</script>
</body>
</html>
