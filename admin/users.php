<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('admin');

require "../config/database.php";
require "../includes/flash.php";


include "../templates/navbar.php";
include "../sidebar.php";
include "../header.php";
/*
|--------------------------------------------------------------------------
| Tambah user
|--------------------------------------------------------------------------
*/
if (isset($_POST['tambah'])) {
    $nama  = $_POST['nama'];
    $user  = $_POST['username'];
    $pass  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role  = $_POST['role'];

    mysqli_query($conn, "
        INSERT INTO users (nama, username, password, role)
        VALUES ('$nama','$user','$pass','$role')
    ");

    setFlash('success', 'User berhasil ditambahkan');
    header("Location: users.php");
    exit;
}

$data = mysqli_query($conn, "SELECT * FROM users ORDER BY role");

include "../templates/header.php";
include "../templates/navbar.php";
?>

<div class="users-wrapper">
    <p>Manajemen Users</>
    <?php flash(); ?>

    <div class="card users-card mb-4">
    <div class="card-body p-4">

        <h5 class="mb-4 fw-bold text-primary">
            <i class="bi bi-person-plus-fill me-2"></i>Tambah User Baru
        </h5>

        <form method="post">
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nama Lengkap</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-person"></i>
                        </span>
                        <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-at"></i>
                        </span>
                        <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Role</label>
                    <select name="role" class="form-select">
                        <option value="admin">Admin</option>
                        <option value="guru">Guru</option>
                        <option value="siswa">Siswa</option>
                        <option value="walikelas">Wali Kelas</option>
                    </select>
                </div>

                <div class="col-12 text-end mt-3">
                    <button type="reset" class="btn btn-outline-secondary me-2">
                        Reset
                    </button>
                    <button name="tambah" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>

            </div>
        </form>

    </div>
</div>

    <table class="table table-bordered users-table">
        <thead class="table-dark">
            <tr>
                <th>Nama</th>
                <th>Username</th>
                <th>Role</th>
            </tr>
        </thead>
        <tbody>
            <?php while($u=mysqli_fetch_assoc($data)) { ?>
            <tr>
                <td><?= htmlspecialchars($u['nama']); ?></td>
                <td><?= htmlspecialchars($u['username']); ?></td>
                <td><?= $u['role']; ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php include "../templates/footer.php"; ?>
