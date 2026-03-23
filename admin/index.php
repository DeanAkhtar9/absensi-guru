<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('admin');

require "../config/database.php";

include "../templates/header.php";
include "../sidebar.php";
include "../header.php";

/* =========================
   STATISTIK LAPORAN
========================= */

$total_laporan = $pdo->query("
SELECT COUNT(*) 
FROM komplain
")->fetchColumn();

$laporan_hari_ini = $pdo->query("
SELECT COUNT(*) 
FROM komplain 
WHERE DATE(created_at)=CURDATE()
")->fetchColumn();

$laporan_minggu = $pdo->query("
SELECT COUNT(*) 
FROM komplain 
WHERE YEARWEEK(created_at,1)=YEARWEEK(CURDATE(),1)
")->fetchColumn();

$laporan_bulan = $pdo->query("
SELECT COUNT(*) 
FROM komplain 
WHERE MONTH(created_at)=MONTH(CURDATE())
")->fetchColumn();


/* =========================
   LAPORAN TERBARU
========================= */

$stmt = $pdo->query("
SELECT 
komplain.*,
users.nama,
siswa.id_siswa
FROM komplain
JOIN siswa ON komplain.id_siswa = siswa.id_siswa
JOIN users ON siswa.id_user = users.id_user
ORDER BY komplain.created_at DESC
LIMIT 5
");

?>

<style>
    .card{
border-radius:12px;
}

.table th{
font-size:12px;
color:#6c757d;
text-align: center;
}

.badge{
border-radius:20px;
padding:6px 12px;
}

</style>

<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/bootstrap.min.css">
<div class="main-content">



<div class="container-fluid py-4">
<div >
<h4 class="fw-bold">Dashboard Admin</h4>
<p class="text-muted mb-0">Ringkasan sistem dan aktivitas laporan sekolah</p>
</div>
<!-- HEADER -->

<div class="d-flex justify-content-between align-items-center mb-4">



<div class="text-muted">

</div>

</div>

<!-- CARD STATISTIK -->

<div class="row mb-4">

<div class="col-md-3">
<div class="card shadow-sm border-0">
<div class="card-body d-flex justify-content-between">

<div>
<div class="text-muted small">Total Laporan</div>
<h4 class="fw-bold"><?=number_format($total_laporan)?></h4>
</div>

<i class="bi bi-file-earmark-text text-primary fs-3"></i>

</div>
</div>
</div>


<div class="col-md-3">
<div class="card shadow-sm border-0">
<div class="card-body d-flex justify-content-between">

<div>
<div class="text-muted small">Laporan Hari Ini</div>
<h4 class="fw-bold"><?=$laporan_hari_ini?></h4>
</div>

<i class="bi bi-exclamation-circle text-danger fs-3"></i>

</div>
</div>
</div>


<div class="col-md-3">
<div class="card shadow-sm border-0">
<div class="card-body d-flex justify-content-between">

<div>
<div class="text-muted small">Minggu Ini</div>
<h4 class="fw-bold"><?=$laporan_minggu?></h4>
</div>

<i class="bi bi-arrow-repeat text-warning fs-3"></i>

</div>
</div>
</div>


<div class="col-md-3">
<div class="card shadow-sm border-0">
<div class="card-body d-flex justify-content-between">

<div>
<div class="text-muted small">Bulan Ini</div>
<h4 class="fw-bold"><?=$laporan_bulan?></h4>
</div>

<i class="bi bi-check-circle text-success fs-3"></i>

</div>
</div>
</div>

</div>

<!-- LAPORAN TERBARU -->

<div class="card shadow-sm border-0">

<div class="card-header bg-white d-flex justify-content-between">

<h6 class="mb-0 fw-bold">Laporan Terbaru</h6>

<a href="laporan.php" class="text-primary">
Lihat Semua
</a>

</div>

<div class="table-responsive">

<table class="table align-middle mb-0">

<thead class="table-light">

<tr>
<th>TANGGAL</th>
<th>NAMA PELAPOR</th>
<th>JENIS LAPORAN</th>
<th>STATUS</th>
<th>AKSI</th>
</tr>

</thead>

<tbody>

<?php while($row=$stmt->fetch()): ?>

<tr>

<td><?=date('d M Y',strtotime($row['created_at']))?></td>

<td><?=$row['nama']?></td>

<td><?= ucfirst($row['jenis_laporan'] ?? '-') ?></td>

<td>
<?php
$status = $row['status'];

$badge = "secondary";

if($status == 'baru') $badge = "primary";
elseif($status == 'diverifikasi') $badge = "info text-dark";
elseif($status == 'ditindaklanjuti') $badge = "warning";
elseif($status == 'selesai') $badge = "success";
?>

<span class="badge bg-<?= $badge ?>">
<?= ucfirst($status) ?>
</span>
</td>


<td>

<a href="detail_komplain.php?id=<?=$row['id_komplain']?>" 
class="btn btn-sm btn-light">

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

</div>

<?php include "../templates/footer.php"; ?>
