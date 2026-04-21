<?php
require_once __DIR__ . '/db.php';

if(isset($_POST['delete'])){
    $id = intval($_POST['id'] ?? 0);

    if($id > 0){
        $conn->query("DELETE FROM medicines WHERE id=$id");
    }
}

// balik sa index after delete
header("Location: index.php");
exit();
?>