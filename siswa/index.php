<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('siswa');

include "../templates/header.php";
include "../templates/navbar.php";
?>

<div class="container">
    <h3>Dashboard Siswa (Perwakilan)</h3>
    <p>Silakan mengisi absensi guru sesuai jadwal pelajaran.</p>

    <div class="alert alert-info">
        Menu utama kamu adalah <b>Absensi Guru</b>.
    </div>
</div>

<?php include "../templates/footer.php"; ?>
