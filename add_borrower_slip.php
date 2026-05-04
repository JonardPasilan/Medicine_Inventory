<?php 
require_once __DIR__ . '/db.php';

$status_msg = '';
$status_type = 'success';

if (isset($_POST['save_slip'])) {
    $borrower_name = mysqli_real_escape_string($conn, $_POST['borrower_name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $availability = mysqli_real_escape_string($conn, $_POST['availability']);
    
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO borrowers_slips (borrower_name, category, availability) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $borrower_name, $category, $availability);
        $stmt->execute();
        $slip_id = $conn->insert_id;
        
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            $item_stmt = $conn->prepare("INSERT INTO borrower_slip_items (slip_id, item_no, quantity, item_description, date_released, date_returned, remarks_purpose) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($_POST['items'] as $item) {
                $item_no = $item['item_no'];
                $qty = intval($item['quantity']);
                $desc = $item['description'];
                $released = !empty($item['released']) ? $item['released'] : null;
                $returned = !empty($item['returned']) ? $item['returned'] : null;
                $remarks = $item['remarks'];
                
                $item_stmt->bind_param("isissss", $slip_id, $item_no, $qty, $desc, $released, $returned, $remarks);
                $item_stmt->execute();
            }
        }
        
        $conn->commit();
        header("Location: borrowers_slip.php?saved=1");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $status_msg = "Error: " . $e->getMessage();
        $status_type = 'error';
    }
}

require_once __DIR__ . '/header.php';

