<?php

require "../config/database.php";

$id = $_GET['id'];
$status = $_GET['status'];

$allowed = ['diverifikasi','ditindaklanjuti','selesai'];

if(!in_array($status,$allowed)){
die("Status tidak valid");
}

mysqli_query($conn,"
UPDATE komplain 
SET status='$status'
WHERE id_komplain='$id'
");

header("Location: laporan.php");

?>
