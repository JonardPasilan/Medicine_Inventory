<?php
require_once __DIR__ . '/db.php';

if(isset($_POST['add'])){
    $id = intval($_POST['id'] ?? 0);
    $add = intval($_POST['add_qty'] ?? 0);

    if($id <= 0 || $add <= 0){
        header("Location: index.php");
        exit();
    }

    $r = $conn->query("SELECT quantity FROM medicines WHERE id=$id");
    if(!$r || $r->num_rows === 0){
        header("Location: index.php");
        exit();
    }
    $row = $r->fetch_assoc();

    $new_qty = (int)$row['quantity'] + $add;

    $conn->query("UPDATE medicines SET quantity=$new_qty WHERE id=$id");

    header("Location: add_stock.php?id=" . $id . "&updated=success");
    exit();
}
?>