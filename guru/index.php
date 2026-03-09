<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('guru');

include "../config/database.php";
include "../templates/header.php";
include "../templates/navbar.php";
include "../sidebar.php";
include "../header.php";

$id_guru = $_SESSION['id_user'];

/* =========================
   DATA RINGKASAN DASHBOARD
========================= */

// jumlah kelas yang diajar
$kelas = mysqli_query($conn,"
    SELECT COUNT(DISTINCT id_kelas) as total_kelas
    FROM jadwal_mengajar
    WHERE id_guru='$id_guru'
");
$total_kelas = mysqli_fetch_assoc($kelas)['total_kelas'] ?? 0;

// total siswa dari semua kelas yang diajar
$siswa = mysqli_query($conn,"
    SELECT COUNT(DISTINCT s.id_siswa) as total_siswa
    FROM siswa s
    JOIN jadwal_mengajar jm ON s.id_kelas = jm.id_kelas
    WHERE jm.id_guru='$id_guru'
");
$total_siswa = mysqli_fetch_assoc($siswa)['total_siswa'] ?? 0;

// total jurnal minggu ini
$jurnal = mysqli_query($conn,"
    SELECT COUNT(*) as total_jurnal
    FROM jurnal_mengajar j
    JOIN absensi_guru ag ON j.id_absensi_guru = ag.id_absensi_guru
    JOIN jadwal_mengajar jm ON ag.id_jadwal = jm.id_jadwal
    WHERE jm.id_guru='$id_guru'
      AND WEEK(ag.tanggal)=WEEK(CURDATE())
");
$total_jurnal = mysqli_fetch_assoc($jurnal)['total_jurnal'] ?? 0;

// rata-rata kehadiran siswa
$kehadiran = mysqli_query($conn,"
    SELECT 
    ROUND(
        (SUM(CASE WHEN asw.status='hadir' THEN 1 ELSE 0 END)
        / COUNT(asw.id_siswa))*100,2
    ) as persen
    FROM absensi_siswa asw
    JOIN jurnal_mengajar j ON asw.id_jurnal=j.id_jurnal
    JOIN absensi_guru ag ON j.id_absensi_guru=ag.id_absensi_guru
    JOIN jadwal_mengajar jm ON ag.id_jadwal=jm.id_jadwal
    WHERE jm.id_guru='$id_guru'
");
$rata_kehadiran = mysqli_fetch_assoc($kehadiran)['persen'] ?? 0;
?>
<div class="dashboard-wrapper">

    <?php include "../sidebar.php"; ?>

    <div class="main-content">

        <div class="content-area">

<!-- WELCOME -->
<div class="mb-4">
    <h4 class="fw-bold">
        Selamat datang kembali, <?= $_SESSION['nama_user'] ?? 'Guru' ?>!
    </h4>
    <p class="text-muted">
        Berikut adalah ringkasan aktivitas pengajaran Anda hari ini.
    </p>
</div>


<!-- RINGKASAN -->
<div class="row g-3 mb-4">

    <!-- Kelas Hari Ini -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">Kelas Hari Ini</small>
                    <h4 class="fw-bold"><?= $total_kelas ?> Kelas</h4>
                </div>
                <div class="bg-light p-3 rounded">
                    📘
                </div>
            </div>
        </div>
    </div>

    <!-- Jurnal Terisi -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">Jurnal Terisi</small>
                    <h4 class="fw-bold"><?= $total_jurnal ?> / <?= $total_kelas ?></h4>
                </div>
                <div class="bg-light p-3 rounded">
                    ✅
                </div>
            </div>
        </div>
    </div>

    <!-- Total Siswa -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">Total Siswa</small>
                    <h4 class="fw-bold"><?= $total_siswa ?> Siswa</h4>
                </div>
                <div class="bg-light p-3 rounded">
                    👥
                </div>
            </div>
        </div>
    </div>

</div>


<!-- JADWAL MENGAJAR -->
<div class="card shadow-sm border-0">
    <div class="card-body">

        <div class="d-flex justify-content-between mb-3">
            <h6 class="fw-bold">Jadwal Mengajar Terdekat</h6>
            <a href="#" class="text-decoration-none">Lihat Semua</a>
        </div>

        <!-- contoh jadwal -->
        <div class="p-3 mb-2 border rounded d-flex justify-content-between align-items-center">

            <div class="d-flex align-items-center">

                <div class="bg-light p-2 rounded me-3 text-center">
                    <small>JAM</small><br>
                    <b>08:00</b>
                </div>

                <div>
                    <b>Matematika - Kelas 10-A</b><br>
                    <small class="text-muted">Ruang Laboratorium 1</small>
                </div>

            </div>

            <span class="badge bg-success">Selesai</span>

        </div>


        <div class="p-3 border rounded d-flex justify-content-between align-items-center">

            <div class="d-flex align-items-center">

                <div class="bg-light p-2 rounded me-3 text-center">
                    <small>JAM</small><br>
                    <b>10:30</b>
                </div>

                <div>
                    <b>Matematika - Kelas 12-C</b><br>
                    <small class="text-muted">Gedung B, Ruang 204</small>
                </div>

            </div>

            <span class="badge bg-primary">Sedang Berlangsung</span>

        </div>

    </div>
</div>
        </div>
    </div>

</div>
</div>