<?php
session_start();

require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('guru');

include "../config/database.php";

$id_guru = $_SESSION['id_user'];

/* =========================
   FILTER
========================= */
$search = $_GET['search'] ?? '';
$bulan  = $_GET['bulan'] ?? '';

$where = "WHERE jm.diisi_oleh = '$id_guru'";

if (!empty($search)) {
    $where .= " AND jm.materi LIKE '%$search%'";
}

if (!empty($bulan)) {
    $where .= " AND MONTH(jm.tanggal) = '$bulan'";
}

/* =========================
   QUERY (JOIN ABSENSI)
========================= */
$query = mysqli_query($conn, "
    SELECT 
        jm.*,
        ag.status AS kehadiran
    FROM jurnal_mengajar jm
    JOIN absensi_guru ag 
        ON jm.id_absensi_guru = ag.id_absensi_guru
    $where
    ORDER BY jm.tanggal DESC
");
?>

<?php include "../templates/header.php"; ?>
<?php include "../sidebar.php"; ?>
<?php include "../header.php"; ?>

<div class="main-content p-4">

<h4 class="fw-bold mb-3">Riwayat Jurnal</h4>

<!-- =========================
     FILTER
========================= -->
<div class="card shadow-sm mb-4">
<div class="card-body">

<form method="GET">
<div class="row g-2">

<div class="col-md-4">
<input type="text" name="search" class="form-control"
placeholder="Cari kegiatan..."
value="<?= htmlspecialchars($search) ?>">
</div>

<div class="col-md-3">
<select name="bulan" class="form-select">
<option value="">Semua Bulan</option>

<?php
$namaBulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
    4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September',
    10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

foreach($namaBulan as $key => $nama){
    $selected = ($bulan == $key) ? "selected" : "";
    echo "<option value='$key' $selected>$nama</option>";
}
?>
</select>
</div>

<div class="col-md-2">
<button class="btn btn-primary w-100">Filter</button>
</div>

</div>
</form>

</div>
</div>

<!-- =========================
     TABLE
========================= -->
<div class="card shadow-sm">
<div class="card-body">

<div class="table-responsive">
<table class="table align-middle">

<thead style="background:#f4f7ff;">
<tr>
<th>Tanggal</th>
<th>Kegiatan</th>
<th>Kehadiran</th>
<th>Status Jurnal</th>
<th class="text-center">Detail</th>
</tr>
</thead>

<tbody>

<?php if(mysqli_num_rows($query) > 0): ?>
<?php while($row = mysqli_fetch_assoc($query)): ?>

<?php
/* STATUS JURNAL */
$status = $row['status_verifikasi'];

if($status == 'diverifikasi'){
    $badgeStatus = "bg-success-subtle text-success";
}elseif($status == 'draft'){
    $badgeStatus = "bg-secondary-subtle text-dark";
}else{
    $badgeStatus = "bg-warning-subtle text-warning";
}

/* KEHADIRAN */
$hadir = $row['kehadiran'];

if($hadir == 'hadir'){
    $badgeHadir = "bg-success";
}elseif($hadir == 'izin'){
    $badgeHadir = "bg-warning";
}else{
    $badgeHadir = "bg-danger";
}
?>

<tr>

<td><?= date('d F Y', strtotime($row['tanggal'])) ?></td>

<td><?= htmlspecialchars($row['materi']) ?></td>

<td>
<span class="badge <?= $badgeHadir ?>">
<?= ucfirst($hadir) ?>
</span>
</td>

<td>
<span class="badge <?= $badgeStatus ?>">
<?= ucfirst($status) ?>
</span>
</td>

<td class="text-center">
<a href="detail_jurnal.php?id=<?= $row['id_jurnal'] ?>"
class="btn btn-sm btn-outline-primary">
Detail
</a>
</td>

</tr>

<?php endwhile; ?>

<?php else: ?>
<tr>
<td colspan="5" class="text-center text-muted">
Belum ada data jurnal
</td>
</tr>
<?php endif; ?>

</tbody>

</table>
</div>

</div>
</div>

</div>

<?php include "../templates/footer.php"; ?>