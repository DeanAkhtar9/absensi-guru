<?php
session_start();

require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('guru');

include "../config/database.php";

date_default_timezone_set('Asia/Jakarta');

$id_guru = $_SESSION['id_user'];

/* =========================
   PROSES SIMPAN JURNAL
========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $tanggal       = $_POST['tanggal'];
    $kegiatan      = mysqli_real_escape_string($conn, $_POST['materi']);
    $status_guru   = $_POST['status_verifikasi'];
    $nama_kelas    = $_POST['kelas'];

    $hari = date('l', strtotime($tanggal));
    $hariIndo = [
        'Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa',
        'Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'
    ];
    $hari = $hariIndo[$hari];

    /* =========================
       AMBIL MASTER SHEET
    ========================= */
    $url_master = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=799813071&single=true&output=csv";

    $csv_master = file_get_contents($url_master);
    $rows_master = array_map("str_getcsv", explode("\n", $csv_master));

    $sheetMap = [];
    foreach ($rows_master as $row) {
        if (count($row) < 2) continue;
        $sheetMap[trim($row[0])] = trim($row[1]);
    }

    if (!isset($sheetMap[$nama_kelas])) {
        $_SESSION['error'] = "❌ Kelas tidak ditemukan!";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }

    /* =========================
       AMBIL JADWAL DARI SHEET
    ========================= */
    $gid = $sheetMap[$nama_kelas];

    $url = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=".$gid."&single=true&output=csv";

    $data = file_get_contents($url);
    $rows = array_map("str_getcsv", explode("\n", $data));

    $jam_sekarang = date("H:i");
    $jadwal_valid = false;

    foreach ($rows as $i => $row) {
        if ($i == 0) continue;
        if (count($row) < 5) continue;

        $id_guru_sheet = intval($row[0]);
        $hari_sheet    = trim($row[2]);
        $jam_mulai     = substr(trim($row[3]),0,5);
        $jam_selesai   = substr(trim($row[4]),0,5);

        if (
            $id_guru_sheet == $id_guru &&
            strtolower($hari_sheet) == strtolower($hari) &&
            $jam_sekarang >= $jam_mulai &&
            $jam_sekarang <= $jam_selesai
        ) {
            $jadwal_valid = true;
            break;
        }
    }

    if (!$jadwal_valid) {
        $_SESSION['error'] = "❌ Anda hanya bisa mengisi jurnal saat jam mengajar!";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }

    /* =========================
       SIMPAN KE DATABASE
    ========================= */
    $query = mysqli_query($conn, "
        INSERT INTO jurnal_mengajar
        (diisi_oleh, tanggal, materi, status_verifikasi, kelas)
        VALUES
        ('$id_guru', '$tanggal', '$kegiatan', '$status_guru', '$nama_kelas')
    ");

    if ($query) {
        $_SESSION['success'] = "✅ Jurnal berhasil disimpan!";
    } else {
        $_SESSION['error'] = "❌ Gagal menyimpan jurnal!";
    }

    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}
?>

<?php
include "../templates/header.php";
include "../sidebar.php";
include "../header.php";
?>

<style>
.content-area{
    padding:30px;
    max-width:1100px;
    margin:0 auto;
}

.form-card{
    background:white;
    border-radius:12px;
    padding:30px;
    box-shadow:0 4px 12px rgba(0,0,0,0.06);
    max-width:700px;
    width:100%;
}

.btn-simpan{
    width:100%;
    padding:12px;
    border-radius:10px;
    font-weight:500;
}

.form-label{
    font-weight:500;
}
</style>

<div class="content-area">

<h4 class="fw-bold">Isi Jurnal Harian</h4>
<p class="text-muted">
Catat kegiatan pembelajaran hari ini.
</p>

<!-- ALERT -->
<?php if(isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <?= $_SESSION['error']; ?>
</div>
<?php unset($_SESSION['error']); endif; ?>

<?php if(isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <?= $_SESSION['success']; ?>
</div>
<?php unset($_SESSION['success']); endif; ?>

<div class="d-flex justify-content-center mt-4">
<div class="form-card">

<form method="POST">

<div class="mb-3">
<label class="form-label">Tanggal</label>
<input type="date" name="tanggal" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Kegiatan</label>
<textarea name="kegiatan" class="form-control" rows="4" required></textarea>
</div>

<div class="row">

<div class="col-md-6 mb-3">
<label class="form-label">Status</label>
<select name="status_guru" class="form-select">
<option value="hadir">Hadir</option>
<option value="izin">Izin</option>
<option value="sakit">Sakit</option>
</select>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Kelas</label>
<select name="kelas" class="form-select" required>

<?php
$url_master = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=799813071&single=true&output=csv";

$csv = file_get_contents($url_master);
$rows = array_map("str_getcsv", explode("\n", $csv));

foreach ($rows as $row) {
    if (!empty($row[0])) {
        echo "<option value='".htmlspecialchars($row[0])."'>".$row[0]."</option>";
    }
}
?>

</select>
</div>

</div>

<button type="submit" class="btn btn-primary btn-simpan">
Simpan Jurnal
</button>

</form>

</div>
</div>
</div>

<script>
// AUTO HILANG ALERT
setTimeout(() => {
    let alert = document.querySelector('.alert');
    if(alert){
        alert.style.transition = "0.5s";
        alert.style.opacity = "0";
        setTimeout(() => alert.remove(), 500);
    }
}, 3000);
</script>

<?php include "../templates/footer.php"; ?>
