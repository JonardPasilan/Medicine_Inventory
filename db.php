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
if (!in_array('category', $existing_cols)) {
    $conn->query("ALTER TABLE medicines ADD COLUMN category VARCHAR(50) DEFAULT 'General' AFTER type");
}
if (!in_array('unit', $existing_cols)) {
    $conn->query("ALTER TABLE medicines ADD COLUMN unit VARCHAR(20) DEFAULT 'pcs' AFTER category");
}
if (!in_array('is_archived', $existing_cols)) {
    $conn->query("ALTER TABLE medicines ADD COLUMN is_archived TINYINT(1) DEFAULT 0 AFTER quantity");
}

// Auto-migration for logs table
$log_cols = $conn->query("SHOW COLUMNS FROM logs");
$existing_log_cols = [];
while($c = $log_cols->fetch_assoc()) { $existing_log_cols[] = $c['Field']; }

if (!in_array('patient_name', $existing_log_cols)) {
    $conn->query("ALTER TABLE logs ADD COLUMN patient_name VARCHAR(100) DEFAULT NULL AFTER action");
}
if (!in_array('prescriber_name', $existing_log_cols)) {
    $conn->query("ALTER TABLE logs ADD COLUMN prescriber_name VARCHAR(100) DEFAULT NULL AFTER patient_name");
}

// Auto-Archive of Expired/Empty Stocks
$conn->query("UPDATE medicines SET is_archived = 1 WHERE quantity <= 0 OR (expiration_date IS NOT NULL AND expiration_date < CURDATE())");

?>
