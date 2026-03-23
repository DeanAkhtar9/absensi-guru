<?php
session_start();

require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('walikelas');

include "../config/database.php";

/* =========================
   PARAMETER FILTER
========================= */
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$tanggal = $_GET['tanggal'] ?? '';

$page = $_GET['page'] ?? 1;
$limit = 5;
$offset = ($page - 1) * $limit;

/* =========================
   QUERY DASAR
========================= */
$where = "WHERE 1=1";

/* search */
if (!empty($search)) {
    $where .= " AND (
        u.nama LIKE '%$search%' OR
        k.pesan LIKE '%$search%'
    )";
}

/* filter status */
if (!empty($status)) {
    $where .= " AND k.status = '$status'";
}

/* filter tanggal */
if (!empty($tanggal)) {
    $where .= " AND DATE(k.created_at) = '$tanggal'";
}

/* =========================
   TOTAL DATA
========================= */
$totalData = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as total
    FROM komplain k
    JOIN users u ON k.id_siswa = u.id_user
    $where
"))['total'];

$totalPage = ceil($totalData / $limit);

/* =========================
   AMBIL DATA
========================= */
$query = mysqli_query($conn, "
    SELECT k.*, u.nama 
    FROM komplain k
    JOIN users u ON k.id_siswa = u.id_user
    $where
    ORDER BY k.created_at DESC
    LIMIT $limit OFFSET $offset
");
?>

<?php include "../templates/header.php"; ?>
<?php include "../sidebar.php"; ?>
<?php include "../header.php"; ?>

<style>
.card-box{
    border-radius:12px;
}

.table thead{
    background:#eef4ff;
}

.table td, .table th{
    border:1px solid rgba(0,0,0,0.05);
}

.badge-custom{
    padding:6px 10px;
    border-radius:8px;
}

.filter-box{
    background:#f8f9fc;
    padding:15px;
    border-radius:12px;
}
</style>

<link rel="stylesheet" href="../assets/css/bootstrap.min.css">
<div class="main-content p-4">

<h5 class="fw-bold">Monitoring Laporan Siswa</h5>
<p class="text-muted">Pantau semua laporan dari siswa di kelas Anda.</p>

<!-- =========================
     FILTER
========================= -->
<form method="GET" class="filter-box mb-4">
<div class="row g-2">

<div class="col-md-2">
<select name="status" class="form-select">
<option value="">Semua Status</option>
<option value="baru" <?= $status=='baru'?'selected':'' ?>>Baru</option>
<option value="diverifikasi" <?= $status=='diverifikasi'?'selected':'' ?>>Diverifikasi</option>
<option value="ditindaklanjuti" <?= $status=='ditindaklanjuti'?'selected':'' ?>>Ditindaklanjuti</option>
<option value="selesai" <?= $status=='selesai'?'selected':'' ?>>Selesai</option>
</select>
</div>

<div class="col-md-3">
<input type="date" name="tanggal" class="form-control" value="<?= $tanggal ?>">
</div>

<div class="col-md-5">
<input type="text" name="search" class="form-control"
placeholder="Cari laporan berdasarkan nama siswa atau deskripsi..."
value="<?= htmlspecialchars($search) ?>">
</div>

<div class="col-md-2">
<button class="btn btn-primary w-100">Terapkan</button>
</div>

</div>
</form>

<!-- =========================
     TABLE
========================= -->
<div class="card shadow-sm card-box">
<div class="card-body">

<div class="table-responsive">
<table class="table align-middle">

<thead>
<tr>
<th>Tanggal</th>
<th>Nama Siswa</th>
<th>Deskripsi</th>
<th>Status</th>
</tr>
</thead>

<tbody>

<?php if(mysqli_num_rows($query) == 0): ?>
<tr>
<td colspan="4" class="text-center text-muted">
Tidak ada data ditemukan
</td>
</tr>
<?php endif; ?>

<?php while($row = mysqli_fetch_assoc($query)): ?>

<?php
$status = strtolower($row['status']);

if($status == 'baru'){
    $badge = "bg-primary-subtle text-primary";
}elseif($status == 'diverifikasi'){
    $badge = "bg-info-subtle text-info";
}elseif($status == 'ditindaklanjuti'){
    $badge = "bg-warning-subtle text-warning";
}else{
    $badge = "bg-success-subtle text-success";
}
?>

<tr>
<td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
<td><?= htmlspecialchars($row['nama']) ?></td>
<td><?= htmlspecialchars(substr($row['pesan'],0,50)) ?>...</td>
<td>
<span class="badge <?= $badge ?>">
<?= ucfirst($status) ?>
</span>
</td>
</tr>

<?php endwhile; ?>

</tbody>

</table>
</div>

<!-- =========================
     PAGINATION
========================= -->
<div class="d-flex justify-content-between align-items-center mt-3">

<div class="text-muted small">
Menampilkan <?= $offset+1 ?> - <?= min($offset+$limit, $totalData) ?> dari <?= $totalData ?> laporan
</div>

<nav>
<ul class="pagination mb-0">

<?php for($i=1; $i<=$totalPage; $i++): ?>
<li class="page-item <?= ($i==$page)?'active':'' ?>">
<a class="page-link"
href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>&tanggal=<?= $tanggal ?>">
<?= $i ?>
</a>
</li>
<?php endfor; ?>

</ul>
</nav>

</div>

</div>
</div>

</div>

<?php include "../templates/footer.php"; ?>
