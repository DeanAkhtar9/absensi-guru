jadwal2:
<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('admin');

date_default_timezone_set('Asia/Jakarta');

include "../templates/header.php";
include "../sidebar.php";
include "../header.php";

/* LIST SHEET (kelas) */
$kelasList = [
    "Sheet1",
    "Sheet2",
    "Sheet3"


];

/* Search */
$search = isset($_GET['search']) ? strtolower($_GET['search']) : "";
?>

<div class="main-content">
<div class="container py-4">

<h3 class="mb-4">Daftar Jadwal Per Kelas</h3>

<form method="GET" class="mb-4">
<div class="row">
<div class="col-md-4">
<input type="text" name="search" class="form-control"
placeholder="Cari nama kelas..."
value="<?= htmlspecialchars($search) ?>">
</div>

<div class="col-md-2">
<button class="btn btn-primary w-100">Search</button>
</div>
</div>
</form>

<div class="row">

<?php
foreach ($kelasList as $nama_kelas) {

    if ($search && strpos(strtolower($nama_kelas), $search) === false) {
        continue;
    }
?>

<div class="col-md-4">
<div class="card shadow-sm mb-4">
<div class="card-body text-center">

<h5 class="mb-3"><?= htmlspecialchars($nama_kelas) ?></h5>

<a href="detail_jadwal.php?kelas=<?= urlencode($nama_kelas) ?>"
class="btn btn-success w-100">
Lihat Jadwal
</a>

</div>
</div>
</div>

<?php } ?>

</div>
</div>
</div>

<?php include "../templates/footer.php"; ?>