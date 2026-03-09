<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('guru');

require "../vendor/autoload.php";

date_default_timezone_set('Asia/Jakarta');

include "../templates/header.php";
include "../templates/navbar.php";
include "../sidebar.php";
include "../header.php";

$id_guru_login = intval($_SESSION['id_user']);

/* ==========================
   GOOGLE API CONFIG
========================== */
$client = new Google_Client();
$client->setAuthConfig('../config/cred.json');
$client->addScope(Google_Service_Sheets::SPREADSHEETS_READONLY);

$service = new Google_Service_Sheets($client);

$spreadsheetId = "1yeYr0ETjoEHmx5HYrS5j2pIu9MJSjotgkffn31JAd4I";

/* ==========================
   AMBIL MASTER SHEET
========================== */
$masterRange = "Master!A2:A";
$masterResponse = $service->spreadsheets_values->get($spreadsheetId, $masterRange);
$masterSheets = $masterResponse->getValues();

/* Mapping hari */
$hariMap = [
    'Sunday'    => 'Minggu',
    'Monday'    => 'Senin',
    'Tuesday'   => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday'  => 'Kamis',
    'Friday'    => 'Jumat',
    'Saturday'  => 'Sabtu'
];

$hari_ini = $hariMap[date('l')];
$jam_sekarang = date('H:i');

?>

<div class="container py-4">
    <h3 class="mb-3">Jadwal Mengajar Hari Ini</h3>

    <div class="mb-4">
        <span class="badge bg-primary">Hari: <?= $hari_ini ?></span>
        <span class="badge bg-dark">Jam: <?= $jam_sekarang ?></span>
    </div>

    <div class="row">

<?php
$adaJadwal = false;

if (!empty($masterSheets)) {

    foreach ($masterSheets as $sheetRow) {

        $sheetName = $sheetRow[0];

        $range = $sheetName . "!A2:F";
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $rows = $response->getValues();

        if (empty($rows)) continue;

        foreach ($rows as $row) {

            if (count($row) < 6) continue;

            $id_guru = $row[0];
            $nama_guru = $row[1];
            $mapel = $row[2];
            $hari = $row[3];
            $jam_mulai = $row[4];
            $jam_selesai = $row[5];

            if ($id_guru == $id_guru_login && $hari == $hari_ini) {

                $adaJadwal = true;

                $now = strtotime($jam_sekarang);
                $mulai = strtotime($jam_mulai);
                $selesai = strtotime($jam_selesai);

                if ($now >= $mulai && $now <= $selesai) {
                    $status = "Sedang Mengajar";
                    $alertClass = "alert-success";
                } elseif ($now < $mulai) {
                    $status = "Belum Mulai";
                    $alertClass = "alert-info";
                } else {
                    $status = "Selesai";
                    $alertClass = "alert-secondary";
                }
?>

        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5><?= htmlspecialchars($mapel) ?></h5>

                    <p class="mb-1">
                        Kelas:
                        <b><?= htmlspecialchars($sheetName) ?></b>
                    </p>

                    <p class="text-muted">
                        <?= $jam_mulai ?> - <?= $jam_selesai ?>
                    </p>

                    <div class="alert <?= $alertClass ?> py-2 text-center">
                        <?= $status ?>
                    </div>

                </div>
            </div>
        </div>

<?php
            }
        }
    }
}

if (!$adaJadwal) {
    echo '<div class="alert alert-secondary">Tidak ada jadwal hari ini.</div>';
}
?>

    </div>
</div>

<?php include "../templates/footer.php"; ?>
