<?php
session_start();

require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('guru');

require "../config/database.php";

date_default_timezone_set('Asia/Jakarta');

/* =========================
   FUNCTION
========================= */
function getCSV($url){
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $out = curl_exec($ch);
    curl_close($ch);
    return $out;
}

function formatJam($jam){
    return str_replace('.', ':', trim($jam));
}

/* =========================
   DATA
========================= */
$id_user = $_SESSION['id_user'];

$jadwal = [];

$url_master = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=799813071&output=csv";

$rows_master = array_map("str_getcsv", explode("\n", getCSV($url_master)));

foreach ($rows_master as $row){

    if(count($row)<2) continue;

    $kelas = trim($row[0]);
    $gid   = trim($row[1]);

    if(!$kelas || !$gid) continue;

    $url = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=$gid&output=csv";

    $rows = array_map("str_getcsv", explode("\n", getCSV($url)));

    foreach ($rows as $i=>$r){

        if($i==0 || count($r)<5) continue;

        $id_guru = intval($r[0]);

        if($id_guru != $id_user) continue;

        $hari = trim($r[2]);
        $mapel = $r[1];
        $jam_mulai = formatJam($r[3]);
        $jam_selesai = formatJam($r[4]);

        $jadwal[$hari][] = [
            'kelas'=>$kelas,
            'mapel'=>$mapel,
            'jam'=>$jam_mulai." - ".$jam_selesai
        ];
    }
}

/* URUTKAN HARI */
$urutan = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];

?>

<?php include "../templates/header.php"; ?>
<?php include "../sidebar.php"; ?>
<?php include "../header.php"; ?>

<div class="main-content p-4">

<h4 class="mb-4">Jadwal Mengajar Saya</h4>

<?php foreach($urutan as $h): ?>

<div class="card mb-3 shadow-sm">
<div class="card-header fw-bold"><?= $h ?></div>
<div class="card-body">

<?php if(!empty($jadwal[$h])): ?>

<?php foreach($jadwal[$h] as $j): ?>

<div class="mb-2">
<b><?= $j['kelas'] ?></b> |
<?= $j['mapel'] ?> |
<?= $j['jam'] ?>
</div>

<?php endforeach; ?>

<?php else: ?>

<div class="text-muted">Tidak ada jadwal</div>

<?php endif; ?>

</div>
</div>

<?php endforeach; ?>

</div>

<?php include "../templates/footer.php"; ?>