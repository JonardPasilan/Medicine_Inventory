<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/header.php';

$message = '';
$msg_type = '';
$preview = [];

// Handle fix action
if (isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'retype') {
        // Re-type selected IDs from medicine to consumable
        $ids = $_POST['ids'] ?? [];
        if (!empty($ids)) {
            $ids_safe = array_map('intval', $ids);
            $ids_str  = implode(',', $ids_safe);
            $conn->query("UPDATE medicines SET type = 'consumable', is_archived = 0 WHERE id IN ($ids_str)");
            $message  = count($ids_safe) . " item(s) successfully re-typed to Consumable and unarchived.";
            $msg_type = 'success';
        } else {
            $message  = "No items selected.";
            $msg_type = 'error';
        }
    }

    if ($action === 'unarchive') {
        // Unarchive zero-qty consumables that shouldn't be archived
        $conn->query("UPDATE medicines SET is_archived = 0 WHERE type = 'consumable' AND is_archived = 1 AND quantity > 0");
        $affected = $conn->affected_rows;
        $message  = "$affected consumable(s) unarchived successfully.";
        $msg_type = 'success';
    }
}

// Preview: recent medicines that might actually be consumables (imported in last 48h)
$recent_meds = $conn->query("
    SELECT m.id, m.name, m.label, m.category, m.unit, m.quantity, m.is_archived,
           m.expiration_date, m.batch_number,
           l.created_at as log_time
    FROM medicines m
    JOIN logs l ON l.medicine_id = m.id AND l.action = 'Imported via CSV'
    WHERE m.type = 'medicine'
    ORDER BY l.created_at DESC
    LIMIT 200
");
?>

<style>
    .container { max-width: 960px; margin: 30px auto; padding: 0 20px; }
    .form-card {
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        padding: 30px;
        box-shadow: var(--shadow-md);
        margin-bottom: 24px;
    }
    h2 { color: var(--color-text-primary); font-size: var(--text-xl); margin-bottom: 6px; display:flex; align-items:center; gap:8px; }
    p.sub { color: var(--color-text-secondary); font-size: var(--text-sm); margin-bottom: 20px; }

    .alert-box {
        padding: 14px 18px;
        border-radius: var(--radius-md);
        margin-bottom: 20px;
        font-size: var(--text-sm);
        font-weight: 500;
    }
    .alert-box.success { background: hsl(140,60%,95%); color: hsl(140,60%,30%); border-left: 4px solid hsl(140,60%,45%); }
    .alert-box.error   { background: hsl(0,100%,97%); color: hsl(0,70%,40%); border-left: 4px solid hsl(0,70%,50%); }

    table { width: 100%; border-collapse: collapse; font-size: var(--text-sm); }
    th { background: var(--color-overlay); color: var(--color-text-secondary); padding: 10px 12px; text-align: left; font-size: var(--text-xs); text-transform: uppercase; letter-spacing: .04em; border-bottom: 1px solid var(--color-border); }
    td { padding: 10px 12px; border-bottom: 1px solid var(--color-border); color: var(--color-text-primary); vertical-align: middle; }
    tr:hover td { background: var(--color-overlay); }

    .badge-archived { background: hsl(0,100%,97%); color: hsl(0,70%,40%); border:1px solid hsl(0,70%,80%); padding:2px 8px; border-radius: var(--radius-full); font-size: var(--text-xs); font-weight:600; }
    .badge-ok       { background: hsl(140,100%,96%); color: hsl(140,70%,35%); border:1px solid hsl(140,70%,80%); padding:2px 8px; border-radius: var(--radius-full); font-size: var(--text-xs); font-weight:600; }

    .btn-fix { padding: 10px 22px; background: var(--color-brand); color: white; border: none; border-radius: var(--radius-sm); font-size: var(--text-sm); font-weight:600; cursor:pointer; font-family:'Inter',sans-serif; }
    .btn-fix:hover { background: var(--color-brand-dark); }
    .btn-outline { padding: 10px 22px; background: transparent; color: var(--color-brand); border: 1px solid var(--color-brand); border-radius: var(--radius-sm); font-size: var(--text-sm); font-weight:600; cursor:pointer; font-family:'Inter',sans-serif; }
    .btn-outline:hover { background: var(--color-brand-light); }

    .toolbar { display:flex; gap:12px; align-items:center; margin-bottom:16px; flex-wrap:wrap; }
    .select-all-wrap { font-size: var(--text-sm); color: var(--color-text-secondary); display:flex; align-items:center; gap:6px; cursor:pointer; }
</style>
</head>
<body>

<div class="container">

    <?php if ($message): ?>
        <div class="alert-box <?= $msg_type ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Section 1: Quick Fix — Unarchive consumables with qty > 0 -->
    <div class="form-card">
        <h2><i data-lucide="refresh-cw" style="width:20px;height:20px;"></i> Quick Fix: Unarchive Consumables</h2>
        <p class="sub">If your imported consumables have correct quantities but are hidden (archived), click below to restore them.</p>
        <form method="POST">
            <input type="hidden" name="action" value="unarchive">
            <button type="submit" class="btn-fix">♻️ Unarchive All Consumables with Qty &gt; 0</button>
        </form>
    </div>

    <!-- Section 2: Re-type recently imported medicines to consumable -->
    <div class="form-card">
        <h2><i data-lucide="tag" style="width:20px;height:20px;"></i> Re-type Imported Medicines → Consumable</h2>
        <p class="sub">Select the items below that were imported as "Medicine" but should be "Consumable". Then click <strong>Re-type Selected</strong>.</p>

        <form method="POST">
            <input type="hidden" name="action" value="retype">

            <div class="toolbar">
                <label class="select-all-wrap">
                    <input type="checkbox" id="selectAll"> Select All
                </label>
                <button type="submit" class="btn-fix">✅ Re-type Selected as Consumable</button>
                <a href="index.php" class="btn-outline">← Back to Dashboard</a>
            </div>

            <div style="overflow-x:auto; border:1px solid var(--color-border); border-radius: var(--radius-md);">
                <table>
                    <thead>
                        <tr>
                            <th style="width:36px;"></th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th>Qty</th>
                            <th>Expiry</th>
                            <th>Status</th>
                            <th>Imported At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($recent_meds && $recent_meds->num_rows > 0):
                            while ($row = $recent_meds->fetch_assoc()):
                                $archived = (int)$row['is_archived'];
                                $badge = $archived ? "<span class='badge-archived'>Archived</span>" : "<span class='badge-ok'>Active</span>";
                                $exp = !empty($row['expiration_date']) && $row['expiration_date'] != '0000-00-00'
                                    ? date('m/d/Y', strtotime($row['expiration_date'])) : 'N/A';
                                $log_time = date('m/d/Y H:i', strtotime($row['log_time']));
                        ?>
                        <tr>
                            <td><input type="checkbox" name="ids[]" value="<?= $row['id'] ?>" class="row-check"></td>
                            <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                            <td><?= htmlspecialchars((string)$row['label']) ?></td>
                            <td><?= htmlspecialchars($row['category'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['unit'] ?? '') ?></td>
                            <td><?= (int)$row['quantity'] ?></td>
                            <td><?= $exp ?></td>
                            <td><?= $badge ?></td>
                            <td style="color:var(--color-text-muted); font-size:var(--text-xs);"><?= $log_time ?></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="9" style="text-align:center; padding:30px; color:var(--color-text-muted);">No recently imported medicine records found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
    });
    lucide.createIcons();
</script>

</body>
</html>
