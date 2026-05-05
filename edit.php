<?php
require_once __DIR__ . '/db.php';

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

require_once __DIR__ . '/header.php';
?>

    <!-- Flatpickr for Date Formatting -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--color-canvas);
            min-height: 100vh;
        }

        .container { max-width: 600px; margin: 40px auto; padding: 0 20px; }

        .form-card {
            background: var(--color-surface);
            border-radius: var(--radius-lg);
            padding: 35px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--color-border);
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .form-header { text-align: center; margin-bottom: 30px; }
        .form-header .icon { font-size: 50px; margin-bottom: 10px; }
        .form-header h2 { color: var(--color-text-primary); font-size: 28px; margin-bottom: 8px; }
        .form-header p  { color: var(--color-text-secondary); font-size: 14px; }

        .batch-id-badge {
            display: inline-block;
            background: var(--color-brand);
            color: white;
            font-size: 12px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 20px;
            margin-top: 6px;
        }

        .medicine-preview {
            background: var(--color-overlay);
            border-radius: var(--radius-sm);
            padding: 15px;
            margin-bottom: 25px;
            border: 1px solid var(--color-border);
        }
        .preview-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid var(--color-border);
            font-size: 14px;
        }
        .preview-item:last-child { border-bottom: none; }
        .preview-label { font-weight: 600; color: var(--color-text-primary); }
        .preview-value { color: var(--color-text-secondary); }

        .form-group { margin-bottom: 20px; }

        label {
            display: block; margin-bottom: 8px;
            color: var(--color-text-primary); font-weight: 500; font-size: 14px;
        }
        label .required { color: #e74c3c; margin-left: 3px; }

        input, select, textarea {
            width: 100%; padding: 12px 15px;
            border: 2px solid var(--color-border); border-radius: var(--radius-sm);
            background: var(--color-overlay); color: var(--color-text-primary);
            font-size: 14px; font-family: inherit;
            transition: all 0.3s ease;
        }
        input:focus, select:focus, textarea:focus {
            outline: none; border-color: var(--color-brand);
            box-shadow: 0 0 0 3px var(--color-brand-light);
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
            background: var(--color-overlay); border: 1px solid var(--color-border);
            color: var(--color-text-secondary); font-size: 16px; font-weight: 600;
            border-radius: var(--radius-sm); cursor: pointer;
            text-decoration: none; text-align: center;
            transition: all 0.3s ease;
        }
        .btn-cancel:hover { background: var(--color-border); color: var(--color-text-primary); transform: translateY(-2px); }

        /* Modal Styles */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5); display: flex;
            align-items: center; justify-content: center; z-index: 1000;
            opacity: 0; pointer-events: none; transition: opacity 0.3s ease;
        }
        .modal-overlay.active { opacity: 1; pointer-events: all; }
        .modal-content {
            background: var(--color-surface); padding: 30px;
            border-radius: var(--radius-lg); width: 90%; max-width: 400px;
            text-align: center; box-shadow: var(--shadow-lg);
            transform: translateY(-20px); transition: transform 0.3s ease;
        }
        .modal-overlay.active .modal-content { transform: translateY(0); }
        .modal-content h3 { margin-bottom: 15px; color: var(--color-text-primary); font-size: 22px; }
        .modal-content p { margin-bottom: 25px; color: var(--color-text-secondary); font-size: 15px; line-height: 1.5; }
        .modal-buttons { display: flex; gap: 15px; justify-content: center; }
        .btn-danger {
            padding: 12px 24px; background: #e74c3c; color: white;
            border: none; border-radius: var(--radius-sm); cursor: pointer; font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-danger:hover { background: #c0392b; transform: translateY(-2px); }
        .btn-secondary {
            padding: 12px 24px; background: var(--color-overlay); color: var(--color-text-primary);
            border: 1px solid var(--color-border); border-radius: var(--radius-sm); cursor: pointer; font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-secondary:hover { background: var(--color-border); transform: translateY(-2px); }

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
                <label>Description (Optional)</label>
                <input type="text" name="label" id="descInput" value="<?php echo htmlspecialchars((string)$row['label']); ?>">
            </div>

            <div class="form-group">
                <label>Quantity <span class="required">*</span></label>
                <input type="number" name="quantity" id="quantityInput" value="<?php echo (int)$row['quantity']; ?>" min="0" required>
            </div>

            <div class="form-group" id="expGroup">
                <label>Expiration Date (Optional)</label>
                <input type="date" name="exp" id="expDate" value="<?php echo htmlspecialchars((string)$row['expiration_date']); ?>">
                <small style="color:#7f8c8d; display:block; margin-top:5px;">(Leave blank if not applicable)</small>
            </div>

            <div class="button-group">
                <button type="submit" name="update" class="btn-update">💾 Update Batch</button>
                <a href="index.php" class="btn-cancel">❌ Cancel</a>
            </div>
        </form>
    </div>
</div>

<!-- Leave Modal -->
<div id="leaveModal" class="modal-overlay">
    <div class="modal-content">
        <h3>Unsaved Changes</h3>
        <p>You have unsaved changes. Are you sure you want to leave this page without saving?</p>
        <div class="modal-buttons">
            <button type="button" id="btnStay" class="btn-secondary">Stay</button>
            <button type="button" id="btnLeave" class="btn-danger">Leave</button>
        </div>
    </div>
</div>

<script>
    let formChanged = false;
    let pendingUrl = '';

    // updateRequiredFields() removed — description and expiration are optional
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('editForm');
        const leaveModal = document.getElementById('leaveModal');
        const btnStay = document.getElementById('btnStay');
        const btnLeave = document.getElementById('btnLeave');

        if (form) {
            // Track changes
            form.querySelectorAll('input, select').forEach(inp => {
                inp.addEventListener('change', () => { formChanged = true; });
                inp.addEventListener('input', () => { formChanged = true; });
            });
            
            form.addEventListener('submit', () => { formChanged = false; });
        }
        
        // Intercept clicks on links
        document.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function(e) {
                if (formChanged && !this.hasAttribute('target') && this.href && !this.href.startsWith('javascript:')) {
                    e.preventDefault();
                    pendingUrl = this.href;
                    leaveModal.classList.add('active');
                }
            });
        });

        if (btnStay) {
            btnStay.addEventListener('click', () => {
                leaveModal.classList.remove('active');
                pendingUrl = '';
            });
        }

        if (btnLeave) {
            btnLeave.addEventListener('click', () => {
                if (pendingUrl) {
                    window.location.href = pendingUrl;
                }
            });
        }

        flatpickr("input[type=date]", {
            altInput: true,
            altFormat: "m/d/Y",
            dateFormat: "Y-m-d",
            allowInput: true
        });
    });
</script>

</body>
</html>