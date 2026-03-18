<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('siswa');

require "../config/database.php";
include "../templates/header.php";
include "../templates/navbar.php";
include "../sidebar.php";
include "../header.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$id_user = $_SESSION['id_user'] ?? 0;
if ($id_user == 0) {
    die("Session tidak ditemukan. Silakan login ulang.");
}

// Ambil data siswa & user, termasuk id_kelas dan nama_kelas
$stmt = $conn->prepare("
    SELECT u.nama AS nama_user, u.email, u.no_telp, s.id_siswa, s.id_kelas, k.nama_kelas
    FROM siswa s
    JOIN users u ON s.id_user = u.id_user
    JOIN kelas k ON s.id_kelas = k.id_kelas
    WHERE u.id_user = ?
");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Data siswa tidak ditemukan.");
}

$data = $result->fetch_assoc();
$stmt->close();

// Ambil hari ini dalam bahasa Indonesia
$nama_hari_array = ['Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'];
$hari_ini = $nama_hari_array[date('l')];

$id_kelas = $data['id_kelas'] ?? 0;

// Ambil jadwal mengajar hari ini untuk kelas siswa
$stmt_jadwal = $conn->prepare("
    SELECT jm.jam_mulai, jm.jam_selesai, jm.mapel, k.nama_kelas AS kelas
    FROM jadwal_mengajar jm
    JOIN kelas k ON jm.id_kelas = k.id_kelas
    WHERE jm.id_kelas = ? AND jm.hari = ?
    ORDER BY jm.jam_mulai ASC
");
$stmt_jadwal->bind_param("is", $id_kelas, $hari_ini);
$stmt_jadwal->execute();
$result_jadwal = $stmt_jadwal->get_result();

$jadwal_hari_ini = [];
while ($row = $result_jadwal->fetch_assoc()) {
    $jadwal_hari_ini[] = [
        'mapel' => $row['mapel'],
        'kelas' => $row['kelas'],
        'jam' => date('H:i', strtotime($row['jam_mulai'])) . " - " . date('H:i', strtotime($row['jam_selesai']))
    ];
}
$stmt_jadwal->close();
?>

        <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
        <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800;900&amp;display=swap" rel="stylesheet"/>
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>

        <main class="ml-72 flex-1 p-8 lg:p-12">
        <header class="mb-10">
            <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Profil Saya</h2>
            <p class="text-slate-500 dark:text-slate-400 mt-2">Kelola informasi pribadi dan jadwal mengajar Anda</p>
        </header>

        <!-- Profil Card -->
        <div class="max-w-4xl bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden mb-8">
        <div class="p-8">
            <div class="flex flex-col md:flex-row items-center gap-8 pb-8 border-b border-slate-100 dark:border-slate-700 mb-8">
                <div class="relative group">
                    <div class="size-32 rounded-full overflow-hidden border-4 border-slate-50 dark:border-slate-700 shadow-md">
                    <img alt="Profile Large" class="w-full h-full object-cover" src="/absensi-guru/assets/img/murid.png"/>
                    </div>
                </div>

                <div class="flex flex-col gap-2 md:text-left text-center">
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white"><?= htmlspecialchars($data['nama_user']) ?></h3>
                    <p class="text-primary font-semibold"><?= htmlspecialchars($data['nama_kelas'])?></p> <!-- dummy, harus pakai logika -->
                    <span class="px-3 py-1 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-full text-xs font-medium"><?= htmlspecialchars($data['email']) ?></span> <!-- dummy, harus pakai logika status aktif atau tidak -->
                </div>
            </div>

            <div class="flex flex-col gap-6 max-w-md">
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Nama Lengkap</label>
                    <div class="px-4 py-3 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-medium">
                        <?= htmlspecialchars($data['nama_user']) ?>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Kelas</label>
                    <div class="px-4 py-3 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-medium">
                        <?= htmlspecialchars($data['nama_kelas']) ?>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Email</label>
                    <div class="px-4 py-3 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-medium">
                        <?= htmlspecialchars($data['email']) ?>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-widest">Nomor Telepon</label>
                    <div class="px-4 py-3 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-medium">
                        <?= htmlspecialchars($data['no_telp']) ?>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <!-- Jadwal Mengajar Card -->
        <div class="max-w-4xl bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="p-8">
            <h4 class="text-xl font-semibold mb-4">Jadwal Mengajar Hari Ini (<?= $hari_ini ?>)</h4>
            <?php if (count($jadwal_hari_ini) > 0): ?>
                <div class="jadwal-list space-y-4">
                    <?php foreach ($jadwal_hari_ini as $item): ?>
                        <div class="jadwal-item p-4 bg-slate-50 dark:bg-slate-700 rounded-md flex justify-between items-center">
                            <div class="mapel font-semibold text-primary"><?= htmlspecialchars($item['mapel']) ?></div>
                            <div class="kelas text-slate-600 dark:text-slate-300"><?= htmlspecialchars($item['kelas']) ?></div>
                            <div class="jam text-slate-500 dark:text-slate-400"><?= htmlspecialchars($item['jam']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-slate-500 dark:text-slate-400">Tidak ada jadwal mengajar untuk hari ini.</p>
            <?php endif; ?>
        </div>
        </div>

        </main>
        </div>
        </body>