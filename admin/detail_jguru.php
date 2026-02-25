<?php
require "../auth/auth_check.php";
require "../config/database.php";
include "../sidebar.php";
include "../header.php"; 

$id = $_GET['id'];

$query = mysqli_query($conn, "
SELECT 
    jurnal_mengajar.*,
    users.nama,
    jadwal_mengajar.mapel,
    kelas.nama_kelas,
    absensi_guru.tanggal

FROM jurnal_mengajar
JOIN absensi_guru ON jurnal_mengajar.id_absensi_guru = absensi_guru.id_absensi_guru
JOIN jadwal_mengajar ON absensi_guru.id_jadwal = jadwal_mengajar.id_jadwal
JOIN users ON jadwal_mengajar.id_guru = users.id_user
JOIN kelas ON jadwal_mengajar.id_kelas = kelas.id_kelas

WHERE jurnal_mengajar.id_jurnal = '$id'
");

$data = mysqli_fetch_assoc($query);
?>
<link rel="stylesheet" href="../assets/css/bootstrap.min.css">
<div class="main-content">
<div class="container py-4">

<div class="card shadow-sm">
<div class="card-header bg-primary text-white">
    Detail Jurnal Guru
</div>

<div class="card-body">

<p><strong>Nama Guru:</strong> <?= $data['nama'] ?></p>
<p><strong>Mapel:</strong> <?= $data['mapel'] ?></p>
<p><strong>Kelas:</strong> <?= $data['nama_kelas'] ?></p>
<p><strong>Tanggal:</strong> <?= date('d M Y', strtotime($data['tanggal'])) ?></p>

<hr>

<p><strong>Materi:</strong></p>
<p><?= $data['materi'] ?></p>

<?php if(!empty($data['catatan'])) { ?>
<p><strong>Catatan:</strong></p>
<p><?= $data['catatan'] ?></p>
<?php } ?>

<a href="jurnal_guru.php" class="btn btn-secondary mt-3">
    Kembali
</a>

</div>
</div>

</div>
</div>
