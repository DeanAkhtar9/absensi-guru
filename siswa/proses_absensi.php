<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('siswa');

require "../config/database.php";

$id_jadwal = intval($_POST['id_jadwal']);
$status    = $_POST['status'];
$tanggal   = date('Y-m-d');
$id_user   = $_SESSION['id_user'];

/* CEK SUDAH ADA ATAU BELUM */
$cek = mysqli_query($conn, "
    SELECT id_absensi_guru
    FROM absensi_guru
    WHERE id_jadwal = '$id_jadwal'
      AND tanggal = '$tanggal'
");

if (mysqli_num_rows($cek) > 0) {
    header("Location: absensi_guru.php");
    exit;
}

/* SIMPAN */
mysqli_query($conn, "
    INSERT INTO absensi_guru
    (id_jadwal, tanggal, status, diinput_oleh)
    VALUES
    ('$id_jadwal', '$tanggal', '$status', '$id_user')
");

header("Location: absensi_guru.php");
exit;
