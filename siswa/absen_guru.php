<?php
session_start();

require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('siswa');

require "../config/database.php";

date_default_timezone_set('Asia/Jakarta');

/* ========================= */
function getCSV($url){
    return @file_get_contents($url);
}
function formatJam($jam){
    return str_replace('.', ':', trim($jam));
}
/* ========================= */

$id_user = $_SESSION['id_user'];

/* AMBIL KELAS */
$qK = mysqli_query($conn,"
SELECT k.nama_kelas 
FROM siswa s
JOIN kelas k ON s.id_kelas=k.id_kelas
WHERE s.id_user='$id_user'
");
$kelas_siswa = mysqli_fetch_assoc($qK)['nama_kelas'] ?? '';

$hari_map = [
 'Sunday'=>'minggu','Monday'=>'senin','Tuesday'=>'selasa',
 'Wednesday'=>'rabu','Thursday'=>'kamis','Friday'=>'jumat','Saturday'=>'sabtu'
];

$hari_ini = $hari_map[date('l')];
$now = time();

$jadwalHariIni = [];

/* MASTER */
$url_master = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=799813071&output=csv";
$rows_master = array_map("str_getcsv", explode("\n", getCSV($url_master)));

foreach($rows_master as $row){

    if(count($row)<2) continue;

    if(trim($row[0]) != $kelas_siswa) continue;

    $gid = trim($row[1]);

    $rows = array_map("str_getcsv", explode("\n",
        getCSV("https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=$gid&output=csv")
    ));

    foreach($rows as $i=>$r){

        if($i==0 || count($r)<5) continue;

        $id_guru = intval($r[0]);
        $mapel = $r[1];
        $hari = strtolower(trim($r[2]));

        if($hari=='jumaat') $hari='jumat';
        if($hari != $hari_ini) continue;

        $jam_mulai = strtotime(date("Y-m-d")." ".formatJam($r[3]));
        $batas = $jam_mulai + (12*60*60);

        $id_jadwal = md5($id_guru.$mapel.$jam_mulai);

        $cek = mysqli_query($conn,"
        SELECT 1 FROM absensi_guru
        WHERE id_user='$id_guru'
        AND DATE(tanggal)=CURDATE()
        ");

        $sudah = mysqli_num_rows($cek) > 0;

        $qGuru = mysqli_query($conn,"SELECT nama FROM users WHERE id_user='$id_guru'");
        $namaGuru = mysqli_fetch_assoc($qGuru)['nama'] ?? '-';

        $jadwalHariIni[] = [
            'id_jadwal'=>$id_jadwal,
            'id_guru'=>$id_guru,
            'nama'=>$namaGuru,
            'mapel'=>$mapel,
            'jam'=>date("H:i",$jam_mulai),
            'mulai'=>$jam_mulai,
            'batas'=>$batas,
            'sudah'=>$sudah
        ];
    }
}
/* ==========================================================
   LOGIKA NOTIFIKASI DATABASE (GANTI KODE SEBELUMNYA)
   ========================================================== */
foreach($jadwalHariIni as $j) {
    // Cek jika: Sedang jam pelajaran DAN belum absen
    if(($now >= $j['mulai'] && $now <= $j['batas']) && !$j['sudah']) {
        
        $id_guru = $j['id_guru'];
        $mapel   = $j['mapel'];
        $judul   = "Peringatan Absensi";
        $isi     = "Waktunya absen guru: $mapel (" . $j['nama'] . ")";
        
        // Cek dulu di database agar tidak SPAM (biar tidak masuk terus tiap refresh)
        // Kita cek apakah hari ini sudah ada notif untuk mapel ini ke siswa ini
        $cek_notif = mysqli_query($conn, "
            SELECT id_notif FROM notifikasi 
            WHERE id_user = '$id_user' 
            AND judul = '$judul' 
            AND pesan LIKE '%$mapel%'
            AND DATE(created_at) = CURDATE()
        ");

        // Jika belum ada notifikasi di database untuk hari ini, baru kita KIRIM
        if (mysqli_num_rows($cek_notif) == 0) {
            // Gunakan fungsi kirimNotifikasi yang ada di database.php
            kirimNotifikasi($id_user, $judul, $isi);
        }
        
        break; // Cukup satu notif saja per sesi
    }
}
?>

<?php include "../templates/header.php"; ?>
<?php include "../sidebar.php"; ?>
<?php include "../header.php"; ?>

<style>
.card-active-pulse {
    border: 2px solid #3B82F6 !important;
    animation: pulse-blue 2s infinite;
}

@keyframes pulse-blue {
    0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
    100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
}
</style>
<div class="main-content p-4">

<h4 class="mb-4">Absensi Guru Hari Ini</h4>

<div class="row">

<?php foreach($jadwalHariIni as $j): ?>

<?php
$aktif = ($now >= $j['mulai'] && $now <= $j['batas']);
?>

<div class="col-md-4 mb-3">

<?php if($aktif && !$j['sudah']): ?>
<a href="isi_absen.php?id=<?= $j['id_jadwal'] ?>&guru=<?= $j['id_guru'] ?>" style="text-decoration:none;">
<?php endif; ?>

<div class="card p-3 shadow-sm 
<?= $j['sudah'] ? 'border-success' : ($aktif ? 'card-active-pulse' : 'bg-light') ?>">

<h5><?= $j['mapel'] ?></h5>
<p><?= $j['nama'] ?></p>
<p><?= $j['jam'] ?></p>

<?php if($j['sudah']): ?>
<span class="badge bg-success">✔ Sudah Absen</span>

<?php elseif(!$aktif): ?>
<span class="badge bg-secondary">Belum waktunya</span>

<?php else: ?>
<span class="badge bg-primary">Klik untuk absen</span>
<?php endif; ?>

</div>

<?php if($aktif && !$j['sudah']): ?>
</a>
<?php endif; ?>

</div>

<?php endforeach; ?>

</div>

</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ambil data dari PHP
    const adaJadwal = <?= json_encode($punya_notif) ?>;
    const pesan = <?= json_encode($pesan_notif) ?>;

    // 1. Cek apakah browser mendukung notifikasi
    if (!("Notification" in window)) {
        console.log("Browser ini tidak mendukung notifikasi desktop");
        return;
    }

    // 2. Fungsi untuk menampilkan notifikasi
    function tampilkanNotif() {
        new Notification("Pengingat Absensi Guru", {
            body: pesan,
            icon: "../assets/img/logo.png" // Pastikan path logo benar
        });
    }

    // 3. Logika eksekusi
    if (adaJadwal) {
        // Jika izin sudah diberikan sebelumnya
        if (Notification.permission === "granted") {
            tampilkanNotif();
        } 
        // Jika belum pernah ditanya, minta izin dulu
        else if (Notification.permission !== "denied") {
            Notification.requestPermission().then(function (permission) {
                if (permission === "granted") {
                    tampilkanNotif();
                }
            });
        }
    }
});
</script>
<?php include "../templates/footer.php"; ?>