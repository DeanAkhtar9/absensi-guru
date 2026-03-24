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
   TOTAL LAPORAN (komplain)
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
   TOTAL JURNAL (guru di kelas ini)
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
   AKTIVITAS TERBARU
========================= */
$aktivitas = mysqli_query($conn, "
    SELECT 'Jurnal Mengajar' as jenis, jm.created_at as tanggal, jm.diisi_oleh as user_id, jm.status_verifikasi as status
    FROM jurnal_mengajar jm
    JOIN absensi_guru ag ON jm.id_absensi_guru = ag.id_absensi_guru
    JOIN jadwal_mengajar j ON ag.id_jadwal = j.id_jadwal
    WHERE j.id_kelas='$id_kelas'

    UNION

    SELECT 'Komplain Siswa', k.created_at, k.id_siswa, 'Baru'
    FROM komplain k
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

<h5 class="fw-bold">Dashboard</h5>

<p class="text-muted">
Selamat datang kembali, <?= $_SESSION['nama'] ?? 'User' ?>!
</p>

<!-- =========================
     STATISTIK
========================= -->
<div class="row g-3 mb-4">

<div class="col-md-3">
<div class="stat-card">
<div class="stat-title">Total Laporan Kelas</div>
<div class="stat-value"><?= $totalLaporan ?></div>
</div>
</div>

<div class="col-md-3">
<div class="stat-card">
<div class="stat-title">Laporan Baru</div>
<div class="stat-value"><?= $laporanBaru ?></div>
</div>
</div>

<div class="col-md-3">
<div class="stat-card">
<div class="stat-title">Total Jurnal Guru</div>
<div class="stat-value"><?= $totalJurnal ?></div>
</div>
</div>

<div class="col-md-3">
<div class="stat-card">
<div class="stat-title">Persentase Kehadiran</div>
<div class="stat-value"><?= $persentase ?>%</div>
</div>
</div>

</div>


<!-- =========================
     AKTIVITAS TERBARU
========================= -->
<div class="card shadow-sm card-dashboard">
<div class="card-body">

<div class="d-flex justify-content-between mb-3">
<h6 class="fw-bold">Aktivitas Terbaru Kelas</h6>
</div>

<div class="table-responsive">
<table class="table align-middle">

<thead>
<tr>
<th>Tanggal</th>
<th>Nama</th>
<th>Jenis Aktivitas</th>
<th>Status</th>
</tr>
</thead>

<tbody>

<?php while($row = mysqli_fetch_assoc($aktivitas)): ?>

<?php
$nama = $userList[$row['user_id']] ?? 'Tidak diketahui';

$status = strtolower($row['status']);

if($status == 'diverifikasi'){
    $badge = "bg-success-subtle text-success";
}elseif($status == 'baru'){
    $badge = "bg-primary-subtle text-primary";
}else{
    $badge = "bg-warning-subtle text-warning";
}
?>

<tr>
<td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
<td><?= htmlspecialchars($nama) ?></td>
<td><?= $row['jenis'] ?></td>
<td>
<span class="badge <?= $badge ?>">
<?= ucfirst($row['status']) ?>
</span>
</td>
</tr>

<?php endwhile; ?>

</tbody>

</table>
</div>

</div>
</div>

</div>

<?php include "../templates/footer.php"; ?>
