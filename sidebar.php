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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/absensi-guru/assets/css/style.css">
    <link rel="stylesheet" href="/absensi-guru/assets/css/responsive.css">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <img src="/absensi-guru/logo.png" alt="Logo">
            </div>
            <div class="brand-text">
                <h2>School</h2>
                <small><?php echo ucfirst($role); ?></small>
            </div>
        </div>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="/absensi-guru/<?php echo $role; ?>/index.php">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- ================= SISWA ================= -->
        <?php if($role == 'siswa'): ?>
            <li><a href="/absensi-guru/siswa/buat_laporan.php"><i class="bi bi-send"></i><span>Buat Laporan</span></a></li>
            <li><a href="/absensi-guru/siswa/riwayat_laporan.php"><i class="bi bi-clock-history"></i><span>Riwayat Laporan</span></a></li>
             <li><a href="/absensi-guru/siswa/profil.php"><i class="bi bi-person"></i><span>Profil</span></a></li>
        <?php endif; ?>
       <!-- ================= GURU ================= -->
        <?php if($role == 'guru'): ?>

            <li><a href="/absensi-guru/guru/jurnal.php"><i class="bi bi-check2-square"></i><span>Jurnal</span></a></li>
            <li><a href="/absensi-guru/guru/riwayat_jurnal.php"><i class="bi bi-stopwatch"></i><span>Riwayat Jurnal</span></a></li>
            <li><a href="/absensi-guru/guru/profile.php"><i class="bi bi-shield-check"></i><span>Profile</span></a></li>
        <?php endif; ?>

        <!-- ================= ADMIN ================= -->
        <?php if($role == 'admin' || $role == 'superadmin'): ?>

         <li>
            <a href="/absensi-guru/admin/laporan.php">
                <i class="bi bi-shield-check"></i>
                Verifikasi Laporan
            </a>
        </li>
            <li>
                <a href="/absensi-guru/admin/jadwal2.php">
                    <i class="bi bi-calendar"></i>
                    Kelola Jadwal Guru
                </a>
            </li>
                <a href="/absensi-guru/admin/jadwal2.php">
                    <i class="bi bi-journal-text"></i>
                    Jadwal Pelajaran
                </a>
            </li>
            </li>
                <a href="/absensi-guru/admin/pengguna.php">
                    <i class="bi bi-journal-text"></i>
                    Pengguna
                </a>
            </li>

        <?php endif; ?>

        <?php if($role == 'superadmin'): ?>
            <li><a href="/absensi-guru/admin/admin.php"><i class="bi bi-person-gear"></i><span>Manajemen Admin</span></a></li>
            <li><a href="/absensi-guru/admin/pengaturan.php"><i class="bi bi-gear"></i><span>Pengaturan</span></a></li>
        <?php endif; ?>
        
    </ul>
</div>

<script>
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const openBtn = document.getElementById('openSidebar'); // Akan ada di header.php
    const closeBtn = document.getElementById('closeSidebar');

    function toggleSidebar() {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }

    // Event listeners akan aktif jika elemen ditemukan (mencegah error)
    if(closeBtn) closeBtn.addEventListener('click', toggleSidebar);
    if(overlay) overlay.addEventListener('click', toggleSidebar);
</script>