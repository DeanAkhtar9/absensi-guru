<?php

require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('admin');

require "../config/database.php";

include "../templates/header.php";
include "../sidebar.php";
include "../header.php";

if(isset($_POST['simpan'])){

$username=$_POST['username'];
$password=password_hash($_POST['password'],PASSWORD_DEFAULT);
$nama=$_POST['nama'];
$role=$_POST['role'];
$telp=$_POST['telp'];
$email=$_POST['email'];

mysqli_query($conn,"
INSERT INTO users
(username,password,nama,role,no_telp,email)
VALUES
('$username','$password','$nama','$role','$telp','$email')
");

header("Location: pengguna.php");

}
?>

<link rel="stylesheet" href="assets/css/bootstrap.min.css">

<div class="container mt-4">

<h4>Tambah Pengguna</h4>

<form method="POST">

<input class="form-control mb-2" name="username" placeholder="Username">

<input class="form-control mb-2" name="password" placeholder="Password">

<input class="form-control mb-2" name="nama" placeholder="Nama">

<select class="form-control mb-2" name="role">

<option value="admin">Admin</option>
<option value="guru">Guru</option>
<option value="siswa">Siswa</option>
<option value="walikelas">Wali Kelas</option>

</select>

<input class="form-control mb-2" name="telp" placeholder="No Telp">

<input class="form-control mb-2" name="email" placeholder="Email">

<button name="simpan" class="btn btn-primary">Simpan</button>

</form>

</div>
