<?php
require_once __DIR__ . '/db.php';

if (!isset($_POST['log_id'])) {
    header('Location: logs.php');
    exit();
}

$log_id       = (int)$_POST['log_id'];
$new_med_name = mysqli_real_escape_string($conn, trim($_POST['new_med_name'] ?? ''));
$new_qty      = floatval($_POST['new_qty']     ?? 0);
$new_patient  = mysqli_real_escape_string($conn, trim($_POST['new_patient']  ?? ''));
$new_prescrib = mysqli_real_escape_string($conn, trim($_POST['new_prescrib'] ?? ''));
$new_staff    = mysqli_real_escape_string($conn, trim($_POST['new_staff']    ?? ''));
$new_date_raw = trim($_POST['new_date'] ?? '');
$edited_by    = mysqli_real_escape_string($conn, trim($_POST['edited_by']    ?? 'System'));

/* ── Validation ── */
if (empty($new_med_name) || $new_qty <= 0) {
    header('Location: logs.php?edit_error=invalid');
    exit();
}

/* ── Fetch the original log entry ── */
$orig = $conn->query("SELECT l.*, m.name AS med_name FROM logs l LEFT JOIN medicines m ON l.medicine_id = m.id WHERE l.id = $log_id")->fetch_assoc();

if (!$orig) {
    header('Location: logs.php?edit_error=notfound');
    exit();
}

/* Sanitise new date and preserve original time */
$orig_date_str = $orig['date'] ?? date('Y-m-d H:i:s');
$orig_time = date('H:i:s', strtotime($orig_date_str));
if (!empty($new_date_raw)) {
    $new_date = $new_date_raw . ' ' . $orig_time;
} else {
    $new_date = $orig_date_str;
}

$old_qty      = (float)($orig['quantity']    ?? 0);
$old_med_id   = (int)($orig['medicine_id']   ?? 0);
$old_med_name = $orig['med_name']             ?? '';
$old_action   = $orig['action']               ?? '';
$now          = date('Y-m-d H:i:s');

/* ── Begin transaction ── */
$conn->begin_transaction();

try {
    /* Only adjust inventory for 'Released to patient' log entries */
    if ($old_action === 'Released to patient') {

        /* STEP 1 — Restore old quantity back to the medicine batch that was deducted */
        if ($old_med_id > 0) {
            $conn->query("UPDATE medicines SET quantity = quantity + $old_qty WHERE id = $old_med_id");
        }

        /* STEP 2 — Find the medicine to deduct from (FIFO) */
        $safe_new_med = mysqli_real_escape_string($conn, $new_med_name);
        $chk = $conn->query("
            SELECT SUM(quantity) AS avail
            FROM medicines
            WHERE name = '$safe_new_med'
              AND (expiration_date >= CURDATE() OR expiration_date IS NULL)
              AND quantity > 0
              AND type IN ('medicine','consumable')");
        $avail_new = (float)($chk->fetch_assoc()['avail'] ?? 0);

        if ($avail_new < $new_qty) {
            $conn->rollback();
            header("Location: logs.php?edit_error=insufficient&need=$new_qty&avail=$avail_new");
            exit();
        }

        /* STEP 3 — FIFO deduct from new medicine batches */
        $batches = $conn->query("
            SELECT id FROM medicines
            WHERE name = '$safe_new_med'
              AND (expiration_date >= CURDATE() OR expiration_date IS NULL)
              AND quantity > 0
              AND type IN ('medicine','consumable')
            ORDER BY expiration_date ASC");

        $remaining = $new_qty;
        $new_med_id = null;

        while ($remaining > 0 && ($b = $batches->fetch_assoc())) {
            /* Get current qty */
            $row = $conn->query("SELECT id, quantity FROM medicines WHERE id = {$b['id']}")->fetch_assoc();
            $take = min($remaining, (float)$row['quantity']);
            $new_stock = (float)$row['quantity'] - $take;
            $conn->query("UPDATE medicines SET quantity = $new_stock WHERE id = {$b['id']}");
            if ($new_med_id === null) $new_med_id = (int)$b['id']; // use first batch id for log reference
            $remaining -= $take;
        }

        /* STEP 4 — Update the log entry with new values */
        $p_val  = !empty($new_patient)  ? "'$new_patient'"  : "NULL";
        $d_val  = !empty($new_prescrib) ? "'$new_prescrib'" : "NULL";
        $s_val  = !empty($new_staff)    ? "'$new_staff'"    : "NULL";
        $eb_val = "'$edited_by'";

        $conn->query("
            UPDATE logs SET
                medicine_id     = " . ($new_med_id ?? $old_med_id) . ",
                quantity        = $new_qty,
                patient_name    = $p_val,
                prescriber_name = $d_val,
                staff_name      = $s_val,
                date            = '$new_date',
                edited_by       = $eb_val,
                edited_at       = '$now'
            WHERE id = $log_id");

        /* STEP 5 — Insert audit trail log */
        $audit_note = mysqli_real_escape_string($conn,
            "Log #$log_id edited by $edited_by. " .
            "Med: '$old_med_name' → '$new_med_name'. " .
            "Qty: $old_qty → $new_qty. " .
            "Patient: '" . ($orig['patient_name'] ?? '') . "' → '$new_patient'. " .
            "Staff: '" . ($orig['staff_name'] ?? '') . "' → '$new_staff'."
        );

        /* Find a medicine_id for the audit row (use new) */
        $audit_med_id = $new_med_id ?? $old_med_id;
        $conn->query("
            INSERT INTO logs (medicine_id, quantity, action, patient_name, staff_name, date, edited_by, edited_at, dispense_slip_note)
            VALUES ($audit_med_id, $new_qty, 'Log Edited', $p_val, $eb_val, '$now', $eb_val, '$now', '$audit_note')");

    } else {
        /* Non-dispense log: just update metadata fields, no inventory adjustment */
        $p_val  = !empty($new_patient)  ? "'$new_patient'"  : "NULL";
        $d_val  = !empty($new_prescrib) ? "'$new_prescrib'" : "NULL";
        $s_val  = !empty($new_staff)    ? "'$new_staff'"    : "NULL";
        $eb_val = "'$edited_by'";

        $conn->query("
            UPDATE logs SET
                patient_name    = $p_val,
                prescriber_name = $d_val,
                staff_name      = $s_val,
                date            = '$new_date',
                edited_by       = $eb_val,
                edited_at       = '$now'
            WHERE id = $log_id");
    }

    $conn->commit();
    header('Location: logs.php?edited=1&log_id=' . $log_id);
    exit();

} catch (Exception $e) {
    $conn->rollback();
    header('Location: logs.php?edit_error=exception');
    exit();
}
