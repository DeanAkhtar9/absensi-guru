<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('guru');

include "../templates/header.php";
include "../templates/navbar.php";
?>

<div class="container">
    <h3>Dashboard Guru</h3>
    <p>Selamat datang, <b><?= htmlspecialchars($_SESSION['nama']) ?></b></p>

    <ul>
        <li>Lihat jadwal mengajar hari ini</li>
        <li>Isi jurnal mengajar</li>
        <li>Lihat rekap absensi pribadi</li>
    </ul>
</div>

<?php include "../templates/footer.php"; ?>
