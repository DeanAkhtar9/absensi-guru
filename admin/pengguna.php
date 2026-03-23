<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('admin');

require "../config/database.php";

include "../templates/header.php";
include "../sidebar.php";
include "../header.php";

/* =========================
SEARCH & FILTER
========================= */

$search = isset($_GET['search']) ? $_GET['search'] : "";
$role   = isset($_GET['role']) ? $_GET['role'] : "";


/* =========================
PAGINATION
========================= */

$limit = 5;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$offset = ($page - 1) * $limit;


/* =========================
QUERY FILTER
========================= */

$where = "WHERE 1=1";

if($search){
$where .= " AND (nama LIKE '%$search%' OR email LIKE '%$search%')";
}

if($role){
$where .= " AND role='$role'";
}


/* =========================
TOTAL DATA
========================= */

$total = mysqli_query($conn,"SELECT COUNT(*) as total FROM users $where");
$total = mysqli_fetch_assoc($total)['total'];

$totalPages = ceil($total / $limit);


/* =========================
AMBIL DATA USER
========================= */

$query = mysqli_query($conn,"
SELECT * FROM users
$where
ORDER BY id_user DESC
LIMIT $limit OFFSET $offset
");

?>

<link rel="stylesheet" href="../assets/css/bootstrap.min.css">
<div class="main-content">
<div class="container py-4">

<h4 class="fw-bold mb-2">Kelola Pengguna</h4>
<p class="text-muted">Daftar semua pengguna dalam sistem</p>


<div class="card shadow-sm border-0">

<div class="card-body">

<!-- SEARCH -->

<form method="GET">

<div class="row mb-3">

<div class="col-md-6">
<input type="text" name="search" class="form-control"
placeholder="Cari nama atau email..."
value="<?=htmlspecialchars($search)?>">
</div>

<div class="col-md-3">

<select name="role" class="form-select">

<option value="">Semua Role</option>

<option value="admin">Admin</option>
<option value="guru">Guru</option>
<option value="siswa">Siswa</option>
<option value="walikelas">Wali Kelas</option>

</select>

</div>

<div class="col-md-3 d-flex gap-2">

<button class="btn btn-primary">Filter</button>

<a href="tambahpengguna.php" class="btn btn-success">
+ Tambah Pengguna
</a>

</div>

</div>

</form>


<!-- TABLE -->

<table class="table align-middle">

<thead class="table-light">
<tr>
<th>NAMA</th>
<th>ID</th> <!-- TAMBAHAN -->
<th>ROLE</th>
<th>EMAIL</th>
<th>NO TELP</th>
<th>AKSI</th>
</tr>
</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($query)): ?>
<tr>
<td><?=$row['nama']?></td>

<td><?=$row['id_user']?></td> <!-- TAMBAHAN -->

<td>
<?php
$role = $row['role'];

if($role=='admin'){
echo "<span class='badge bg-dark'>Admin</span>";
}
elseif($role=='guru'){
echo "<span class='badge bg-primary'>Guru</span>";
}
elseif($role=='siswa'){
echo "<span class='badge bg-success'>Siswa</span>";
}
elseif($role=='walikelas'){
echo "<span class='badge bg-warning text-dark'>Wali Kelas</span>";
}
?>

</td>

<td><?=$row['email']?></td>

<td><?=$row['no_telp']?></td>

<td>

<a href="updatepengguna.php?id=<?=$row['id_user']?>" 
class="btn btn-sm btn-primary">

Edit

</a>

<a href="hapuspengguna.php?id=<?=$row['id_user']?>" 
class="btn btn-sm btn-danger"
onclick="return confirm('Hapus user ini?')">

Hapus

</a>

</td>

</tr>

<?php endwhile ?>

</tbody>

</table>


<!-- PAGINATION -->

<nav>

<ul class="pagination">

<?php for($i=1;$i<=$totalPages;$i++): ?>

<li class="page-item <?=($i==$page)?'active':''?>">

<a class="page-link" href="?page=<?=$i?>">
<?=$i?>
</a>

</li>

<?php endfor ?>

</ul>

</nav>


</div>
</div>
</div>
</div>

<?php include "../templates/footer.php"; ?>
