<?php
require "../config/database.php";

$q = $_GET['q'];
$role = $_GET['role'];
$query = mysqli_query($conn, "SELECT id_user, nama FROM users WHERE role='$role' AND nama LIKE '%$q%' LIMIT 10");
$result = [];
while($row = mysqli_fetch_assoc($query)) {
    $result[] = $row;
}
echo json_encode($result);