<?php
session_start();

require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('walikelas');

include "../config/database.php";

$id_user = $_SESSION['id_user'];

/* =========================
   AMBIL DATA USER
========================= */
$user = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT * FROM users WHERE id_user='$id_user'
"));

/* =========================
   AMBIL KELAS WALI (OPSIONAL)
========================= */
$kelas = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT nama_kelas FROM kelas 
    WHERE id_walikelas='$id_user'
"));

$nama_kelas = $kelas['nama_kelas'] ?? '-';
?>

<?php include "../templates/header.php"; ?>
<?php include "../sidebar.php"; ?>
<?php include "../header.php"; ?>

<style>
.profile-card{
    background:white;
    border-radius:14px;
    padding:30px;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
}

.avatar{
    width:80px;
    height:80px;
    border-radius:50%;
    background:#eef4ff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:30px;
}

.info-item{
    display:flex;
    align-items:center;
    gap:15px;
    padding:15px 0;
    border-bottom:1px solid rgba(0,0,0,0.05);
}

.icon-box{
    width:40px;
    height:40px;
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

<link rel="stylesheet" href="../assets/css/bootstrap.min.css">


<div class="main-content p-4">

<div class="profile-card">

<!-- HEADER PROFILE -->
<div class="d-flex align-items-center gap-3 mb-4">

<div class="avatar">
👨‍🏫
</div>

<div>
<h5 class="fw-bold mb-1">
<?= htmlspecialchars($user['nama']) ?>
</h5>

<div class="text-primary small">
WALAS - <?= htmlspecialchars($nama_kelas) ?>
</div>

<div class="mt-1">
<span class="badge bg-primary-subtle text-primary">
NIP: 123456789
</span>

<span class="badge bg-success-subtle text-success">
Status: Aktif
</span>
</div>

</div>

</div>

<hr>

<!-- INFO -->
<div class="mt-3">

<!-- EMAIL -->
<div class="info-item">
<div class="icon-box">📧</div>
<div>
<div class="label">EMAIL INSTANSI</div>
<div class="value"><?= htmlspecialchars($user['email']) ?></div>
</div>
</div>

<!-- TELEPON -->
<div class="info-item">
<div class="icon-box">📞</div>
<div>
<div class="label">NOMOR TELEPON</div>
<div class="value"><?= htmlspecialchars($user['no_telp']) ?></div>
</div>
</div>

<!-- UNIT -->
<div class="info-item" style="border-bottom:none;">
<div class="icon-box">🏫</div>
<div>
<div class="label">UNIT KERJA</div>
<div class="value">SMKN 10 SURABAYA</div>
</div>
</div>

</div>

</div>

</div>

<?php include "../templates/footer.php"; ?>
