<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('siswa');

require "../config/database.php";

include "../templates/header.php";
include "../sidebar.php";
include "../header.php";

/* =========================
AMBIL ID SISWA
========================= */

$id_user = $_SESSION['id_user'];

$getSiswa = mysqli_query($conn,"
SELECT id_siswa 
FROM siswa 
WHERE id_user='$id_user'
");

$dataSiswa = mysqli_fetch_assoc($getSiswa);
$id_siswa = $dataSiswa['id_siswa'];


/* =========================
SEARCH & FILTER
========================= */

$search = isset($_GET['search']) ? $_GET['search'] : "";
$mapel  = isset($_GET['mapel']) ? $_GET['mapel'] : "";
$status = isset($_GET['status']) ? $_GET['status'] : "";


/* =========================
PAGINATION
========================= */

$limit = 5;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$offset = ($page - 1) * $limit;


/* =========================
QUERY FILTER
========================= */

$where = "WHERE k.id_siswa='$id_siswa'";

if($search){
$where .= " AND (k.pesan LIKE '%$search%' OR u.nama LIKE '%$search%' OR jm.mapel LIKE '%$search%')";
}

if($mapel){
$where .= " AND jm.mapel='$mapel'";
}

if($status){
$where .= " AND k.status='$status'";
}


/* =========================
TOTAL DATA
========================= */

$total = mysqli_query($conn,"
SELECT COUNT(*) as total
FROM komplain k
JOIN jadwal_mengajar jm ON k.id_jadwal = jm.id_jadwal
JOIN users u ON jm.id_guru = u.id_user
$where
");

$total = mysqli_fetch_assoc($total)['total'];

$totalPages = ceil($total / $limit);


/* =========================
AMBIL DATA LAPORAN
========================= */

$query = mysqli_query($conn,"
SELECT k.*, jm.mapel, u.nama AS guru
FROM komplain k
JOIN jadwal_mengajar jm ON k.id_jadwal = jm.id_jadwal
JOIN users u ON jm.id_guru = u.id_user
$where
ORDER BY k.tanggal DESC
LIMIT $limit OFFSET $offset
");


/* =========================
LIST MAPEL
========================= */

$mapelList = mysqli_query($conn,"
SELECT DISTINCT mapel 
FROM jadwal_mengajar
");

?>

<link rel="stylesheet" href="../assets/css/bootstrap.min.css">

<div class="main-content">

<div class="container py-4">

<h4 class="fw-bold mb-2">Riwayat Laporan</h4>

<p class="text-muted">
Lihat laporan kehadiran guru yang pernah kamu kirim
</p>


<div class="card shadow-sm border-0">

<div class="card-body">

<!-- SEARCH -->

<form method="GET">

<div class="row mb-3">

<div class="col-md-4">

<input type="text" 
name="search" 
class="form-control"
placeholder="Cari laporan..."
value="<?=htmlspecialchars($search)?>">

</div>
<div class="col-md-3">

<select name="status" class="form-select">

<option value="">Semua Status</option>

<option value="baru">Baru</option>
<option value="diverifikasi">Diverifikasi</option>
<option value="ditindaklanjuti">Ditindaklanjuti</option>
<option value="selesai">Selesai</option>

</select>

</div>


<div class="col-md-2 d-flex gap-2">

<button class="btn btn-primary">
Filter
</button>

<a href="riwayat_laporan.php" class="btn btn-secondary">
Reset
</a>

</div>

</div>

</form>


<!-- TABLE -->

<table class="table align-middle">

<thead class="table-light">

<tr>

<th>TANGGAL</th>
<th>GURU</th>
<th>MAPEL</th>
<th>LAPORAN</th>
<th>STATUS</th>

</tr>

</thead>

<tbody>

<?php if(mysqli_num_rows($query)>0): ?>

<?php while($row=mysqli_fetch_assoc($query)): ?>

<tr>

<td><?=$row['tanggal']?></td>

<td><?=$row['guru']?></td>

<td><?=$row['mapel']?></td>

<td><?=$row['pesan']?></td>

<td>

<?php

$status = $row['status'];

if($status=='baru'){
echo "<span class='badge bg-primary'>Baru</span>";
}
elseif($status=='diverifikasi'){
echo "<span class='badge bg-warning text-dark'>Diverifikasi</span>";
}
elseif($status=='ditindaklanjuti'){
echo "<span class='badge bg-info'>Ditindaklanjuti</span>";
}
elseif($status=='selesai'){
echo "<span class='badge bg-success'>Selesai</span>";
}

?>

</td>

</tr>

<?php endwhile ?>

<?php else: ?>

<tr>
<td colspan="5" class="text-center text-muted">
Belum ada laporan
</td>
</tr>

<?php endif ?>

</tbody>

</table>


<!-- PAGINATION -->

<nav>

<ul class="pagination">

<?php for($i=1;$i<=$totalPages;$i++): ?>

<li class="page-item <?=($i==$page)?'active':''?>">

<a class="page-link"
href="?page=<?=$i?>&search=<?=$search?>&mapel=<?=$mapel?>&status=<?=$status?>">

<?=$i?>

</a>

</li>

<?php endfor ?>

</ul>

</nav>

</div>
</div>
</div>
</div>

<?php include "../templates/footer.php"; ?>