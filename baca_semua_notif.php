<?php
session_start();

// 1. Koneksi ke Database
// Sesuaikan path jika folder config Anda berbeda
require "config/database.php"; 

// 2. Cek apakah user sudah login
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['id_user'];

// 3. Update semua notifikasi milik user ini yang belum dibaca
// Kita set is_read = 1 (artinya sudah dibaca)
$query = "UPDATE notifikasi SET is_read = 1 WHERE id_user = '$user_id' AND is_read = 0";

if (mysqli_query($conn, $query)) {
    // 4. Jika berhasil, kembalikan ke halaman sebelumnya (referrer)
    // Jika tidak ada halaman sebelumnya, arahkan ke index.php
    if (isset($_SERVER['HTTP_REFERER'])) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
    } else {
        header("Location: index.php");
    }
    exit;
} else {
    // Jika terjadi error pada database
    echo "Gagal memperbarui notifikasi: " . mysqli_error($conn);
}
?>