<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('siswa');
require "../config/database.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include "../templates/header.php";
include "../templates/navbar.php";
include "../sidebar.php";
include "../header.php";

/* =========================
   CEK SESSION
========================= */
$id_user = $_SESSION['id_user'] ?? 0;
if ($id_user == 0) {
    die("Session tidak ditemukan. Silakan login ulang.");
}

/* =========================
   AMBIL id_siswa
========================= */
$stmt = $conn->prepare("SELECT id_siswa FROM siswa WHERE id_user = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Data siswa tidak ditemukan.");
}

$dataSiswa = $result->fetch_assoc();
$id_siswa = $dataSiswa['id_siswa'];
$stmt->close();

/* =========================
   STATISTIK KOMPLAIN (FIX)
========================= */

// TOTAL LAPORAN
$stmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM komplain 
    WHERE id_siswa=?
");
$stmt->bind_param("i", $id_siswa);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// DIPROSES (DIV + DITINDAKLANJUTI)
$stmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM komplain 
    WHERE id_siswa=? 
    AND status IN ('diverifikasi','ditindaklanjuti')
");
$stmt->bind_param("i", $id_siswa);
$stmt->execute();
$diproses = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// SELESAI
$stmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM komplain 
    WHERE id_siswa=? 
    AND status='selesai'
");
$stmt->bind_param("i", $id_siswa);
$stmt->execute();
$selesai = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

/* =========================
   5 LAPORAN TERBARU
========================= */
$stmt = $conn->prepare("
    SELECT 
        k.created_at,
        k.jenis_laporan,
        k.pesan,
        k.status
    FROM komplain k
    WHERE k.id_siswa = ?
    ORDER BY k.created_at DESC
    LIMIT 5
");

$stmt->bind_param("i", $id_siswa);
$stmt->execute();
$terbaru = $stmt->get_result();
?>

<main class="main-content">

<header class="flex flex-col gap-1">
    <h2 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white" style="color: #202020;">Dashboard Siswa</h2>
    <p class="text-slate-500 dark:text-slate-400">Ringkasan laporan dan aktivitas Anda hari ini.</p>
</header>

<section class="bg-white dark:bg-slate-800 rounded-xl p-8 shadow-sm border border-slate-200 dark:border-slate-700 relative overflow-hidden">
    <div class="relative z-10 max-w-2xl">
        <h3 class="text-2xl font-bold mb-2" style="color: #1E3A8A;">
            Selamat Datang, <?= htmlspecialchars($_SESSION['nama'] ?? 'Siswa'); ?> 👋
        </h3>
        <p class="text-slate-600 dark:text-slate-300 leading-relaxed" style="color: #4f6584;">
            Pantau dan kelola laporan akademik serta administratif Anda dengan mudah
            melalui sistem manajemen terpadu ini.
        </p>
    </div>
</section>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

<!-- TOTAL -->
<div class="bg-white dark:bg-slate-800 p-6 rounded-xl border shadow-sm flex justify-between">
    <div>
        <p class="text-sm text-slate-500 uppercase">Total Laporan</p>
        <p class="text-4xl font-black"><?= $total ?></p>
    </div>
    <img src="/absensi-guru/assets/img/doc.png" class="w-12 h-12">
</div>

<!-- DIPROSES -->
<div class="bg-white dark:bg-slate-800 p-6 rounded-xl border shadow-sm flex justify-between">
    <div>
        <p class="text-sm text-slate-500 uppercase">Laporan Diproses</p>
        <p class="text-4xl font-black"><?= $diproses ?></p>
    </div>
    <img src="/absensi-guru/assets/img/doc-proses.png" class="w-12 h-12">
</div>

<!-- SELESAI -->
<div class="bg-white dark:bg-slate-800 p-6 rounded-xl border shadow-sm flex justify-between">
    <div>
        <p class="text-sm text-slate-500 uppercase">Laporan Selesai</p>
        <p class="text-4xl font-black"><?= $selesai ?></p>
    </div>
    <img src="/absensi-guru/assets/img/doc-selesai.png" class="w-12 h-12">
</div>

</div>

<!-- TABEL -->
<section class="mt-5">

<h3 class="text-xl font-bold mb-3">Laporan Terbaru Anda</h3>

<table class="table w-full">
<thead>
<tr>
<th>Tanggal</th>
<th>Jenis</th>
<th>Deskripsi</th>
<th>Status</th>
</tr>
</thead>

<tbody>

<?php while($row = $terbaru->fetch_assoc()): ?>

<tr>
<td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
<td><?= htmlspecialchars($row['jenis_laporan']) ?></td>
<td><?= htmlspecialchars(substr($row['pesan'],0,50)) ?>...</td>
<td>
<?php
$status = strtolower($row['status']);

if($status == "baru"){
    echo '<span class="badge bg-primary">Baru</span>';
}elseif($status == "diverifikasi"){
    echo '<span class="badge bg-info">Diverifikasi</span>';
}elseif($status == "ditindaklanjuti"){
    echo '<span class="badge bg-warning">Diproses</span>';
}else{
    echo '<span class="badge bg-success">Selesai</span>';
}
?>
</td>
</tr>

<?php endwhile; ?>

</tbody>
</table>

</section>

</main>

<?php include "../templates/footer.php"; ?>