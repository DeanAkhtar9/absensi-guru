<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('walikelas');

include "../templates/header.php";
include "../templates/navbar.php";
?>

<div class="container">
    <h3>Dashboard Wali Kelas</h3>
    <p>Monitoring absensi guru dan jurnal kelas.</p>

    <div class="alert alert-warning">
        Kamu hanya <?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('walikelas');

include "../templates/header.php";
include "../templates/navbar.php";

$id_walikelas = $_SESSION['id_user'];

/* ambil kelas yang diwalikan */
$kelas = mysqli_query($conn, "
    SELECT id_kelas, nama_kelas
    FROM kelas
    WHERE id_walikelas = '$id_walikelas'
");
$dataKelas = mysqli_fetch_assoc($kelas);
$id_kelas = $dataKelas['id_kelas'];
?>

<div class="container">
    <h3>Rekap Absensi Guru</h3>
    <p>Kelas: <b><?= $dataKelas['nama_kelas'] ?></b></p>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Guru</th>
                <th>Mapel</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>

<?php
$query = mysqli_query($conn, "
    SELECT 
        ag.tanggal,
        u.nama AS nama_guru,
        m.nama_mapel,
        ag.status
    FROM absensi_guru ag
    JOIN jadwal_mengajar jm ON ag.id_jadwal = jm.id_jadwal
    JOIN users u ON jm.id_guru = u.id_user
    JOIN mapel m ON jm.id_mapel = m.id_mapel
    WHERE jm.id_kelas = '$id_kelas'
    ORDER BY ag.tanggal DESC
");

if (mysqli_num_rows($query) == 0):
?>
        <tr>
            <td colspan="4" class="text-center text-muted">
                Belum ada data absensi.
            </td>
        </tr>
<?php endif; ?>

<?php while ($row = mysqli_fetch_assoc($query)): ?>
        <tr>
            <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
            <td><?= $row['nama_guru'] ?></td>
            <td><?= $row['nama_mapel'] ?></td>
            <td>
                <span class="badge bg-<?= 
                    $row['status']=='hadir' ? 'success' : 'danger' ?>">
                    <?= ucfirst(str_replace('_',' ',$row['status'])) ?>
                </span>
            </td>
        </tr>
<?php endwhile; ?>

        </tbody>
    </table>

    <a href="jurnal.php" class="btn btn-primary">
        Lihat Jurnal Mengajar
    </a>
</div>

<?php include "../templates/footer.php"; ?>
bisa melihat data kelas yang kamu walikan.
    </div>
</div>

<?php include "../templates/footer.php"; ?>
