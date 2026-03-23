<?php
session_start();

require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('guru');

include "../config/database.php";

date_default_timezone_set('Asia/Jakarta');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$id_user = $_SESSION['id_user'];

/* =========================
   FUNCTION CURL
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
   AUTO DETECT JADWAL
========================= */
$kelas_aktif = null;
$jam_mulai_fix = null;
$jam_selesai_fix = null;

$url_master = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=799813071&single=true&output=csv";

$csv_master = getCSV($url_master);
$rows_master = array_map("str_getcsv", explode("\n", $csv_master));

$jam_sekarang = date("H:i");

$hari = date('l');
$hariIndo = [
 'Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa',
 'Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'
];
$hari = $hariIndo[$hari];

/* LOOP SEMUA KELAS */
foreach ($rows_master as $row) {

    if(count($row) < 2) continue;

    $kelas = trim($row[0]);
    $gid   = trim($row[1]);

    if(!$kelas || !$gid) continue;

    $url = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=".$gid."&output=csv";

    $csv = getCSV($url);
    $rows = array_map("str_getcsv", explode("\n", $csv));

    foreach ($rows as $i => $r) {
        if ($i == 0) continue;
        if (count($r) < 5) continue;

        $id_guru_sheet = intval($r[0]);
        $hari_sheet    = trim($r[2]);
        $jam_mulai     = substr(trim($r[3]),0,5);
        $jam_selesai   = substr(trim($r[4]),0,5);

        if (
            $id_guru_sheet == $id_user &&
            strtolower($hari_sheet) == strtolower($hari) &&
            $jam_sekarang >= $jam_mulai &&
            $jam_sekarang <= $jam_selesai
        ) {
            $kelas_aktif = $kelas;
            $jam_mulai_fix = $jam_mulai;
            $jam_selesai_fix = $jam_selesai;
            break 2;
        }
    }
}

/* =========================
   PROSES SIMPAN
========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $tanggal = $_POST['tanggal'];
    $materi  = mysqli_real_escape_string($conn, $_POST['materi']);

    if(!$kelas_aktif){
        $_SESSION['error'] = "❌ Tidak ada jadwal mengajar!";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }

    /* CEK ABSENSI */
    $cek = mysqli_query($conn, "
        SELECT id_absensi_guru, status
        FROM absensi_guru
        WHERE id_user='$id_user'
        AND DATE(tanggal)='$tanggal'
    ");

    $absen = mysqli_fetch_assoc($cek);

    if (!$absen) {
        $_SESSION['error'] = "❌ Belum absen!";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }

    if (!in_array($absen['status'], ['hadir','izin'])) {
        $_SESSION['error'] = "❌ Tidak bisa isi jurnal!";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }

    $id_absensi = $absen['id_absensi_guru'];

    /* SIMPAN */
    $query = mysqli_query($conn, "
        INSERT INTO jurnal_mengajar
        (id_absensi_guru, diisi_oleh, tanggal, materi, kelas, status_verifikasi)
        VALUES
        ('$id_absensi', '$id_user', '$tanggal', '$materi', '$kelas_aktif', 'tersimpan')
    ");

    $_SESSION[$query ? 'success' : 'error'] =
        $query ? "✅ Jurnal berhasil disimpan!" : "❌ Gagal menyimpan!";

    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}
?>

<?php
include "../templates/header.php";
include "../sidebar.php";
include "../header.php";
?>

<!-- =========================
     CONTENT (AMAN SIDEBAR)
========================= -->
<div class="content-area">

<h4 class="fw-bold mb-3">Isi Jurnal Mengajar</h4>

<?php if(isset($_SESSION['error'])): ?>
<div class="alert alert-danger"><?= $_SESSION['error']; ?></div>
<?php unset($_SESSION['error']); endif; ?>

<?php if(isset($_SESSION['success'])): ?>
<div class="alert alert-success"><?= $_SESSION['success']; ?></div>
<?php unset($_SESSION['success']); endif; ?>


<?php if($kelas_aktif): ?>

<!-- CARD JADWAL -->
<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <h5 class="fw-bold">Jadwal Aktif</h5>
        <p class="mb-1"><b>Kelas:</b> <?= $kelas_aktif ?></p>
        <p class="mb-1"><b>Jam:</b> <?= $jam_mulai_fix ?> - <?= $jam_selesai_fix ?></p>
    </div>
</div>

<!-- FORM -->
<form method="POST" class="card p-4 shadow-sm">

<div class="mb-3">
<label>Tanggal</label>
<input type="date" name="tanggal" class="form-control" required>
</div>

<div class="mb-3">
<label>Materi</label>
<textarea name="materi" class="form-control" rows="4" required></textarea>
</div>

<button type="submit" class="btn btn-primary w-100">
Simpan Jurnal
</button>

</form>

<?php else: ?>

<!-- ALERT JIKA TIDAK ADA JADWAL -->
<div class="alert alert-warning">
    ❗ Saat ini tidak ada jadwal mengajar
</div>

<?php endif; ?>

</div>

<style>
.content-area{
    padding: 20px;
    max-width: 800px;
    margin: auto;
}
</style>

<script>
setTimeout(()=>{
 let a=document.querySelector('.alert');
 if(a){
    a.style.opacity=0;
    setTimeout(()=>a.remove(),500);
 }
},3000);
</script>

<?php include "../templates/footer.php"; ?>
