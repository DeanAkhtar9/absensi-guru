<?php
require "../config/config.php";  
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('admin');

require "../config/database.php";
require "../config/functions.php";

include "../sidebar.php";
include "../header.php";
?>

<link rel="stylesheet" href="../assets/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<div class="content-wrapper">

<?php
$total_laporan = $pdo->query("SELECT COUNT(*) FROM laporan_pengaduan")->fetchColumn();
$laporan_baru = $pdo->query("SELECT COUNT(*) FROM laporan_pengaduan WHERE status='BARU'")->fetchColumn();
$laporan_diproses = $pdo->query("SELECT COUNT(*) FROM laporan_pengaduan WHERE status IN ('DIVERIFIKASI','DITINDAKLANJUTI')")->fetchColumn();
$laporan_selesai = $pdo->query("SELECT COUNT(*) FROM laporan_pengaduan WHERE status='SELESAI'")->fetchColumn();
?>

<!-- Header Dashboard -->

<div class="d-flex justify-content-between align-items-center mb-4">

<div>
<h4 class="fw-bold">Dashboard Admin</h4>
<p class="text-muted">Ringkasan sistem dan aktivitas laporan sekolah</p>
</div>

</div>

<!-- Statistik -->

<div class="row mb-4">

<div class="col-md-3">
<div class="card shadow-sm border-0">
<div class="card-body d-flex justify-content-between">

<div>
<h3><?=$total_laporan?></h3>
<small class="text-muted">Total Laporan</small>
</div>

<i class="bi bi-file-earmark-text fs-3 text-primary"></i>

</div>
</div>
</div>

<div class="col-md-3">
<div class="card shadow-sm border-0">
<div class="card-body d-flex justify-content-between">

<div>
<h3><?=$laporan_baru?></h3>
<small class="text-muted">Laporan Baru</small>
</div>

<i class="bi bi-exclamation-circle fs-3 text-danger"></i>

</div>
</div>
</div>

<div class="col-md-3">
<div class="card shadow-sm border-0">
<div class="card-body d-flex justify-content-between">

<div>
<h3><?=$laporan_diproses?></h3>
<small class="text-muted">Diproses</small>
</div>

<i class="bi bi-arrow-repeat fs-3 text-warning"></i>

</div>
</div>
</div>

<div class="col-md-3">
<div class="card shadow-sm border-0">
<div class="card-body d-flex justify-content-between">

<div>
<h3><?=$laporan_selesai?></h3>
<small class="text-muted">Selesai</small>
</div>

<i class="bi bi-check-circle fs-3 text-success"></i>

</div>
</div>
</div>

</div>

<!-- Laporan Terbaru -->

<div class="card shadow-sm border-0">

<div class="card-header bg-white d-flex justify-content-between">

<h5 class="mb-0">Laporan Terbaru</h5>

<a href="verifikasi_laporan.php">Lihat Semua</a>

</div>

<div class="card-body p-0">

<table class="table mb-0">

<thead class="table-light">

<tr>
<th>Tanggal</th>
<th>Nama Pelapor</th>
<th>Jenis Laporan</th>
<th>Status</th>
<th>Aksi</th>
</tr>

</thead>

<tbody>

<?php
$stmt=$pdo->query("SELECT * FROM laporan_pengaduan ORDER BY tanggal_lapor DESC LIMIT 5");

while($row=$stmt->fetch()):
?>

<tr>

<td><?=date('d M Y',strtotime($row['tanggal_lapor']))?></td>

<td><?=$row['nama_pelapor']?></td>

<td><?=$row['jenis_laporan']?></td>

<td>

<?php

$badge="secondary";

if($row['status']=="BARU") $badge="danger";
if($row['status']=="DIVERIFIKASI") $badge="warning";
if($row['status']=="DITINDAKLANJUTI") $badge="primary";
if($row['status']=="SELESAI") $badge="success";

?>

<span class="badge bg-<?=$badge?>">
<?=$row['status']?>
</span>

</td>

<td>

<a href="verifikasi_laporan.php?id=<?=$row['id_laporan']?>" class="btn btn-sm btn-light">
Detail
</a>

</td>

</tr>

<?php endwhile ?>

</tbody>

</table>

</div>

</div>

</div>

<style>

.content-wrapper{
margin-left:250px;
padding:25px;
background:#f5f6fa;
min-height:100vh;
}

</style>
