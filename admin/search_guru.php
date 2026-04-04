<?php
require "../config/database.php";

$q = $_GET['q'] ?? '';
$role = $_GET['role'] ?? '';

$data=[];

$sql = "SELECT id_user,nama FROM users WHERE nama LIKE '%$q%'";

if($role){
    $sql .= " AND role='$role'";
}

$sql .= " LIMIT 10";

$res = mysqli_query($conn,$sql);

while($row=mysqli_fetch_assoc($res)){
    $data[]=$row;
}

echo json_encode($data);