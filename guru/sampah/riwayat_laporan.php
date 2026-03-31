<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('guru');

require "../config/database.php";

include "../templates/header.php";
include "../templates/navbar.php";
include "../sidebar.php";

$id_guru = $_SESSION['id_user'];

/* ==============================
   Ambil semua laporan terkait guru ini
============================== */
$query = mysqli_query($conn, "
    SELECT 
        kmp.id_komplain,
        u_siswa.nama AS nama_siswa,
        jm.mapel,
        kl.nama_kelas,
        jm.hari,
        jm.jam_mulai,
        jm.jam_selesai,
        kmp.tanggal,
        kmp.pesan,
        u_guru.nama AS nama_guru
    FROM komplain kmp
    JOIN siswa s ON kmp.id_siswa = s.id_siswa
    JOIN users u_siswa ON s.id_user = u_siswa.id_user
    JOIN jadwal_mengajar jm ON kmp.id_jadwal = jm.id_jadwal
    JOIN kelas kl ON jm.id_kelas = kl.id_kelas
    JOIN users u_guru ON jm.id_guru = u_guru.id_user
    WHERE jm.id_guru = '$id_guru'
    ORDER BY kmp.tanggal DESC, jm.jam_mulai
");

?>

<div class="container mt-4">
    <h3>Riwayat Laporan Guru</h3>

    <?php if (mysqli_num_rows($query) == 0): ?>
        <div class="alert alert-info">
            Belum ada laporan dari siswa untuk Anda.
        </div>
    <?php else: ?>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Tanggal Laporan</th>
                    <th>Siswa</th>
                    <th>Hari / Jam</th>
                    <th>Mata Pelajaran</th>
                    <th>Kelas</th>
                    <th>Pesan / Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($query)): ?>
                <tr>
                    <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                    <td><?= htmlspecialchars($row['nama_siswa']) ?></td>
                    <td><?= $row['hari'] ?> / <?= substr($row['jam_mulai'],0,5) ?> - <?= substr($row['jam_selesai'],0,5) ?></td>
                    <td><?= htmlspecialchars($row['mapel']) ?></td>
                    <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
                    <td><?= htmlspecialchars($row['pesan']) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include "../templates/footer.php"; ?>
