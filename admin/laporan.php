<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('admin');

require "../config/database.php";

include "../templates/header.php";
include "../sidebar.php";
include "../header.php";


/* =========================
   PAGINATION
========================= */

$limit = 5;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$offset = ($page - 1) * $limit;


/* =========================
   TOTAL DATA
========================= */

$totalData = mysqli_query($conn,"SELECT COUNT(*) as total FROM komplain");
$totalData = mysqli_fetch_assoc($totalData)['total'];

$totalPages = ceil($totalData / $limit);


/* =========================
   DATA KOMPLAIN
========================= */

$query = mysqli_query($conn,"
SELECT 
komplain.*,
users.nama
FROM komplain
JOIN siswa ON komplain.id_siswa = siswa.id_siswa
JOIN users ON siswa.id_user = users.id_user
ORDER BY komplain.created_at DESC
LIMIT $limit OFFSET $offset
");

?>

<style>
    .table td{
vertical-align:middle;
}

.badge{
padding:6px 12px;
border-radius:20px;
font-size:12px;
}

.btn-sm{
font-size:12px;
}

</style>

<link rel="stylesheet" href="assets/css/bootstrap.min.css">

<div class="main-content">

<div class="container py-4">

<h4 class="mb-3 fw-bold">Verifikasi Laporan</h4>

<p class="text-muted">Kelola dan perbarui status laporan siswa</p>


<div class="card shadow-sm border-0">

<div class="table-responsive">

<table class="table align-middle">

<thead class="table-light">

<tr>
<th>TANGGAL</th>
<th>NAMA PELAPOR</th>
<th>JENIS LAPORAN</th>
<th>DESKRIPSI</th>
<th>STATUS</th>
<th>AKSI</th>
</tr>

</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($query)): ?>

<tr>

<td>
<?=date('d M Y',strtotime($row['created_at']))?>
</td>

<td><?=$row['nama']?></td>

<td>Sarana Prasarana</td>

<td style="max-width:250px;">
<?=substr($row['pesan'],0,50)?>...
</td>

<td>

<?php
$status=$row['status'];

if($status=='baru'){
echo "<span class='badge bg-primary'>Baru</span>";
}

elseif($status=='diverifikasi'){
echo "<span class='badge bg-info'>Diverifikasi</span>";
}

elseif($status=='ditindaklanjuti'){
echo "<span class='badge bg-warning text-dark'>Ditindaklanjuti</span>";
}

elseif($status=='selesai'){
echo "<span class='badge bg-success'>Selesai</span>";
}
?>

</td>

<td>

<a href="ulaporan.php?id=<?=$row['id_komplain']?>&status=diverifikasi"
class="btn btn-sm btn-primary mb-1">
Diverifikasi
</a>

<a href="ulaporan.php?id=<?=$row['id_komplain']?>&status=ditindaklanjuti"
class="btn btn-sm btn-warning mb-1">
Tindak
</a>

<a href="ulaporan.php?id=<?=$row['id_komplain']?>&status=selesai"
class="btn btn-sm btn-success">
Selesai
</a>

</td>

</tr>

<?php endwhile ?>

</tbody>

</table>

</div>


<!-- PAGINATION -->

<div class="d-flex justify-content-between p-3">

<div class="text-muted">

Menampilkan <?= $offset+1 ?> - <?= min($offset+$limit,$totalData) ?> dari <?= $totalData ?> laporan

</div>

<nav>

<ul class="pagination mb-0">

<?php for($i=1;$i<=$totalPages;$i++): ?>

<li class="page-item <?=($i==$page)?'active':''?>">

<a class="page-link" href="?page=<?=$i?>">
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
