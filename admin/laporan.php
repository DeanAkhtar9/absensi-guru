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
   QUERY WHERE
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

<link rel="stylesheet" href="../assets/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/css/style.css">

<style>
/* 🔥 Fix warna warning */
.btn-warning {
    color: #fff !important;
}

/* 🔥 Tombol kecil & rapi */
.btn-sm {
    font-size: 12px;
    border-radius: 6px;
    padding: 4px 8px;
}

/* 🔥 Supaya tombol tidak terlalu lebar */
td .btn {
    min-width: 80px;
}
</style>

<div class="main-content">
<div class="container-fluid py-4">

<!-- HEADER -->
<h4 class="fw-bold mb-1">Verifikasi Laporan</h4>
<p class="text-muted mb-4">Kelola dan perbarui status laporan siswa</p>

<!-- =========================
     FILTER
========================= -->
<form method="GET" style="display:flex; gap:10px; margin-bottom:30px;">

    <!-- SEARCH -->
    <div style="position:relative; flex:1;">
        <i class="bi bi-search"
        style="position:absolute; top:50%; left:12px; transform:translateY(-50%); color:#6c757d;">
        </i>

        <input type="text" name="search"
            value="<?= htmlspecialchars($search) ?>"
            placeholder="Cari nama atau laporan..."
            class="form-control"
            style="height:45px; padding-left:38px;">
    </div>

    <!-- JENIS -->
    <select name="jenis" style="width:250px; height:45px;" class="form-select">
        <option value="">Semua Jenis</option>
        <option value="akademik" <?=($jenis=='akademik')?'selected':''?>>Akademik</option>
        <option value="fasilitas" <?=($jenis=='fasilitas')?'selected':''?>>Fasilitas</option>
        <option value="kedisiplinan" <?=($jenis=='kedisiplinan')?'selected':''?>>Kedisiplinan</option>
    </select>

    <!-- STATUS -->
    <select name="status" style="width:250px; height:45px;" class="form-select">
        <option value="">Semua Status</option>
        <option value="baru" <?=($status=='baru')?'selected':''?>>Baru</option>
        <option value="diverifikasi" <?=($status=='diverifikasi')?'selected':''?>>Diverifikasi</option>
        <option value="ditindaklanjuti" <?=($status=='ditindaklanjuti')?'selected':''?>>Ditindaklanjuti</option>
        <option value="selesai" <?=($status=='selesai')?'selected':''?>>Selesai</option>
    </select>

    <!-- BUTTON -->
    <button class="btn btn-primary" style="width:140px; height:45px;">
        <i class="bi bi-funnel"></i> Filter
    </button>

</form>

<!-- =========================
     TABLE
========================= -->
<div class="card shadow-sm border-0">
<div class="card-body">

<div class="table-responsive">
<table class="table align-middle">

<thead class="table-light">
<tr>
<th style="width:12%;">TANGGAL</th>
<th style="width:18%;">NAMA</th>
<th style="width:15%;">JENIS</th>
<th style="width:30%;">DESKRIPSI</th>
<th style="width:15%;">STATUS</th>
<th style="width:10%;">AKSI</th>
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
$statusRow = $row['status'];

if($statusRow=='baru'){
echo "<span class='badge bg-primary'>Baru</span>";
}
elseif($statusRow=='diverifikasi'){
echo "<span class='badge bg-info'>Diverifikasi</span>";
}
elseif($statusRow=='ditindaklanjuti'){
echo "<span class='badge bg-warning text-white'>Ditindaklanjuti</span>";
}
elseif($statusRow=='selesai'){
echo "<span class='badge bg-success'>Selesai</span>";
}
?><td>
    <div class="aksi-group">

        <a href="ulaporan.php?id=<?=$row['id_komplain']?>&status=diverifikasi"
        class="btn btn-primary btn-aksi">
        Verifikasi
        </a>

        <a href="ulaporan.php?id=<?=$row['id_komplain']?>&status=ditindaklanjuti"
        class="btn btn-warning btn-aksi">
        Tindak
        </a>

        <a href="ulaporan.php?id=<?=$row['id_komplain']?>&status=selesai"
        class="btn btn-success btn-aksi">
        Selesai
        </a>

    </div>
</td>

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

<!-- PAGINATION -->
<div class="d-flex justify-content-between mt-3">

<div class="text-muted">
Menampilkan <?= $offset+1 ?> - <?= min($offset+$limit,$totalData) ?> dari <?= $totalData ?>
</div>

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

</div>

</div>
</div>

</div>
</div>

<?php include "../templates/footer.php"; ?>