<?php
session_start();

require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('walikelas');

include "../config/database.php";

date_default_timezone_set('Asia/Jakarta');

$id_user = $_SESSION['id_user'];

/* =========================
   AMBIL ID KELAS WALIKELAS
========================= */
$kelasData = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT id_kelas FROM kelas WHERE id_walikelas='$id_user'
"));

$id_kelas = $kelasData['id_kelas'] ?? 0;

/* =========================
   AMBIL SISWA DI KELAS
========================= */
$siswaList = [];
$qSiswa = mysqli_query($conn, "
    SELECT id_siswa, id_user 
    FROM siswa 
    WHERE id_kelas='$id_kelas'
");

while($s = mysqli_fetch_assoc($qSiswa)){
    $siswaList[] = $s['id_siswa'];
}

$siswaIDs = !empty($siswaList) ? implode(',', $siswaList) : '0';

/* =========================
   TOTAL LAPORAN
========================= */
$totalLaporan = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM komplain
    WHERE id_siswa IN ($siswaIDs)
"))['total'];

/* =========================
   LAPORAN BARU
========================= */
$laporanBaru = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM komplain 
    WHERE id_siswa IN ($siswaIDs)
    AND DATE(created_at) = CURDATE()
"))['total'];

/* =========================
   TOTAL JURNAL
========================= */
$totalJurnal = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM jurnal_mengajar jm
    JOIN absensi_guru ag ON jm.id_absensi_guru = ag.id_absensi_guru
    JOIN jadwal_mengajar j ON ag.id_jadwal = j.id_jadwal
    WHERE j.id_kelas='$id_kelas'
"))['total'];

/* =========================
   KEHADIRAN
========================= */
$hadir = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM absensi_guru ag
    JOIN jadwal_mengajar j ON ag.id_jadwal = j.id_jadwal
    WHERE j.id_kelas='$id_kelas'
    AND ag.status='hadir'
"))['total'];

$totalAbsensi = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM absensi_guru ag
    JOIN jadwal_mengajar j ON ag.id_jadwal = j.id_jadwal
    WHERE j.id_kelas='$id_kelas'
"))['total'];

$persentase = $totalAbsensi > 0 
    ? round(($hadir / $totalAbsensi) * 100, 1)
    : 0;

/* =========================
   AKTIVITAS TERBARU (FIX)
========================= */
$aktivitas = mysqli_query($conn, "
    SELECT 
        'Jurnal Mengajar' as jenis, 
        jm.created_at as tanggal, 
        jm.diisi_oleh as user_id, 
        jm.status_verifikasi as status
    FROM jurnal_mengajar jm
    JOIN absensi_guru ag ON jm.id_absensi_guru = ag.id_absensi_guru
    JOIN jadwal_mengajar j ON ag.id_jadwal = j.id_jadwal
    WHERE j.id_kelas='$id_kelas'

    UNION

    SELECT 
        'Komplain Siswa' as jenis, 
        k.created_at as tanggal, 
        s.id_user as user_id, 
        k.status
    FROM komplain k
    JOIN siswa s ON k.id_siswa = s.id_siswa
    WHERE k.id_siswa IN ($siswaIDs)

    ORDER BY tanggal DESC
    LIMIT 5
");

/* =========================
   USER LIST
========================= */
$userList = [];
$qUser = mysqli_query($conn, "SELECT id_user, nama FROM users");
while($u = mysqli_fetch_assoc($qUser)){
    $userList[$u['id_user']] = $u['nama'];
}
?>

<?php include "../templates/header.php"; ?>
<?php include "../sidebar.php"; ?>
<?php include "../header.php"; ?>

<style>
.card-dashboard{
    border-radius:12px;
}

.stat-card{
    border-radius:12px;
    padding:15px;
    background:white;
    box-shadow:0 4px 10px rgba(0,0,0,0.05);
}

.stat-title{
    font-size:13px;
    color:#6c757d;
}

.stat-value{
    font-size:22px;
    font-weight:bold;
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
</style>

<link rel="stylesheet" href="../assets/css/bootstrap.min.css">

<div class="main-content p-4">

<h2 class="fw-bold" style="margin-bottom:3px;">Dashboard Walikelas</h2>

<p class="text-muted">
Selamat datang kembali, <?= $_SESSION['nama'] ?? 'User' ?>!
</p>

<!-- STATISTIK -->
<div class="row g-3 mb-4">

<div class="col-md-3">
<div class="card shadow-sm border-0">
<div class="card-body d-flex align-items-center gap-3">
<i class="bi bi-file-earmark-text text-primary fs-3"></i>
<div>
<div class="text-muted small">Total Laporan Kelas</div>
<h4 class="fw-bold mb-0"><?= $totalLaporan ?></h4>
</div>
</div>
</div>
</div>

<div class="col-md-3">
<div class="card shadow-sm border-0">
<div class="card-body d-flex align-items-center gap-3">
<i class="bi bi-exclamation-circle text-danger fs-3"></i>
<div>
<div class="text-muted small">Laporan Baru</div>
<h4 class="fw-bold mb-0"><?= $laporanBaru ?></h4>
</div>
</div>
</div>
</div>

<div class="col-md-3">
<div class="card shadow-sm border-0">
<div class="card-body d-flex align-items-center gap-3">
<i class="bi bi-journal-text text-warning fs-3"></i>
<div>
<div class="text-muted small">Total Jurnal Guru</div>
<h4 class="fw-bold mb-0"><?= $totalJurnal ?></h4>
</div>
</div>
</div>
</div>

<div class="col-md-3">
<div class="card shadow-sm border-0">
<div class="card-body d-flex align-items-center gap-3">
<i class="bi bi-bar-chart text-success fs-3"></i>
<div>
<div class="text-muted small">Persentase Kehadiran</div>
<h4 class="fw-bold mb-0"><?= $persentase ?>%</h4>
</div>
</div>
</div>
</div>

</div>

<!-- AKTIVITAS TERBARU -->
<div class="card shadow-sm border-0">

<div class="card-header bg-white d-flex justify-content-between">
<h6 class="mb-0 fw-bold">Aktivitas Terbaru Kelas</h6>
</div>

<div class="table-responsive">

<table class="table align-middle mb-0">

<thead class="table-light">
<tr>
<th style="width:25%;">TANGGAL</th>
<th style="width:25%;">NAMA</th>
<th style="width:25%;">JENIS AKTIVITAS</th>
<th style="width:15%;">STATUS</th>
</tr>
</thead>

<tbody>

<?php while($row = mysqli_fetch_assoc($aktivitas)): ?>

<?php
$nama = $userList[$row['user_id']] ?? 'Tidak diketahui';

$status = strtolower($row['status']);
$badge = "secondary";

if($status == 'diverifikasi') $badge = "success";
elseif($status == 'baru') $badge = "primary";
elseif($status == 'selesai') $badge = "success";
else $badge = "warning";
?>

<tr>

<td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
<td><?= htmlspecialchars($nama) ?></td>
<td><?= $row['jenis'] ?></td>

<td>
<span class="badge bg-<?= $badge ?>" style="height:30px; padding-top:8px;">
<?= ucfirst($row['status']) ?>
</span>
</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>
</div>

<?php include "../templates/footer.php"; ?>