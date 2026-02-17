<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('guru');

include "../config/database.php";
include "../templates/header.php";
include "../templates/navbar.php";
include "../sidebar.php";

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

<div class="container mt-4">
    <h3>Dashboard Guru</h3>
    <p>Selamat datang, <b><?= $_SESSION['nama'] ?></b></p>

    <div class="row mt-4">

        <div class="col-md-3">
            <div class="card text-center shadow">
                <div class="card-body">
                    <h6>Jumlah Kelas</h6>
                    <h2><?= $total_kelas ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center shadow">
                <div class="card-body">
                    <h6>Total Siswa</h6>
                    <h2><?= $total_siswa ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center shadow">
                <div class="card-body">
                    <h6>Jurnal Minggu Ini</h6>
                    <h2><?= $total_jurnal ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center shadow">
                <div class="card-body">
                    <h6>Rata Kehadiran</h6>
                    <h2><?= $rata_kehadiran ?>%</h2>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include "../templates/footer.php"; ?>
