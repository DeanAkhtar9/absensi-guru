<?php
session_start();

require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('siswa');

require "../config/database.php";

$id_user = $_SESSION['id_user'];

$id_jadwal = $_GET['id'] ?? '';
$id_guru   = $_GET['guru'] ?? '';

if(!$id_jadwal || !$id_guru){
    die("Data tidak valid");
}

/* CEK SUDAH ABSEN */
$cek = mysqli_query($conn,"
SELECT 1 FROM absensi_guru
WHERE id_user='$id_guru'
AND DATE(tanggal)=CURDATE()
");

if(mysqli_num_rows($cek)>0){
    die("Sudah diabsen");
}

/* PROSES */
if($_SERVER['REQUEST_METHOD']=='POST'){

    $status = $_POST['status'];
    $ket    = $_POST['keterangan'] ?? '';

    mysqli_query($conn,"
    INSERT INTO absensi_guru
    (id_jadwal,id_user,diinput_oleh,status,keterangan,tanggal)
    VALUES
    ('$id_jadwal','$id_guru','$id_user','$status','$ket',NOW())
    ");

    header("Location: absen_guru.php");
    exit;
}
?>

<?php include "../templates/header.php"; ?>

<div class="main-content p-4">

<div class="card p-4 shadow-sm">

<h5>Isi Absensi</h5>

<form method="POST">

<select name="status" class="form-control mb-2" required>
<option value="">Pilih Status</option>
<option value="hadir">Hadir</option>
<option value="izin">Izin</option>
<option value="tidak_hadir">Tidak Hadir</option>
</select>

<textarea name="keterangan" class="form-control mb-2" placeholder="Keterangan..."></textarea>

<button class="btn btn-primary w-100">Simpan</button>

</form>

</div>

</div>