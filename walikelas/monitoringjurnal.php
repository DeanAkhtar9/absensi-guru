<?php
session_start();

require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('walikelas');

include "../config/database.php";

date_default_timezone_set('Asia/Jakarta');

/* =========================
   PARAMETER
========================= */
$search = $_GET['search'] ?? '';
$mapel = $_GET['mapel'] ?? '';

$page = $_GET['page'] ?? 1;
$limit = 5;
$offset = ($page - 1) * $limit;

/* =========================
   AMBIL DATA USER (GURU)
========================= */
$userList = [];
$qUser = mysqli_query($conn, "SELECT id_user, nama FROM users WHERE role='guru'");
while($u = mysqli_fetch_assoc($qUser)){
    $userList[$u['id_user']] = $u['nama'];
}

/* =========================
   AMBIL DATA MAPEL DARI SHEET
========================= */
$url_master = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=799813071&single=true&output=csv";

$csv_master = file_get_contents($url_master);
$rows_master = array_map("str_getcsv", explode("\n", $csv_master));

$mapelList = [];
$jadwalMap = []; // id_guru => mapel

foreach ($rows_master as $row) {

    if (count($row) < 2) continue;

    $kelas = trim($row[0]);
    $gid = trim($row[1]);

    $url = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=$gid&single=true&output=csv";

    $csv = @file_get_contents($url);
    if (!$csv) continue;

    $rows = array_map("str_getcsv", explode("\n", $csv));

    foreach ($rows as $i => $r) {
        if ($i == 0) continue;
        if (count($r) < 5) continue;

        $id_guru = intval($r[0]);
        $mapel_sheet = $r[1];

        $jadwalMap[$id_guru] = $mapel_sheet;

        if (!in_array($mapel_sheet, $mapelList)) {
            $mapelList[] = $mapel_sheet;
        }
    }
}

/* =========================
   QUERY JURNAL
========================= */
$where = "WHERE 1=1";

if (!empty($search)) {
    $where .= " AND jm.kegiatan LIKE '%$search%'";
}

/* filter mapel (via mapping sheet) */
if (!empty($mapel)) {
    $ids = [];
    foreach ($jadwalMap as $id => $mp) {
        if ($mp == $mapel) {
            $ids[] = $id;
        }
    }

    if (!empty($ids)) {
        $where .= " AND jm.diisi_oleh IN (" . implode(',', $ids) . ")";
    } else {
        $where .= " AND 1=0";
    }
}

/* total data */
$totalData = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as total FROM jurnal_mengajar jm
    $where
"))['total'];

$totalPage = ceil($totalData / $limit);

/* ambil data */
$query = mysqli_query($conn, "
    SELECT * FROM jurnal_mengajar jm
    $where
    ORDER BY jm.tanggal DESC
    LIMIT $limit OFFSET $offset
");

/* =========================
   STATISTIK
========================= */
$totalJurnal = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as total FROM jurnal_mengajar 
    WHERE YEARWEEK(tanggal, 1) = YEARWEEK(CURDATE(), 1)
"))['total'];

$guruHariIni = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(DISTINCT diisi_oleh) as total 
    FROM jurnal_mengajar 
    WHERE DATE(tanggal) = CURDATE()
"))['total'];

$tidakHadir = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM absensi_guru 
    WHERE status!='hadir' AND DATE(tanggal)=CURDATE()
"))['total'];
?>

<?php include "../templates/header.php"; ?>
<?php include "../sidebar.php"; ?>
<?php include "../header.php"; ?>

<style>
.card-stat{
    background:white;
    padding:15px;
    border-radius:12px;
    box-shadow:0 4px 10px rgba(0,0,0,0.05);
}

.table thead{
    background:#eef4ff;
}

.table td, .table th{
    border:1px solid rgba(0,0,0,0.05);
}

.badge-dot{
    display:inline-flex;
    align-items:center;
    gap:6px;
}

.dot{
    width:8px;
    height:8px;
    border-radius:50%;
}
</style>

<link rel="stylesheet" href="../assets/css/bootstrap.min.css">


<div class="main-content p-4">

<h5 class="fw-bold">Monitoring Jurnal Guru</h5>
<p class="text-muted">Lihat jurnal pembelajaran guru di kelas Anda secara real-time.</p>

<!-- =========================
     SEARCH & FILTER
========================= -->
<form method="GET" class="row g-2 mb-3">

<div class="col-md-4">
<input type="text" name="search" class="form-control"
placeholder="Cari jurnal..."
value="<?= htmlspecialchars($search) ?>">
</div>

<div class="col-md-3">
<select name="mapel" class="form-select">
<option value="">Semua Mata Pelajaran</option>
<?php foreach($mapelList as $m): ?>
<option value="<?= $m ?>" <?= $mapel==$m?'selected':'' ?>>
<?= $m ?>
</option>
<?php endforeach; ?>
</select>
</div>

<div class="col-md-2">
<button class="btn btn-primary w-100">Filter</button>
</div>

</form>

<!-- =========================
     STATISTIK
========================= -->
<div class="row g-3 mb-4">

<div class="col-md-4">
<div class="card-stat">
Total Jurnal Minggu Ini
<h5><?= $totalJurnal ?></h5>
</div>
</div>

<div class="col-md-4">
<div class="card-stat">
Guru Mengajar Hari Ini
<h5><?= $guruHariIni ?></h5>
</div>
</div>

<div class="col-md-4">
<div class="card-stat">
Ketidakhadiran Guru
<h5><?= $tidakHadir ?></h5>
</div>
</div>

</div>

<!-- =========================
     TABLE
========================= -->
<div class="card shadow-sm">
<div class="card-body">

<div class="table-responsive">
<table class="table align-middle">

<thead>
<tr>
<th>Tanggal</th>
<th>Nama Guru</th>
<th>Mata Pelajaran</th>
<th>Ringkasan Kegiatan</th>
<th>Kehadiran</th>
</tr>
</thead>

<tbody>

<?php if(mysqli_num_rows($query)==0): ?>
<tr><td colspan="5" class="text-center">Tidak ada data</td></tr>
<?php endif; ?>

<?php while($row = mysqli_fetch_assoc($query)): ?>

<?php
$nama = $userList[$row['diisi_oleh']] ?? '-';
$mapelGuru = $jadwalMap[$row['diisi_oleh']] ?? '-';

/* status hadir */
$status = $row['status_verifikasi'];

if($status == 'hadir'){
    $color = 'green';
}elseif($status == 'izin'){
    $color = 'orange';
}else{
    $color = 'red';
}
?>

<tr>
<td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
<td><?= htmlspecialchars($nama) ?></td>
<td><?= htmlspecialchars($mapelGuru) ?></td>
<td><?= substr(htmlspecialchars($row['materi']),0,60) ?>...</td>
<td>
<span class="badge-dot">
<span class="dot" style="background:<?= $color ?>"></span>
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
<div class="d-flex justify-content-end mt-3">

<ul class="pagination">

<?php for($i=1;$i<=$totalPage;$i++): ?>
<li class="page-item <?= $i==$page?'active':'' ?>">
<a class="page-link"
href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&mapel=<?= $mapel ?>">
<?= $i ?>
</a>
</li>
<?php endfor; ?>

</ul>

</div>

</div>
</div>

</div>

<?php include "../templates/footer.php"; ?>
