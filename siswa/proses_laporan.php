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
$pesan = !empty($keterangan) ? $status_kehadiran . " - " . $keterangan : $status_kehadiran;

$stmt = $conn->prepare("
    INSERT INTO komplain (id_siswa, id_jadwal, tanggal, pesan, created_at)
    VALUES (?, ?, ?, ?, NOW())
");
$stmt->bind_param("iiss", $id_siswa, $id_jadwal, $tanggal, $pesan);
if (!$stmt->execute()) {
    die("Gagal menyimpan laporan: " . $stmt->error);
}
$stmt->close();

/* =========================
   UPDATE / INSERT ABSENSI GURU OTOMATIS
========================= */
/* Konversi status ke enum absensi_guru */
$status_enum = strtolower(str_replace(" ", "_", $status_kehadiran));

$stmt = $conn->prepare("
    SELECT id_absensi_guru FROM absensi_guru
    WHERE id_jadwal = ? AND tanggal = ?
");
$stmt->bind_param("is", $id_jadwal, $tanggal);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    /* BELUM ADA, INSERT BARU */
    $stmt2 = $conn->prepare("
        INSERT INTO absensi_guru (id_jadwal, tanggal, status, diinput_oleh, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $diinput_oleh = $id_siswa; // siapa yang input
    $stmt2->bind_param("issi", $id_jadwal, $tanggal, $status_enum, $diinput_oleh);
    $stmt2->execute();
    $stmt2->close();
} else {
    /* SUDAH ADA, UPDATE STATUS */
    $row = $result->fetch_assoc();
    $id_absensi_guru = $row['id_absensi_guru'];

    $stmt2 = $conn->prepare("
        UPDATE absensi_guru
        SET status = ?, diinput_oleh = ?
        WHERE id_absensi_guru = ?
    ");
    $stmt2->bind_param("sii", $status_enum, $id_siswa, $id_absensi_guru);
    $stmt2->execute();
    $stmt2->close();
}
$stmt->close();
$conn->close();

/* =========================
   REDIRECT
========================= */
header("Location: riwayat_laporan.php?status=sukses");
exit;
?>
