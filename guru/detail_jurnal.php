<?php
session_start();

require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('guru');

include "../config/database.php";

/* =========================
   VALIDASI ID
========================= */
if (!isset($_GET['id'])) {
    die("ID tidak ditemukan");
}

$id = $_GET['id'];

$id_guru = $_SESSION['id_user'];

$query = mysqli_query($conn, "
    SELECT * FROM jurnal_mengajar 
    WHERE id_jurnal = '$id'
    AND diisi_oleh = '$id_guru'
");

$data = mysqli_fetch_assoc($query);

if (!$data) {
    die("Data tidak ditemukan atau bukan milik Anda");
}

?>

<?php include "../templates/header.php"; ?>
<?php include "../sidebar.php"; ?>
<?php include "../header.php"; ?>

<!-- =========================
     CONTENT
========================= -->
<div class="main-content p-4">

<h4 class="fw-bold mb-3">Detail Jurnal</h4>

<!-- tombol kembali -->
<a href="riwayat_jurnal.php" 
class="btn btn-light border mb-4">
← Kembali
</a>

<!-- CARD -->
<div class="card shadow-sm">
<div class="card-body">

<?php
$status = $data['status_verifikasi'];

if($status == 'diverifikasi'){
    $badge = "bg-success-subtle text-success";
}elseif($status == 'draft'){
    $badge = "bg-secondary-subtle text-dark";
}else{
    $badge = "bg-warning-subtle text-warning";
}
?>

<div class="row mb-3">
<div class="col-md-6">
<label class="text-muted small">Tanggal</label>
<div class="fw-semibold">
<?= date('d M Y H:i', strtotime($data['tanggal'])) ?>
</div>
</div>

<div class="col-md-6">
<label class="text-muted small">Status</label><br>
<span class="badge <?= $badge ?>">
<?= ucfirst($status) ?>
</span>
</div>
</div>

<div class="row mb-3">
<div class="col-md-6">
<label class="text-muted small">Kehadiran</label>
<div class="fw-semibold">
<?= ucfirst($data['status_verifikasi']) ?>
</div>
</div>

<div class="col-md-6">
<label class="text-muted small">Kelas</label>
<div class="fw-semibold">
<?= htmlspecialchars($data['kelas']) ?>
</div>
</div>
</div>

<hr>

<div class="mb-2">
<label class="text-muted small">Kegiatan Pembelajaran</label>
<div class="p-3 bg-light rounded mt-1" style="white-space: pre-line;">
<?= htmlspecialchars($data['materi']) ?>
</div>
</div>

</div>
</div>

</div>

<?php include "../templates/footer.php"; ?>
