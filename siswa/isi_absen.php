<?php
session_start();
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('siswa');
require "../config/database.php";

$id_user = $_SESSION['id_user']; // ID Siswa yang mengabsen
$nama_siswa = $_SESSION['nama']; // Nama Siswa

$id_jadwal = $_GET['id'] ?? '';
$id_guru   = $_GET['guru'] ?? '';
$nama_kelas = $_GET['kelas'] ?? 'Kelas Anda'; // Tambahkan parameter kelas jika ada

if(!$id_jadwal || !$id_guru){
    die("Data tidak valid");
}

/* CEK SUDAH ABSEN */
$cek = mysqli_query($conn,"SELECT 1 FROM absensi_guru WHERE id_user='$id_guru' AND DATE(tanggal)=CURDATE()");
if(mysqli_num_rows($cek)>0){
    die("Sudah diabsen");
}

/* PROSES */
if($_SERVER['REQUEST_METHOD']=='POST'){
    $status = $_POST['status'];
    $ket    = $_POST['keterangan'] ?? '';

    // 1. Simpan ke Database
    $query = mysqli_query($conn,"
        INSERT INTO absensi_guru (id_jadwal, id_user, diinput_oleh, status, keterangan, tanggal)
        VALUES ('$id_jadwal', '$id_guru', '$id_user', '$status', '$ket', NOW())
    ");

    // ... Kode setelah query INSERT INTO absensi_guru berhasil ...
if($query){
        // 1. Notifikasi konfirmasi bahwa guru telah diabsen (opsional)
        $judul1 = "Konfirmasi Kehadiran";
        $pesan1 = "Siswa ($nama_siswa) telah mengisi absensi Anda dengan status: " . strtoupper($status);
        
        // Gunakan fungsi kirimNotifikasi jika sudah ada di database.php
        if(function_exists('kirimNotifikasi')){
            kirimNotifikasi($id_guru, $judul1, $pesan1);
        }

        // 2. Notifikasi KHUSUS untuk mengisi Jurnal (Hanya jika guru Hadir/Izin)
        if($status == 'hadir' || $status == 'izin'){
            $judul2 = "Tugas: Isi Jurnal Mengajar";
            $pesan2 = "Absensi sudah masuk. Jangan lupa untuk segera mengisi laporan jurnal mengajar Anda untuk kelas ini.";
            
            // Menggunakan query manual agar lebih pasti masuk ke database
            mysqli_query($conn, "INSERT INTO notifikasi (id_user, judul, pesan, is_read, created_at) 
                                VALUES ('$id_guru', '$judul2', '$pesan2', 0, NOW())");
        }

        $_SESSION['success'] = "Berhasil mengabsen guru.";
        header("Location: absen_guru.php");
        exit;
    }
}
?>

<?php include "../templates/header.php"; ?>

<div class="main-content p-4">

<div class="card p-4 shadow-sm">

<h5>Isi Absensi</h5>

<form method="POST">

<select name="status" class="form-control mb-2" required>
<option value="">Pilih Status</option>
<option value="hadir">Hadir</option>
<option value="izin">Izin</option>
<option value="tidak_hadir">Tidak Hadir</option>
</select>

<textarea name="keterangan" class="form-control mb-2" placeholder="Keterangan..."></textarea>

<button class="btn btn-primary w-100">Simpan</button>

</form>

</div>

</div>