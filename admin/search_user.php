<?php
require "../config/database.php";

header('Content-Type: application/json');

$q = $_GET['q'] ?? '';
$role = $_GET['role'] ?? '';

$q = trim($q);

if(!$q){
    echo json_encode([]);
    exit;
}

/* =========================
   FILTER ROLE
========================= */
if($role == 'walikelas'){
    // ambil hanya guru
    $stmt = $conn->prepare("
        SELECT id_user, nama 
        FROM users 
        WHERE role='guru' 
        AND nama LIKE CONCAT(?, '%')
        LIMIT 10
    ");
}
elseif($role == 'siswa'){
    // ambil hanya siswa yang SUDAH punya data di tabel siswa
    $stmt = $conn->prepare("
        SELECT u.id_user, u.nama
        FROM users u
        JOIN siswa s ON u.id_user = s.id_user
        WHERE u.role='siswa'
        AND u.nama LIKE CONCAT(?, '%')
        LIMIT 10
    ");
}
else{
    echo json_encode([]);
    exit;
}

$stmt->bind_param("s", $q);
$stmt->execute();

$result = $stmt->get_result();

$data = [];
while($row = $result->fetch_assoc()){
    $data[] = $row;
}

echo json_encode($data);