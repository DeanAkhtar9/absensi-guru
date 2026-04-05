<?php
// config/database.php

$host = "localhost";
$user = "root";
$pass = "";
$db   = "absensi_guru";

// Koneksi MySQLi
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Koneksi PDO (Opsional jika kamu pakai PDO di file lain)
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // die("Koneksi PDO gagal: " . $e->getMessage()); 
}

/* ==========================================================
   FUNGSI KIRIM NOTIFIKASI
   ========================================================== */
if (!function_exists('kirimNotifikasi')) {
    function kirimNotifikasi($id_penerima, $judul, $pesan) {
        // Panggil variabel $conn yang ada di luar fungsi
        global $conn; 
        
        // Cek apakah koneksi tersedia
        if (!$conn) {
            return false; 
        }

        // Bersihkan input agar aman dari SQL Injection
        $judul_clean = mysqli_real_escape_string($conn, $judul);
        $pesan_clean = mysqli_real_escape_string($conn, $pesan);
        $id_clean    = intval($id_penerima);

        // Query Insert
        $sql = "INSERT INTO notifikasi (id_user, judul, pesan, is_read, created_at) 
                VALUES ('$id_clean', '$judul_clean', '$pesan_clean', 0, NOW())";
        
        $eksekusi = mysqli_query($conn, $sql);

        return $eksekusi;
    }
}