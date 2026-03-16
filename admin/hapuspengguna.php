<?php

require "../config/database.php";

$id=$_GET['id'];

mysqli_query($conn,"
DELETE FROM users WHERE id_user='$id'
");

header("Location: pengguna.php");

?>
