<?php
/**
 * add_stock.php — Legacy redirect
 * Old "Add Stock" links pass ?id=X (a specific batch ID).
 * We look up the medicine name and label from that batch,
 * then redirect to add.php so the user can create a NEW batch entry.
 */
require_once __DIR__ . '/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id']);
$r  = $conn->query("SELECT name, label FROM medicines WHERE id = $id");

if ($r && $r->num_rows > 0) {
    $row   = $r->fetch_assoc();
    $name  = urlencode($row['name']);
    $label = urlencode((string)$row['label']);
    header("Location: add.php?name={$name}&label={$label}");
} else {
    header("Location: add.php");
}
exit();
?>