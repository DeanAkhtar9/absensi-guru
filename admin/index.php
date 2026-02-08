<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('admin');

require "../config/database.php";
include "../templates/header.php";
include "../templates/navbar.php";
?>

<div class="container">
    <h3>Dashboard Admin</h3>
    <p>Kelola data master sistem absensi guru.</p>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5>Kelas</h5>
                    <p>Kelola data kelas</p>
                    <a href="kelas.php" class="btn btn-primary btn-sm">Kelola</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5>Jadwal</h5>
                    <p>Atur jadwal mengajar</p>
                    <button class="btn btn-secondary btn-sm" disabled>Coming soon</button>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5>Users</h5>
                    <p>Manajemen akun</p>
                    <button class="btn btn-secondary btn-sm" disabled>Coming soon</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../templates/footer.php"; ?>
