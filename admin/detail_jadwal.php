<?php
ob_start();
session_start();

require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('admin');

require "../config/database.php";

date_default_timezone_set('Asia/Jakarta');

/* =========================
   VALIDASI
========================= */
$nama_kelas = $_GET['kelas'] ?? '';
if(!$nama_kelas) die("Kelas tidak ditemukan");

/* =========================
   DATA KELAS
========================= */
$kelas = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT * FROM kelas WHERE nama_kelas='$nama_kelas'
"));
$id_kelas = $kelas['id_kelas'] ?? 0;

/* =========================
   UPDATE WALI
========================= */
if(isset($_POST['update_wali'])){
    $id = $_POST['id_wali'];

    mysqli_query($conn,"
        UPDATE kelas 
        SET id_walikelas=".($id ? "'$id'" : "NULL")."
        WHERE id_kelas='$id_kelas'
    ");

    header("Location: ".$_SERVER['REQUEST_URI']);
    exit;
}

/* =========================
   UPDATE PENGURUS
========================= */
if(isset($_POST['update_pengurus'])){
    $id = $_POST['id_pengurus'];

    mysqli_query($conn,"UPDATE siswa SET id_kelas=NULL WHERE id_kelas='$id_kelas'");

    if($id){
        mysqli_query($conn,"
            UPDATE siswa SET id_kelas='$id_kelas'
            WHERE id_user='$id'
        ");
    }

    header("Location: ".$_SERVER['REQUEST_URI']);
    exit;
}

/* =========================
   AMBIL NAMA
========================= */
$wali = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT nama FROM users WHERE id_user='".($kelas['id_walikelas'] ?? 0)."'
"));

$pengurus = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT u.nama FROM siswa s
JOIN users u ON s.id_user=u.id_user
WHERE s.id_kelas='$id_kelas'
LIMIT 1
"));

$nama_wali = $wali['nama'] ?? 'Belum ada';
$nama_pengurus = $pengurus['nama'] ?? 'Belum ada';

/* =========================
   AMBIL GID
========================= */
$gid = 0;

$url_master = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=799813071&output=csv";

$data = file_get_contents($url_master);
$rows_master = array_map("str_getcsv", explode("\n", $data));

foreach($rows_master as $r){
    if(count($r)<2) continue;
    if(trim($r[0]) == $nama_kelas){
        $gid = trim($r[1]);
        break;
    }
}

/* =========================
   JADWAL
========================= */
$rows = [];

if($gid){
    $url = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=$gid&output=csv";
    $csv = file_get_contents($url);
    $rows = array_map("str_getcsv", explode("\n", $csv));
}

/* =========================
   DATA GURU
========================= */
$guruList=[];
$q=mysqli_query($conn,"SELECT id_user,nama FROM users");
while($g=mysqli_fetch_assoc($q)){
    $guruList[$g['id_user']]=$g['nama'];
}

/* =========================
   INCLUDE UI
========================= */
include "../templates/header.php";
include "../templates/navbar.php";
include "../sidebar.php";
include "../header.php";
?>

<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<script src="../assets/js/bootstrap.bundle.min.js"></script>

<style>
.card:hover{
    transform: translateY(-2px);
    transition: 0.2s;
}
</style>

<div class="main-content p-4">

<!-- HEADER -->
<div class="d-flex justify-content-between mb-4">
<h5 class="fw-bold">Detail Kelas <?= htmlspecialchars($nama_kelas) ?></h5>
<a href="jadwal2.php" class="btn btn-secondary btn-sm">← Kembali</a>
</div>

<!-- INFO CARD -->
<div class="card mb-3 p-3 shadow-sm">

<div class="d-flex justify-content-between align-items-start">

    <!-- KIRI -->
    <div>

        <div class="mb-2">
            <i class="bi bi-person-badge text-warning"></i>
            <span class="text-muted"> Wali:</span>
            <span class="fw-semibold">
                <?= $nama_wali == 'Belum ada' 
                ? '<span class="text-danger">Tidak ada</span>' 
                : htmlspecialchars($nama_wali) ?>
            </span>
        </div>

        <div>
            <i class="bi bi-people text-info"></i>
            <span class="text-muted"> Pengurus:</span>
            <span class="fw-semibold">
                <?= $nama_pengurus == 'Belum ada' 
                ? '<span class="text-danger">Tidak ada</span>' 
                : htmlspecialchars($nama_pengurus) ?>
            </span>
        </div>

    </div>

    <!-- KANAN -->
    <div class="d-flex gap-2">

        <button class="btn btn-outline-warning btn-sm"
        data-bs-toggle="modal" data-bs-target="#modalWali">
        <i class="bi bi-pencil-square"></i> Wali
        </button>

        <button class="btn btn-outline-info btn-sm"
        data-bs-toggle="modal" data-bs-target="#modalPengurus">
        <i class="bi bi-person-plus"></i> Pengurus
        </button>

        <a href="https://docs.google.com/spreadsheets/d/1OUnML_ulkjy46R1bJn_lpbCKhOncgvh3AYM5KDAdsu4/edit#gid=<?= $gid ?>" 
        target="_blank"
        class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle"></i> Jadwal
        </a>

    </div>

</div>

<hr class="my-2">

</div>

<!-- TABLE -->
<div class="card shadow-sm">
<div class="card-body">

<table class="table table-bordered table-hover">
<tr>
<th>No</th><th>Guru</th><th>Mapel</th><th>Hari</th><th>Jam</th>
</tr>

<?php
$no=1;
foreach($rows as $i=>$r){
if($i==0||count($r)<5)continue;
echo "<tr>
<td>".$no++."</td>
<td>".($guruList[intval($r[0])]??'-')."</td>
<td>$r[1]</td>
<td>$r[2]</td>
<td>$r[3] - $r[4]</td>
</tr>";
}
?>

</table>

</div>
</div>

</div>

<!-- MODAL + SEARCH -->
<div class="modal fade" id="modalWali" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">

<form method="POST">

<div class="modal-header">
<h6 class="modal-title">Update Wali Kelas</h6>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<input type="text" id="searchWali" class="form-control" placeholder="Cari wali...">
<div id="resultWali" class="list-group mt-2"></div>
<input type="hidden" name="id_wali" id="idWali">
</div>

<div class="modal-footer">
<button type="submit" name="update_wali" class="btn btn-primary">Simpan</button>
</div>

</form>

</div>
</div>
</div>


<div class="modal fade" id="modalPengurus" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">

<form method="POST">

<div class="modal-header">
<h6 class="modal-title">Update Pengurus</h6>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<input type="text" id="searchSiswa" class="form-control" placeholder="Cari siswa...">
<div id="resultSiswa" class="list-group mt-2"></div>
<input type="hidden" name="id_pengurus" id="idSiswa">
</div>

<div class="modal-footer">
<button type="submit" name="update_pengurus" class="btn btn-primary">Simpan</button>
</div>

</form>

</div>
</div>
</div>

<script>
function setupSearch(inputId,resultId,hiddenId,role){

let timeout=null;

document.getElementById(inputId).addEventListener("keyup",function(){

clearTimeout(timeout);
let keyword=this.value;

timeout=setTimeout(()=>{

if(keyword.length<1){
document.getElementById(resultId).innerHTML="";
return;
}

fetch("search_user.php?q="+keyword+"&role="+role)
.then(res=>res.json())
.then(data=>{

let html="";
data.forEach(u=>{
html+=`<a href="#" class="list-group-item list-group-item-action"
onclick="selectUser('${u.id_user}','${u.nama}','${inputId}','${hiddenId}','${resultId}')">
${u.nama}
</a>`;
});

document.getElementById(resultId).innerHTML=html;

});

},300);

});
}

function selectUser(id,nama,inputId,hiddenId,resultId){
document.getElementById(inputId).value=nama;
document.getElementById(hiddenId).value=id;
document.getElementById(resultId).innerHTML="";
}

setupSearch("searchWali","resultWali","idWali","walikelas");
setupSearch("searchSiswa","resultSiswa","idSiswa","siswa");
</script>

<?php include "../templates/footer.php"; ?>