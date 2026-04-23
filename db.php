<?php
$conn = new mysqli("localhost", "root", "", "clinic_inventory");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Auto-migration: Ensure necessary columns exist
$cols = $conn->query("SHOW COLUMNS FROM medicines");
$existing_cols = [];
while($c = $cols->fetch_assoc()) { $existing_cols[] = $c['Field']; }

if (!in_array('batch_number', $existing_cols)) {
    $conn->query("ALTER TABLE medicines ADD COLUMN batch_number INT DEFAULT 1 AFTER label");
}
if (!in_array('type', $existing_cols)) {
    $conn->query("ALTER TABLE medicines ADD COLUMN type VARCHAR(20) DEFAULT 'medicine' AFTER batch_number");
}
?>
