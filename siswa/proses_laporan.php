<?php
session_start();

// 1. Load Keamanan dan Koneksi
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('siswa');
require "../config/database.php";

// 2. Ambil data session
$id_user_siswa = $_SESSION['id_user'] ?? 0;
$nama_siswa    = $_SESSION['nama'] ?? 'Siswa';

if ($id_user_siswa == 0) {
    die("Session login tidak ditemukan. Silakan login ulang.");
}

// 3. Ambil data dari Form
$id_jadwal        = isset($_POST['id_jadwal']) ? intval($_POST['id_jadwal']) : 0;
$tanggal          = $_POST['tanggal'] ?? '';
$status_kehadiran = trim($_POST['deskripsi'] ?? '');
$keterangan       = trim($_POST['keterangan'] ?? '');

// 4. Validasi Input Dasar
$allowed_status = ["Hadir", "Tidak Hadir", "Tidak Ada Keterangan"];

if ($id_jadwal == 0 || empty($tanggal) || empty($status_kehadiran)) {
    die("Data tidak lengkap!");
}

if (!in_array($status_kehadiran, $allowed_status)) {
    die("Status kehadiran tidak valid.");
}

if ($status_kehadiran == "Tidak Hadir" && empty($keterangan)) {
    die("Keterangan wajib diisi jika guru tidak hadir.");
}

/* ==========================================================
   STEP 1: CARI DATA SISWA & WALI KELASNYA
   ========================================================== */
$query_siswa = $conn->prepare("
    SELECT s.id_siswa, k.id_walikelas, k.nama_kelas 
    FROM siswa s 
    LEFT JOIN kelas k ON s.id_kelas = k.id_kelas 
    WHERE s.id_user = ?
");
$query_siswa->bind_param("i", $id_user_siswa);
$query_siswa->execute();
$res_siswa = $query_siswa->get_result();
$data_siswa = $res_siswa->fetch_assoc();

if (!$data_siswa) {
    die("Data siswa atau kelas tidak ditemukan.");
}

$id_siswa   = $data_siswa['id_siswa'];
$id_wali    = $data_siswa['id_walikelas']; // Ini adalah id_user sang guru wali
$nama_kelas = $data_siswa['nama_kelas'];
$query_siswa->close();

/* ==========================================================
   STEP 2: CEK DUPLIKAT LAPORAN
   ========================================================== */
$query_cek = $conn->prepare("SELECT id_komplain FROM komplain WHERE id_siswa = ? AND id_jadwal = ? AND tanggal = ?");
$query_cek->bind_param("iis", $id_siswa, $id_jadwal, $tanggal);
$query_cek->execute();
if ($query_cek->get_result()->num_rows > 0) {
    die("Anda sudah mengirim laporan untuk jadwal ini di tanggal tersebut.");
}
$query_cek->close();

/* ==========================================================
   STEP 3: SIMPAN KE TABEL KOMPLAIN
   ========================================================== */
$pesan_komplain = !empty($keterangan) ? $status_kehadiran . " - " . $keterangan : $status_kehadiran;

$query_ins = $conn->prepare("INSERT INTO komplain (id_siswa, id_jadwal, tanggal, pesan, created_at) VALUES (?, ?, ?, ?, NOW())");
$query_ins->bind_param("iiss", $id_siswa, $id_jadwal, $tanggal, $pesan_komplain);

if (!$query_ins->execute()) {
    die("Gagal menyimpan laporan: " . $query_ins->error);
}
$query_ins->close();

/* ==========================================================
   STEP 4: UPDATE / INSERT TABEL ABSENSI_GURU
   ========================================================== */
$status_enum = strtolower(str_replace(" ", "_", $status_kehadiran));

$query_abs = $conn->prepare("SELECT id_absensi_guru FROM absensi_guru WHERE id_jadwal = ? AND tanggal = ?");
$query_abs->bind_param("is", $id_jadwal, $tanggal);
$query_abs->execute();
$res_abs = $query_abs->get_result();

if ($res_abs->num_rows == 0) {
    // Belum ada, insert baru
    $ins_abs = $conn->prepare("INSERT INTO absensi_guru (id_jadwal, tanggal, status, diinput_oleh, created_at) VALUES (?, ?, ?, ?, NOW())");
    $ins_abs->bind_param("issi", $id_jadwal, $tanggal, $status_enum, $id_siswa);
    $ins_abs->execute();
    $ins_abs->close();
} else {
    // Sudah ada, update status
    $row_abs = $res_abs->fetch_assoc();
    $id_abs_guru = $row_abs['id_absensi_guru'];
    $upd_abs = $conn->prepare("UPDATE absensi_guru SET status = ?, diinput_oleh = ? WHERE id_absensi_guru = ?");
    $upd_abs->bind_param("sii", $status_enum, $id_siswa, $id_abs_guru);
    $upd_abs->execute();
    $upd_abs->close();
}
$query_abs->close();

/* ==========================================================
   STEP 5: KIRIM NOTIFIKASI KE WALI & ADMIN
   ========================================================== */
$judul_notif = "Laporan Laporan Kehadiran Guru";
$isi_notif   = "Siswa $nama_siswa ($nama_kelas) melaporkan kehadiran guru pada $tanggal: $status_kehadiran.";

// 1. Kirim ke Wali Kelas (jika kelas ada wali-nya)
if (!empty($id_wali)) {
    kirimNotifikasi($id_wali, $judul_notif, $isi_notif);
}

// 2. Kirim ke Semua Admin
$q_admin = mysqli_query($conn, "SELECT id_user FROM users WHERE role = 'admin'");
while ($adm = mysqli_fetch_assoc($q_admin)) {
    kirimNotifikasi($adm['id_user'], $judul_notif, $isi_notif);
}

/* ==========================================================
   STEP 5: KIRIM NOTIFIKASI (DIRECT INSERT & DEBUG)
   ========================================================== */
$judul_notif = "Laporan Kehadiran Guru";
$isi_notif   = "Siswa $nama_siswa ($nama_kelas) melaporkan kehadiran guru pada $tanggal: $status_kehadiran.";

// 1. Ambil Semua Admin (Cek role 'admin' atau 'Admin')
$q_admin = mysqli_query($conn, "SELECT id_user FROM users WHERE LOWER(role) = 'admin'");

if ($q_admin && mysqli_num_rows($q_admin) > 0) {
    while ($adm = mysqli_fetch_assoc($q_admin)) {
        $id_target_admin = $adm['id_user'];
        
        // GUNAKAN INSERT LANGSUNG (Tanpa Fungsi) untuk Tes
        $ins_notif = mysqli_query($conn, "
            INSERT INTO notifikasi (id_user, judul, pesan, is_read, created_at) 
            VALUES ('$id_target_admin', '$judul_notif', '$isi_notif', 0, NOW())
        ");

        if (!$ins_notif) {
            // Jika baris ini muncul, berarti ada masalah di Struktur Tabel Notifikasi
            die("Gagal Simpan ke Tabel Notifikasi: " . mysqli_error($conn));
        }
    }
}

// 2. Kirim ke Wali Kelas (Direct Insert)
if (!empty($id_wali)) {
    mysqli_query($conn, "
        INSERT INTO notifikasi (id_user, judul, pesan, is_read, created_at) 
        VALUES ('$id_wali', '$judul_notif', '$isi_notif', 0, NOW())
    ");
}
// 6. Tutup Koneksi & Redirect
$conn->close();
header("Location: riwayat_laporan.php?status=sukses");
exit;
?>