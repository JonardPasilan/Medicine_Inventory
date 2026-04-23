<?php
require_once __DIR__ . '/db.php';

// Only allow POST requests for deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['single_id'])) {
        $id = intval($_POST['single_id']);
        $conn->query("DELETE FROM medicines WHERE id = $id AND is_archived = 1");
        header("Location: expired.php");
        exit();
    } else {
        // Delete all archived AND expired medicines
        $today_date = date('Y-m-d');
        $conn->query("DELETE FROM medicines WHERE is_archived = 1 AND expiration_date < '$today_date'");
        header("Location: index.php");
        exit();
    }
}

// Fallback redirect
header("Location: index.php");
exit();
?>
