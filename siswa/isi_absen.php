
<?php
session_start();
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('siswa');
require "../config/database.php";

$id_user = $_SESSION['id_user'];
$nama_siswa = $_SESSION['nama'];

$id_jadwal = $_GET['id'] ?? '';
$id_guru   = $_GET['guru'] ?? '';
$nama_kelas = $_GET['kelas'] ?? 'Kelas Anda';

if(!$id_jadwal || !$id_guru){
    die("Data tidak valid");
}

/* =========================
   CEK SUDAH ABSEN
========================= */
$cek = mysqli_query($conn,"
SELECT 1 FROM absensi_guru 
WHERE id_user='$id_guru' 
AND DATE(tanggal)=CURDATE()
");

$sudahAbsen = mysqli_num_rows($cek) > 0;

/* =========================
   PROSES SIMPAN
========================= */
if($_SERVER['REQUEST_METHOD']=='POST' && !$sudahAbsen){

    $status = $_POST['status'];
    $ket    = $_POST['keterangan'] ?? '';

    $query = mysqli_query($conn,"
        INSERT INTO absensi_guru 
        (id_jadwal, id_user, diinput_oleh, status, keterangan, tanggal)
        VALUES 
        ('$id_jadwal', '$id_guru', '$id_user', '$status', '$ket', NOW())
    ");

    if($query){
        kirimNotifikasi($id_guru, "Absensi Masuk",
            "Siswa ($nama_siswa) telah mengisi absensi Anda dengan status: ".strtoupper($status)
        );

        $_SESSION['success'] = "Berhasil mengabsen guru.";
        header("Location: absen_guru.php");
        exit;
    }
}
?>

<?php include "../templates/header.php"; ?>
<?php include "../sidebar.php"; ?>
<?php include "../header.php"; ?>

<div class="main-content">

<div class="absen-wrapper">

<div class="absen-card">

<h4>Isi Absensi Guru</h4>
<p class="text-muted">
Silakan isi kehadiran guru sesuai kondisi pembelajaran hari ini.
</p>

<?php if($sudahAbsen): ?>

<!-- ✅ SUDAH ABSEN -->
<div class="alert-success-custom">
    ✔ Guru sudah diabsen hari ini
</div>

<div class="mt-3">
    <a href="absen_guru.php" class="btn btn-kembali">
        ← Kembali
    </a>
</div>

<?php else: ?>

<!-- ✅ FORM -->
<form method="POST">

<div class="mb-3">
<label>Status Kehadiran</label>
<select name="status" class="form-control" required>
<option value="">Pilih Status</option>
<option value="hadir">Hadir</option>
<option value="izin">Izin</option>
<option value="tidak_hadir">Tidak Hadir</option>
</select>
</div>

<div class="mb-4">
<label>Keterangan (Opsional)</label>
<textarea name="keterangan" class="form-control" rows="4"
placeholder="Tambahkan catatan jika diperlukan..."></textarea>
</div>

<a href="absen_guru.php" class="btn btn-kembali" style="background-color: #08745f; color:#fff;">
    Kembali
</a>

<button class="btn btn-simpan" style="color:#fff;">
    Simpan Absensi
</button>


</div>

</form>

<?php endif; ?>

</div>
</div>

</div>

<style>
/* WRAPPER */
.absen-wrapper{
    display: flex;
    justify-content: flex-start;
    padding: 40px;
}

/* CARD */
.absen-card{
    background: #fff;
    border-radius: 16px;
    padding: 30px;
    width: 650px;
    max-width: 100%;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

/* LABEL */
.absen-card label{
    font-size: 13px;
    color: #6b7280;
}

/* INPUT */
.form-control{
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    padding: 10px;
    font-size: 13px;
    background: #f9fafb;
}

/* TEXTAREA */
textarea.form-control{
    resize: none;
}

/* BUTTON SIMPAN */
.btn-simpan{
    background: linear-gradient(135deg,#2563eb,#1d4ed8);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 10px 18px;
    font-size: 13px;
}

/* BUTTON KEMBALI */
.btn-kembali{
    background: #14b8a6;
    color: #fff;
    border-radius: 10px;
    padding: 10px 18px;
    font-size: 13px;
    text-decoration: none;
}

/* ALERT */
.alert-success-custom{
    background: #ecfdf5;
    color: #065f46;
    padding: 12px;
    border-radius: 10px;
    font-size: 14px;
}
</style>

<?php include "../templates/footer.php"; ?>