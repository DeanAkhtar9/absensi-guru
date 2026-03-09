<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('guru');

require "../config/database.php";

include "../templates/header.php";
include "../templates/navbar.php";
include "../header.php";
include "../sidebar.php";

/* SEARCH */
$cari = isset($_GET['cari']) ? $_GET['cari'] : '';

$where = "";

if($cari != ""){
    $where = "WHERE materi LIKE '%$cari%' 
              OR catatan LIKE '%$cari%'";
}

/* QUERY */
$query = mysqli_query($conn,"
SELECT *
FROM jurnal_mengajar
$where
ORDER BY created_at DESC
");

?>

<style>
    .wrapper{
    display:flex;
}

.sidebar{
    width:260px;
    min-height:100vh;
    position:fixed;
    left:0;
    top:0;
}

.main-content{
    margin-left:260px;
    width:100%;
    padding:30px;
    background:#f5f6fa;
    min-height:100vh;

h4{
    margin-top: -20px;
    margin-bottom: -5px;
}    
}
</style>

<div class="wrapper">

    <?php include "../sidebar.php"; ?>

    <div class="main-content">
        
<div class="container-fluid p-4">

<h4 class="mb-3"><b>Riwayat Jurnal</b></h4>
<p class="text-muted">Lihat jurnal mengajar yang sudah dibuat</p>

<form method="GET" class="row mb-3">

<div class="col-md-10">
<input type="text" name="cari" class="form-control"
placeholder="Cari materi atau catatan..."
value="<?= $cari ?>">
</div>

<div class="col-md-2">
<button class="btn btn-primary w-100">Cari</button>
</div>

</form>

<div class="card shadow-sm">
<div class="card-body">

<table class="table table-hover">

<thead class="table-light">
<tr>
<th>ID</th>
<th>Materi</th>
<th>Catatan</th>
<th>Tanggal Dibuat</th>
<th>Aksi</th>
</tr>
</thead>

<tbody>

<?php while($data = mysqli_fetch_assoc($query)) { ?>

<tr>

<td><?= $data['id_jurnal'] ?></td>

<td><?= $data['materi'] ?></td>

<td><?= $data['catatan'] ?></td>

<td>
<?= date("d M Y H:i", strtotime($data['created_at'])) ?>
</td>

<td>
<a href="edit-jurnal.php?id=<?= $data['id_jurnal'] ?>"
class="btn btn-sm btn-primary bi bi-pencil">
Edit
</a>
</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>
</div>

</div>
</div>
</div>

<?php
include "../templates/footer.php";
?>