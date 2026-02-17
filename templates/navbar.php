<?php
// templates/navbar.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login'])) {
    return; // navbar tidak tampil, tapi halaman tetap lanjut
}

$role = $_SESSION['role'];
$nama = $_SESSION['nama'];
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="<?= BASE_URL ?>/index.php">Absensi Guru</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto">

                <?php if ($role === 'siswa'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/siswa/index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/siswa/absensi_guru.php">Absensi Guru</a>
                    </li>

                <?php elseif ($role === 'guru'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/guru/index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/guru/jadwal.php">Jadwal</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/guru/rekap.php">Rekap</a>
                    </li>

                <?php elseif ($role === 'walikelas'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/walikelas/index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/walikelas/jurnal.php">Jurnal</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/walikelas/absensi.php">Absensi</a>
                    </li>
                <?php endif; ?>

            </ul>

            <span class="navbar-text me-3 text-light">
                <?= htmlspecialchars($nama) ?> (<?= htmlspecialchars($role) ?>)
            </span>

            <a href="<?= BASE_URL ?>/logout.php" class="btn btn-sm btn-outline-light">
                Logout
            </a>
        </div>
    </div>
</nav>
