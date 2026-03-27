<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('admin');

require "../config/database.php";

include "../templates/header.php";
include "../sidebar.php";
include "../header.php";

$id=$_GET['id'];

$data=mysqli_fetch_assoc(mysqli_query($conn,"
SELECT * FROM users WHERE id_user='$id'
"));

if(isset($_POST['update'])){

$nama=$_POST['nama'];
$telp=$_POST['telp'];
$email=$_POST['email'];
$role=$_POST['role'];

mysqli_query($conn,"
UPDATE users
SET
nama='$nama',
no_telp='$telp',
email='$email',
role='$role'
WHERE id_user='$id'
");

header("Location: pengguna.php");

}
?>


<link rel="stylesheet" href="../assets/css/bootstrap.min.css">

<div class="container mt-4">

<h4>Edit User</h4>

<form method="POST">

<input class="form-control mb-2" name="nama"
value="<?=$data['nama']?>">

<select class="form-control mb-2" name="role">

<option value="admin">Admin</option>
<option value="guru">Guru</option>
<option value="siswa">Siswa</option>
<option value="walikelas">Wali Kelas</option>

</select>

<input class="form-control mb-2" name="telp"
value="<?=$data['no_telp']?>">

<input class="form-control mb-2" name="email"
value="<?=$data['email']?>">

<div class="d-flex justify-content-between mt-3">
    
        <button name="update" class="btn btn-success">
            Update  
        </button>
        
        <a href="pengguna.php" class="btn btn-secondary btn-sm " style="width:120px; background-color: #067b9b; padding-top:11px;">←  Kembali</a>
   

</div>
</div>
</form>

</div>
