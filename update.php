<?php
require_once __DIR__ . '/db.php';

if(isset($_POST['update'])){
    $id = intval($_POST['id'] ?? 0);
    $n = $conn->real_escape_string((string)($_POST['name'] ?? ''));
    $l = $conn->real_escape_string((string)($_POST['label'] ?? ''));
    $t = $conn->real_escape_string((string)($_POST['type'] ?? 'medicine'));
    $c = $conn->real_escape_string((string)($_POST['category'] ?? 'General'));
    $u = $conn->real_escape_string((string)($_POST['unit'] ?? 'pcs'));
    $q = intval($_POST['quantity'] ?? 0);
    $e = $conn->real_escape_string((string)($_POST['exp'] ?? ''));

    if($id > 0){
        $val_exp = !empty($e) ? "'$e'" : "NULL";
        $conn->query("UPDATE medicines SET 
            name='$n',
            label='$l',
            type='$t',
            category='$c',
            unit='$u',
            quantity='$q',
            expiration_date=$val_exp
            WHERE id=$id
        ");
    }

    header("Location: index.php");
    exit();
}
?>