<?php
if(session_status() == PHP_SESSION_NONE){
    session_start();
}

if(!isset($_SESSION['role'])){
    header("Location: /absensi-guru/login.php");
    exit;
}

$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="/absensi-guru/assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>

<div class="sidebar">

    <!-- HEADER -->
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <img src="/absensi-guru/logo.png" alt="Logo" width="40">
            </div>
            <div class="brand-text">
                <h2>School Portal</h2>
                <small><?php echo ucfirst($role); ?></small>
            </div>
        </div>
    </div>

    <!-- MENU -->
    <ul class="sidebar-menu">

        <!-- DASHBOARD (SEMUA ROLE) -->
        <li>
            <a href="/absensi-guru/<?php echo $role; ?>/index.php">
                <i class="bi bi-grid"></i>
                Dashboard
            </a>
        </li>

        <!-- ================= SISWA ================= -->
        <?php if($role == 'siswa'): ?>
            
            <li>
                <a href="/absensi-guru/siswa/buat_laporan.php">
                    <i class="bi bi-send"></i>
                    Buat Laporan
                </a>
            </li>

            <li>
                <a href="/absensi-guru/siswa/riwayat_laporan.php">
                    <i class="bi bi-clock-history"></i>
                    Riwayat Laporan
                </a>
            </li>

        <?php endif; ?>


        <!-- ================= GURU ================= -->
        <?php if($role == 'guru'): ?>

            <li>
                <a href="/absensi-guru/guru/laporan.php">
                    <i class="bi bi-file-earmark-text"></i>
                    Laporan Masuk
                </a>
            </li>

             <li>
                <a href="/absensi-guru/guru/jurnal.php">
                    <i class="bi bi-check2-square"></i>
                    jurnal
                </a>
            </li>
            <li>
                <a href="/absensi-guru/guru/konfirmasi.php">
                    <i class="bi bi-check2-square"></i>
                    Konfirmasi Laporan
                </a>
            </li>

        <?php endif; ?>


        <!-- ================= ADMIN ================= -->
        <?php if($role == 'admin' || $role == 'superadmin'): ?>

            <li>
                <a href="/absensi-guru/admin/absensi.php">
                    <i class="bi bi-calendar-check"></i>
                    Data Absensi
                </a>
            </li>

            <li>
                <a href="/absensi-guru/admin/guru.php">
                    <i class="bi bi-person-badge"></i>
                    Data Guru
                </a>
            </li>

            <li>
                <a href="/absensi-guru/admin/siswa.php">
                    <i class="bi bi-people"></i>
                    Data Siswa
                </a>
            </li>

            <li>
                <a href="/absensi-guru/admin/laporan.php">
                    <i class="bi bi-file-earmark-text"></i>
                    Semua Laporan
                </a>
            </li>

        <?php endif; ?>


        <!-- ================= SUPER ADMIN ================= -->
        <?php if($role == 'superadmin'): ?>

            <li>
                <a href="/absensi-guru/admin/admin.php">
                    <i class="bi bi-person-gear"></i>
                    Manajemen Admin
                </a>
            </li>

            <li>
                <a href="/absensi-guru/admin/pengaturan.php">
                    <i class="bi bi-gear"></i>
                    Pengaturan Sistem
                </a>
            </li>

        <?php endif; ?>


        <!-- LOGOUT -->
        <li class="logout">
            <a href="/absensi-guru/logout.php">
                <i class="bi bi-box-arrow-right"></i>
                Logout
            </a>
        </li>

    </ul>

</div>
