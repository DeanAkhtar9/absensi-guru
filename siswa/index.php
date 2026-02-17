<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('siswa');
require "../config/database.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include "../templates/header.php";
include "../templates/navbar.php";
include "../sidebar.php";

/* =========================
   CEK SESSION
========================= */
$id_user = $_SESSION['id_user'] ?? 0;

if ($id_user == 0) {
    die("Session tidak ditemukan. Silakan login ulang.");
}

/* =========================
   AMBIL id_siswa
========================= */
$stmt = $conn->prepare("SELECT id_siswa FROM siswa WHERE id_user = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Data siswa tidak ditemukan.");
}

$dataSiswa = $result->fetch_assoc();
$id_siswa = $dataSiswa['id_siswa'];
$stmt->close();

/* =========================
   STATISTIK KEHADIRAN
========================= */
function hitung($conn, $id_siswa, $status) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM komplain WHERE id_siswa=? AND pesan=?");
    $stmt->bind_param("is", $id_siswa, $status);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
    return $res;
}

$total = $conn->query("SELECT COUNT(*) as total FROM komplain WHERE id_siswa='$id_siswa'")
              ->fetch_assoc()['total'];

$hadir        = hitung($conn, $id_siswa, "Hadir");
$tidak_hadir  = hitung($conn, $id_siswa, "Tidak Hadir");
$tanpa_ket    = hitung($conn, $id_siswa, "Tidak Ada Keterangan");

/* =========================
   5 LAPORAN TERBARU
========================= */
$stmt = $conn->prepare("
    SELECT k.tanggal, k.pesan, u.nama, jm.mapel
    FROM komplain k
    JOIN jadwal_mengajar jm ON k.id_jadwal = jm.id_jadwal
    JOIN users u ON jm.id_guru = u.id_user
    WHERE k.id_siswa = ?
    ORDER BY k.created_at DESC
    LIMIT 5
");
$stmt->bind_param("i", $id_siswa);
$stmt->execute();
$terbaru = $stmt->get_result();
?>

<div class="container">
<h3>Dashboard Kehadiran Guru</h3>

<div class="row my-3">

<div class="col-md-3">
<div class="card p-3 text-center">
<h6>Total Laporan</h6>
<h3><?= $total ?></h3>
</div>
</div>

<div class="col-md-3">
<div class="card p-3 text-center">
<h6>Hadir</h6>
<h3 class="text-success"><?= $hadir ?></h3>
</div>
</div>

<div class="col-md-3">
<div class="card p-3 text-center">
<h6>Tidak Hadir</h6>
<h3 class="text-danger"><?= $tidak_hadir ?></h3>
</div>
</div>

<div class="col-md-3">
<div class="card p-3 text-center">
<h6>Tanpa Keterangan</h6>
<h3 class="text-warning"><?= $tanpa_ket ?></h3>
</div>
</div>

</div>

<h5>5 Laporan Terbaru</h5>
<table class="table table-bordered">
<tr>
<th>Tanggal</th>
<th>Guru</th>
<th>Mapel</th>
<th>Kehadiran</th>
</tr>

<?php while($row = $terbaru->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($row['tanggal']) ?></td>
<td><?= htmlspecialchars($row['nama']) ?></td>
<td><?= htmlspecialchars($row['mapel']) ?></td>
<td><strong><?= htmlspecialchars($row['pesan']) ?></strong></td>
</tr>
<?php endwhile; ?>
</table>

<div class="alert alert-info mt-4">
<b>Panduan:</b><br>
1. Isi sesuai kejadian sebenarnya.<br>
2. Gunakan bahasa yang sopan.<br>
3. Laporan palsu dapat dikenakan sanksi sekolah.
</div>

</div>

<?php include "../templates/footer.php"; ?>