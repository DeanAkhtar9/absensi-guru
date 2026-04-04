<?php
require "../config/database.php";

$q = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';
$role = isset($_GET['role']) ? mysqli_real_escape_string($conn, $_GET['role']) : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';

$result = [];

if ($q !== '') {
    if ($role == 'guru' && $type != 'jadwal') {
        // Filter: Hanya guru yang BELUM jadi wali di kelas manapun
        $sql = "SELECT u.id_user, u.nama FROM users u 
                LEFT JOIN kelas k ON u.id_user = k.id_walikelas 
                WHERE u.role = 'guru' AND u.nama LIKE '%$q%' 
                AND k.id_walikelas IS NULL LIMIT 10";
    } elseif ($role == 'siswa') {
        // Filter: Hanya siswa yang BELUM masuk ke tabel siswa (belum punya kelas)
        $sql = "SELECT u.id_user, u.nama FROM users u 
                LEFT JOIN siswa s ON u.id_user = s.id_user 
                WHERE u.role = 'siswa' AND u.nama LIKE '%$q%' 
                AND s.id_user IS NULL LIMIT 10";
    } else {
        // Untuk jadwal: Tampilkan semua guru (karena wali pun boleh mengajar)
        $sql = "SELECT id_user, nama FROM users 
                WHERE role = '$role' AND nama LIKE '%$q%' LIMIT 10";
    }

    $query = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
        $result[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($result);