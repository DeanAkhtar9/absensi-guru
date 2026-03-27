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
   AMBIL ID SISWA
========================= */
$id_user = $_SESSION['id_user'] ?? 0;

if ($id_user == 0) {
    die("<div class='alert alert-danger m-4'>Error: Session user belum terisi. Silakan login ulang.</div>");
}

$stmt = $conn->prepare("SELECT id_siswa FROM siswa WHERE id_user=?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_assoc();
$id_siswa = $data['id_siswa'] ?? 0;

if ($id_siswa == 0) {
    die("<div class='alert alert-danger m-4'>Error: Data siswa tidak ditemukan untuk id_user=".$id_user."</div>");
}

/* =========================
   INISIALISASI VARIABEL
========================= */
$jenis = '';
$tanggal = '';
$pesan = '';

$success = "";
$error = "";

$allowedJenis = ['akademik','fasilitas','kedisiplinan'];

/* =========================
   PROSES SIMPAN LAPORAN
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $jenis = $_POST['jenis'] ?? '';
    $tanggal = $_POST['tanggal'] ?? '';
    $pesan = $_POST['pesan'] ?? '';

    // Validasi
    if (!$jenis || !$tanggal || !$pesan) {
        $error = "Semua field wajib diisi!";
    } elseif(!in_array($jenis, $allowedJenis)) {
        $error = "Jenis laporan tidak valid!";
    } else {

        $stmt = $conn->prepare("
            INSERT INTO komplain (id_siswa, jenis_laporan, tanggal, pesan, status)
            VALUES (?, ?, ?, ?, 'baru')
        ");

        $stmt->bind_param("isss", $id_siswa, $jenis, $tanggal, $pesan);

        if ($stmt->execute()) {
            $success = "Laporan berhasil dikirim!";
            // reset form
            $jenis = $tanggal = $pesan = '';
        } else {
            $error = "Gagal mengirim laporan! ".$conn->error;
        }
    }
}
?>

<link rel="stylesheet" href="../assets/css/bootstrap.min.css">
<style>
.page-wrapper{padding:40px;}
.laporan-card{background:#fff;border-radius:16px;padding:30px;box-shadow:0 6px 20px rgba(0,0,0,0.05);border:1px solid #eee;}
.page-title{font-weight:700;font-size:18px;margin-top:-40px;}
.page-subtitle{color:#6b7280;font-size:14px;}
.form-label{font-size:13px;font-weight:600;color:#374151;}
.form-control, .form-select{border-radius:10px;padding:10px 12px;border:1px solid #e5e7eb;font-size:14px;}
.form-control:focus, .form-select:focus{border-color:#3B82F6;box-shadow:0 0 0 2px rgba(59,130,246,0.15);}
textarea.form-control{resize:none;}
.btn-submit{background:linear-gradient(90deg,#3B82F6,#2563EB);border:none;border-radius:10px;padding:12px;font-weight:500;color:white;transition:0.3s;}
.btn-submit:hover{opacity:0.9;}
.alert{border-radius:10px;}
</style>

<div class="main-content">
<div class="page-wrapper">

<div class="row">
<div class="col-md-12">

<div class="mb-4">
    <div class="page-title" style="font-size:30px;">Buat Laporan</div>
    <div class="page-subtitle">Laporkan kejadian yang terjadi di sekolah dengan detail dan akurat</div>
</div>

<div class="laporan-card">

<?php if($success): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<?php if($error): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form method="POST">
<div class="row">
<div class="col-md-6 mb-3">
<label class="form-label">Jenis Laporan</label>
<select name="jenis" class="form-select" required>
<option value="">Pilih jenis laporan</option>
<?php foreach($allowedJenis as $j): ?>
<option value="<?= $j ?>" <?= ($jenis==$j)?'selected':'' ?>><?= ucfirst($j) ?></option>
<?php endforeach; ?>
</select>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Tanggal</label>
<input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($tanggal) ?>" required>
</div>
</div>

<div class="mb-3">
<label class="form-label">Deskripsi</label>
<textarea name="pesan" class="form-control" rows="7" placeholder="Tuliskan detail laporan..." required><?= htmlspecialchars($pesan) ?></textarea>
</div>

<button type="submit" class="btn-submit w-100 mt-2">➤ Kirim Laporan</button>

</form>

</div>
</div>
</div>
</div>
</div>

<?php include "../templates/footer.php"; ?>