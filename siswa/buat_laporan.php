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
$id_user = $_SESSION['id_user'];

$stmt = $conn->prepare("SELECT id_siswa FROM siswa WHERE id_user=?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_assoc();
$id_siswa = $data['id_siswa'] ?? 0;

/* =========================
   PROSES SIMPAN LAPORAN
========================= */
$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $jenis = $_POST['jenis'] ?? '';
    $tanggal = $_POST['tanggal'] ?? '';
    $pesan = $_POST['pesan'] ?? '';

    if (!$jenis || !$tanggal || !$pesan) {
        $error = "Semua field wajib diisi!";
    } else {

        $stmt = $conn->prepare("
            INSERT INTO komplain (id_siswa, tanggal, pesan, status)
            VALUES (?, ?, ?, 'baru')
        ");

        $stmt->bind_param("iss", $id_siswa, $tanggal, $pesan);

        if ($stmt->execute()) {
            $success = "Laporan berhasil dikirim!";
        } else {
            $error = "Gagal mengirim laporan!";
        }
    }
}
?>
<link rel="stylesheet" href="../assets/css/bootstrap.min.css">
<style>
/* container utama */
.page-wrapper{
    padding:40px;
}

/* card utama */
.laporan-card{
    background:#fff;
    border-radius:16px;
    padding:30px;
    box-shadow:0 6px 20px rgba(0,0,0,0.05);
    border:1px solid #eee;
}

/* title */
.page-title{
    font-weight:700;
    font-size:22px;
}

.page-subtitle{
    color:#6b7280;
    font-size:14px;
}

/* label */
.form-label{
    font-size:13px;
    font-weight:600;
    color:#374151;
}

/* input */
.form-control, .form-select{
    border-radius:10px;
    padding:10px 12px;
    border:1px solid #e5e7eb;
    font-size:14px;
}

.form-control:focus, .form-select:focus{
    border-color:#3B82F6;
    box-shadow:0 0 0 2px rgba(59,130,246,0.15);
}

/* textarea */
textarea.form-control{
    resize:none;
}

/* button */
.btn-submit{
    background:linear-gradient(90deg,#3B82F6,#2563EB);
    border:none;
    border-radius:10px;
    padding:12px;
    font-weight:500;
    color:white;
    transition:0.3s;
}

.btn-submit:hover{
    opacity:0.9;
}

/* alert */
.alert{
    border-radius:10px;
}
</style>

<div class="main-content">
<div class="page-wrapper">

<div class="row justify-content-center">
<div class="col-md-8">

<!-- HEADER -->
<div class="mb-4">
    <div class="page-title">Buat Laporan</div>
    <div class="page-subtitle">
        Laporkan kejadian yang terjadi di sekolah dengan detail dan akurat
    </div>
</div>

<!-- CARD -->
<div class="laporan-card">

<?php if($success): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<?php if($error): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form method="POST">

<div class="row">

<!-- JENIS -->
<div class="col-md-6 mb-3">
<label class="form-label">Jenis Laporan</label>
<select name="jenis" class="form-select" required>
<option value="">Pilih jenis laporan</option>
<option value="akademik">Akademik</option>
<option value="fasilitas">Fasilitas</option>
<option value="kedisiplinan">Kedisiplinan</option>
</select>
</div>

<!-- TANGGAL -->
<div class="col-md-6 mb-3">
<label class="form-label">Tanggal</label>
<input type="date" name="tanggal" class="form-control" required>
</div>

</div>

<!-- DESKRIPSI -->
<div class="mb-3">
<label class="form-label">Deskripsi</label>
<textarea 
    name="pesan"
    class="form-control"
    rows="5"
    placeholder="Tuliskan detail laporan secara jelas..."
    required
></textarea>
</div>

<!-- BUTTON -->
<button type="submit" class="btn-submit w-100 mt-2">
    ➤ Kirim Laporan
</button>

</form>

</div>

</div>
</div>

</div>
</div>


<?php include "../templates/footer.php"; ?>
