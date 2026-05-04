<?php
require_once __DIR__ . '/db.php';
$conn->query("ALTER TABLE medicines ADD COLUMN type VARCHAR(20) DEFAULT 'medicine' AFTER batch_number");
echo "Column 'type' added successfully.";
unlink(__FILE__);
?>
