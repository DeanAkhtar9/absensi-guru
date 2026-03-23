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
   STATISTIK KEHADIRAN (FIX)
========================= */
function hitung($conn, $id_siswa, $status) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM absensi_siswa 
        WHERE id_siswa=? AND status=?
    ");
    $stmt->bind_param("is", $id_siswa, $status);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
    return $res;
}

$total = $conn->query("
    SELECT COUNT(*) as total 
    FROM komplain 
    WHERE id_siswa='$id_siswa'
")->fetch_assoc()['total'];


$hadir = hitung($conn, $id_siswa, "hadir");
$tidak_hadir = hitung($conn, $id_siswa, "tidak_hadir");
$tanpa_ket = hitung($conn, $id_siswa, "alpa");


/* =========================
   5 LAPORAN TERBARU
========================= */
$stmt = $conn->prepare("
    SELECT 
        jm.tanggal,
        jm.materi,
        u.nama
    FROM jurnal_mengajar jm
    JOIN users u ON jm.diisi_oleh = u.id_user
    WHERE DATE(jm.tanggal) = CURDATE()
    ORDER BY jm.tanggal DESC
    LIMIT 5
");

$stmt->execute();
$terbaru = $stmt->get_result();
?>

<!-- Tailwind CSS -->

<main class="flex-1 overflow-y-auto bg-background-light dark:bg-background-dark p-8 max-w-7xl mx-auto space-y-8">

    <!-- Page Header -->
    <header class="flex flex-col gap-1">
        <h2 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Dashboard Siswa</h2>
        <p class="text-slate-500 dark:text-slate-400">Ringkasan laporan dan aktivitas Anda hari ini.</p>
    </header>

    <!-- Welcome Card -->
    <section class="bg-white dark:bg-slate-800 rounded-xl p-8 shadow-sm border border-slate-200 dark:border-slate-700 relative overflow-hidden">
        <div class="relative z-10 max-w-2xl">
            <h3 class="text-2xl font-bold mb-2" style="color: #1E3A8A;">
                Selamat Datang, <?= htmlspecialchars($_SESSION['nama'] ?? 'Siswa'); ?> 👋
            </h3>
            <p class="text-slate-600 dark:text-slate-300 leading-relaxed">
                Pantau dan kelola laporan akademik serta administratif Anda dengan mudah
                melalui sistem manajemen terpadu ini.
            </p>
        </div>
        <div class="absolute right-0 bottom-0 opacity-10 dark:opacity-20 translate-x-1/4 translate-y-1/4 pointer-events-none">
            <img src="/absensi-guru/assets/img/toga.png" alt="Toga Sekolah" class="w-[200px] h-[200px] object-contain">
        </div>
    </section>

    <!-- Statistic Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Laporan -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex justify-between items-start">
            <div class="space-y-1">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Laporan</p>
                <p class="text-4xl font-black text-balck-600 leading-tight"><?= $total ?></p>
            </div>
            <div class="bg-primary/10 dark:bg-primary/20 p-3 rounded-lg flex items-center justify-center">
                <img src="/absensi-guru/assets/img/doc.png" alt="Total Laporan" class="w-10 h-10">
            </div>
        </div>

    <!-- Laporan Diproses -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex justify-between items-start">
        <div class="space-y-1">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Laporan Diproses</p>
            <p class="text-4xl font-black text-balck-600 leading-tight"><?= $hadir ?></p>
        </div>
        <div class=" p-3 rounded-lg flex items-center justify-center">
            <img src="/absensi-guru/assets/img/doc-proses.png" alt="Diproses" class="w-10 h-10">
        </div>
    </div>

    <!-- Laporan Selesai -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex justify-between items-start">
        <div class="space-y-1">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Laporan Selesai</p>
            <p class="text-4xl font-black text-balck-600 leading-tight"><?= $tidak_hadir ?></p>
        </div>
        <div class="p-3 rounded-lg flex items-center justify-center">
            <img src="/absensi-guru/assets/img/doc-selesai.png" alt="Selesai" class="w-10 h-10">
        </div>
    </div>
</div>

 <!-- Recent Activity / Laporan Terbaru -->
<section class="space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="text-xl font-bold text-slate-900 dark:text-white">Laporan Terbaru Anda</h3>
    <button class="text-blue-500 hover:text-blue-700 transition-colors text-sm font-semibold flex items-center gap-1">
        Lihat Semua
    </button>
    </div>

    <!-- Card / container table -->
    <div class="laporan-table-container overflow-x-auto rounded-xl p-6">
        <table class="laporan-table w-full text-left border-collapse">
            <thead>
                <tr>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider" style="color: #64748B;">Tanggal</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider" style="color: #64748B;">Guru</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider" style="color: #64748B;">Mapel</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider" style="color: #64748B;">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                <?php while($row = $terbaru->fetch_assoc()): ?>
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="px-6 py-4 text-sm" style="color: #475569;"><?= htmlspecialchars($row['tanggal']) ?></td>
                        <td class="px-6 py-4 text-sm font-bold" style="color: #434e5f;"><?= htmlspecialchars($row['nama']) ?></td>
                        <td class="px-6 py-4 text-sm" style="color: #475569;"><?= htmlspecialchars($row['materi']) ?></td>
                        <td class="px-6 py-4">
                            <?php
                                $status = $row['-'];
                                if($status == "Hadir"){
                                    echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">Hadir</span>';
                                } elseif($status == "Tidak Hadir"){
                                    echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300">Tidak Hadir</span>';
                                } else {
                                    echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/40 text-yellow-700 dark:text-yellow-300">Tanpa Keterangan</span>';
                                }
                            ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</section>
</main>

<?php include "../templates/footer.php"; ?>