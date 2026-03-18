<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('guru');

require "../config/database.php";

date_default_timezone_set('Asia/Jakarta');

include "../templates/header.php";
include "../sidebar.php";
include "../header.php";

/* DATA USER */
$id_user = $_SESSION['id_user'];
$nama_user = $_SESSION['nama'];

/* HARI INI */
$hari_ini = date('l');
$hari_map = [
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu'
];
$hari_ini = $hari_map[$hari_ini];

/* AMBIL MASTER SHEET */
$url_master = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=799813071&single=true&output=csv";

$csv_master = file_get_contents($url_master);
$rows_master = array_map("str_getcsv", explode("\n", $csv_master));

$sheetMap = [];

foreach ($rows_master as $row) {
    if (count($row) < 2) continue;
    $sheetMap[trim($row[0])] = trim($row[1]);
}

/* AMBIL SEMUA JADWAL */
$jadwalHariIni = [];

foreach ($sheetMap as $kelas => $gid) {

    $url = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=$gid&single=true&output=csv";

    $csv = file_get_contents($url);
    $rows = array_map("str_getcsv", explode("\n", $csv));

    foreach ($rows as $i => $row) {
        if ($i == 0 || count($row) < 5) continue;

        if ((int)$row[0] == $id_user && $row[2] == $hari_ini) {
            $jadwalHariIni[] = [
                'kelas' => $kelas,
                'mapel' => $row[1],
                'jam_mulai' => $row[3],
                'jam_selesai' => $row[4]
            ];
        }
    }
}

/* HITUNG KELAS */
$total_kelas = count($jadwalHariIni);

/* HITUNG JURNAL */
$qJurnal = mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM jurnal_mengajar jm
    JOIN absensi_guru ag ON jm.id_absensi_guru = ag.id_absensi_guru
    WHERE ag.diinput_oleh = $id_user
    AND DATE(ag.created_at) = CURDATE()
");

$jurnal = mysqli_fetch_assoc($qJurnal)['total'] ?? 0;

/* TOTAL SISWA */
$qSiswa = mysqli_query($conn, "SELECT COUNT(*) as total FROM siswa");
$total_siswa = mysqli_fetch_assoc($qSiswa)['total'];
?>

<link rel="stylesheet" href="../assets/css/bootstrap.min.css">

<div class="main-content">
<div class="container py-4">

<!-- WELCOME -->
<h5 class="fw-semibold">Selamat datang kembali, <?= htmlspecialchars($nama_user) ?>!</h5>
<p class="text-muted mb-4">Berikut adalah ringkasan aktivitas mengajar Anda hari ini.</p>

<!-- CARD SUMMARY -->
<div class="row g-3 mb-4">

<div class="col-md-3">
<div class="summary-card">
<h6>Kelas Hari Ini</h6>
<h4><?= $total_kelas ?> Kelas</h4>
</div>
</div>

<div class="col-md-3">
<div class="summary-card">
<h6>Jurnal Terisi</h6>
<h4><?= $jurnal ?> / <?= $total_kelas ?></h4>
</div>
</div>

<div class="col-md-3">
<div class="summary-card">
<h6>Total Siswa</h6>
<h4><?= $total_siswa ?> Siswa</h4>
</div>
</div>

</div>

<!-- JADWAL -->
<div class="card jadwal-card">
<div class="card-body">

<div class="d-flex justify-content-between mb-3">
<h6 class="fw-semibold">Jadwal Mengajar Terdekat</h6>
</div>

<?php if (empty($jadwalHariIni)) { ?>
<p class="text-muted">Tidak ada jadwal hari ini.</p>
<?php } ?>

<?php foreach ($jadwalHariIni as $j) { ?>

<div class="jadwal-item">
<div>
<div class="jam-box">
<?= substr($j['jam_mulai'],0,5) ?>
</div>
</div>

<div class="flex-grow-1 ms-3">
<strong><?= $j['mapel'] ?> - <?= $j['kelas'] ?></strong><br>
<small class="text-muted">Jam <?= $j['jam_mulai'] ?> - <?= $j['jam_selesai'] ?></small>
</div>

<div>
<span class="badge bg-success">Jadwal</span>
</div>
</div>

<?php } ?>

</div>
</div>

</div>
</div>



<?php include "../templates/footer.php"; ?>



<style>

        /* SUMMARY CARD */
.summary-card {
    background: #fff;
    border-radius: 12px;
    padding: 15px;
    border: 1px solid #e3e6f0;
}

.summary-card h6 {
    font-size: 13px;
    color: #858796;
}

.summary-card h4 {
    font-weight: bold;
}

/* JADWAL CARD */
.jadwal-card {
    border-radius: 12px;
    border: 1px solid #e3e6f0;
}

/* ITEM */
.jadwal-item {
    display: flex;
    align-items: center;
    padding: 12px;
    border: 1px solid #edf2f7;
    border-radius: 10px;
    margin-bottom: 10px;
}

/* JAM BOX */
.jam-box {
    background: #eef2ff;
    padding: 8px;
    border-radius: 8px;
    font-size: 12px;
    text-align: center;
}


</style>
