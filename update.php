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

    // Equipment fields
    $brand = $conn->real_escape_string((string)($_POST['brand_serial'] ?? ''));
    $ris = $conn->real_escape_string((string)($_POST['ris_id'] ?? ''));
    $color = $conn->real_escape_string((string)($_POST['color'] ?? ''));
    $date_acq = $conn->real_escape_string((string)($_POST['date_acquired'] ?? ''));
    $qsrv = intval($_POST['qty_serviceable'] ?? 0);
    $qunsrv = intval($_POST['qty_unserviceable'] ?? 0);
    $qrep = intval($_POST['qty_repair'] ?? 0);
    $rem = $conn->real_escape_string((string)($_POST['remarks'] ?? ''));

    if($id > 0){
        $val_exp = !empty($e) ? "'$e'" : "NULL";
        $val_acq = !empty($date_acq) ? "'$date_acq'" : "NULL";
        $conn->query("UPDATE medicines SET 
            name='$n',
            label='$l',
            type='$t',
            category='$c',
            unit='$u',
            quantity='$q',
            expiration_date=$val_exp,
            brand_serial='$brand',
            ris_id='$ris',
            color='$color',
            date_acquired=$val_acq,
            qty_serviceable=$qsrv,
            qty_unserviceable=$qunsrv,
            qty_repair=$qrep,
            remarks='$rem'
            WHERE id=$id
        ");
    }

    header("Location: index.php");
    exit();
}
?>