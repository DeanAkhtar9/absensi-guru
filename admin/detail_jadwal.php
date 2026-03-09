<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('admin');

require "../config/database.php";
require "../vendor/autoload.php";

date_default_timezone_set('Asia/Jakarta');

include "../templates/header.php";
include "../sidebar.php";
include "../header.php";

/* =============================
   VALIDASI PARAMETER
============================= */
if (!isset($_GET['kelas']) || empty($_GET['kelas'])) {
    die("Kelas tidak ditemukan.");
}

$nama_kelas = trim($_GET['kelas']);

/* =============================
   GOOGLE API SETUP
============================= */
$client = new Google_Client();
$client->setAuthConfig('../config/credentials.json');
$client->addScope(Google_Service_Sheets::SPREADSHEETS_READONLY);

$service = new Google_Service_Sheets($client);
$spreadsheetId = "1yeYr0ETjoEHmx5HYrS5j2pIu9MJSjotgkffn31JAd4I";

$range = "'" . $nama_kelas . "'!A2:E";

/* =============================
   AMBIL DATA SHEET
============================= */
try {
    $response = $service->spreadsheets_values->get($spreadsheetId, $range);
    $rows = $response->getValues();
} catch (Exception $e) {
    $rows = [];
}

/* =============================
   AMBIL SEMUA DATA GURU (1x QUERY)
============================= */
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

<?php if (empty($rows)) : ?>

<div class="alert alert-secondary">
    Tidak ada jadwal untuk kelas ini.
</div>

<?php else: ?>

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

foreach ($rows as $row) {

    if (count($row) < 5) continue;

    $id_guru = intval($row[0]);
    $mapel = $row[1];
    $hari = $row[2];
    $jam_mulai = $row[3];
    $jam_selesai = $row[4];

    $nama_guru = isset($guruList[$id_guru]) 
        ? $guruList[$id_guru] 
        : '<span class="text-danger">Tidak ditemukan</span>';

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

<?php endif; ?>

</div>
</div>

<?php include "../templates/footer.php"; ?>
