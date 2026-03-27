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
$search = $_GET['search'] ?? "";
$role   = $_GET['role'] ?? "";

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
$totalQuery = mysqli_query($conn,"SELECT COUNT(*) as total FROM users $where");
$total = mysqli_fetch_assoc($totalQuery)['total'];
$totalPages = ceil($total / $limit);

/* =========================
   DATA USER
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
<div class="container-fluid py-4 px-4">

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Kelola Pengguna</h4>
        <p class="text-muted mb-0">Daftar semua pengguna dalam sistem</p>
    </div>

    <a href="tambahpengguna.php" class="btn btn-primary">
        <i class="bi bi-plus"></i> Tambah Pengguna
    </a>
</div>

<!-- =========================
     FILTER CARD (FLEX FIX)
========================= -->

        <form method="GET" style="display:flex; gap:10px; margin-bottom:30px; margin-top:40px;">

            <div style="display:flex; align-items:center; flex:1; position:relative;">

            <i class="bi bi-search"
            style="position:absolute; left:12px; color:#6c757d; font-size:14px;">
            </i>

            <input type="text" name="search"
                value="<?= htmlspecialchars($search) ?>"
                placeholder="Cari nama atau email..."
                class="form-control search-input"
                style="height:45px; padding-left:38px; line-height:45px;">

        </div>

            <select name="role" style="width:680px; height:45px;" class="form-select">
                <option value="" <?= $role=='' ? 'selected' : '' ?>>Cari role</option>
                <option value="admin" <?=($role=='admin')?'selected':''?>>Admin</option>
                <option value="guru" <?=($role=='guru')?'selected':''?>>Guru</option>
                <option value="siswa" <?=($role=='siswa')?'selected':''?>>Siswa</option>
                <option value="walikelas" <?=($role=='walikelas')?'selected':''?>>Wali Kelas</option>
            </select>

            <button style="width:140px; height:45px;" class="badge role-admin">
                <i class="bi bi-funnel"></i> Filter
            </button>

        </form>


<!-- =========================
     TABLE
========================= -->
<div class="card shadow-sm border-0">
<div class="card-body">

<div class="table-responsive">
<table class="table align-middle">

<thead class="table-light">
<tr>
<th>NAMA</th>
<th>ID</th>
<th>ROLE</th>
<th>EMAIL</th>
<th>NO TELP</th>
<th>AKSI</th>
</tr>
</thead>

<tbody>

<?php if(mysqli_num_rows($query)>0): ?>
<?php while($row=mysqli_fetch_assoc($query)): ?>

<tr>
<td><?= htmlspecialchars($row['nama']) ?></td>
<td><?= $row['id_user'] ?></td>

<td>
<?php
$roleUser = $row['role'];

if($roleUser=='admin'){
    echo "<span class='badge role-admin'>Admin</span>";
}
elseif($roleUser=='guru'){
    echo "<span class='badge role-guru'>Guru</span>";
}
elseif($roleUser=='siswa'){
    echo "<span class='badge role-siswa'>Siswa</span>";
}
elseif($roleUser=='walikelas'){
    echo "<span class='badge role-walikelas'>Wali Kelas</span>";
}
?>
</td>

<td><?= htmlspecialchars($row['email']) ?></td>
<td><?= htmlspecialchars($row['no_telp']) ?></td>

<td>
<a href="updatepengguna.php?id=<?=$row['id_user']?>" class="text-primary me-2">
<i class="bi bi-pencil"></i>
</a>

<a href="hapuspengguna.php?id=<?=$row['id_user']?>" 
class="text-danger"
onclick="return confirm('Hapus user ini?')">
<i class="bi bi-trash"></i>
</a>
</td>

</tr>

<?php endwhile; ?>
<?php else: ?>

<tr>
<td colspan="6" class="text-center text-muted">
Tidak ada data
</td>
</tr>

<?php endif; ?>

</tbody>

</table>
</div>

<!-- PAGINATION -->
<div class="d-flex justify-content-between mt-3">

<div class="text-muted">
Menampilkan <?= $offset+1 ?> - <?= min($offset+$limit,$total) ?> dari <?= $total ?>
</div>

<ul class="pagination mb-0">
<?php for($i=1;$i<=$totalPages;$i++): ?>
<li class="page-item <?=($i==$page)?'active':''?>">
<a class="page-link" 
href="?page=<?=$i?>&search=<?=$search?>&role=<?=$role?>">
<?=$i?>
</a>
</li>
<?php endfor ?>
</ul>

</div>

</div>
</div>

</div>
</div>

<?php include "../templates/footer.php"; ?>