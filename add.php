<?php
require_once __DIR__ . '/db.php';

// Pre-fill values when coming from "New Batch" button
$prefill_name  = isset($_GET['name'])  ? htmlspecialchars($_GET['name'],  ENT_QUOTES, 'UTF-8') : '';
$prefill_label = isset($_GET['label']) ? htmlspecialchars($_GET['label'], ENT_QUOTES, 'UTF-8') : '';
$prefill_type  = isset($_GET['type'])  ? htmlspecialchars($_GET['type'],  ENT_QUOTES, 'UTF-8') : 'medicine';
$prefill_cat   = isset($_GET['cat'])   ? htmlspecialchars($_GET['cat'],   ENT_QUOTES, 'UTF-8') : 'General';
$prefill_unit  = isset($_GET['unit'])  ? htmlspecialchars($_GET['unit'],  ENT_QUOTES, 'UTF-8') : 'pcs';
$is_new_batch  = ($prefill_name !== '');

// Redirect equipment types to the dedicated form
if (in_array($prefill_type, ['dental', 'medical'])) {
    header("Location: add_equipment.php");
    exit();
}

require_once __DIR__ . '/header.php';
?>

<!-- Flatpickr for Date Formatting -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', sans-serif; background: var(--color-canvas); min-height: 100vh; }

    .container { max-width: 600px; margin: 40px auto; padding: 0 20px; }

    .form-card {
        background: var(--color-surface); border-radius: var(--radius-lg); padding: 35px;
        box-shadow: var(--shadow-md); border: 1px solid var(--color-border);
        animation: fadeIn 0.6s cubic-bezier(0.23, 1, 0.32, 1);
    }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(30px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }

    .form-header { text-align: center; margin-bottom: 30px; }
    .form-header h2 { color: var(--color-text-primary); font-size: 28px; margin-bottom: 10px; }
    .form-header p  { color: var(--color-text-secondary); font-size: 14px; }
    .form-header .icon { font-size: 50px; margin-bottom: 10px; transition: transform 0.5s ease; display: block; }
    .form-card:hover .icon { transform: rotate(15deg) scale(1.1); }

    .batch-banner {
        background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
        border-left: 4px solid #27ae60; border-radius: 8px;
        padding: 14px 18px; margin-bottom: 25px;
        display: flex; align-items: center; gap: 10px;
        font-size: 14px; color: #1e8449;
        animation: slideInLeft 0.5s ease forwards;
    }
    @keyframes slideInLeft { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }

    .form-group { margin-bottom: 20px; }
    label { display: block; margin-bottom: 8px; color: var(--color-text-primary); font-weight: 500; font-size: 14px; }
    label .required { color: #e74c3c; margin-left: 3px; }

    input, select, textarea {
        width: 100%; padding: 12px 15px;
        border: 2px solid var(--color-border); border-radius: var(--radius-sm);
        background: var(--color-overlay); color: var(--color-text-primary);
        font-size: 14px; font-family: inherit; transition: all 0.3s ease;
    }
    input:focus, select:focus, textarea:focus {
        outline: none; border-color: var(--color-brand);
        box-shadow: 0 0 0 3px var(--color-brand-light); transform: translateY(-1px);
    }

    .btn-submit {
        width: 100%; padding: 14px; background: var(--color-brand);
        border: none; color: white; font-size: 16px; font-weight: 600;
        border-radius: 8px; cursor: pointer; transition: all 0.3s ease;
        margin-top: 10px; position: relative; overflow: hidden;
    }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(31,79,135,0.4); }
    .btn-submit:active { transform: translateY(0); }

    .ripple {
        position: absolute; background: rgba(255,255,255,0.4); border-radius: 50%;
        transform: scale(0); animation: ripple-animation 0.6s linear; pointer-events: none;
    }
    @keyframes ripple-animation { to { transform: scale(4); opacity: 0; } }

    #loadingOverlay {
        display: none; position: fixed; inset: 0;
        background: rgba(255,255,255,0.7); z-index: 10000;
        align-items: center; justify-content: center;
        flex-direction: column; gap: 15px; backdrop-filter: blur(2px);
    }
    .spinner {
        width: 50px; height: 50px; border: 5px solid var(--color-border); border-top: 5px solid var(--color-brand);
        border-radius: 50%; animation: spin 1s linear infinite;
    }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

    #toastContainer { position: fixed; bottom: 30px; right: 30px; z-index: 10001; }
    .toast {
        background: var(--color-surface); padding: 15px 25px; border-radius: var(--radius-md);
        box-shadow: var(--shadow-lg); border: 1px solid var(--color-border);
        display: flex; align-items: center; gap: 12px; margin-top: 10px;
        transform: translateX(120%); transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        border-left: 5px solid hsl(140, 60%, 45%);
    }
    .toast.show { transform: translateX(0); }
    .toast.error { border-left-color: hsl(0, 70%, 50%); }
    .toast-icon { font-size: 20px; }

    .info-box {
        background: var(--color-overlay); border-radius: var(--radius-sm); padding: 15px;
        margin-top: 20px; text-align: center; border: 1px solid var(--color-border);
    }
    .info-box p { color: var(--color-text-muted); font-size: 13px; }
    .info-box a { color: var(--color-brand); text-decoration: none; font-weight: 500; }
    .info-box a:hover { text-decoration: underline; }

    @media (max-width: 768px) {
        .container { margin: 20px auto; }
        .form-card { padding: 25px; }
        .form-header h2 { font-size: 24px; }
    }
