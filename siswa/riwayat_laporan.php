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

$stmt = $conn->prepare("SELECT id_siswa FROM siswa WHERE id_user=?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0){
    die("Data siswa tidak ditemukan");
}

$id_siswa = $result->fetch_assoc()['id_siswa'];

/* =========================
FILTER
========================= */
$search = $_GET['search'] ?? "";
$status = $_GET['status'] ?? "";

/* =========================
PAGINATION
========================= */
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

/* =========================
QUERY DINAMIS
========================= */
$where = "WHERE id_siswa=?";
$params = [$id_siswa];
$types = "i";

if($search){
    $where .= " AND (pesan LIKE ? OR jenis_laporan LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

if($status){
    $where .= " AND status=?";
    $params[] = $status;
    $types .= "s";
}

/* =========================
TOTAL DATA
========================= */
$sql_total = "SELECT COUNT(*) as total FROM komplain $where";
$stmt = $conn->prepare($sql_total);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

$totalPages = ceil($total / $limit);

/* =========================
AMBIL DATA
========================= */
$sql = "
SELECT *
FROM komplain
$where
ORDER BY created_at DESC
LIMIT $limit OFFSET $offset
";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$query = $stmt->get_result();
?>

<style>
.card-custom{
    border-radius:16px;
    border:none;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
}

.table thead{
    background:#f1f5f9;
}

.badge-status{
    padding:6px 10px;
    border-radius:8px;
    font-size:12px;
}
</style>
<link rel="stylesheet" href="assets/css/bootstrap.min.css">
<div class="main-content">
<div class="container py-4">

<h4 class="fw-bold">Riwayat Laporan</h4>
<p class="text-muted">Lihat semua laporan yang pernah kamu kirim</p>

<div class="card card-custom mt-3">
<div class="card-body">

<!-- FILTER -->
<form method="GET">
<div class="row mb-3">

<div class="col-md-5">
<input type="text" name="search" class="form-control"
placeholder="Cari jenis laporan / deskripsi..."
value="<?= htmlspecialchars($search) ?>">
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

<div class="col-md-4 d-flex gap-2">
<button class="btn btn-primary w-100">Filter</button>
<a href="riwayat_laporan.php" class="btn btn-light w-100">Reset</a>
</div>

</div>
</form>

<!-- TABLE -->
<div class="table-responsive">
<table class="table align-middle">

<thead>
<tr>
<th>Tanggal</th>
<th>Jenis Laporan</th>
<th>Deskripsi</th>
<th>Status</th>
</tr>
</thead>

<tbody>

<?php if($query->num_rows > 0): ?>
<?php while($row = $query->fetch_assoc()): ?>

<tr>

<td><?= date('d M Y', strtotime($row['created_at'])) ?></td>

<td>
<span class="fw-semibold">
<?= htmlspecialchars($row['jenis_laporan'] ?? '-') ?>
</span>
</td>

<td><?= htmlspecialchars($row['pesan']) ?></td>

<td>
<?php
switch($row['status']){
    case 'baru':
        echo "<span class='badge bg-primary badge-status'>Baru</span>";
        break;
    case 'diverifikasi':
        echo "<span class='badge bg-warning text-dark badge-status'>Diverifikasi</span>";
        break;
    case 'ditindaklanjuti':
        echo "<span class='badge bg-info badge-status'>Diproses</span>";
        break;
    case 'selesai':
        echo "<span class='badge bg-success badge-status'>Selesai</span>";
        break;
    default:
        echo "<span class='badge bg-secondary badge-status'>-</span>";
}
?>
</td>

</tr>

<?php endwhile; ?>
<?php else: ?>

<tr>
<td colspan="4" class="text-center text-muted py-4">
Belum ada laporan
</td>
</tr>

<?php endif; ?>

</tbody>
</table>
</div>

<!-- PAGINATION -->
<nav class="mt-3">
<ul class="pagination justify-content-end">

<?php for($i=1; $i<=$totalPages; $i++): ?>
<li class="page-item <?=($i==$page)?'active':''?>">
<a class="page-link"
href="?page=<?=$i?>&search=<?=$search?>&status=<?=$status?>">
<?=$i?>
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
