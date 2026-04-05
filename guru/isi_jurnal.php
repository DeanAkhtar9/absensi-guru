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

        /* =========================
           NOTIF KE WALI KELAS
        ========================= */
        $stmt = $conn->prepare("
            SELECT id_walikelas 
            FROM kelas 
            WHERE nama_kelas = ?
        ");
        $stmt->bind_param("s", $kelas);
        $stmt->execute();
        $res = $stmt->get_result();
        $wali = $res->fetch_assoc();

        if($wali && !empty($wali['id_walikelas'])){
            $judul = "Jurnal Terisi: $kelas";
            $pesan = "Guru ".$_SESSION['nama']." telah mengisi jurnal $mapel hari ini.";
            kirimNotifikasi($wali['id_walikelas'], $judul, $pesan);
        }

        $stmt->close();

        $_SESSION['success']="✅ Jurnal berhasil disimpan";

    }else{
        $_SESSION['error']="❌ Gagal simpan jurnal!";
    }

    header("Location: jurnal.php"); 
    exit;
}

/* ==========================================================
   KIRIM NOTIFIKASI KE WALI KELAS SAAT JURNAL TERISI
   ========================================================== */
if ($query_insert_jurnal_berhasil) { // Ganti dengan variabel status query kamu
    
    // 1. Cari ID Wali Kelas berdasarkan Nama Kelas yang sedang diajar
    // Kita asumsikan nama kelas di jurnal sesuai dengan nama kelas di tabel kelas
    $nama_kelas_input = $_POST['kelas']; // atau dari variable yang ada
    $mapel_input = $_POST['mapel'];
    $nama_guru_pengajar = $_SESSION['nama'];

    $stmt_wali = $conn->prepare("SELECT id_walikelas FROM kelas WHERE nama_kelas = ?");
    $stmt_wali->bind_param("s", $nama_kelas_input);
    $stmt_wali->execute();
    $res_wali = $stmt_wali->get_result();
    $data_wali = $res_wali->fetch_assoc();

    if ($data_wali && !empty($data_wali['id_walikelas'])) {
        $id_wali = $data_wali['id_walikelas'];
        $judul_wali = "Jurnal Terisi: $nama_kelas_input";
        $isi_wali = "Guru $nama_guru_pengajar telah mengisi jurnal mengajar untuk mata pelajaran $mapel_input hari ini.";
        
        kirimNotifikasi($id_wali, $judul_wali, $isi_wali);
    }
    $stmt_wali->close();
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