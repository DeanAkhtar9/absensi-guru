<?php
session_start();

require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('siswa');

require "../config/database.php";

date_default_timezone_set('Asia/Jakarta');

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
   DATA USER
========================= */
$id_user = $_SESSION['id_user']; // siswa login

$q = mysqli_query($conn,"SELECT id_siswa FROM siswa WHERE id_user='$id_user'");
$data = mysqli_fetch_assoc($q);
$id_siswa = $data['id_siswa'] ?? 0;

/* =========================
   DETECT JADWAL
========================= */
$jadwalHariIni = [];

$hari_map = [
 'Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa',
 'Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'
];

$hari_ini = strtolower($hari_map[date('l')]);
$jam_sekarang = date("H:i");

/* MASTER */
$url_master = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=799813071&output=csv";

$csv_master = getCSV($url_master);
$rows_master = array_map("str_getcsv", explode("\n", $csv_master));

foreach ($rows_master as $row) {

    if(count($row) < 2) continue;

    $kelas = trim($row[0]);
    $gid   = trim($row[1]);

    if(!$kelas || !$gid) continue;

    $url = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=$gid&output=csv";

    $csv = getCSV($url);
    $rows = array_map("str_getcsv", explode("\n", $csv));

    foreach ($rows as $i => $r) {
        if ($i == 0 || count($r) < 5) continue;

        $id_guru_sheet = intval(trim($r[0]));
        $hari_sheet = strtolower(trim($r[2]));

        if($hari_sheet == 'jumaat') $hari_sheet = 'jumat';

        $jam_mulai = str_replace('.', ':', trim($r[3]));
        $jam_selesai = str_replace('.', ':', trim($r[4]));

        if (
            $hari_sheet == $hari_ini &&
            $jam_sekarang >= $jam_mulai &&
            $jam_sekarang <= $jam_selesai
        ) {

            $id_jadwal = md5(
                $id_guru_sheet .
                $kelas .
                $hari_sheet .
                $jam_mulai .
                $jam_selesai
            );

            $jadwalHariIni[] = [
                'id_jadwal' => $id_jadwal,
                'id_guru'   => $id_guru_sheet,
                'kelas'     => $kelas,
                'mapel'     => $r[1],
                'jam_mulai' => $jam_mulai,
                'jam_selesai'=> $jam_selesai
            ];
        }
    }
}

/* =========================
   PROSES ABSEN
========================= */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id_jadwal = $_POST['id_jadwal'] ?? null;
    $id_guru   = $_POST['id_guru'] ?? null;
    $status    = $_POST['status'] ?? null;

    if(!$id_jadwal || !$id_guru || !$status){
        $_SESSION['error'] = "❌ Data tidak lengkap!";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }

    /* CEK SUDAH ABSEN BELUM */
    $cek = mysqli_query($conn,"
        SELECT * FROM absensi_guru 
        WHERE id_jadwal='$id_jadwal'
        AND DATE(tanggal)=CURDATE()
    ");

    if(mysqli_num_rows($cek) > 0){
        $_SESSION['error'] = "❌ Guru sudah diabsen!";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }

    /* INSERT */
    $stmt = $conn->prepare("
        INSERT INTO absensi_guru 
        (id_jadwal, id_user, diinput_oleh, status, tanggal)
        VALUES (?, ?, ?, ?, NOW())
    ");

    $stmt->bind_param(
        "siis",
        $id_jadwal,
        $id_guru,      // guru
        $id_user,      // siswa (fix FK)
        $status
    );

    if($stmt->execute()){
        $_SESSION['success'] = "✅ Absensi berhasil!";
    } else {
        $_SESSION['error'] = "❌ Gagal absen!";
    }

    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}
?>

<?php include "../templates/header.php"; ?>
<?php include "../sidebar.php"; ?>
<?php include "../header.php"; ?>

<div class="content-area">

<h4 class="fw-bold mb-3">Absensi Guru</h4>

<?php if(isset($_SESSION['error'])): ?>
<div class="alert alert-danger"><?= $_SESSION['error']; ?></div>
<?php unset($_SESSION['error']); endif; ?>

<?php if(isset($_SESSION['success'])): ?>
<div class="alert alert-success"><?= $_SESSION['success']; ?></div>
<?php unset($_SESSION['success']); endif; ?>

<?php if(empty($jadwalHariIni)): ?>

<div class="alert alert-warning">
    ❗ Tidak ada jadwal saat ini
</div>

<?php else: ?>

<?php foreach($jadwalHariIni as $j): ?>

<div class="card mb-3 shadow-sm">
<div class="card-body">

<h5><?= htmlspecialchars($j['mapel']) ?> - <?= htmlspecialchars($j['kelas']) ?></h5>
<p><?= $j['jam_mulai'] ?> - <?= $j['jam_selesai'] ?></p>

<form method="POST">

<input type="hidden" name="id_jadwal" value="<?= $j['id_jadwal'] ?>">
<input type="hidden" name="id_guru" value="<?= $j['id_guru'] ?>">

<select name="status" class="form-control mb-2" required>
    <option value="">-- Pilih Status --</option>
    <option value="hadir">Hadir</option>
    <option value="tidak_hadir">Tidak Hadir</option>
    <option value="izin">Izin</option>
</select>

<button class="btn btn-primary w-100">Kirim Absensi</button>

</form>

</div>
</div>

<?php endforeach; ?>

<?php endif; ?>

</div>

<style>
.content-area{
    padding:20px;
    max-width:800px;
    margin:auto;
}
</style>

<?php include "../templates/footer.php"; ?>
