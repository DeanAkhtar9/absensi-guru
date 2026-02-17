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
   CEK SESSION LOGIN
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
   AMBIL RIWAYAT LAPORAN KEHADIRAN
========================= */
$stmt = $conn->prepare("
    SELECT k.tanggal, k.pesan, u.nama, jm.mapel
    FROM komplain k
    JOIN jadwal_mengajar jm ON k.id_jadwal = jm.id_jadwal
    JOIN users u ON jm.id_guru = u.id_user
    WHERE k.id_siswa = ?
    ORDER BY k.tanggal DESC
");
$stmt->bind_param("i", $id_siswa);
$stmt->execute();
$query = $stmt->get_result();
?>

<div class="container mt-4">
    <h3>Riwayat Laporan Kehadiran Guru</h3>

    <table class="table table-bordered table-striped">
        <tr>
            <th>Tanggal</th>
            <th>Guru</th>
            <th>Mapel</th>
            <th>Kehadiran Guru</th>
        </tr>

        <?php if ($query->num_rows > 0): ?>
            <?php while ($row = $query->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['tanggal']) ?></td>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td><?= htmlspecialchars($row['mapel']) ?></td>
                    <td>
                        <?= nl2br(htmlspecialchars($row['pesan'])) ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" class="text-center">Belum ada laporan.</td>
            </tr>
        <?php endif; ?>
    </table>
</div>

<?php include "../templates/footer.php"; ?>