</style>

<div id="loadingOverlay">
    <div class="spinner"></div>
    <p style="color: #1f4f87; font-weight: 600;">Saving item...</p>
</div>
<div id="toastContainer"></div>

<div class="container">
    <div class="form-card">
        <div class="form-header">
            <span class="icon"><?php echo $prefill_type == 'consumable' ? '🧴' : '💊'; ?></span>
            <h2><?php echo $is_new_batch ? 'Add New Batch' : 'Add Medicine / Consumable'; ?></h2>
            <p><?php echo $is_new_batch
                ? 'Adding a new batch for: <strong>' . $prefill_name . '</strong>'
                : 'Enter the details of the medicine or consumable supply.'; ?></p>
        </div>

        <?php if ($is_new_batch): ?>
        <div class="batch-banner">
            <span>📦</span>
            <div>
                <strong>New Batch For: <?php echo $prefill_name; ?></strong><br>
                <small>A new batch record will be created with its own quantity and expiration date.</small>
            </div>
        </div>
        <?php endif; ?>

        <?php
        $status_msg  = '';
        $status_type = 'success';
        if (isset($_POST['add'])) {
            $n = mysqli_real_escape_string($conn, trim($_POST['name']        ?? ''));
            $l = mysqli_real_escape_string($conn, trim($_POST['Description'] ?? ''));
            $t = mysqli_real_escape_string($conn, trim($_POST['type']        ?? 'medicine'));
            $c = mysqli_real_escape_string($conn, trim($_POST['category']    ?? 'General'));
            $u = mysqli_real_escape_string($conn, trim($_POST['unit']        ?? 'pcs'));
            $q = intval($_POST['quantity'] ?? 0);
            $e = mysqli_real_escape_string($conn, trim($_POST['exp']         ?? ''));

            $errors = [];
            if (empty($n)) $errors[] = "Name is required.";
            if ($q < 0)   $errors[] = "Quantity cannot be negative.";

            if (empty($errors)) {
                $bn_res  = $conn->query("SELECT MAX(batch_number) AS max_bn FROM medicines WHERE name = '$n' AND label = '$l' AND type = '$t'");
                $next_bn = 1;
                if ($bn_res && $row = $bn_res->fetch_assoc()) {
                    $next_bn = intval($row['max_bn']) + 1;
                }

                $val_exp = !empty($e) ? "'$e'" : "NULL";
                $sql = "INSERT INTO medicines (name, label, type, category, unit, batch_number, quantity, expiration_date, is_archived)
                        VALUES ('$n', '$l', '$t', '$c', '$u', $next_bn, $q, $val_exp, 0)";

                if ($conn->query($sql)) {
                    $new_id = $conn->insert_id;
                    $conn->query("INSERT INTO logs (medicine_id, quantity, action) VALUES ($new_id, $q, 'New Batch Added')");
                    $status_msg  = ($is_new_batch ? "New batch added for $n" : "$n added") . "! Qty: $q units.";
                    $status_type = 'success';
                } else {
                    $status_msg  = "Error: " . $conn->error;
                    $status_type = 'error';
                }
            } else {
                $status_msg  = implode(" ", $errors);
                $status_type = 'error';
            }
        }
        ?>

        <form method="POST" id="addForm" onsubmit="handleSubmit(event)">

            <!-- Item Type: Medicine or Consumable only -->
            <div class="form-group">
                <label>Item Type <span class="required">*</span></label>
                <select name="type" id="typeSelect" required <?php echo $is_new_batch ? 'disabled' : ''; ?> onchange="toggleExpiry()">
                    <option value="medicine"   <?php echo $prefill_type == 'medicine'   ? 'selected' : ''; ?>>💊 Medicine</option>
                    <option value="consumable" <?php echo $prefill_type == 'consumable' ? 'selected' : ''; ?>>🧴 Consumable Supply</option>
                </select>
                <?php if ($is_new_batch): ?>
                    <input type="hidden" name="type" value="<?php echo $prefill_type; ?>">
                <?php endif; ?>
            </div>

            <!-- Item Name -->
            <div class="form-group">
                <label>Item Name <span class="required">*</span></label>
                <input type="text" name="name" value="<?php echo $prefill_name; ?>"
                       placeholder="e.g., Paracetamol, Gauze Pad" required
                       <?php echo $is_new_batch ? 'readonly' : ''; ?>>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label>Description (Optional)</label>
                <input type="text" name="Description" id="descInput"
                       value="<?php echo $prefill_label; ?>"
                       placeholder="e.g., 500mg tablet, 100pcs/box">
            </div>

            <!-- Category & Unit -->
            <div class="form-group" style="display:flex; gap:15px;">
                <div style="flex:1;">
                    <label>Category <span class="required">*</span></label>
                    <input type="text" name="category" list="categoryList"
                           value="<?php echo $prefill_cat; ?>"
                           placeholder="e.g., Tablet, Syrup" required
                           <?php echo $is_new_batch ? 'readonly' : ''; ?>>
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
                <div style="flex:1;">
                    <label>Unit <span class="required">*</span></label>
                    <input type="text" name="unit" list="unitList"
                           value="<?php echo $prefill_unit; ?>"
                           placeholder="e.g., pcs, box" required
                           <?php echo $is_new_batch ? 'readonly' : ''; ?>>
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

            <!-- Quantity -->
            <div class="form-group">
                <label>Quantity <span class="required">*</span></label>
                <input type="number" name="quantity" min="0" placeholder="Enter number of units" required>
            </div>

            <!-- Expiration Date -->
            <div class="form-group" id="expGroup">
                <label>Expiration Date (Optional)</label>
                <input type="date" name="exp" id="expDate">
                <small style="color:#7f8c8d; display:block; margin-top:5px;">(Leave blank if not applicable)</small>
            </div>

            <button type="submit" name="add" class="btn-submit">
                <?php echo $is_new_batch ? '📦 Add New Batch to Inventory' : '➕ Add to Inventory'; ?>
            </button>
        </form>

        <div class="info-box">
            <p>📊 <a href="index.php">← Back to Inventory</a> &nbsp;|&nbsp; <a href="add_equipment.php">🦷 Add Device & Equipment →</a></p>
        </div>
    </div>
