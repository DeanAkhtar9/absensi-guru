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
   DATABASE: DATA KELAS
========================= */
$qKelas = mysqli_query($conn,"SELECT * FROM kelas WHERE nama_kelas='$nama_kelas'");
$kelas = mysqli_fetch_assoc($qKelas);
if(!$kelas) die("Data kelas tidak ditemukan");
$id_kelas = $kelas['id_kelas'];

/* =========================
   LOGIKA UPDATE WALI & PENGURUS
========================= */
if(isset($_POST['update_wali'])){
    $id = intval($_POST['id_wali']);
    mysqli_query($conn,"UPDATE kelas SET id_walikelas='$id' WHERE id_kelas='$id_kelas'");
    $_SESSION['success']="✅ Wali kelas berhasil diperbarui";
    header("Location: ".$_SERVER['REQUEST_URI']); exit;
}

if(isset($_POST['update_pengurus'])){
    $id = intval($_POST['id_pengurus']);
    mysqli_query($conn,"DELETE FROM siswa WHERE id_kelas='$id_kelas'");
    mysqli_query($conn,"INSERT INTO siswa (id_user,id_kelas) VALUES ('$id','$id_kelas')");
    $_SESSION['success']="✅ Pengurus berhasil diperbarui";
    header("Location: ".$_SERVER['REQUEST_URI']); exit;
}

/* =========================
   SPREADSHEET: AMBIL SELURUH JADWAL (SYNC)
========================= */
$rows = [];
$gid = 0;
$t = time(); // Token unik agar Google tidak memberi data lama (Cache)

// 1. Ambil GID (ID Tab) berdasarkan nama kelas dari Master Sheet
$url_master = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=799813071&output=csv&cache_buster=$t";

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
}

// 2. Jika GID ditemukan, ambil SEMUA data dari tab tersebut
if($gid){
    $url_sheet = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=$gid&output=csv&cache_buster=$t";
    $csv = @file_get_contents($url_sheet);
    if($csv){
        // Mengubah string CSV menjadi array multidimensi
        $rows = array_map("str_getcsv", explode("\n", $csv));
    }
}

/* =========================
   API: TAMBAH JADWAL
========================= */
if(isset($_POST['tambah_jadwal'])){
    $url_api = "https://script.google.com/macros/s/AKfycbzdLKhyFr2ud5qr_hCk5inhOwnVp8YJti7UHD7iC1lnOb2N_L6uVtcICx7BiHEftFhD/exec";
    $data = [
        "action" => "add",
        "gid" => $_POST['gid'],
        "id_guru" => $_POST['id_user'],
        "mapel" => $_POST['mapel'],
        "hari" => $_POST['hari'],
        "jam_mulai" => $_POST['jam_mulai'],
        "jam_selesai" => $_POST['jam_selesai']
    ];

    $ch = curl_init($url_api);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $res = curl_exec($ch);
    if(trim($res) == "OK") $_SESSION['success'] = "✅ Jadwal berhasil ditambahkan!";
    else $_SESSION['error'] = "❌ Gagal ke Sheets: " . $res;
    curl_close($ch);

    header("Location: ".$_SERVER['REQUEST_URI']); exit;
}

// Persiapan List Guru untuk tampilan tabel
$guruList=[];
$qG = mysqli_query($conn,"SELECT id_user,nama FROM users WHERE role='guru'");
while($g=mysqli_fetch_assoc($qG)) $guruList[$g['id_user']]=$g['nama'];

$wali = mysqli_fetch_assoc(mysqli_query($conn,"SELECT nama FROM users WHERE id_user='".($kelas['id_walikelas'] ?? 0)."'"));
$pengurus = mysqli_fetch_assoc(mysqli_query($conn,"SELECT u.nama FROM siswa s JOIN users u ON s.id_user=u.id_user WHERE s.id_kelas='$id_kelas' LIMIT 1"));

include "../templates/header.php";
include "../sidebar.php";
include "../header.php";
?>

