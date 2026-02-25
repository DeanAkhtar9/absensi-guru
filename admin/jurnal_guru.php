<?php
require "../config/database.php";
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('admin');
include "../sidebar.php";
include "../header.php"; 

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

$search  = isset($_GET['search']) ? $_GET['search'] : '';
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';

$where = [];

/* Filter search */
if(!empty($search)) {
    $where[] = "(users.nama LIKE '%$search%' 
                OR jadwal_mengajar.mapel LIKE '%$search%')";
}

/* Filter tanggal */
if(!empty($tanggal)) {
    $where[] = "DATE(jurnal_mengajar.created_at) = '$tanggal'";
}

$whereSQL = "";
if(count($where) > 0) {
    $whereSQL = "WHERE " . implode(" AND ", $where);
}

/* Hitung total data */
$totalQuery = mysqli_query($conn, "
SELECT COUNT(*) as total
FROM jurnal_mengajar
JOIN absensi_guru ON jurnal_mengajar.id_absensi_guru = absensi_guru.id_absensi_guru
JOIN jadwal_mengajar ON absensi_guru.id_jadwal = jadwal_mengajar.id_jadwal
JOIN users ON jadwal_mengajar.id_guru = users.id_user
$whereSQL
");

$totalData = mysqli_fetch_assoc($totalQuery)['total'];
$totalPage = ceil($totalData / $limit);

/* Query utama */
$query = mysqli_query($conn, "
SELECT 
    jurnal_mengajar.id_jurnal,
    users.nama,
    jadwal_mengajar.mapel,
    kelas.nama_kelas,
    jurnal_mengajar.created_at

FROM jurnal_mengajar
JOIN absensi_guru ON jurnal_mengajar.id_absensi_guru = absensi_guru.id_absensi_guru
JOIN jadwal_mengajar ON absensi_guru.id_jadwal = jadwal_mengajar.id_jadwal
JOIN users ON jadwal_mengajar.id_guru = users.id_user
JOIN kelas ON jadwal_mengajar.id_kelas = kelas.id_kelas

$whereSQL
ORDER BY jurnal_mengajar.created_at DESC
LIMIT $start, $limit
");
?>


<div class="main-content">
<div class="container-fluid py-4">

<h2 class="mb-4">Data Jurnal Guru</h2>

<!-- SEARCH -->
 <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
<form method="GET" class="mb-4">
<div class="row g-2 align-items-end">

    <div class="col-md-4">
        
        <label class="form-label">Cari Nama / Mapel</label>
        <input type="text" name="search" class="form-control"
        value="<?= htmlspecialchars($search) ?>">
    </div>

    <div class="col-md-3">
        <label class="form-label">Filter Tanggal</label>
        <input type="date" name="tanggal" class="form-control"
        value="<?= $tanggal ?>">
    </div>

    <div class="col-md-2">

        <button class="btn btn-primary w-100">
        <i class="bi bi-funnel"></i>
        Filter</button>
    </div>

    <div class="col-md-2">
        <a href="?tanggal=<?= date('Y-m-d') ?>" 
           class="btn btn-success w-100">
           <i class="bi bi-clock-history"></i>
           Hari Ini
        </a>
    </div>

    <div class="col-md-1">
        <a href="jurnal_guru.php" 
           class="btn btn-secondary w-100">
           <i class="bi bi-arrow-clockwise"></i>
           Reset
        </a>
    </div>

</div>
</form>


<!-- TABLE -->
<div class="card shadow-sm">
<div class="card-body">

<table class="table table-hover">
<thead class="table-primary">
<tr>
    <th>Nama Guru</th>
    <th>Mapel</th>
    <th>Kelas</th>
    <th>Aksi</th>
</tr>
</thead>

<tbody>
<?php while($row = mysqli_fetch_assoc($query)) { ?>
<tr>
    <td><?= $row['nama'] ?></td>
    <td><?= $row['mapel'] ?></td>
    <td><?= $row['nama_kelas'] ?></td>
    <td>
        <a href="detail_jguru.php?id=<?= $row['id_jurnal'] ?>" 
           class="btn btn-sm btn-success">
           Detail
        </a>
    </td>
</tr>
<?php } ?>
</tbody>
</table>

</div>
</div>

<!-- PAGINATION -->
<nav class="mt-4">
<ul class="pagination">

<?php for($i = 1; $i <= $totalPage; $i++) { ?>
<li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
    <a class="page-link" 
       href="?page=<?= $i ?>&search=<?= $search ?>&tanggal=<?= $tanggal ?>">
       <?= $i ?>
    </a>
</li>
<?php } ?>

</ul>
</nav>


</div>
</div>
