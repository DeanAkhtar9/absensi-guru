<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('admin');

require "../config/database.php";

include "../templates/header.php";
include "../sidebar.php";
include "../header.php";

/* =========================
   FILTER & SEARCH
========================= */

$search = $_GET['search'] ?? "";
$status = $_GET['status'] ?? "";
$jenis  = $_GET['jenis'] ?? "";

/* =========================
   PAGINATION
========================= */

$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

/* =========================
   QUERY WHERE DINAMIS
========================= */

$where = "WHERE 1=1";

if($search){
    $where .= " AND (users.nama LIKE '%$search%' OR komplain.pesan LIKE '%$search%')";
}

if($status){
    $where .= " AND komplain.status='$status'";
}

if($jenis){
    $where .= " AND komplain.jenis_laporan='$jenis'";
}

/* =========================
   TOTAL DATA
========================= */

$totalData = mysqli_query($conn,"
SELECT COUNT(*) as total 
FROM komplain
JOIN siswa ON komplain.id_siswa = siswa.id_siswa
JOIN users ON siswa.id_user = users.id_user
$where
");

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
$where
ORDER BY komplain.created_at DESC
LIMIT $limit OFFSET $offset
");
?>

<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/bootstrap.min.css">

<div class="main-content">
<div class="container-fluid py-4">

<h4 class="mb-3 fw-bold">Verifikasi Laporan</h4>
<p class="text-muted">Kelola dan perbarui status laporan siswa</p>


<div class="card shadow-sm border-0">
<div class="card-body">
<!-- =========================
     FORM FILTER
========================= -->
<form method="GET" class="row g-2 mb-3">

<div class="col-md-4">
<input type="text" name="search" class="form-control"
placeholder="Cari nama / laporan..."
value="<?= htmlspecialchars($search) ?>">
</div>

<div class="col-md-2">
<select name="jenis" class="form-select">
<option value="">Semua Jenis</option>
<option value="akademik">Akademik</option>
<option value="fasilitas">Fasilitas</option>
<option value="kedisiplinan">Kedisiplinan</option>
</select>
</div>

<div class="col-md-2">
<select name="status" class="form-select">
<option value="">Semua Status</option>
<option value="baru">Baru</option>
<option value="diverifikasi">Diverifikasi</option>
<option value="ditindaklanjuti">Ditindaklanjuti</option>
<option value="selesai">Selesai</option>
</select>
</div>

<div class="col-md-2">
<button class="btn btn-primary w-100">Filter</button>
</div>

<div class="col-md-2">
<a href="verifikasi_laporan.php" class="btn btn-secondary w-100">Reset</a>
</div>

</form>

</div>
</div>
</div>

<div class="card shadow-sm border-0">
<div class="card-body">


<!-- =========================
     TABLE
========================= -->

<div class="table-responsive">

<table class="table align-middle">

<thead class="table-light">
<tr>
<th>TANGGAL</th>
<th>NAMA</th>
<th>JENIS</th>
<th>DESKRIPSI</th>
<th>STATUS</th>
<th>AKSI</th>
</tr>
</thead>

<tbody>

<?php if(mysqli_num_rows($query)>0): ?>

<?php while($row=mysqli_fetch_assoc($query)): ?>

<tr>

<td><?=date('d M Y',strtotime($row['created_at']))?></td>

<td><?= htmlspecialchars($row['nama']) ?></td>

<td><?= ucfirst($row['jenis_laporan']) ?></td>

<td style="max-width:260px;">
<?=substr($row['pesan'],0,60)?>...
</td>

<td>
<?php
$status = $row['status'];

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
class="btn btn-sm btn-primary mb-1">Verifikasi</a>

<a href="ulaporan.php?id=<?=$row['id_komplain']?>&status=ditindaklanjuti"
class="btn btn-sm btn-warning mb-1">Tindak</a>

<a href="ulaporan.php?id=<?=$row['id_komplain']?>&status=selesai"
class="btn btn-sm btn-success">Selesai</a>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>
<td colspan="6" class="text-center text-muted">
Tidak ada data
</td>
</tr>

<?php endif; ?>

</tbody>

</table>

</div>

<!-- =========================
     PAGINATION
========================= -->

<div class="d-flex justify-content-between p-3">

<div class="text-muted">
Menampilkan <?= $offset+1 ?> - <?= min($offset+$limit,$totalData) ?> dari <?= $totalData ?>
</div>

<nav>
<ul class="pagination mb-0">

<?php for($i=1;$i<=$totalPages;$i++): ?>

<li class="page-item <?=($i==$page)?'active':''?>">
<a class="page-link"
href="?page=<?=$i?>&search=<?=$search?>&status=<?=$status?>&jenis=<?=$jenis?>">
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
