<?php
session_start();

require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('guru');

include "../config/database.php";

$id_guru = intval($_SESSION['id_user']);

/* =========================
   FILTER
========================= */
$search = $_GET['search'] ?? '';
$bulan  = $_GET['bulan'] ?? '';

$where = "WHERE jm.diisi_oleh = $id_guru";

if (!empty($search)) {
    $where .= " AND jm.materi LIKE '%$search%'";
}

if (!empty($bulan)) {
    $where .= " AND MONTH(jm.created_at) = '$bulan'";
}

/* =========================
   QUERY (PAKAI CREATED_AT)
========================= */
$sql = "
    SELECT 
        jm.*,
        ag.status AS kehadiran
    FROM jurnal_mengajar jm
    JOIN absensi_guru ag 
        ON jm.id_absensi_guru = ag.id_absensi_guru
    $where
    ORDER BY jm.created_at DESC
";

$query = mysqli_query($conn, $sql);

/* =========================
   EXPORT EXCEL
========================= */
if(isset($_GET['export']) && $_GET['export']=="excel"){

    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=rekap_jurnal.xls");

    echo "<table border='1'>
    <tr>
    <th>Tanggal</th>
    <th>Jam</th>
    <th>Kelas</th>
    <th>Mapel</th>
    <th>Kehadiran</th>
    <th>Status</th>
    </tr>";

    $q = mysqli_query($conn, $sql);

    while($row = mysqli_fetch_assoc($q)){
        echo "<tr>
        <td>".date('d-m-Y', strtotime($row['created_at']))."</td>
        <td>".date('H:i', strtotime($row['created_at']))."</td>
        <td>{$row['kelas']}</td>
        <td>{$row['mapel']}</td>
        <td>{$row['kehadiran']}</td>
        <td>{$row['status_verifikasi']}</td>
        </tr>";
    }

    echo "</table>";
    exit;
}
?>

<?php include "../templates/header.php"; ?>
<?php include "../sidebar.php"; ?>
<?php include "../header.php"; ?>

<!-- =========================
     STYLE PRINT (TIDAK UBAH UI)
========================= -->
<style>
@media print {
    .sidebar, .navbar, .btn, form {
        display: none !important;
    }

    .main-content {
        margin: 0 !important;
        padding: 0 !important;
    }

    table {
        border-collapse: collapse;
        width: 100%;
    }

    table, th, td {
        border: 1px solid black;
    }

    th {
        background: #eee;
    }
}
</style>

<div class="main-content"><div class="px-4 py-3 w-100">

<div class="mb-4">
    <div class="page-title" style="font-size:26px; margin-bottom:24px;">
        Riwayat Jurnal
    </div>

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
$namaBulan = [
1=>"Januari","Februari","Maret","April","Mei","Juni",
"Juli","Agustus","September","Oktober","November","Desember"
];

foreach($namaBulan as $key => $nama){
    $selected = ($bulan == $key) ? "selected" : "";
    echo "<option value='$key' $selected>$nama</option>";
}
?>
</select>
</div>

<!-- BUTTON -->
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

<!-- ACTION -->
<div class="mb-3 d-flex gap-2">
<a href="?<?= http_build_query($_GET) ?>&export=excel"
class="btn btn-success">
Export Excel
</a>

<button onclick="window.print()" class="btn btn-primary">
Print
</button>
</div>

<!-- =========================
     TABLE (TIDAK DIUBAH)
========================= -->
<div class="card shadow-sm">
<div class="card-body">

<div class="table-responsive">
<table class="table align-middle">

<thead style="background:#f4f7ff;">
<tr>
<th>Tanggal</th>
<th>Jam</th>
<th>Matapelajaran</th>
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
    $badgeHadir = "bg-success text-light";
}elseif($hadir == 'izin'){
    $badgeHadir = "bg-warning text-light";
}else{
    $badgeHadir = "bg-danger text-light";
}

/* 🔥 FIX JAM DISINI */
$tgl = strtotime($row['created_at']);
?>

<tr>

<td><?= date('d F Y', $tgl) ?></td>

<td><?= date('H:i', $tgl) ?></td>

<td><?= htmlspecialchars($row['mapel'] ?? '-') ?></td>

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
<td colspan="6" class="text-center text-muted">
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