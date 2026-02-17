<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('siswa');
require "../config/database.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/* =========================
   AMBIL DATA SESSION
========================= */
$id_user = $_SESSION['id_user'] ?? 0;

if ($id_user == 0) {
    die("Session login tidak ditemukan. Silakan login ulang.");
}

/* =========================
   AMBIL DATA FORM
========================= */
$id_jadwal = isset($_POST['id_jadwal']) ? intval($_POST['id_jadwal']) : 0;
$tanggal   = $_POST['tanggal'] ?? '';
$status_kehadiran = trim($_POST['deskripsi'] ?? '');
$keterangan       = trim($_POST['keterangan'] ?? '');


/* =========================
   VALIDASI FORM
========================= */
$allowed_status = ["Hadir", "Tidak Hadir", "Tidak Ada Keterangan"];

if ($id_jadwal == 0 || empty($tanggal) || empty($status_kehadiran)) {
    die("Data tidak lengkap!");
}

if (!in_array($status_kehadiran, $allowed_status)) {
    die("Status tidak valid.");
}

/* Jika Tidak Hadir wajib isi keterangan */
if ($status_kehadiran == "Tidak Hadir" && empty($keterangan)) {
    die("Jika guru tidak hadir, keterangan wajib diisi (izin atau tanpa pemberitahuan).");
}


/* =========================
   CEK DATA SISWA
========================= */
$stmt = $conn->prepare("SELECT id_siswa FROM siswa WHERE id_user = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Data siswa tidak ditemukan untuk akun ini.");
}

$data = $result->fetch_assoc();
$id_siswa = $data['id_siswa'];
$stmt->close();

/* =========================
   CEK DUPLIKAT LAPORAN
   (1 siswa, 1 jadwal, 1 hari)
========================= */
$stmt = $conn->prepare("
    SELECT id_komplain FROM komplain
    WHERE id_siswa = ? AND id_jadwal = ? AND tanggal = ?
");
$stmt->bind_param("iis", $id_siswa, $id_jadwal, $tanggal);
$stmt->execute();
$cek = $stmt->get_result();

if ($cek->num_rows > 0) {
    die("Anda sudah mengirim laporan untuk jadwal ini di tanggal tersebut.");
}
$stmt->close();


/* =========================
   INSERT KE TABEL KOMPLAIN
========================= */
/* Gabungkan status dan keterangan */
if (!empty($keterangan)) {
    $pesan = $status_kehadiran . " - " . $keterangan;
} else {
    $pesan = $status_kehadiran;
}

$stmt = $conn->prepare("
    INSERT INTO komplain (id_siswa, id_jadwal, tanggal, pesan, status, created_at)
    VALUES (?, ?, ?, ?, 'menunggu', NOW())
");

$stmt->bind_param("iiss", $id_siswa, $id_jadwal, $tanggal, $pesan);

if ($stmt->execute()) {
    header("Location: riwayat_laporan.php?status=sukses");
    exit;
} else {
    die("Gagal menyimpan laporan: " . $stmt->error);
}

$stmt->close();
$conn->close();
?>