<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('admin');

require "../config/database.php";

$success = "";
$error = "";

// =========================
// PROSES SIMPAN PENGGUNA
// =========================
if(isset($_POST['simpan'])){

    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nama = $_POST['nama'];
    $role = $_POST['role'];
    $telp = $_POST['telp'];
    $email = $_POST['email'];

    if(empty($username) || empty($password) || empty($nama) || empty($role)){
        $error = "Username, Password, Nama, dan Role wajib diisi!";
    } else {
        $query = mysqli_query($conn,"
            INSERT INTO users
            (username,password,nama,role,no_telp,email)
            VALUES
            ('$username','$password','$nama','$role','$telp','$email')
        ");

        if($query){
            $success = "Pengguna berhasil ditambahkan!";
            // reset form
            $_POST = [];
        } else {
            $error = "Gagal menambahkan pengguna! ".$conn->error;
        }
    }
}

// =========================
// INCLUDE TEMPLATE
// =========================
include "../templates/header.php";
include "../sidebar.php";
include "../header.php";
?>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/bootstrap.min.css">
<style>
.main-content { padding:30px; }
.form-wrapper { background:#fff; padding:25px; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.05); border:1px solid #eee; max-width:600px; }
.form-control, .form-select { margin-bottom:12px; border-radius:8px; padding:10px 12px; border:1px solid #ddd; }
.btn-submit { border-radius:8px; padding:10px 15px; }
.alert { border-radius:8px; cursor:pointer; }
</style>

<div class="main-content">

    <h4 class="mb-4">Tambah Pengguna</h4>

    <div class="form-wrapper">

        <!-- ALERT -->
        <?php if($success): ?>
        <div class="alert alert-success" id="alertBoxSuccess"><?= $success ?></div>
        <?php endif; ?>

        <?php if($error): ?>
        <div class="alert alert-danger" id="alertBoxError"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">

            <input class="form-control" name="username" placeholder="Username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
            <input class="form-control" type="password" name="password" placeholder="Password" required>
            <input class="form-control" name="nama" placeholder="Nama" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>

            <select class="form-select" name="role" required>
                <option value="">Pilih Role</option>
                <?php 
                $roles = ['admin','guru','siswa','walikelas'];
                foreach($roles as $r):
                $sel = (($_POST['role'] ?? '')==$r)?'selected':'';
                ?>
                <option value="<?= $r ?>" <?= $sel ?>><?= ucfirst($r) ?></option>
                <?php endforeach; ?>
            </select>

            <input class="form-control" name="telp" placeholder="No Telp" value="<?= htmlspecialchars($_POST['telp'] ?? '') ?>">
            <input class="form-control" type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

            

            <div class="d-flex justify-content-between mt-3">

                <button name="simpan" class="btn btn-primary btn-submit mt-2">Simpan</button>
                <a href="pengguna.php" class="btn btn-secondary btn-sm " style="width:120px; background-color: #067b9b; padding-top:11px; margin-top:9px;">←  Kembali</a>
        
            </div>
        </form>
    </div>

</div>

<script>
// Hilangkan alert otomatis setelah 3 detik
setTimeout(function(){
    const alertSuccess = document.getElementById('alertBoxSuccess');
    if(alertSuccess) alertSuccess.style.display = 'none';

    const alertError = document.getElementById('alertBoxError');
    if(alertError) alertError.style.display = 'none';
}, 3000);

// Hilangkan alert saat diklik
document.querySelectorAll('.alert').forEach(function(el){
    el.addEventListener('click', function(){
        el.style.display = 'none';
    });
});
</script>

<?php include "../templates/footer.php"; ?>