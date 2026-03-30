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

<div class="main-content"><div class="px-4 py-3 w-100">

<div class="mb-4">
    <div class="page-title" style="font-size:26px; margin-bottom:24px;">Riwayat Jurnal</div>

<form method="GET" class="mb-4">

<div class="row g-3 align-items-center">

<!-- SEARCH -->
<div class="col-md-5">
<input type="text" name="search"
class="form-control"
placeholder="Cari kegiatan..."
value="<?= htmlspecialchars($search) ?>"
style="height:48px; border-radius:10px; border-color: #d0d0d0;">
</div>

<!-- BULAN -->
<div class="col-md-3">
<select name="bulan" class="form-select"
style="height:48px; border-radius:10px; border-color: #d0d0d0;">
<option value="">Semua Bulan</option>

<?php
foreach($namaBulan as $key => $nama){
    $selected = ($bulan == $key) ? "selected" : "";
    echo "<option value='$key' $selected>$nama</option>";
}
?>
</select>
</div>

<!-- BUTTON FILTER + RESET -->
<div class="col-md-4">
<div class="d-flex gap-2">

<button class="btn btn-primary w-50"
style="height:48px; border-radius:10px;">
Filter
</button>

<a href="?"
class="btn btn-secondary w-50"
style="height:48px; border-radius:10px; display:flex; align-items:center; justify-content:center;">
Reset
</a>

</div>
</div>

</div>

</form> 

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
$status = $row['status_verifikasi'];

if($status == 'diverifikasi'){
    $badgeStatus = "bg-success-subtle text-success";
}elseif($status == 'draft'){
    $badgeStatus = "bg-secondary-subtle text-dark";
}else{
    $badgeStatus = "bg-warning-subtle text-warning";
}

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
</div>

<?php include "../templates/footer.php"; ?>