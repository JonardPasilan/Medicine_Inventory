<?php
require_once __DIR__ . '/db.php';

if(isset($_POST['update'])){
    $id = intval($_POST['id'] ?? 0);
    $n = $conn->real_escape_string((string)($_POST['name'] ?? ''));
    $l = $conn->real_escape_string((string)($_POST['label'] ?? ''));
    $q = intval($_POST['quantity'] ?? 0);
    $e = $conn->real_escape_string((string)($_POST['exp'] ?? ''));

    if($id > 0){
        $conn->query("UPDATE medicines SET 
            name='$n',
            label='$l',
            quantity='$q',
            expiration_date='$e'
            WHERE id=$id
        ");
    }

    header("Location: index.php");
    exit();
}
?>