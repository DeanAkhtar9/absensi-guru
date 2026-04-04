<?php
// config/database.php

$host = "localhost";
$user = "root";
$pass = "";
$db   = "absensi_guru";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}


// ... kode koneksi database kamu (mysqli_connect) ...

if (!function_exists('kirimNotifikasi')) {
    function kirimNotifikasi($id_penerima, $judul, $pesan) {
        // Ambil koneksi dari luar fungsi
        global $conn; 
        
        // Jika koneksi belum ada, coba include ulang (opsional tapi aman)
        if (!$conn) {
            include __DIR__ . "/database.php";
        }

        $judul_clean = mysqli_real_escape_string($conn, $judul);
        $pesan_clean = mysqli_real_escape_string($conn, $pesan);
        $id_clean    = intval($id_penerima);

        $sql = "INSERT INTO notifikasi (id_user, judul, pesan, is_read, created_at) 
                VALUES ('$id_clean', '$judul_clean', '$pesan_clean', 0, NOW())";
        
        return mysqli_query($conn, $sql);
    }
}