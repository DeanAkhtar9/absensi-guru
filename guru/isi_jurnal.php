<?php
session_start();
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('guru');

include "../config/database.php";

date_default_timezone_set('Asia/Jakarta');

$id_user = $_SESSION['id_user'];

/* =========================
   AMBIL PARAMETER
========================= */
$kelas = isset($_GET['kelas']) ? trim($_GET['kelas']) : '';
$mapel = isset($_GET['mapel']) ? trim($_GET['mapel']) : '';

if($kelas=='' || $mapel==''){
    $_SESSION['error'] = "❌ Data tidak valid!";
    header("Location: jurnal.php");
    exit;
}

/* =========================
   CEK ABSENSI
========================= */
$stmt = $conn->prepare("
    SELECT id_absensi_guru, status, created_at 
    FROM absensi_guru
    WHERE id_user = ?
    AND DATE(tanggal) = CURDATE()
    ORDER BY id_absensi_guru DESC
    LIMIT 1
");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$absen = $stmt->get_result()->fetch_assoc();

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
   BATAS 12 JAM
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
$stmt2 = $conn->prepare("
    SELECT 1 FROM jurnal_mengajar
    WHERE diisi_oleh = ?
    AND DATE(tanggal) = CURDATE()
    AND kelas = ?
    AND mapel = ?
");
$stmt2->bind_param("iss", $id_user, $kelas, $mapel);
$stmt2->execute();
$res2 = $stmt2->get_result();

if($res2->num_rows > 0){
    $_SESSION['error']="❌ Jurnal sudah diisi!";
    header("Location: jurnal.php"); 
    exit;
}

/* =========================
   SIMPAN DATA + NOTIF
========================= */
if($_SERVER['REQUEST_METHOD']=='POST'){

    $materi = trim($_POST['materi']);
    $tanggal = date("Y-m-d H:i:s");

    // INSERT
    $stmt3 = $conn->prepare("
        INSERT INTO jurnal_mengajar
        (id_absensi_guru, diisi_oleh, tanggal, materi, kelas, mapel, status_verifikasi)
        VALUES (?, ?, ?, ?, ?, ?, 'tersimpan')
    ");
    $stmt3->bind_param(
        "iissss",
        $absen['id_absensi_guru'],
        $id_user,
        $tanggal,
        $materi,
        $kelas,
        $mapel
    );

    if($stmt3->execute()){

        // =========================
        // NOTIFIKASI KE WALI KELAS
        // =========================
        $stmt_wali = $conn->prepare("SELECT id_walikelas FROM kelas WHERE nama_kelas = ?");
        $stmt_wali->bind_param("s", $kelas);
        $stmt_wali->execute();
        $data_wali = $stmt_wali->get_result()->fetch_assoc();

        if($data_wali && !empty($data_wali['id_walikelas'])){
            $id_wali = $data_wali['id_walikelas'];
            $nama_guru = $_SESSION['nama'];

            $judul = "Jurnal Terisi: $kelas";
            $pesan = "Guru $nama_guru telah mengisi jurnal mapel $mapel hari ini.";

            // INSERT NOTIF LANGSUNG (AMAN)
            $stmt_notif = $conn->prepare("
                INSERT INTO notifikasi (id_user, judul, pesan)
                VALUES (?, ?, ?)
            ");
            $stmt_notif->bind_param("iss", $id_wali, $judul, $pesan);
            $stmt_notif->execute();
        }

        $_SESSION['success']="✅ Jurnal berhasil disimpan";

    } else {
        $_SESSION['error']="❌ Gagal simpan jurnal! ".$stmt3->error;
    }

    header("Location: jurnal.php"); 
    exit;
}

include "../templates/header.php";
include "../sidebar.php";
include "../header.php";
?>

<div class="main-content">

<div class="container-jurnal">

<div class="card-jurnal">

<h4 class="title">Isi Jurnal Mengajar</h4>
<p class="subtitle">
Silakan dokumentasikan kegiatan pembelajaran hari ini
</p>

<!-- INFO -->
<div class="info-box">

<div class="info-item">
    <span class="label">MATA PELAJARAN</span>
    <span class="value"><?= htmlspecialchars($mapel) ?></span>
</div>

<div class="info-item">
    <span class="label">KELAS</span>
    <span class="value"><?= htmlspecialchars($kelas) ?></span>
</div>

<div class="info-item">
    <span class="label">WAKTU</span>
    <span class="value"><?= date("H:i") ?></span>
</div>

</div>

<form method="POST">

<label>Materi Pembelajaran</label>
<textarea name="materi" required></textarea>

<div class="form-actions">

<a href="jurnal.php" class="btn-kembali">
    Kembali
</a>

<button type="submit" class="btn-simpan">
    Simpan Jurnal
</button>

</div>

</form>

</div>
</div>
</div>

<?php include "../templates/footer.php"; ?>

<style>
    .container-jurnal{
    padding: 40px;
}

/* CARD */
.card-jurnal{
    background: #fff;
    border-radius: 16px;
    padding: 30px;
    width: 700px;
    box-shadow: 0 15px 30px rgba(0,0,0,0.07);
}

/* TITLE */
.title{
    font-weight: bold;
    margin-bottom: 5px;
}

.subtitle{
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 20px;
}

/* INFO BOX */
.info-box{
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
}

.info-item{
    flex: 1;
    padding: 10px;
    border-radius: 10px;
}

.label{
    display: block;
    font-size: 11px;
    color: #6b7280;
    margin-bottom: 4px;
}

.value{
    display: block;           /* biar full baris */
    width: 100%;              /* full lebar */
    font-size: 13px;
    font-weight: 600;
    color: #2563eb;
    background: #f1f5f9;
    padding: 8px 10px;        /* biar ada isi */
    border-radius: 8px;
}

/* TEXTAREA */
textarea{
    width: 100%;
    height: 140px;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    padding: 12px;
    margin-top: 5px;
    background: #f9fafb;
}

/* ACTION BUTTON */
.form-actions{
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}

/* BACK */
.btn-kembali{
    background: #14b8a6;
    color: #fff;
    padding: 10px 18px;
    border-radius: 10px;
    text-decoration: none;
}

/* SUBMIT */
.btn-simpan{
    background: linear-gradient(135deg,#2563eb,#1d4ed8);
    color: #fff;
    border: none;
    padding: 10px 18px;
    border-radius: 10px;
}
</style>