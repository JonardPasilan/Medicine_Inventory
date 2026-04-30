<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/header.php';

// Validate batch ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id']);
$r  = $conn->query("SELECT * FROM medicines WHERE id = $id");

if (!$r || $r->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$row = $r->fetch_assoc();

// Redirect equipment types to their dedicated form
if (in_array($row['type'], ['dental', 'medical'])) {
    header("Location: edit_equipment.php?id=$id");
    exit();
}
?>

    <!-- Flatpickr for Date Formatting -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
            min-height: 100vh;
        }

        .container { max-width: 600px; margin: 40px auto; padding: 0 20px; }

        .form-card {
            background: white;
            border-radius: 15px;
            padding: 35px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .form-header { text-align: center; margin-bottom: 30px; }
        .form-header .icon { font-size: 50px; margin-bottom: 10px; }
        .form-header h2 { color: #2c3e50; font-size: 28px; margin-bottom: 8px; }
        .form-header p  { color: #7f8c8d; font-size: 14px; }

        .batch-id-badge {
            display: inline-block;
            background: #1f4f87;
            color: white;
            font-size: 12px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            margin-top: 6px;
        }

        .medicine-preview {
            background: #eef2f7;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 25px;
            border: 1px solid #e0e0e0;
        }
        .preview-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
        }
        .preview-item:last-child { border-bottom: none; }
        .preview-label { font-weight: 600; color: #2c3e50; }
        .preview-value { color: #7f8c8d; }

        .form-group { margin-bottom: 20px; }

        label {
            display: block; margin-bottom: 8px;
            color: #2c3e50; font-weight: 500; font-size: 14px;
        }
        label .required { color: #e74c3c; margin-left: 3px; }

        input, select, textarea {
            width: 100%; padding: 12px 15px;
            border: 2px solid #e0e0e0; border-radius: 8px;
            font-size: 14px; font-family: inherit;
            transition: all 0.3s ease;
        }
        input:focus, select:focus, textarea:focus {
            outline: none; border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.1);
        }

        .button-group { display: flex; gap: 15px; margin-top: 10px; }

        .btn-update {
            flex: 1; padding: 14px;
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            border: none; color: white;
            font-size: 16px; font-weight: 600;
            border-radius: 8px; cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39,174,96,0.4);
        }

        .btn-cancel {
            flex: 1; padding: 14px;
            background: #95a5a6; border: none;
            color: white; font-size: 16px; font-weight: 600;
            border-radius: 8px; cursor: pointer;
            text-decoration: none; text-align: center;
            transition: all 0.3s ease;
        }
        .btn-cancel:hover { background: #7f8c8d; transform: translateY(-2px); }

        @media (max-width: 768px) {
            .container { margin: 20px auto; }
            .form-card { padding: 25px; }
            .button-group { flex-direction: column; }
            .form-header h2 { font-size: 24px; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="form-card">
        <div class="form-header">
            <div class="icon">✏️</div>
            <h2>Edit Batch</h2>
            <p>Update this specific batch entry</p>
            <span class="batch-id-badge">Batch ID #<?php echo (int)$row['id']; ?></span>
        </div>

        <div class="medicine-preview">
            <div class="preview-item">
                <span class="preview-label">Type:</span>
                <span class="preview-value" style="text-transform: capitalize;"><?php echo htmlspecialchars($row['type']); ?></span>
            </div>
            <div class="preview-item">
                <span class="preview-label">Current Stock:</span>
                <span class="preview-value"><?php echo (int)$row['quantity']; ?> units</span>
            </div>
        </div>

        <form method="POST" action="update.php" id="editForm">
            <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">

            <div class="form-group">
                <label>Item Type <span class="required">*</span></label>
                <select name="type" id="typeSelect" required onchange="updateRequiredFields()">
                    <option value="medicine"   <?php echo $row['type'] == 'medicine'   ? 'selected' : ''; ?>>💊 Medicine</option>
                    <option value="consumable" <?php echo $row['type'] == 'consumable' ? 'selected' : ''; ?>>🧴 Consumable</option>
                </select>
            </div>

            <div class="form-group">
                <label>Item Name <span class="required">*</span></label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required>
            </div>

            <div class="form-group" style="display:flex; gap:15px;">
                <div style="flex:1;" id="categoryGroup">
                    <label>Category <span class="required">*</span></label>
                    <input type="text" name="category" list="categoryList"
                           value="<?php echo htmlspecialchars($row['category'] ?? 'General'); ?>" required>
                    <datalist id="categoryList">
                        <option value="Tablet">
                        <option value="Syrup">
                        <option value="Capsule">
                        <option value="Injectable">
                        <option value="Topical">
                        <option value="Drops">
                        <option value="General">
                        <option value="Consumable">
                    </datalist>
                </div>
                <div style="flex:1;" id="unitGroup">
                    <label>Unit <span class="required">*</span></label>
                    <input type="text" name="unit" list="unitList"
                           value="<?php echo htmlspecialchars($row['unit'] ?? 'pcs'); ?>" required>
                    <datalist id="unitList">
                        <option value="pcs">
                        <option value="PCS">
                        <option value="SET">
                        <option value="Unit">
                        <option value="box">
                        <option value="ml">
                        <option value="mg">
                        <option value="vial">
                        <option value="bottle">
                        <option value="pack">
                    </datalist>
                </div>
            </div>

            <div class="form-group">
                <label>Description <span class="required" id="descReq" style="<?php echo ($row['type'] == 'medicine' || $row['type'] == 'consumable') ? '' : 'display:none;'; ?>">*</span></label>
                <input type="text" name="label" id="descInput" value="<?php echo htmlspecialchars((string)$row['label']); ?>" <?php echo ($row['type'] == 'medicine' || $row['type'] == 'consumable') ? 'required' : ''; ?>>
            </div>

            <div class="form-group">
                <label>Quantity <span class="required">*</span></label>
                <input type="number" name="quantity" id="quantityInput" value="<?php echo (int)$row['quantity']; ?>" min="0" required>
            </div>

            <div class="form-group" id="expGroup">
                <label>Expiration Date <span class="required" id="expReq" style="<?php echo $row['type'] == 'medicine' ? '' : 'display:none;'; ?>">*</span></label>
                <input type="date" name="exp" id="expDate" value="<?php echo htmlspecialchars((string)$row['expiration_date']); ?>" <?php echo $row['type'] == 'medicine' ? 'required' : ''; ?>>
                <small style="color:#7f8c8d; display:block; margin-top:5px;">(Optional for consumables)</small>
            </div>

            <div class="button-group">
                <button type="submit" name="update" class="btn-update">💾 Update Batch</button>
                <a href="index.php" class="btn-cancel">❌ Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
    let formChanged = false;
    const form = document.getElementById('editForm');
    form.querySelectorAll('input, select').forEach(inp => inp.addEventListener('change', () => { formChanged = true; }));
    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = 'You have unsaved changes.';
        }
    });
    form.addEventListener('submit', () => { formChanged = false; });

    function updateRequiredFields() {
        const type     = document.getElementById('typeSelect').value;
        const expInput = document.getElementById('expDate');
        const expStar  = document.getElementById('expReq');
        const catInput = document.querySelector('input[name="category"]');
        const descInput = document.getElementById('descInput');
        const descStar  = document.getElementById('descReq');

        if (type === 'medicine') {
            if (expInput._flatpickr && expInput._flatpickr.altInput) {
                expInput.required = false;
                expInput._flatpickr.altInput.required = true;
            } else {
                expInput.required = true;
            }
            expStar.style.display = 'inline';
        } else {
            if (expInput._flatpickr && expInput._flatpickr.altInput) {
                expInput._flatpickr.altInput.required = false;
            }
            expInput.required = false;
            expStar.style.display = 'none';
        }

        descInput.required = true;
        descStar.style.display = 'inline';
        catInput.required = true;
    }

    // Initial check
    document.addEventListener('DOMContentLoaded', () => {
        flatpickr("input[type=date]", {
            altInput: true,
            altFormat: "m/d/Y",
            dateFormat: "Y-m-d",
            allowInput: true
        });
        updateRequiredFields();
    });
</script>

</body>
</html>