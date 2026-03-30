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
<div class="px-4 py-3 w-100">

<div class="mb-4">
    <div class="page-title" style="font-size:30px;">Riwayat Laporan</div>
    <div class="page-subtitle">Lihat semua laporan yang telah Anda kirim ke pihak sekolah</div>
</div>

<!-- FILTER -->
<div class="mb-4">
<form method="GET">
<div class="d-flex gap-3 align-items-center">

<input type="text" name="search"
class="form-control"
placeholder="Cari laporan..."
value="<?= htmlspecialchars($search) ?>"
style="border-radius:10px; height:45px; border:none;">

<select name="status" class="form-select"
style="max-width:200px; border-radius:10px; border:none;">
<option value="">Semua Status</option>
<option value="baru" <?=($status=='baru')?'selected':''?>>Baru</option>
<option value="diverifikasi" <?=($status=='diverifikasi')?'selected':''?>>Diverifikasi</option>
<option value="ditindaklanjuti" <?=($status=='ditindaklanjuti')?'selected':''?>>Ditindaklanjuti</option>
<option value="selesai" <?=($status=='selesai')?'selected':''?>>Selesai</option>
</select>

<!-- TOMBOL FILTER + RESET -->
<div class="d-flex gap-2">

<button class="btn btn-primary px-4"
style="border-radius:10px; height:45px;">
Filter
</button>

<a href="?"
class="btn btn-secondary px-4"
style="border-radius:10px; height:45px; display:flex; align-items:center; justify-content:center;">
Reset
</a>

</div>

</div>
</form>
</div>

<div class="card-custom p-4">

<!-- TABLE -->
<div class="table-responsive">
<table class="table align-middle">

<thead style="background:#f1f5f9;">
<tr>
<th style="color:#64748B;">Tanggal</th>
<th style="color:#64748B;">Jenis Laporan</th>
<th style="color:#64748B;">Deskripsi</th>
<th style="color:#64748B;">Status</th>
</tr>
</thead>

<tbody style="background:white;">

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
$status_row = strtolower($row['status']);

if($status_row == "baru"){
    echo '<span style="background:#DBEAFE; color:#1D4ED8; padding:5px 10px; border-radius:999px; font-size:12px;">Baru</span>';
} elseif($status_row == "diverifikasi"){
    echo '<span style="background:#E0E7FF; color:#3730A3; padding:5px 10px; border-radius:999px; font-size:12px;">Diverifikasi</span>';
} elseif($status_row == "ditindaklanjuti"){
    echo '<span style="background:#FEF3C7; color:#92400E; padding:5px 10px; border-radius:999px; font-size:12px;">Ditindaklanjuti</span>';
} else {
    echo '<span style="background:#D1FAE5; color:#065F46; padding:5px 10px; border-radius:999px; font-size:12px;">Selesai</span>';
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
<nav class="mt-4">
<ul class="pagination justify-content-end">

<?php for($i=1; $i<=$totalPages; $i++): ?>
<li class="page-item <?=($i==$page)?'active':''?>">
<a class="page-link"
style="border-radius:8px; margin:0 2px;"
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