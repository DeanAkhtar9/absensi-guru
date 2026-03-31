<?php
session_start();

require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('guru');

include "../config/database.php";

date_default_timezone_set('Asia/Jakarta');

$id_user = $_SESSION['id_user'];

/* =========================
   FUNCTION AMBIL CSV
========================= */
function getCSV($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

/* =========================
   AMBIL DATA USER
========================= */
$user = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT * FROM users WHERE id_user='$id_user'
"));

/* =========================
   AMBIL MAPEL DARI CSV (HARI INI)
========================= */
$nama_mapel = '-';

$hari_map = [
 'Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa',
 'Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'
];

$hari_ini = strtolower($hari_map[date('l')]);

$url_master = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=799813071&output=csv";

$csv_master = getCSV($url_master);
$rows_master = array_map("str_getcsv", explode("\n", $csv_master));

foreach ($rows_master as $row) {

    if(count($row) < 2) continue;

    $gid = trim($row[1]);
    if(!$gid) continue;

    $url = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=$gid&output=csv";

    $csv = getCSV($url);
    $rows = array_map("str_getcsv", explode("\n", $csv));

    foreach ($rows as $i => $r) {
        if ($i == 0 || count($r) < 3) continue;

        $id_guru_sheet = intval(trim($r[0]));
        $hari_sheet = strtolower(trim($r[2]));

        if($hari_sheet == 'jumaat') $hari_sheet = 'jumat';

        if ($id_guru_sheet == $id_user && $hari_sheet == $hari_ini) {
            $nama_mapel = $r[1];
            break 2; // keluar dari 2 loop
        }
    }
}

/* =========================
   AMBIL KELAS WALI
========================= */
$kelas = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT nama_kelas FROM kelas 
    WHERE id_walikelas='$id_user'
"));

$nama_kelas = $kelas['nama_kelas'] ?? '-';
?>

<?php include "../templates/header.php"; ?>
<?php include "../sidebar.php"; ?>
<?php include "../header.php"; ?>

<link rel="stylesheet" href="../assets/css/bootstrap.min.css">

<style>
.profile-card{
    background:white;
    border-radius:14px;
    padding:20px 30px;
}

.info-item{
    display:flex;
    align-items:center;
    gap:20px;
    padding:20px 0;
    border-bottom:1px solid rgba(0,0,0,0.05);
}

.icon-box{
    width:60px;
    height:60px;
    font-size:28px;
    color:#3d4eff;
    border-radius:10px;
    background:#eef4ff;
    display:flex;
    align-items:center;
    justify-content:center;
}

.label{
    font-size:12px;
    color:#6c757d;
}

.value{
    font-weight:500;
}
.badge
</style>

<div class="main-content p-4">

<div class="profile-card">

<!-- HEADER -->
<div class="d-flex align-items-center gap-3 mb-4">

<div>
<h5 class="fw-bold mb-1">
<?= htmlspecialchars($user['nama']) ?>
</h5>

<div class="d-flex align-items-center gap-0 small" style="color: #0a64d2;">
<?= htmlspecialchars($role)?></span> <?= htmlspecialchars($nama_mapel)?>
</div>

</div>

</div>

<hr>

<!-- INFO -->
<div class="mt-3">

<div class="info-item">
<div class="icon-box bi bi-envelope"></div>
<div>
<div class="label">EMAIL INSTANSI</div>
<div class="value"><?= htmlspecialchars($user['email']) ?></div>
</div>
</div>

<div class="info-item">
<div class="icon-box bi bi-telephone"></div>
<div>
<div class="label">NOMOR TELEPON</div>
<div class="value"><?= htmlspecialchars($user['no_telp']) ?></div>
</div>
</div>

<div class="info-item">
<div class="icon-box bi bi-house"></div>
<div>
<div class="label">UNIT KERJA</div>
<div class="value">SMKN 10 SURABAYA</div>
</div>
</div>

</div>

</div>

</div>

<?php include "../templates/footer.php"; ?>