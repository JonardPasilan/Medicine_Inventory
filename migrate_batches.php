<?php
require_once __DIR__ . '/db.php';

// 1. Add batch_number column if it doesn't exist
$conn->query("ALTER TABLE medicines ADD COLUMN batch_number INT DEFAULT 1 AFTER label");

// 2. Populate batch_number sequentially for each medicine group
$res = $conn->query("SELECT id, name, label FROM medicines ORDER BY name, label, id");
$meds = [];
while ($row = $res->fetch_assoc()) {
    $key = $row['name'] . '|' . $row['label'];
    if (!isset($meds[$key])) {
        $meds[$key] = 1;
    } else {
        $meds[$key]++;
    }
    $bn = $meds[$key];
    $conn->query("UPDATE medicines SET batch_number = $bn WHERE id = {$row['id']}");
}

echo "Migration completed successfully.";
unlink(__FILE__);
?>
