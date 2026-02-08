<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('admin');

require "../config/database.php";
require "../includes/flash.php";

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

<div class="container">
    <h3>Manajemen Users</h3>
    <?php flash(); ?>

    <form method="post" class="row g-2 mb-4">
        <div class="col">
            <input type="text" name="nama" class="form-control" placeholder="Nama" required>
        </div>
        <div class="col">
            <input type="text" name="username" class="form-control" placeholder="Username" required>
        </div>
        <div class="col">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <div class="col">
            <select name="role" class="form-control">
                <option value="admin">Admin</option>
                <option value="guru">Guru</option>
                <option value="siswa">Siswa</option>
                <option value="walikelas">Wali Kelas</option>
            </select>
        </div>
        <div class="col">
            <button name="tambah" class="btn btn-primary">Tambah</button>
        </div>
    </form>

    <table class="table table-bordered">
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
