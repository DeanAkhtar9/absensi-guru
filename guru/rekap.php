<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('guru');

include "../templates/header.php";
include "../templates/navbar.php";
include "../sidebar.php";

$id_guru = $_SESSION['id_user'];

/* AMBIL REKAP */
$query = mysqli_query($conn, "
    SELECT 
        ag.id_absensi_guru,
        ag.tanggal,
        k.nama_kelas,
        jm.mapel,
        ag.status AS status_guru,
        j.materi,
        COUNT(CASE WHEN asw.status != 'hadir' THEN 1 END) AS tidak_hadir
    FROM absensi_guru ag
    JOIN jadwal_mengajar jm ON ag.id_jadwal = jm.id_jadwal
    JOIN kelas k ON jm.id_kelas = k.id_kelas
    LEFT JOIN jurnal_mengajar j ON ag.id_absensi_guru = j.id_absensi_guru
    LEFT JOIN absensi_siswa asw ON j.id_jurnal = asw.id_jurnal
    WHERE jm.id_guru = '$id_guru'
    GROUP BY 
        ag.id_absensi_guru,
        ag.tanggal,
        k.nama_kelas,
        jm.mapel,
        ag.status,
        j.materi
    ORDER BY ag.tanggal DESC
");

if (!$query) {
    die("Query Error: " . mysqli_error($conn));
}

?>

<div class="container">
    <h3>Rekap Mengajar Saya</h3>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kelas</th>
                <th>Mapel</th>
                <th>Status Guru</th>
                <th>Materi</th>
                <th>Siswa Tidak Hadir</th>
            </tr>
        </thead>
        <tbody>
        <?php if (mysqli_num_rows($query) == 0): ?>
            <tr>
                <td colspan="6" class="text-center text-muted">
                    Belum ada data rekap.
                </td>
            </tr>
        <?php endif; ?>

        <?php while ($row = mysqli_fetch_assoc($query)): ?>
            <tr>
                <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                <td><?= $row['nama_kelas'] ?></td>
                <td><?= $row['mapel'] ?></td>
                <td>
                    <span class="badge bg-<?= 
                        $row['status_guru'] == 'hadir' ? 'success' : 'danger' ?>">
                        <?= ucfirst(str_replace('_',' ',$row['status_guru'])) ?>
                    </span>
                </td>
                <td><?= $row['materi'] ?: '-' ?></td>
                <td class="text-center"><?= $row['tidak_hadir'] ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include "../templates/footer.php"; ?>
