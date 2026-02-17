<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('admin');

require "../config/database.php";
require "../config/functions.php";

include "../templates/header.php";
include "../templates/navbar.php";
include "../sidebar.php";


?>

<div class="container">
    <div class="container dashboard-wrapper">
    <div class="dashboard-header">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <h2>Dashboard Admin</h2>
        <p class="sub-title">Kelola sistem absensi guru dengan mudah.</p>
    </div>

    <!-- STATISTICS CARDS -->
    <div class="stat-cards">

        <div class="card">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Total Guru</h5>
                <div class="icon-box-guru">
                    <i class="bi bi-people"></i>
                </div>
            </div>
            <div class="card-total">
                <h3><?= getUserByRole("guru", $conn); ?></h3>
            </div>
        </div>

        <div class="card">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Pengumpulan Jurnal</h5>
                <div class="icon-box-kelas">
                    <i class="bi bi-book"></i>
                </div>
            </div>
            <div class="card-total">
                <h3><?= getTotal("kelas", $conn); ?></h3>
            </div>
        </div>

        <div class="card">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Total Siswa</h5>
                <div class="icon-box-siswa">
                    <i class="bi bi-book"></i>
                </div>
            </div>
                <div class="card-total">
                    <h3><?= getTotal("siswa", $conn); ?></h3>
                </div>
        </div>
        
        <div class="card">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Laporan</h5>
            <div class="icon-box-laporan">
                <i class="bi bi-bell"></i>
            </div>
        </div>
            <div class="card-total">
                <h3><?= getUserByRole("guru", $conn); ?></h3>
            </div>
        </div>

    </div>


    <!-- ACTION CARDS -->
    <div class="action-cards">
        <div class="card action-card">
            <h5 class="action-title">Kelas</h5>
            <p>Kelola data kelas di sini.</p>
            <a href="kelas.php" class="btn btn-primary">Kelola</a>
        </div>

        <div class="card action-card">
            <h5 class="action-title">Jadwal</h5>
            <p>Atur jadwal mengajar.</p>
            <button class="btn btn-secondary" disabled>Coming Soon</button>
        </div>

        <div class="card action-card">
            <h5 class="action-title">Users</h5>
            <p>Manajemen akun.</p>
            <button class="btn btn-secondary" disabled>Coming Soon</button>
        </div>
    </div>
</div>

<?php include "../templates/footer.php"; ?>
