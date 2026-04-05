<?php
session_start();

require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('walikelas');

include "../config/database.php";

// =========================
// VALIDASI SESSION
// =========================
if (!isset($_SESSION['id_user'])) {
    die("Session tidak valid");
}

$id_user = $_SESSION['id_user'];
$role = $_SESSION['role'] ?? '';

// =========================
// AMBIL DATA USER
// =========================
$query_user = mysqli_query($conn, "
    SELECT * FROM users WHERE id_user='$id_user'
");

if (!$query_user) {
    die("Query error: " . mysqli_error($conn));
}

if (mysqli_num_rows($query_user) == 0) {
    die("User tidak ditemukan");
}

$user_data = mysqli_fetch_assoc($query_user);

// =========================
// AMBIL KELAS WALI
// =========================
$query_kelas = mysqli_query($conn, "
    SELECT nama_kelas FROM kelas 
    WHERE id_walikelas='$id_user'
");

$nama_kelas = '-';

if ($query_kelas && mysqli_num_rows($query_kelas) > 0) {
    $kelas = mysqli_fetch_assoc($query_kelas);
    $nama_kelas = $kelas['nama_kelas'];
}
?>

<?php include "../templates/header.php"; ?>
<?php include "../sidebar.php"; ?>
<?php include "../header.php"; ?>

<link rel="stylesheet" href="../assets/css/bootstrap.min.css">

<style>
.profile-card{
    background:white;
    border-radius:14px;
    padding:2px 30px 30px 30px;
}

.info-item{
    display:flex;
    align-items:center;
    gap:20px;
    padding:30px 10px 30px 0px;
    border-bottom:1px solid rgba(0,0,0,0.05);
}

.icon-box{
    width:70px;
    height:70px;
    font-size:35px;
    color: #3d4eff;
    border-radius:10px;
    background:#eef4ff;
    display:flex;
    align-items:center;
    justify-content:center;
}

.label{
    font-size:12px;
    color:#6c757d;
}

.value{
    font-weight:500;
}
</style>

<div class="main-content p-4">
<div class="profile-card">

<div class="d-flex align-items-center gap-3 mb-4 mt-4">
<div>
<h5 class="fw-bold mb-1">
<?= htmlspecialchars($user_data['nama'] ?? '-') ?>
</h5>

<div class="text-primary small">
<?= htmlspecialchars($role) ?> - <?= htmlspecialchars($nama_kelas) ?>
</div>
</div>
</div>

<hr>

<div class="mt-3">

<!-- EMAIL -->
<div class="info-item">
<div class="icon-box bi bi-envelope"></div>
<div>
<div class="label">EMAIL INSTANSI</div>
<div class="value"><?= htmlspecialchars($user_data['email'] ?? '-') ?></div>
</div>
</div>

<!-- TELEPON -->
<div class="info-item">
<div class="icon-box bi bi-telephone"></div>
<div>
<div class="label">NOMOR TELEPON</div>
<div class="value"><?= htmlspecialchars($user_data['no_telp'] ?? '-') ?></div>
</div>
</div>

<!-- UNIT -->
<div class="info-item" style="border-bottom:none;">
<div class="icon-box bi bi-house"></div>
<div>
<div class="label">UNIT KERJA</div>
<div class="value">SMKN 10 SURABAYA</div>
</div>
</div>

</div>

</div>
</div>

<?php include "../templates/footer.php"; ?>