</div>

<script>
    // Ripple Effect
    document.querySelectorAll('.btn-submit').forEach(button => {
        button.addEventListener('click', function(e) {
            let x = e.clientX - e.target.getBoundingClientRect().left;
            let y = e.clientY - e.target.getBoundingClientRect().top;
            let ripples = document.createElement('span');
            ripples.className = 'ripple';
            ripples.style.left = x + 'px';
            ripples.style.top  = y + 'px';
            this.appendChild(ripples);
            setTimeout(() => ripples.remove(), 600);
        });
    });

    function handleSubmit(e) {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }

    function showToast(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast ${type === 'error' ? 'error' : ''}`;
        toast.innerHTML = `<span class="toast-icon">${type === 'success' ? '✅' : '❌'}</span><span>${message}</span>`;
        container.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 100);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 400);
        }, 5000);
    }

    // toggleExpiry() removed — expiration is optional for all types

    // PHP passed status
    <?php if ($status_msg): ?>
        window.onload = function() {
            document.getElementById('loadingOverlay').style.display = 'none';
            showToast("<?php echo addslashes($status_msg); ?>", "<?php echo $status_type; ?>");
            <?php if ($status_type === 'success'): ?>
                setTimeout(() => { document.getElementById('addForm').reset(); toggleExpiry(); }, 500);
            <?php endif; ?>
        };
    <?php endif; ?>

    // Initial check on page load
    document.addEventListener('DOMContentLoaded', () => {
        flatpickr("input[type=date]", {
            altInput: true, altFormat: "m/d/Y",
            dateFormat: "Y-m-d", allowInput: true
        });
        toggleExpiry();
    });
</script>

</body>
</html>