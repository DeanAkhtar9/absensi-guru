<?php
session_start();
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('guru');

include "../config/database.php";

date_default_timezone_set('Asia/Jakarta');

$id_user = $_SESSION['id_user'];

/* ========================= */
function getCSV($url){
    return @file_get_contents($url);
}

function hariFix($h){
    return strtolower(str_replace(['jumaat','jum\'at'],'jumat',trim($h)));
}
/* ========================= */

$jadwalHariIni = [];

$hari_map = [
 'Sunday'=>'minggu','Monday'=>'senin','Tuesday'=>'selasa',
 'Wednesday'=>'rabu','Thursday'=>'kamis','Friday'=>'jumat','Saturday'=>'sabtu'
];

$hari_now = $hari_map[date('l')];

/* =========================
   CEK ABSENSI
========================= */
$qAbsen = mysqli_query($conn,"
SELECT id_absensi_guru,status,created_at 
FROM absensi_guru
WHERE id_user='$id_user'
AND DATE(tanggal)=CURDATE()
ORDER BY id_absensi_guru DESC
LIMIT 1
");

$absen = mysqli_fetch_assoc($qAbsen);

/* =========================
   AMBIL JADWAL
========================= */
$url_master = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=799813071&output=csv";

$rows_master = array_map("str_getcsv", explode("\n", getCSV($url_master)));

foreach ($rows_master as $row){

    if(count($row)<2) continue;

    $kelas = trim($row[0]);
    $gid   = trim($row[1]);

    $csv = getCSV("https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=$gid&output=csv");

    if(!$csv) continue;

    $rows = array_map("str_getcsv", explode("\n", $csv));

    foreach($rows as $i=>$r){

        if($i==0 || count($r)<5) continue;

        $id_guru = intval($r[0]);
        $hari = hariFix($r[2]);

        if($id_guru != $id_user || $hari != $hari_now) continue;

        /* =========================
           CEK JURNAL
        ========================= */
        $qJurnal = mysqli_query($conn,"
        SELECT 1 FROM jurnal_mengajar
        WHERE diisi_oleh='$id_user'
        AND kelas='$kelas'
        AND mapel='".$r[1]."'
        AND DATE(tanggal)=CURDATE()
        ");

        $sudahIsi = mysqli_num_rows($qJurnal)>0;

        /* =========================
           VALIDASI 12 JAM (FIX TOTAL)
        ========================= */

        $bolehIsi = false;

        // Ambil jam mulai dari jadwal (contoh: 01:00)
        $jam_mulai = strtotime(date('Y-m-d').' '.$r[3]);

        // FIX untuk jam dini hari (misal 01:00 dianggap kemarin)
        if($jam_mulai > time()){
            $jam_mulai = strtotime('-1 day', $jam_mulai);
        }

        $batas = $jam_mulai + 43200; // +12 jam

        if($absen && in_array($absen['status'],['hadir','izin'])){

            if(time() <= $batas && !$sudahIsi){
                $bolehIsi = true;
            }
        }

        /* =========================
           STATUS TAMBAHAN
        ========================= */

        $statusText = '';
        $badge = '';

        if($sudahIsi){
            $statusText = '✔ Sudah diisi';
            $badge = 'bg-success';
        }
        elseif(!$absen){
            $statusText = 'Belum diabsen';
            $badge = 'bg-danger';
        }
        elseif(time() > $batas){
            $statusText = 'Lewat 12 jam';
            $badge = 'bg-secondary';
        }
        elseif(time() < $jam_mulai){
            $statusText = 'Belum waktunya';
            $badge = 'bg-warning';
        }
        else{
            $statusText = 'Isi Jurnal';
            $badge = 'bg-primary';
        }

        $jadwalHariIni[] = [
            'kelas'=>$kelas,
            'mapel'=>$r[1],
            'jam'=>$r[3]." - ".$r[4],
            'status'=>$absen['status'] ?? null,
            'boleh'=>$bolehIsi,
            'sudah'=>$sudahIsi,
            'text'=>$statusText,
            'badge'=>$badge
        ];
    }
}


/* =========================
   OTOMATIS KIRIM NOTIF KE GURU (PENGINGAT)
========================= */
foreach ($jadwalHariIni as $j) {
    // Jika statusnya adalah "Isi Jurnal" (artinya sudah boleh isi tapi belum diisi)
    if ($j['text'] == 'Isi Jurnal' && $j['boleh'] == true) {
        $judul_pengingat = "Pengingat Jurnal: " . $j['mapel'];
        $isi_pengingat = "Anda memiliki jadwal mengajar di kelas " . $j['kelas'] . " yang belum diisi jurnalnya. Silakan segera diisi.";
        
        // Cek dulu apakah hari ini sudah pernah dikirim notif serupa agar tidak spam
        $cekNotif = mysqli_query($conn, "SELECT id_notif FROM notifikasi 
                                        WHERE id_user = '$id_user' 
                                        AND judul = '$judul_pengingat' 
                                        AND DATE(created_at) = CURDATE()");
        
        if (mysqli_num_rows($cekNotif) == 0) {
            kirimNotifikasi($id_user, $judul_pengingat, $isi_pengingat);
        }
    }
}
?>

<?php include "../templates/header.php"; ?>
<?php include "../sidebar.php"; ?>
<?php include "../header.php"; ?>

<div class="main-content">

<div class="container-jurnal">

<h4 class="title">Jurnal Hari Ini</h4>
<p class="subtitle">
Silakan isi jurnal mengajar sesuai jadwal hari ini
</p>

<div class="grid-jurnal">

<?php foreach($jadwalHariIni as $j): ?>
<div class="card-jurnal"
style="cursor:<?= $j['boleh'] ? 'pointer':'not-allowed' ?>;
opacity:<?= $j['boleh'] ? '1':'0.6' ?>;"
onclick="<?= $j['boleh'] ? "window.location.href='isi_jurnal.php?kelas=".urlencode($j['kelas'])."&mapel=".urlencode($j['mapel'])."'" : "" ?>"
>

<div class="card-body">

<!-- HEADER FLEX -->
<div class="top-row">
    <div class="kelas">Kelas <?= $j['kelas'] ?></div>
    <div class="jam">
        <i class="bi bi-clock"></i> <?= $j['jam'] ?>
    </div>
</div>

<!-- MAPEL -->
<div class="mapel"><?= $j['mapel'] ?></div>

<hr class="divider">

</div>

<div class="card-footer">
<span class="badge-custom <?= $j['badge'] ?>">
    <?= $j['text'] ?>
</span>
</div>

</div>
<?php endforeach; ?>

</div>
</div>
</div>
<?php include "../templates/footer.php"; ?>