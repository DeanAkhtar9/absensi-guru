<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('guru');

require "../config/database.php";

date_default_timezone_set('Asia/Jakarta');

include "../templates/header.php";
include "../sidebar.php";
include "../header.php";

/* =========================
   FUNCTION AMBIL CSV (WAJIB)
========================= */
function getCSV($url){
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // penting
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');

    $output = curl_exec($ch);
    curl_close($ch);

    return $output;
}

/* =========================
   DATA USER
========================= */
$id_user = $_SESSION['id_user'];
$nama_user = $_SESSION['nama'];
$role = $_SESSION['role'];

/* =========================
   HARI INI
========================= */
$hari_map = [
    'Sunday' => 'Minggu',
    'Monday' => 'Senin',
    'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis',
    'Friday' => 'Jumat',
    'Saturday' => 'Sabtu'
];

$hari_ini = $hari_map[date('l')];

/* =========================
   AMBIL MASTER SHEET
========================= */
$url_master = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=799813071&single=true&output=csv";

$csv_master = getCSV($url_master);
$rows_master = array_map("str_getcsv", explode("\n", $csv_master));

$sheetMap = [];

foreach ($rows_master as $row) {
    if (count($row) < 2) continue;

    $kelas = trim($row[0]);
    $gid   = trim($row[1]);

    if ($kelas && $gid) {
        $sheetMap[$kelas] = $gid;
    }
}

/* =========================
   AMBIL JADWAL HARI INI
========================= */
$jadwalHariIni = [];

foreach ($sheetMap as $kelas => $gid) {

    $url = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=$gid&output=csv";

    $csv = getCSV($url);
    

    $rows = array_map("str_getcsv", explode("\n", $csv));

    foreach ($rows as $i => $row) {

        if ($i == 0 || count($row) < 5) continue;

        $id_sheet  = intval(trim($row[0]));
        $hari_sheet = strtolower(trim($row[2]));

        if (
            $id_sheet == intval($id_user) &&
            $hari_sheet == strtolower(trim($hari_ini))
        ) {
            $jadwalHariIni[] = [
                'kelas' => $kelas,
                'mapel' => trim($row[1]),
                'jam_mulai' => substr(trim($row[3]),0,5),
                'jam_selesai' => substr(trim($row[4]),0,5)
            ];
        }
    }
}

/* =========================
   HITUNG DATA
========================= */
$total_kelas = count($jadwalHariIni);

/* JURNAL */
$qJurnal = mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM jurnal_mengajar jm
    JOIN absensi_guru ag ON jm.id_absensi_guru = ag.id_absensi_guru
    WHERE ag.id_user = '$id_user'
    AND DATE(ag.created_at) = CURDATE()
");

$jurnal = mysqli_fetch_assoc($qJurnal)['total'] ?? 0;

/* SISWA */
$qSiswa = mysqli_query($conn, "SELECT COUNT(*) as total FROM siswa");
$total_siswa = mysqli_fetch_assoc($qSiswa)['total'] ?? 0;
?>

<link rel="stylesheet" href="../assets/css/bootstrap.min.css">

<div class="main-content">
<div class="px-5 py-4 w-100">

<!-- HEADER -->
<h4 class="fw-bold">Dashboard <?= $role ?> </h4>    
<p class="text-muted mb-4">
Ringkasan aktivitas mengajar hari ini
</p>

<div class="d-flex gap-3 mb-4 flex-wrap">

<!-- Kelas -->
<div class="summary-card flex-fill d-flex align-items-center gap-3">
<i class="bi bi-easel text-primary fs-2"></i>
<div>
<h6>Kelas Hari Ini</h6>
<h4><?= $total_kelas ?> Kelas</h4>
</div>
</div>

<!-- Jurnal -->
<div class="summary-card flex-fill d-flex align-items-center gap-3">
<i class="bi bi-journal-check text-success fs-2"></i>
<div>
<h6>Jurnal Terisi</h6>
<h4><?= $jurnal ?> / <?= $total_kelas ?></h4>
</div>
</div>

<!-- Siswa -->
<div class="summary-card flex-fill d-flex align-items-center gap-3">
<i class="bi bi-people text-warning fs-2"></i>
<div>
<h6>Total Siswa</h6>
<h4><?= $total_siswa ?> Siswa</h4>
</div>
</div>

</div>

<!-- JADWAL -->
<div class="card jadwal-card">
<div class="card-body">

<h6 class="fw-semibold mb-3">Jadwal Mengajar Hari Ini</h6>

<?php if (empty($jadwalHariIni)) { ?>

<div class="alert alert-warning">
Tidak ada jadwal hari ini
</div>

<?php } ?>

<?php foreach ($jadwalHariIni as $j) { ?>

<div class="jadwal-item">

<div>
<div class="jam-box">
<?= $j['jam_mulai'] ?>
</div>
</div>

<div class="flex-grow-1 ms-3">
<strong><?= $j['mapel'] ?> - <?= $j['kelas'] ?></strong><br>
<small class="text-muted">
<?= $j['jam_mulai'] ?> - <?= $j['jam_selesai'] ?>
</small>
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

.jadwal-card {
    border-radius: 12px;
    border: 1px solid #e3e6f0;
}

.jadwal-item {
    display: flex;
    align-items: center;
    padding: 12px;
    border: 1px solid #edf2f7;
    border-radius: 10px;
    margin-bottom: 10px;
}

.jam-box {
    background: #eef2ff;
    padding: 8px;
    border-radius: 8px;
    font-size: 12px;
    text-align: center;
}
</style>
