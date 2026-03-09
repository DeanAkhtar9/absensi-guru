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

/* MAPPING GID SHEET */
$sheetMap = [
    "Sheet1" => "0",
    "Sheet2" => "527615816",
    "Sheet3" => "1166029159"
];

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

<div class="main-content">
<div class="container py-4">

<h3 class="mb-3">Jadwal Kelas <?= htmlspecialchars($nama_kelas) ?></h3>

<a href="jadwal2.php" class="btn btn-secondary mb-4">
← Kembali
</a>

<div class="card shadow-sm">
<div class="card-body">

<div class="table-responsive">
<table class="table table-bordered table-striped align-middle">

<thead class="table-dark text-center">
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

    echo "<tr>";
    echo "<td class='text-center'>".$no++."</td>";
    echo "<td>".htmlspecialchars($nama_guru)."</td>";
    echo "<td>".htmlspecialchars($mapel)."</td>";
    echo "<td class='text-center'>".htmlspecialchars($hari)."</td>";
    echo "<td class='text-center'>".$jam_mulai." - ".$jam_selesai."</td>";
    echo "</tr>";
}
?>

</tbody>
</table>
</div>

</div>
</div>

</div>
</div>

<?php include "../templates/footer.php"; ?>