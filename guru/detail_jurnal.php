<?php
session_start();

require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('guru');

include "../config/database.php";

/* =========================
   VALIDASI ID
========================= */
if (!isset($_GET['id'])) {
    die("ID tidak ditemukan");
}

$id = $_GET['id'];

$id_guru = $_SESSION['id_user'];


    $query = mysqli_query($conn, "
    SELECT j.*, a.status AS kehadiran, a.keterangan AS ket_absen
    FROM jurnal_mengajar j
    JOIN absensi_guru a ON j.id_absensi_guru = a.id_absensi_guru
    WHERE j.id_jurnal = '$id'
    AND j.diisi_oleh = '$id_guru'");


$data = mysqli_fetch_assoc($query);

if (!$data) {
    die("Data tidak ditemukan atau bukan milik Anda");
}

?>

<?php include "../templates/header.php"; ?>
<?php include "../sidebar.php"; ?>
<?php include "../header.php"; ?>
<style>
@media print {

    body * {
        visibility: hidden;
    }

    #areaPrint, #areaPrint * {
        visibility: visible;
    }

    #areaPrint {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }

}
</style>
<!-- =========================
     CONTENT
========================= -->
<div class="main-content p-4">

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-start mb-4">

    <!-- KIRI -->
    <div>
        <h5 class="fw-bold mb-1" style="font-size:28px;">Detail Jurnal</h5>
        <p class="text-muted mb-0">Jurnal Kegiatan Pembelajaran</p>
    </div>

    <!-- KANAN -->


<div class="d-flex gap-2">
    <a href="riwayat_jurnal.php" 
       class="btn btn-secondary btn-sm"
       style="width:120px; background-color:#067b9b;">
       ← Kembali
    </a>
    
        <button onclick="printJurnal()" 
class="btn btn-success btn-sm">
🖨 Print
</button>

<button onclick="downloadJurnal()" 
class="btn btn-primary btn-sm">
⬇ Download
</button>
</div>
</div>

<!-- CARD -->
<div class="card shadow-sm border-0" id="areaPrint">
<div class="card-body p-4">

<?php
$status = $data['status_verifikasi'];

if($status == 'diverifikasi'){
    $badge = "bg-success-subtle text-success";
}elseif($status == 'draft'){
    $badge = "bg-secondary-subtle text-dark";
}else{
    $badge = "bg-warning-subtle text-warning";
}
?>

<!-- INFO UTAMA -->
<div class="row mb-4">

<div class="col-md-6 mb-3">
    <label class="text-muted small">Tanggal</label>
    <div class="fw-semibold">
        <?= date('d M Y H:i', strtotime($data['tanggal'])) ?>
    </div>
</div>

<div class="col-md-6 mb-3">
    <label class="text-muted small">Status</label><br>
    <span class="badge <?= $badge ?>">
        <?= ucfirst($status) ?>
    </span>
</div>

<div class="col-md-6 mb-3">
    <label class="text-muted small">Kehadiran</label>
    <div class="fw-semibold">
        <?= ucfirst($data['status_verifikasi']) ?>
    </div>
</div>

<div class="col-md-6 mb-3">
    <label class="text-muted small">Kelas</label>
    <div class="fw-semibold">
        <?= htmlspecialchars($data['kelas']) ?>
    </div>
</div>


<div class="col-md-6 mb-3">
    <label class="text-muted small">MataPelajaran</label>
    <div class="fw-semibold">
        <?= htmlspecialchars($data['mapel'] ?? '-') ?>
    </div>
</div>

<div class="col-md-6 mb-3">
    <label class="text-muted small">Kehadiran</label>
    <div class="fw-semibold">
        <?= ucfirst($data['kehadiran']) ?>
    </div>
</div>

<?php if(!empty($data['ket_absen'])): ?>
<div class="col-md-6 mb-3">
    <label class="text-muted small">Keterangan Absensi</label>
    <div class="fw-semibold">
        <?= htmlspecialchars($data['ket_absen']) ?>
    </div>
</div>
<?php endif; ?>

</div>

<hr>

<!-- MATERI -->
<div>
    <label class="text-muted small">Kegiatan Pembelajaran</label>

    <div class="mt-2 p-3"
         style="
            background:#f8fafc;
            border-radius:12px;
            border:1px solid #e5e7eb;
            white-space:pre-line;
         ">
        <?= htmlspecialchars($data['materi']) ?>
    </div>
</div>

</div>
</div>

</div>
<script>
function printJurnal(){
    window.print();
}

function downloadJurnal(){
    window.print(); // user pilih "Save as PDF"
}
</script>
<?php include "../templates/footer.php"; ?>
