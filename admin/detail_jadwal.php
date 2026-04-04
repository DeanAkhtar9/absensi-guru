<?php
ob_start();
session_start();

require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('admin');
require "../config/database.php";

date_default_timezone_set('Asia/Jakarta');

$nama_kelas = $_GET['kelas'] ?? '';
if(!$nama_kelas) die("Kelas tidak ditemukan");

/* =========================
   AMBIL DATA KELAS
========================= */
$qKelas = mysqli_query($conn,"SELECT * FROM kelas WHERE nama_kelas='$nama_kelas'");
$kelas = mysqli_fetch_assoc($qKelas);
if(!$kelas) die("Data kelas tidak ditemukan");

$id_kelas = $kelas['id_kelas'];

/* =========================
   UPDATE WALI
========================= */
if(isset($_POST['update_wali'])){
    $id = intval($_POST['id_wali']);

    $cek = mysqli_query($conn,"
    SELECT * FROM kelas 
    WHERE id_walikelas='$id' AND id_kelas!='$id_kelas'
    ");

    if(mysqli_num_rows($cek)>0){
        $_SESSION['error']="❌ Guru sudah jadi wali di kelas lain!";
    }else{
        mysqli_query($conn,"
        UPDATE kelas SET id_walikelas='$id'
        WHERE id_kelas='$id_kelas'
        ");
        $_SESSION['success']="✅ Wali berhasil diupdate";
    }

    header("Location: ".$_SERVER['REQUEST_URI']); exit;
}

/* =========================
   UPDATE PENGURUS
========================= */
if(isset($_POST['update_pengurus'])){
    $id = intval($_POST['id_pengurus']);

    mysqli_query($conn,"DELETE FROM siswa WHERE id_kelas='$id_kelas'");
    mysqli_query($conn,"INSERT INTO siswa (id_user,id_kelas) VALUES ('$id','$id_kelas')");

    $_SESSION['success']="✅ Pengurus berhasil diupdate";
    header("Location: ".$_SERVER['REQUEST_URI']); exit;
}

/* =========================
   DATA WALI & PENGURUS
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

$nama_wali = $wali['nama'] ?? 'Tidak ada';
$nama_pengurus = $pengurus['nama'] ?? 'Tidak ada';


/* =========================
   AMBIL JADWAL (FIX)
========================= */

$rows = [];
$gid = 0;

$url_master = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=799813071&output=csv";

$master = @file_get_contents($url_master);

if($master){

    $rows_master = array_map("str_getcsv", explode("\n", $master));

    foreach ($rows_master as $row){

        if(count($row) < 2) continue;

        if(trim($row[0]) == $nama_kelas){
            $gid = trim($row[1]);
            break;
        }
    }

    if($gid){

        $url_sheet = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=$gid&output=csv";

        $csv = @file_get_contents($url_sheet);

        if($csv){
            $rows = array_map("str_getcsv", explode("\n", $csv));
        }
    }
}

/* =========================
   DATA GURU
========================= */
$guruList=[];
$q=mysqli_query($conn,"SELECT id_user,nama FROM users WHERE role='guru'");
while($g=mysqli_fetch_assoc($q)){
$guruList[$g['id_user']]=$g['nama'];
}


/* =========================
   TAMBAH JADWAL KE SPREADSHEET
========================= */
if(isset($_POST['tambah_jadwal'])){

    $id_user = $_POST['id_user'];
    $mapel = $_POST['mapel'];
    $hari = $_POST['hari'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];

    // VALIDASI
    if(!$id_user || !$mapel || !$hari || !$jam_mulai || !$jam_selesai){
        $_SESSION['error'] = "❌ Semua field wajib diisi!";
    }else{

        // URL WEB APP (GANTI DENGAN PUNYA KAMU)
        $url = "https://script.google.com/macros/s/AKfycbzwxlpXdx3nwnbuV07ZuIiJp_Y3zIQiI86BwvpzzjSj8VYLaAwTkwwySLpMvAxAmpMV/exec";

        $data = [
            "action" => "add",
            "gid" => $_POST['gid'],
            "id_guru" => $id_user,
            "mapel" => $mapel,
            "hari" => $hari,
            "jam_mulai" => $jam_mulai,
            "jam_selesai" => $jam_selesai
        ];

        $options = [
            "http" => [
                "header"  => "Content-Type: application/json",
                "method"  => "POST",
                "content" => json_encode($data),
            ]
        ];

        $context  = stream_context_create($options);
      $ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

// penting biar tidak error SSL di localhost
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$result = curl_exec($ch);

if($result === false){
    $_SESSION['error'] = "❌ CURL ERROR: " . curl_error($ch);
}

curl_close($ch);

        if(trim($result) == "OK"){
            $_SESSION['success'] = "✅ Jadwal berhasil ditambahkan";
        }elseif(trim($result) == "BENTROK"){
            $_SESSION['error'] = "❌ Jadwal bentrok dengan jadwal lain!";
        }else{
            $_SESSION['error'] = "❌ Gagal kirim ke spreadsheet";
        }
    }

    header("Location: ".$_SERVER['REQUEST_URI']); exit;
}


?>

<?php include "../templates/header.php"; ?>
<?php include "../sidebar.php"; ?>
<?php include "../header.php"; ?>

<div class="main-content p-4">

<h4 class="fw-bold mb-3">Detail Kelas <?= $nama_kelas ?></h4>

<?php if(isset($_SESSION['error'])): ?>
<div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<?php if(isset($_SESSION['success'])): ?>
<div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<div class="card p-3 mb-3">
<b>Wali:</b> <?= $nama_wali ?><br>
<b>Pengurus:</b> <?= $nama_pengurus ?>
</div>

<button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modalWali">Edit Wali</button>
<button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalPengurus">Edit Pengurus</button>
<button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalJadwal">
+ Tambah Jadwal
</button>
<hr>

<table class="table table-bordered">
<tr><th>No</th><th>Guru</th><th>Mapel</th><th>Hari</th><th>Jam</th></tr>

<?php
$no=1;
foreach($rows as $i=>$r){
if($i==0||count($r)<5) continue;
echo "<tr>
<td>".$no++."</td>
<td>".($guruList[$r[0]]??'-')."</td>
<td>$r[1]</td>
<td>$r[2]</td>
<td>$r[3] - $r[4]</td>
</tr>";
}
?>
</table>

</div>

<!-- MODAL WALI -->
<div class="modal fade" id="modalWali" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST">

<div class="modal-header">
<h5>Edit Wali</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<input type="text" id="searchWali" class="form-control" placeholder="Cari guru...">
<div id="resultWali" class="border mt-2"></div>
<input type="hidden" name="id_wali" id="idWali">
</div>

<div class="modal-footer">
<button type="submit" name="update_wali" class="btn btn-primary">Simpan</button>
</div>

</form>
</div>
</div>
</div>

<!-- MODAL PENGURUS -->
<div class="modal fade" id="modalPengurus" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST">

<div class="modal-header">
<h5>Edit Pengurus</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<input type="text" id="searchSiswa" class="form-control" placeholder="Cari siswa...">
<div id="resultSiswa" class="border mt-2"></div>
<input type="hidden" name="id_pengurus" id="idSiswa">
</div>

<div class="modal-footer">
<button type="submit" name="update_pengurus" class="btn btn-primary">Simpan</button>
</div>

</form>
</div>
</div>
</div>

<div class="modal fade" id="modalJadwal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">

<div class="modal-header">
<h5>Tambah Jadwal</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form method="POST">
<div class="modal-body">

<input type="text" id="searchGuru" class="form-control" placeholder="Cari guru...">
<div id="resultGuru" class="border mt-2"></div>
<input type="hidden" name="id_user" id="idGuru">
<input type="hidden" name="gid" value="<?= $gid ?>">
<input type="text" name="mapel" class="form-control mt-2" placeholder="Mapel">

<select name="hari" class="form-control mt-2">
<option>senin</option>
<option>selasa</option>
<option>rabu</option>
<option>kamis</option>
<option>jumat</option>
<option>sabtu</option>
</select>

<input type="time" name="jam_mulai" class="form-control mt-2">
<input type="time" name="jam_selesai" class="form-control mt-2">

</div>

<div class="modal-footer">
<button name="tambah_jadwal" class="btn btn-primary">Simpan</button>
</div>

</form>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function setup(input,result,hidden,role){
document.getElementById(input).addEventListener("keyup",function(){

fetch("search_user.php?q="+this.value+"&role="+role)
.then(res=>res.json())
.then(data=>{
let html="";
data.forEach(u=>{
html+=`<div style="cursor:pointer;padding:5px"
onclick="pilih('${u.id_user}','${u.nama}','${input}','${hidden}')">
${u.nama}
</div>`;
});
document.getElementById(result).innerHTML=html;
});

});
}

function pilih(id,nama,input,hidden){
document.getElementById(input).value=nama;
document.getElementById(hidden).value=id;
document.getElementById("resultWali").innerHTML="";
document.getElementById("resultSiswa").innerHTML="";
}

setup("searchWali","resultWali","idWali","guru");
setup("searchSiswa","resultSiswa","idSiswa","siswa");
</script>

<script>
    document.getElementById("searchGuru").addEventListener("keyup",function(){

fetch("search_guru.php?q="+this.value+"&role=guru")
.then(res=>res.json())
.then(data=>{
let html="";
data.forEach(u=>{
html+=`<div style="cursor:pointer;padding:5px"
onclick="pilihGuru('${u.id_user}','${u.nama}')">
${u.nama}
</div>`;
});
document.getElementById("resultGuru").innerHTML=html;
});

});

function pilihGuru(id,nama){
document.getElementById("searchGuru").value=nama;
document.getElementById("idGuru").value=id;
document.getElementById("resultGuru").innerHTML="";
}
</script>
<?php include "../templates/footer.php"; ?>