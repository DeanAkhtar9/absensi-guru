<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('admin');

require "../config/database.php";

$success = "";
$error = "";

/* =========================
   AMBIL WALIKELAS (YANG BELUM PUNYA KELAS)
========================= */
$wali = mysqli_query($conn, "
    SELECT u.id_user, u.nama
    FROM users u
    LEFT JOIN kelas k ON u.id_user = k.id_walikelas
    WHERE u.role='walikelas'
    AND k.id_walikelas IS NULL
");

/* =========================
   AMBIL SISWA (YANG BELUM PUNYA KELAS)
========================= */
$siswa = mysqli_query($conn, "
    SELECT u.id_user, u.nama
    FROM users u
    LEFT JOIN siswa s ON u.id_user = s.id_user
    WHERE u.role='siswa' 
    AND s.id_user IS NULL
");

/* =========================
   GOOGLE SCRIPT URL
========================= */
$googleScriptURL = "https://script.google.com/macros/s/AKfycbyStNpBWHRa4GzKouqcfoZuV-QH7wGSA_kECTNtEGnzKomCyhixDMQul4RXLCx-FIqV/exec";

/* =========================
   PROSES SIMPAN
========================= */
if(isset($_POST['simpan'])){

    $nama_kelas   = $_POST['nama_kelas'];
    $id_walikelas = $_POST['id_walikelas'];
    $id_pengurus  = $_POST['id_pengurus'];

    if(!$nama_kelas || !$id_walikelas || !$id_pengurus){
        $error = "Semua field wajib diisi!";
    } else {

        // =========================
        // VALIDASI WALIKELAS
        // =========================
        $cekWali = mysqli_query($conn,"
            SELECT * FROM kelas WHERE id_walikelas='$id_walikelas'
        ");

        if(mysqli_num_rows($cekWali) > 0){
            $error = "Wali kelas sudah digunakan!";
        } else {

            // =========================
            // INSERT KELAS
            // =========================
            $insertKelas = mysqli_query($conn,"
                INSERT INTO kelas (nama_kelas, id_walikelas)
                VALUES ('$nama_kelas','$id_walikelas')
            ");

            if($insertKelas){

                $id_kelas = mysqli_insert_id($conn);

                // =========================
                // VALIDASI SISWA
                // =========================
                $cek = mysqli_query($conn,"
                    SELECT * FROM siswa WHERE id_user='$id_pengurus'
                ");

                if(mysqli_num_rows($cek) > 0){

                    // rollback
                    mysqli_query($conn,"DELETE FROM kelas WHERE id_kelas='$id_kelas'");

                    $error = "Siswa sudah memiliki kelas!";

                } else {

                    // =========================
                    // INSERT KE TABEL SISWA
                    // =========================
                    mysqli_query($conn,"
                        INSERT INTO siswa (id_user, id_kelas)
                        VALUES ('$id_pengurus','$id_kelas')
                    ");

                    // =========================
                    // KIRIM KE GOOGLE SHEET
                    // =========================
                    $getWali = mysqli_fetch_assoc(mysqli_query($conn,"
                        SELECT nama FROM users WHERE id_user='$id_walikelas'
                    "));

                    $nama_wali = $getWali['nama'] ?? '-';

                    $data = json_encode([
                        "nama_kelas" => $nama_kelas,
                        "wali_kelas" => $nama_wali
                    ]);

                    $options = [
                        'http' => [
                            'header'  => "Content-type: application/json",
                            'method'  => 'POST',
                            'content' => $data,
                        ]
                    ];

                    $context  = stream_context_create($options);

                    file_get_contents($googleScriptURL, false, $context);

                    $success = "Kelas & pengurus berhasil ditambahkan!";
                    $_POST = [];
                }

            } else {
                $error = "Gagal menyimpan kelas!";
            }
        }
    }
}

/* =========================
   TEMPLATE
========================= */
include "../templates/header.php";
include "../sidebar.php";
include "../header.php";
?>

<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/bootstrap.min.css">

<style>
.main-content { padding:30px; }

.card-form{
    background:#fff;
    padding:25px;
    border-radius:12px;
    box-shadow:0 4px 15px rgba(0,0,0,0.05);
    max-width:500px;
}

.alert{
    border-radius:8px;
    cursor:pointer;
}
</style>

<div class="main-content">

<h4 class="mb-4">Tambah Kelas</h4>

<div class="card-form">

<!-- ALERT -->
<?php if($success): ?>
<div class="alert alert-success" id="alertBox"><?= $success ?></div>
<?php endif; ?>

<?php if($error): ?>
<div class="alert alert-danger" id="alertBox"><?= $error ?></div>
<?php endif; ?>

<form method="POST">

<!-- NAMA KELAS -->
<input 
    class="form-control mb-2" 
    name="nama_kelas"
    placeholder="Nama Kelas (contoh: X RPL 1)"
    value="<?= $_POST['nama_kelas'] ?? '' ?>"
>

<!-- WALIKELAS -->
<select class="form-control mb-2" name="id_walikelas">
<option value="">Pilih Wali Kelas</option>

<?php if(mysqli_num_rows($wali) == 0): ?>
<option value="">Semua wali kelas sudah terpakai</option>
<?php else: ?>
<?php while($w=mysqli_fetch_assoc($wali)): ?>
<option value="<?= $w['id_user'] ?>">
<?= $w['nama'] ?>
</option>
<?php endwhile; ?>
<?php endif; ?>

</select>

<!-- PENGURUS -->
<select class="form-control mb-2" name="id_pengurus">

<option value="">Pilih Pengurus (Siswa)</option>

<?php if(mysqli_num_rows($siswa) == 0): ?>
<option value="">Semua siswa sudah punya kelas</option>
<?php else: ?>
<?php while($s=mysqli_fetch_assoc($siswa)): ?>
<option value="<?= $s['id_user'] ?>">
<?= $s['nama'] ?>
</option>
<?php endwhile; ?>
<?php endif; ?>

</select>

<button name="simpan" class="btn btn-primary">Simpan</button>

</form>

</div>

</div>

<script>
// auto hilang 3 detik
setTimeout(()=>{
    const a = document.getElementById('alertBox');
    if(a) a.style.display='none';
},3000);

// klik hilang
document.querySelectorAll('.alert').forEach(el=>{
    el.onclick = () => el.style.display='none';
});
</script>

<?php include "../templates/footer.php"; ?>