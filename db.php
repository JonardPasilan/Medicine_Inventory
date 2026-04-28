<?php
$conn = new mysqli("localhost", "root", "", "medicine");

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

// Equipment specific columns
if (!in_array('brand_serial', $existing_cols)) {
    $conn->query("ALTER TABLE medicines ADD COLUMN brand_serial VARCHAR(255) NULL AFTER is_archived");
}
if (!in_array('ris_id', $existing_cols)) {
    $conn->query("ALTER TABLE medicines ADD COLUMN ris_id VARCHAR(255) NULL AFTER brand_serial");
}
if (!in_array('color', $existing_cols)) {
    $conn->query("ALTER TABLE medicines ADD COLUMN color VARCHAR(100) NULL AFTER ris_id");
}
if (!in_array('date_acquired', $existing_cols)) {
    $conn->query("ALTER TABLE medicines ADD COLUMN date_acquired DATE NULL AFTER color");
}
if (!in_array('qty_serviceable', $existing_cols)) {
    $conn->query("ALTER TABLE medicines ADD COLUMN qty_serviceable INT DEFAULT 0 AFTER date_acquired");
}
if (!in_array('qty_unserviceable', $existing_cols)) {
    $conn->query("ALTER TABLE medicines ADD COLUMN qty_unserviceable INT DEFAULT 0 AFTER qty_serviceable");
}
if (!in_array('qty_repair', $existing_cols)) {
    $conn->query("ALTER TABLE medicines ADD COLUMN qty_repair INT DEFAULT 0 AFTER qty_unserviceable");
}
if (!in_array('remarks', $existing_cols)) {
    $conn->query("ALTER TABLE medicines ADD COLUMN remarks TEXT NULL AFTER qty_repair");
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
