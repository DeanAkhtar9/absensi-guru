<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('admin');

require "../config/database.php";

include "../templates/header.php";


/* =========================
   AMBIL ID
========================= */
$id = $_GET['id'] ?? 0;

if(!$id){
    echo "<div class='alert alert-danger'>ID tidak ditemukan</div>";
    exit;
}

/* =========================
   AMBIL DATA LAPORAN
========================= */
$stmt = $pdo->prepare("
SELECT 
k.*,
u.nama
FROM komplain k
JOIN siswa s ON k.id_siswa = s.id_siswa
JOIN users u ON s.id_user = u.id_user
WHERE k.id_komplain = ?
");

$stmt->execute([$id]);
$data = $stmt->fetch();

if(!$data){
    echo "<div class='alert alert-danger'>Data tidak ditemukan</div>";
    exit;
}

/* =========================
   UPDATE STATUS (OPSIONAL)
========================= */
if(isset($_POST['status'])){
    
    $status = $_POST['status'];

    $update = $pdo->prepare("
        UPDATE komplain 
        SET status=? 
        WHERE id_komplain=?
    ");

    $update->execute([$status, $id]);

    header("Location: detail_komplain.php?id=".$id);
    exit;
}
?>
<?php

include "../sidebar.php";
include "../header.php";

?>
<link rel="stylesheet" href="../assets/css/bootstrap.min.css">

<div class="main-content">
<div class="container py-4">

<h4 class="fw-bold mb-3">Detail Laporan</h4>

<div class="card shadow-sm border-0">
<div class="card-body">

<!-- INFO UTAMA -->
<div class="mb-3">
<label class="fw-bold">Nama Pelapor</label>
<p><?= htmlspecialchars($data['nama']) ?></p>
</div>

<div class="mb-3">
<label class="fw-bold">Tanggal</label>
<p><?= date('d M Y', strtotime($data['tanggal'])) ?></p>
</div>

<div class="mb-3">
<label class="fw-bold">Jenis Laporan</label>
<p><?= ucfirst($data['jenis_laporan']) ?></p>
</div>

<div class="mb-3">
<label class="fw-bold">Deskripsi</label>
<div class="border rounded p-3 bg-light">
<?= nl2br(htmlspecialchars($data['pesan'])) ?>
</div>
</div>

<div class="mb-3">
<label class="fw-bold">Status</label><br>

<?php
$status = $data['status'];
$badge = "secondary";

if($status=='baru') $badge="primary";
elseif($status=='diverifikasi') $badge="warning text-dark";
elseif($status=='ditindaklanjuti') $badge="info";
elseif($status=='selesai') $badge="success";
?>

<span class="badge bg-<?=$badge?>">
<?= ucfirst($status) ?>
</span>

</div>

<hr>

<!-- UPDATE STATUS -->
<h6 class="fw-bold mb-2">Ubah Status</h6>

<form method="POST" class="row g-2">

<div class="col-md-4">
<select name="status" class="form-select">

<option value="baru" <?= $status=='baru'?'selected':'' ?>>Baru</option>
<option value="diverifikasi" <?= $status=='diverifikasi'?'selected':'' ?>>Diverifikasi</option>
<option value="ditindaklanjuti" <?= $status=='ditindaklanjuti'?'selected':'' ?>>Ditindaklanjuti</option>
<option value="selesai" <?= $status=='selesai'?'selected':'' ?>>Selesai</option>

</select>
</div>

<div class="col-md-2">
<button class="btn btn-primary w-100">
Update
</button>
</div>

</form>

</div>
</div>

<a href="index.php" class="btn btn-secondary mt-3">
Kembali
</a>

</div>
</div>

<?php include "../templates/footer.php"; ?>
