<?php
session_start();

require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('walikelas');

include "../config/database.php";
include "../templates/header.php";
include "../sidebar.php";
include "../header.php";

/* =========================
   AMBIL ID KELAS WALIKELAS
========================= */
$id_walikelas = $_SESSION['id_user'];

$kelasWalikelas = [];
$qKelas = mysqli_query($conn, "
    SELECT id_kelas 
    FROM kelas 
    WHERE id_walikelas='$id_walikelas'
");

while($k = mysqli_fetch_assoc($qKelas)){
    $kelasWalikelas[] = $k['id_kelas'];
}

if(empty($kelasWalikelas)){
    $kelasWalikelas[] = 0;
}

$kelasIDs = implode(',', $kelasWalikelas);

/* =========================
   PARAMETER
========================= */
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 5;
$offset = ($page - 1) * $limit;

/* =========================
   WHERE FIX
========================= */
$where = "WHERE jm.id_kelas IN ($kelasIDs)";

if(!empty($search)){
    $search_safe = mysqli_real_escape_string($conn, $search);
    $where .= " AND j.materi LIKE '%$search_safe%'";
}

/* =========================
   TOTAL DATA
========================= */
$totalData = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT COUNT(*) as total
    FROM jurnal_mengajar j
    JOIN absensi_guru ag ON j.id_absensi_guru=ag.id_absensi_guru
    JOIN jadwal_mengajar jm ON ag.id_jadwal=jm.id_jadwal
    $where
"))['total'];

$totalPage = ceil($totalData / $limit);

/* =========================
   STATISTIK
========================= */
$totalJurnal = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT COUNT(*) as total 
    FROM jurnal_mengajar j
    JOIN absensi_guru ag ON j.id_absensi_guru=ag.id_absensi_guru
    JOIN jadwal_mengajar jm ON ag.id_jadwal=jm.id_jadwal
    $where AND YEARWEEK(ag.tanggal,1)=YEARWEEK(CURDATE(),1)
"))['total'];

$guruHariIni = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT COUNT(DISTINCT j.diisi_oleh) as total
    FROM jurnal_mengajar j
    JOIN absensi_guru ag ON j.id_absensi_guru=ag.id_absensi_guru
    JOIN jadwal_mengajar jm ON ag.id_jadwal=jm.id_jadwal
    $where AND DATE(ag.tanggal)=CURDATE()
"))['total'];

$tidakHadir = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT COUNT(*) as total
    FROM absensi_guru ag
    JOIN jadwal_mengajar jm ON ag.id_jadwal=jm.id_jadwal
    $where AND ag.status!='hadir' AND DATE(ag.tanggal)=CURDATE()
"))['total'];

/* =========================
   QUERY UTAMA
========================= */
$query = mysqli_query($conn,"
    SELECT 
        ag.tanggal,
        k.nama_kelas,
        j.materi,
        u.nama AS nama_guru,
        ag.status
    FROM jurnal_mengajar j
    JOIN absensi_guru ag ON j.id_absensi_guru=ag.id_absensi_guru
    JOIN jadwal_mengajar jm ON ag.id_jadwal=jm.id_jadwal
    JOIN kelas k ON jm.id_kelas=k.id_kelas
    JOIN users u ON j.diisi_oleh=u.id_user
    $where
    ORDER BY ag.tanggal DESC
    LIMIT $limit OFFSET $offset
");
?>

<link rel="stylesheet" href="../assets/css/bootstrap.min.css">

<style>
.card-stat{
    background:white;
    padding:15px;
    border-radius:12px;
    box-shadow:0 4px 10px rgba(0,0,0,0.05);
    text-align:center;
}
.table thead{
    background:#eef4ff;
}
.table td, .table th{
    border:1px solid rgba(0,0,0,0.05);
}
</style>

<div class="main-content p-4">

<h5 class="fw-bold">Monitoring Jurnal Guru</h5>

<!-- SEARCH -->
<form method="GET" class="mb-4">
<div class="row g-2 align-items-center">

<div class="col-md-9">
<input type="text" name="search" class="form-control border-0 border-bottom"
placeholder="Cari materi..." 
value="<?= htmlspecialchars($search) ?>">
</div>

<div class="col-md-3">
<div class="d-flex gap-2">

<button class="btn btn-primary w-50">Cari</button>

<a href="?"
class="btn btn-secondary w-50 d-flex align-items-center justify-content-center">
Reset
</a>

</div>
</div>

</div>
</form>

<!-- STAT CARD -->
<div class="d-flex gap-3 mb-4 flex-wrap">

<div class="stat-card flex-fill">
    <div class="stat-icon icon-primary">
        <i class="bi bi-journal-text"></i>
    </div>
    <div>
        <div class="text-muted small">Jurnal Minggu Ini</div>
        <h5 class="fw-bold mb-0"><?= $totalJurnal ?></h5>
    </div>
</div>

<div class="stat-card flex-fill">
    <div class="stat-icon icon-success">
        <i class="bi bi-person-check"></i>
    </div>
    <div>
        <div class="text-muted small">Guru Hari Ini</div>
        <h5 class="fw-bold mb-0"><?= $guruHariIni ?></h5>
    </div>
</div>

<div class="stat-card flex-fill">
    <div class="stat-icon icon-danger">
        <i class="bi bi-person-x"></i>
    </div>
    <div>
        <div class="text-muted small">Tidak Hadir</div>
        <h5 class="fw-bold mb-0"><?= $tidakHadir ?></h5>
    </div>
</div>

</div>

<!-- TABLE -->
<div class="card shadow-sm border-0">

<div class="card-header bg-white d-flex justify-content-between">
<h6 class="mb-0 fw-bold">Data Jurnal Guru</h6>
</div>

<div class="table-responsive">

<table class="table align-middle mb-0">

<thead class="table-light">
<tr>
<th style="width:20%;">TANGGAL</th>
<th style="width:20%;">NAMA GURU</th>
<th style="width:15%;">KELAS</th>
<th style="width:25%;">MATERI</th>
<th style="width:20%;">KEHADIRAN</th>
</tr>
</thead>

<tbody>

<?php if(mysqli_num_rows($query) == 0): ?>
<tr>
<td colspan="5" class="text-center text-muted">
Tidak ada data
</td>
</tr>
<?php endif; ?>

<?php while($r = mysqli_fetch_assoc($query)): ?>

<?php
$status = $r['status'] ?? '';

$badge = "secondary";
if($status == 'hadir') $badge = "success";
elseif($status == 'izin') $badge = "warning";
elseif($status == 'alpha') $badge = "danger";
?>

<tr>

<td><?= date('d M Y', strtotime($r['tanggal'])) ?></td>

<td><?= htmlspecialchars($r['nama_guru']) ?></td>

<td><?= htmlspecialchars($r['nama_kelas']) ?></td>

<td><?= htmlspecialchars($r['materi']) ?></td>

<td>
<span class="badge bg-<?= $badge ?>">
<?= ucfirst($status) ?>
</span>
</td>

</tr>

<?php endwhile; ?>

</tbody>
</table>

<!-- PAGINATION -->
<div class="d-flex justify-content-end mt-3">
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
</div>
</div>



</div>

<?php include "../templates/footer.php"; ?>