<div class="main-content p-4">
    <h4 class="fw-bold">Manajemen Kelas: <?= $nama_kelas ?></h4>
    <hr>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <div class="card p-3 mb-3 shadow-sm border-0 bg-light">
        <div class="row align-items-center">
            <div class="col-md-5">
                <small class="text-muted">Wali Kelas:</small>
                <div class="fw-bold text-primary"><?= $wali['nama'] ?? 'Belum ditentukan' ?></div>
            </div>
            <div class="col-md-5">
                <small class="text-muted">Pengurus (Siswa):</small>
                <div class="fw-bold text-success"><?= $pengurus['nama'] ?? 'Belum ditentukan' ?></div>
            </div>
            <div class="col-md-2 text-end">
                <div class="dropdown">
                    <button class="btn btn-dark btn-sm dropdown-toggle" data-bs-toggle="dropdown">Aksi Kelas</button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalWali">Ubah Wali</a></li>
                        <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#modalPengurus">Ubah Pengurus</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 text-secondary"><i class="bi bi-calendar3 me-2"></i>Jadwal Pelajaran</h5>
        <button class="btn btn-success btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalJadwal">
            <i class="bi bi-plus-circle me-1"></i> Tambah Jadwal
        </button>
    </div>

    <div class="table-responsive shadow-sm rounded">
        <table class="table table-hover table-bordered bg-white mb-0">
            <thead class="table-dark">
                <tr>
                    <th width="50">No</th>
                    <th>Guru Pengajar</th>
                    <th>Mata Pelajaran</th>
                    <th>Hari</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no=1;
                // Loop SEMUA data dari array $rows (hasil fetch spreadsheet)
                foreach($rows as $index => $kolom){
                    // Lewati baris pertama (Header di Spreadsheet)
                    if($index == 0) continue;
                    
                    // Lewati jika baris kosong atau tidak lengkap
                    if(count($kolom) < 5 || empty($kolom[0])) continue;
                    
                    echo "<tr>
                        <td>".$no++."</td>
                        <td>".($guruList[$kolom[0]] ?? $kolom[0])."</td>
                        <td><span class='fw-semibold'>$kolom[1]</span></td>
                        <td>".ucfirst($kolom[2])."</td>
                        <td><span class='badge bg-info text-dark'>$kolom[3] - $kolom[4]</span></td>
                    </tr>";
                }
                
                if($no == 1): ?>
                    <tr>
                        <td colspan="5" class="text-center p-5 text-muted">
                            <i class="bi bi-info-circle d-block mb-2 fs-4"></i>
                            Tidak ada jadwal yang ditemukan di Spreadsheet untuk kelas ini.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalWali" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header"><h5>Atur Wali Kelas</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <label class="small text-muted mb-1">Cari Guru (Hanya yang belum memegang kelas):</label>
                    <input type="text" id="inWali" class="form-control" placeholder="Ketik nama guru..." autocomplete="off">
                    <div id="outWali" class="list-group mt-1 shadow-sm"></div>
                    <input type="hidden" name="id_wali" id="idWali">
                </div>
                <div class="modal-footer"><button type="submit" name="update_wali" class="btn btn-primary">Simpan</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPengurus" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header"><h5>Atur Pengurus Kelas</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <label class="small text-muted mb-1">Cari Siswa (Hanya yang belum masuk kelas):</label>
                    <input type="text" id="inSiswa" class="form-control" placeholder="Ketik nama siswa..." autocomplete="off">
                    <div id="outSiswa" class="list-group mt-1 shadow-sm"></div>
                    <input type="hidden" name="id_pengurus" id="idSiswa">
                </div>
                <div class="modal-footer"><button type="submit" name="update_pengurus" class="btn btn-primary">Simpan</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalJadwal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form method="POST">
                <div class="modal-header bg-success text-white"><h5>Tambah Jadwal Baru</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <label class="small fw-bold">Guru Pengajar:</label>
                    <input type="text" id="inGuru" class="form-control" placeholder="Cari nama guru..." autocomplete="off">
                    <div id="outGuru" class="list-group mt-1 shadow-sm" style="max-height:150px; overflow-y:auto;"></div>
                    <input type="hidden" name="id_user" id="idGuru">
                    <input type="hidden" name="gid" value="<?= $gid ?>">

                    <label class="small fw-bold mt-3">Mata Pelajaran:</label>
                    <input type="text" name="mapel" class="form-control" required placeholder="Misal: Fisika">

                    <label class="small fw-bold mt-3">Hari:</label>
                    <select name="hari" class="form-control">
                        <option value="Senin">Senin</option><option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option><option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option><option value="Sabtu">Sabtu</option>
                    </select>

                    <div class="row mt-3">
                        <div class="col"><label class="small fw-bold">Jam Mulai:</label><input type="time" name="jam_mulai" class="form-control" required></div>
                        <div class="col"><label class="small fw-bold">Jam Selesai:</label><input type="time" name="jam_selesai" class="form-control" required></div>
                    </div>
                </div>
                <div class="modal-footer bg-light"><button name="tambah_jadwal" class="btn btn-success w-100 py-2 fw-bold">Kirim ke Spreadsheet</button></div>
            </form>
        </div>
    </div>
</div>

<script>
// FUNGSI SEARCH UNIVERSAL (Ketik 1 Huruf)
function setupSearch(inputId, outputId, hiddenId, role, extra = "") {
    const el = document.getElementById(inputId);
    const res = document.getElementById(outputId);

    el.addEventListener("keyup", function() {
        let keyword = this.value;
        if (keyword.length >= 1) { 
            fetch(`search_user.php?q=${keyword}&role=${role}${extra}`)
                .then(r => r.json())
                .then(data => {
                    let items = "";
                    data.forEach(u => {
                        items += `<button type="button" class="list-group-item list-group-item-action" 
                                 onclick="pilih('${u.id_user}','${u.nama.replace(/'/g, "\\'")}','${inputId}','${hiddenId}','${outputId}')">
                                 ${u.nama}</button>`;
                    });
                    res.innerHTML = items || '<div class="list-group-item small text-muted">Tidak tersedia</div>';
                });
        } else { res.innerHTML = ""; }
    });
}

function pilih(id, nama, inId, hidId, outId) {
    document.getElementById(inId).value = nama;
    document.getElementById(hidId).value = id;
    document.getElementById(outId).innerHTML = "";
}

document.addEventListener("DOMContentLoaded", function() {
    setupSearch("inWali", "outWali", "idWali", "guru");
    setupSearch("inSiswa", "outSiswa", "idSiswa", "siswa");
    setupSearch("inGuru", "outGuru", "idGuru", "guru", "&type=jadwal");
});
</script>

<?php include "../templates/footer.php"; ?>