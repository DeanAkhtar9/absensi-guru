<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('siswa');

include "../templates/header.php";
include "../templates/navbar.php";

/* mapping hari */
$hariMap = [
    'Sunday'    => 'Minggu',
    'Monday'    => 'Senin',
    'Tuesday'   => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday'  => 'Kamis',
    'Friday'    => 'Jumat',
    'Saturday'  => 'Sabtu'
];

$hari_ini = $hariMap[date('l')];
$tanggal  = date('Y-m-d');
?>

<div class="container">
    <h3>Absensi Guru</h3>
    <p>Hari: <b><?= $hari_ini ?></b></p>

    <table class="table table-bordered">
        <tr>
            <th>Guru</th>
            <th>Mapel</th>
            <th>Kelas</th>
            <th>Jam</th>
            <th>Status</th>
        </tr>

<?php
$query = mysqli_query($conn, "
    SELECT 
        jm.id_jadwal,
        u.nama AS nama_guru,
        m.nama_mapel,
        k.nama_kelas,
        jm.jam_mulai,
        jm.jam_selesai,
        ag.id_absensi_guru
    FROM jadwal_mengajar jm
    JOIN users u ON jm.id_guru = u.id_user
    JOIN mapel m ON jm.id_mapel = m.id_mapel
    JOIN kelas k ON jm.id_kelas = k.id_kelas
    LEFT JOIN absensi_guru ag 
        ON jm.id_jadwal = ag.id_jadwal
        AND ag.tanggal = '$tanggal'
    WHERE jm.hari = '$hari_ini'
    ORDER BY jm.jam_mulai
");

if (mysqli_num_rows($query) == 0):
?>
        <tr>
            <td colspan="5" class="text-center text-muted">
                Tidak ada jadwal hari ini.
            </td>
        </tr>
<?php
endif;

while ($row = mysqli_fetch_assoc($query)):
?>
        <tr>
            <td><?= $row['nama_guru'] ?></td>
            <td><?= $row['nama_mapel'] ?></td>
            <td><?= $row['nama_kelas'] ?></td>
            <td><?= substr($row['jam_mulai'],0,5) ?> - <?= substr($row['jam_selesai'],0,5) ?></td>
            <td>
                <?php if ($row['id_absensi_guru']): ?>
                    <span class="badge bg-success">Sudah Diisi</span>
                <?php else: ?>
                    <form method="post" action="proses_absensi.php" class="d-flex">
                        <input type="hidden" name="id_jadwal" value="<?= $row['id_jadwal'] ?>">
                        <select name="status" class="form-select form-select-sm me-2" required>
                            <option value="">-- Pilih --</option>
                            <option value="hadir">Hadir</option>
                            <option value="izin">Izin</option>
                            <option value="tidak_hadir">Tidak Hadir</option>
                        </select>
                        <button class="btn btn-sm btn-primary">Simpan</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
<?php endwhile; ?>
    </table>
</div>

<?php include "../templates/footer.php"; ?>
