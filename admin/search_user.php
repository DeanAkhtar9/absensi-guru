<?php
require "../config/database.php";

$keyword = $_GET['q'] ?? '';
$role = $_GET['role'] ?? '';

$whereRole = "";
if($role == 'siswa'){
    $whereRole = "AND u.role='siswa'";
}elseif($role == 'walikelas'){
    $whereRole = "AND u.role='walikelas'";
}

$query = mysqli_query($conn,"
SELECT u.id_user,u.nama
FROM users u
LEFT JOIN siswa s ON u.id_user=s.id_user
WHERE u.nama LIKE '%$keyword%'
$whereRole
LIMIT 10
");

$data = [];

while($row = mysqli_fetch_assoc($query)){
    $data[] = $row;
}

echo json_encode($data);