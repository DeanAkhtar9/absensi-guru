<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('walikelas');

include "../templates/header.php";
include "../templates/navbar.php";

$id_walikelas = $_SESSION['id_user'];

/* kelas wali */
$kelas = mysqli_query($conn, "
    SELECT id_kelas, nama_kelas
    FROM kelas
    WHERE id_walikelas = '$id_walikelas'
");
$dataKelas = mysqli_fetch_assoc($kelas);
$id_kelas = $dataKelas['id_kelas'];
?>

<div class="container">
    <h3>Jurnal Mengajar</h3>
    <p>Kelas: <b><?= $dataKelas['nama_kelas'] ?></b></p>

<?php
$query = mysqli_query($conn, "
    SELECT 
        j.id_jurnal,
        ag.tanggal,
        u.nama AS nama_guru,
        m.nama_mapel,
        j.materi
    FROM jurnal_mengajar j
    JOIN absensi_guru ag ON j.id_absensi_guru = ag.id_absensi_guru
    JOIN jadwal_mengajar jm ON ag.id_jadwal = jm.id_jadwal
    JOIN users u ON jm.id_guru = u.id_user
    JOIN mapel m ON jm.id_mapel = m.id_mapel
    WHERE jm.id_kelas = '$id_kelas'
    ORDER BY ag.tanggal DESC
");

if (mysqli_num_rows($query) == 0):
?>
    <div class="alert alert-secondary">
        Belum ada jurnal.
    </div>
<?php endif; ?>

<?php while ($row = mysqli_fetch_assoc($query)): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h6>
                <?= date('d-m-Y', strtotime($row['tanggal'])) ?> |
                <?= $row['nama_mapel'] ?> |
                <?= $row['nama_guru'] ?>
            </h6>

            <p><b>Materi:</b><br><?= nl2br($row['materi']) ?></p>

            <p>
                <b>Siswa Tidak Hadir:</b>
                <ul>
                <?php
                $absen = mysqli_query($conn, "
                    SELECT s.nama
                    FROM absensi_siswa a
                    JOIN siswa s ON a.id_siswa = s.id_siswa
                    WHERE a.id_jurnal = '{$row['id_jurnal']}'
                      AND a.status != 'hadir'
                ");

                if (mysqli_num_rows($absen)==0) {
                    echo "<li class='text-muted'>Tidak ada</li>";
                }

                while ($s = mysqli_fetch_assoc($absen)) {
                    echo "<li>{$s['nama']}</li>";
                }
                ?>
                </ul>
            </p>
        </div>
    </div>
<?php endwhile; ?>

</div>

<?php include "../templates/footer.php"; ?>
