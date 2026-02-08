<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('walikelas');

require "../config/database.php";

$id_user = $_SESSION['id_user'];

/*
  Ambil kelas yang diwalikan oleh user ini
*/
$kelas = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT k.id_kelas, k.nama_kelas
    FROM kelas k
    JOIN walikelas w ON k.id_kelas = w.id_kelas
    WHERE w.id_user = '$id_user'
"));

if (!$kelas) {
    die("Kelas wali tidak ditemukan.");
}

$id_kelas = $kelas['id_kelas'];

/*
  Ambil absensi + jurnal guru di kelas ini
*/
$data = mysqli_query($conn, "
    SELECT 
        a.tanggal,
        g.nama_guru,
        m.nama_mapel,
        a.status_hadir,
        j.judul_materi
    FROM absensi_guru a
    JOIN guru g ON a.id_guru = g.id_guru
    JOIN mapel m ON a.id_mapel = m.id_mapel
    LEFT JOIN jurnal_guru j ON a.id_absensi = j.id_absensi
    WHERE a.id_kelas = '$id_kelas'
    ORDER BY a.tanggal DESC
");

include "../templates/header.php";
include "../templates/navbar.php";
?>

<div class="container">
    <h3>Monitoring Absensi Guru</h3>
    <p>Kelas: <b><?= $kelas['nama_kelas']; ?></b></p>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Tanggal</th>
                <th>Guru</th>
                <th>Mapel</th>
                <th>Status</th>
                <th>Jurnal</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($data)) { ?>
                <tr>
                    <td><?= $row['tanggal']; ?></td>
                    <td><?= $row['nama_guru']; ?></td>
                    <td><?= $row['nama_mapel']; ?></td>
                    <td>
                        <?= $row['status_hadir'] == 'hadir'
                            ? '<span class="badge bg-success">Hadir</span>'
                            : '<span class="badge bg-danger">Tidak Hadir</span>'; ?>
                    </td>
                    <td><?= $row['judul_materi'] ?? '<i>Belum diisi</i>'; ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php include "../templates/footer.php"; ?>
