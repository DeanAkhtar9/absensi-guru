detail_jadwal:
<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('admin');

require "../config/database.php";

date_default_timezone_set('Asia/Jakarta');

include "../templates/header.php";
include "../sidebar.php";
include "../header.php";

/* VALIDASI PARAM */
if (!isset($_GET['kelas']) || empty($_GET['kelas'])) {
    die("Kelas tidak ditemukan.");
}

$nama_kelas = $_GET['kelas'];
// echo "Kelas dari URL: ".$nama_kelas."<br>";

/* AMBIL DATA MASTER */
$url_master = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=799813071&single=true&output=csv";

$csv_master = file_get_contents($url_master);
$rows_master = array_map("str_getcsv", explode("\n", $csv_master));

$sheetMap = [];

foreach ($rows_master as $i => $row) {

    // if ($i == 0) continue; // skip header
    if (count($row) < 2) continue;

    $kelas = trim($row[0]);
    $gid = trim($row[1]);

    $sheetMap[$kelas] = $gid;
}
// echo "<pre>";
// print_r($sheetMap);
// echo "</pre>";
// exit;

/* CEK SHEET ADA */
if (!isset($sheetMap[$nama_kelas])) {
    die("Sheet tidak valid.");
}

/* URL CSV GOOGLE SHEET */
$url = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=".$sheetMap[$nama_kelas]."&single=true&output=csv";

/* AMBIL DATA */
$data = file_get_contents($url);
$rows = array_map("str_getcsv", explode("\n", $data));

/* AMBIL DATA GURU */
$guruResult = mysqli_query($conn, "SELECT id_user, nama FROM users");
$guruList = [];

while ($g = mysqli_fetch_assoc($guruResult)) {
    $guruList[$g['id_user']] = $g['nama'];
}
?>

<style>

/* CARD */
.jadwal-card {
    border: 1px solid #e3e6f0;
    border-radius: 12px;
    background: #ffffff;
    padding: 10px;
}

/* HEADER TABLE (THEAD) */
.table-custom thead {
    background: #eef2ff; /* biru sangat samar */
}

.table-custom thead th {
    border-bottom: 1px solid #e3e6f0;
    font-size: 12px;
    text-transform: uppercase;
    color: #3b5bdb;
    font-weight: 600;
    text-align: center;
    padding: 14px;
}

/* TABLE BODY */
.table-custom tbody td {
    border-bottom: 1px solid #edf2f7; /* garis tipis */
    padding: 14px;
    font-size: 14px;
}

/* HOVER ROW */
.table-custom tbody tr:hover {
    background: #f8f9ff;
}

/* BUTTON KEMBALI (MINIMALIS) */
.btn-kembali {
    background: transparent;
    border: none;
    color: #4e73df;
    font-size: 14px;
    padding: 6px 10px;
    border-radius: 8px;
    transition: 0.2s;
}

/* HOVER */
.btn-kembali:hover {
    background: #eef2ff;
    color: #224abe;
}

/* FONT STYLE LEBIH HALUS */
body {
    font-family: 'Inter', sans-serif;
}



</style>

<link rel="stylesheet" href="../assets/css/bootstrap.min.css">

<div class="main-content">
<div class="container py-4">

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-semibold mb-0">
        Jadwal Kelas <?= htmlspecialchars($nama_kelas) ?>
    </h5>

    <a href="jadwal2.php" class="btn-kembali">
    ← Kembali
</a>

</div>

<!-- CARD -->
<div class="card jadwal-card">
<div class="card-body">

<div class="table-responsive">
<table class="table align-middle table-custom">

<thead>
<tr>
<th>No</th>
<th>Nama Guru</th>
<th>Mapel</th>
<th>Hari</th>
<th>Jam</th>
</tr>
</thead>

<tbody>

<?php
$no = 1;

foreach ($rows as $i => $row) {

    if ($i == 0) continue;
    if (count($row) < 5) continue;

    $id_guru = intval($row[0]);
    $mapel = $row[1];
    $hari = $row[2];
    $jam_mulai = $row[3];
    $jam_selesai = $row[4];

    $nama_guru = isset($guruList[$id_guru])
        ? $guruList[$id_guru]
        : "<span class='text-danger'>Tidak ditemukan</span>";
?>

<tr>
<td class="text-center"><?= $no++ ?></td>
<td><?= htmlspecialchars($nama_guru) ?></td>
<td><?= htmlspecialchars($mapel) ?></td>
<td class="text-center"><?= htmlspecialchars($hari) ?></td>
<td class="text-center"><?= $jam_mulai ?> - <?= $jam_selesai ?></td>
</tr>

<?php } ?>

</tbody>
</table>
</div>

</div>
</div>

</div>
</div>


<?php include "../templates/footer.php"; ?>