// Fetch available equipment from inventory for dropdown
$equipment_list = $conn->query("
    SELECT id, name, brand_serial, qty_serviceable
    FROM medicines
    WHERE type IN ('dental', 'medical') AND is_archived = 0
    ORDER BY name ASC
");
$equipment_options = [];
if ($equipment_list) {
    while ($eq = $equipment_list->fetch_assoc()) {
        $equipment_options[] = $eq;
    }
}
?>

<style>
    .container {
        max-width: 1000px;
        margin: 30px auto;
        padding: 0 20px;
    }
    .form-card {
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        padding: 30px;
        box-shadow: var(--shadow-md);
    }
    .form-header {
        margin-bottom: 25px;
        border-bottom: 2px solid var(--color-brand-light);
        padding-bottom: 15px;
    }
    .grid-header {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
    }
    .form-group {
        margin-bottom: 15px;
    }
    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        font-size: var(--text-sm);
        color: var(--color-text-secondary);
    }
    input, select, textarea {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--color-border);
        border-radius: var(--radius-sm);
        background: var(--color-overlay);
        font-family: inherit;
        font-size: var(--text-sm);
    }
    input:focus, select:focus, textarea:focus {
        outline: none;
        border-color: var(--color-brand);
        box-shadow: 0 0 0 2px var(--color-brand-light);
    }
    
    .items-table {
        width: 100%;
        margin-top: 20px;
        border-collapse: collapse;
    }
    .items-table th {
        background: var(--color-overlay);
        padding: 10px;
        text-align: left;
        font-size: 11px;
        text-transform: uppercase;
        border: 1px solid var(--color-border);
    }
    .items-table td {
        padding: 8px;
        border: 1px solid var(--color-border);
    }
    
    .btn-add-row {
        margin-top: 15px;
        background: white;
        border: 1px dashed var(--color-brand);
        color: var(--color-brand);
        padding: 8px 15px;
        border-radius: var(--radius-sm);
        cursor: pointer;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 5px;
        width: fit-content;
    }
    .btn-add-row:hover {
        background: var(--color-brand-light);
    }
    
    .btn-save {
        margin-top: 30px;
        background: var(--color-brand);
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: var(--radius-sm);
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        width: 100%;
        justify-content: center;
    }
    .btn-save:hover {
        background: var(--color-brand-dark);
    }
    
    .remove-row {
        color: #e53935;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

<div class="container">
    <div class="form-card">
        <form method="POST" id="slipForm">
            <div class="form-header">
                <h2 style="color: var(--color-brand-dark);"><i data-lucide="file-text"></i> Borrower's Slip for Medical Equipment</h2>
            </div>
            
            <?php if ($status_msg): ?>
                <div style="padding: 10px; background: #ffebee; color: #c62828; border-radius: 4px; margin-bottom: 20px;">
                    <?php echo $status_msg; ?>
                </div>
            <?php endif; ?>

            <div class="grid-header">
                <div class="form-group">
                    <label>Borrower Name</label>
                    <input type="text" name="borrower_name" required placeholder="Full Name">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" required>
                        <option value="Student">Student</option>
                        <option value="Personnel">Personnel</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Availability</label>
                    <select name="availability" required>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table class="items-table" id="itemsTable">
                    <thead>
                        <tr>
                            <th style="width: 100px;">Item No</th>
                            <th style="width: 80px;">Qty</th>
                            <th>Item Description</th>
                            <th>Date/Time Released</th>
                            <th>Date/Time Returned</th>
                            <th>Remarks/Purpose</th>
                            <th style="width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="text" name="items[0][item_no]"></td>
                            <td><input type="number" name="items[0][quantity]" value="1" min="1"></td>
                        <td>
                            <select name="items[0][description]" required style="width:100%; padding:6px; border:1px solid var(--color-border); border-radius:var(--radius-sm); font-size:var(--text-sm);">
                                <option value="">-- Select Equipment --</option>
                                <?php foreach ($equipment_options as $eq): ?>
                                    <option value="<?php echo htmlspecialchars($eq['name']); ?>">
                                        <?php echo htmlspecialchars($eq['name']); ?>
                                        <?php if (!empty($eq['brand_serial'])): ?> (<?php echo htmlspecialchars($eq['brand_serial']); ?>)<?php endif; ?>
                                        — <?php echo (int)$eq['qty_serviceable']; ?> available
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                            <td><input type="datetime-local" name="items[0][released]"></td>
                            <td><input type="datetime-local" name="items[0][returned]"></td>
                            <td><textarea name="items[0][remarks]" rows="1"></textarea></td>
                            <td><div class="remove-row" onclick="removeRow(this)"><i data-lucide="x-circle" style="width: 18px; height: 18px;"></i></div></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <button type="button" class="btn-add-row" onclick="addRow()">
                <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i> Add Another Item
            </button>

            <button type="submit" name="save_slip" class="btn-save">
                <i data-lucide="save" style="width: 18px; height: 18px;"></i> Save Borrower's Slip
            </button>
            
            <p style="text-align: center; margin-top: 15px;">
                <a href="borrowers_slip.php" style="color: var(--color-text-secondary); text-decoration: none; font-size: 13px;">Cancel and Go Back</a>
            </p>
        </form>
    </div>
</div>

<script>
    // Equipment options from PHP for use in dynamically added rows
    const equipmentOptions = <?php
        $opts = [['value' => '', 'label' => '-- Select Equipment --']];
        foreach ($equipment_options as $eq) {
            $label = htmlspecialchars($eq['name'], ENT_QUOTES);
            if (!empty($eq['brand_serial'])) $label .= ' (' . htmlspecialchars($eq['brand_serial'], ENT_QUOTES) . ')';
            $label .= ' — ' . (int)$eq['qty_serviceable'] . ' available';
            $opts[] = ['value' => htmlspecialchars($eq['name'], ENT_QUOTES), 'label' => $label];
        }
        echo json_encode($opts);
    ?>;

    function buildEquipmentDropdown(name) {
        let opts = equipmentOptions.map(o =>
            `<option value="${o.value}">${o.label}</option>`
        ).join('');
        return `<select name="${name}" required style="width:100%; padding:6px; border:1px solid #ddd; border-radius:4px; font-size:13px;">${opts}</select>`;
    }

    let rowCount = 1;
    function addRow() {
        const table = document.getElementById('itemsTable').getElementsByTagName('tbody')[0];
        const newRow = table.insertRow();
        
        newRow.innerHTML = `
            <td><input type="text" name="items[${rowCount}][item_no]"></td>
            <td><input type="number" name="items[${rowCount}][quantity]" value="1" min="1"></td>
            <td>${buildEquipmentDropdown(`items[${rowCount}][description]`)}</td>
            <td><input type="datetime-local" name="items[${rowCount}][released]"></td>
            <td><input type="datetime-local" name="items[${rowCount}][returned]"></td>
            <td><textarea name="items[${rowCount}][remarks]" rows="1"></textarea></td>
            <td><div class="remove-row" onclick="removeRow(this)"><i data-lucide="x-circle" style="width: 18px; height: 18px;"></i></div></td>
        `;
        
        rowCount++;
        lucide.createIcons();
    }

    function removeRow(btn) {
        const row = btn.parentNode.parentNode;
        if (document.getElementById('itemsTable').rows.length > 2) {
            row.parentNode.removeChild(row);
        } else {
            showAlert("Required", "At least one item is required in the borrower's slip.", "error");
        }
    }

    lucide.createIcons();
</script>
</body>
</html>
