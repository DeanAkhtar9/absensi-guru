<?php
session_start();
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('guru');

include "../config/database.php";

date_default_timezone_set('Asia/Jakarta');

$id_user = $_SESSION['id_user'];

/* =========================
   AMBIL PARAMETER (FIX)
========================= */
$kelas = isset($_GET['kelas']) ? trim($_GET['kelas']) : '';
$mapel = isset($_GET['mapel']) ? trim($_GET['mapel']) : '';

if($kelas=='' || $mapel==''){
    $_SESSION['error'] = "❌ Data tidak valid!";
    header("Location: jurnal.php");
    exit;
}

/* =========================
   CEK ABSENSI (FIX)
========================= */
$cek = mysqli_query($conn,"
SELECT id_absensi_guru,status,created_at 
FROM absensi_guru
WHERE id_user='$id_user'
AND DATE(tanggal)=CURDATE()
ORDER BY id_absensi_guru DESC
LIMIT 1
");

$absen = mysqli_fetch_assoc($cek);

if(!$absen){
    $_SESSION['error']="❌ Anda belum diabsen!";
    header("Location: jurnal.php"); 
    exit;
}

if(!in_array($absen['status'],['hadir','izin'])){
    $_SESSION['error']="❌ Status absensi tidak valid!";
    header("Location: jurnal.php"); 
    exit;
}

/* =========================
   BATAS 12 JAM (FIX TOTAL)
========================= */
$batas = strtotime($absen['created_at']) + 43200;

if(time() > $batas){
    $_SESSION['error']="❌ Waktu isi jurnal sudah lewat!";
    header("Location: jurnal.php"); 
    exit;
}

/* =========================
   CEK DUPLIKAT
========================= */
$cek2 = mysqli_query($conn,"
SELECT 1 FROM jurnal_mengajar
WHERE diisi_oleh='$id_user'
AND DATE(tanggal)=CURDATE()
AND kelas='$kelas'
AND mapel='$mapel'
");

if(mysqli_num_rows($cek2)>0){
    $_SESSION['error']="❌ Jurnal sudah diisi!";
    header("Location: jurnal.php"); 
    exit;
}

/* =========================
   SIMPAN DATA
========================= */
if($_SERVER['REQUEST_METHOD']=='POST'){

    $materi = mysqli_real_escape_string($conn,$_POST['materi']);
    $tanggal = date("Y-m-d H:i:s");

    $insert = mysqli_query($conn,"
    INSERT INTO jurnal_mengajar
    (id_absensi_guru,diisi_oleh,tanggal,materi,kelas,mapel,status_verifikasi)
    VALUES
    ('".$absen['id_absensi_guru']."','$id_user','$tanggal','$materi','$kelas','$mapel','tersimpan')
    ");

    if($insert){
        $_SESSION['success']="✅ Jurnal berhasil disimpan";
    }else{
        $_SESSION['error']="❌ Gagal simpan jurnal!";
    }

    header("Location: jurnal.php"); 
    exit;
}

include "../templates/header.php";
include "../sidebar.php";
include "../header.php";
?>

<div class="main-content p-4">

<a href="jurnal.php" class="btn btn-secondary mb-3">← Kembali</a>

<div class="card p-4 shadow-sm">

<h5 class="fw-bold">Isi Jurnal</h5>

<p><b>Kelas:</b> <?= htmlspecialchars($kelas) ?></p>
<p><b>Mapel:</b> <?= htmlspecialchars($mapel) ?></p>

<form method="POST">

<label class="form-label">Materi</label>
<textarea name="materi" class="form-control" rows="5" required></textarea>

<button class="btn btn-primary w-100 mt-3">
Simpan Jurnal
</button>

</form>

</div>

</div>

<?php include "../templates/footer.php"; ?>