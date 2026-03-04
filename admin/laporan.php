<?php
require "../config/config.php";
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('admin');

require "../config/database.php";


/*
|--------------------------------------------------------------------------
| Ambil data komplain + nama siswa
|--------------------------------------------------------------------------
*/
$query = mysqli_query($conn, "
    SELECT 
        komplain.id_komplain,
        komplain.id_siswa,
        komplain.pesan,
        komplain.tanggal,
        komplain.created_at,

        pelapor.nama AS nama_siswa,
        kelas.nama_kelas,
        guru.nama AS nama_guru,

        absensi_guru.status AS status_absensi

    FROM komplain

    -- Pelapor (Siswa)
    JOIN users AS pelapor
        ON komplain.id_siswa = pelapor.id_user

    -- Jadwal
    JOIN jadwal_mengajar 
        ON komplain.id_jadwal = jadwal_mengajar.id_jadwal

    -- Guru
    JOIN users AS guru
        ON jadwal_mengajar.id_guru = guru.id_user

    -- Kelas
    JOIN kelas
        ON jadwal_mengajar.id_kelas = kelas.id_kelas

    -- Absensi guru
    LEFT JOIN absensi_guru
        ON absensi_guru.id_jadwal = komplain.id_jadwal
        AND DATE(absensi_guru.tanggal) = DATE(komplain.tanggal)

    ORDER BY komplain.created_at DESC
");




include "../templates/header.php";
include "../sidebar.php";
include "../header.php";
?>

<div class="main-content">

    <!-- TITLE -->
    <div class="page-header">
        <h2>Laporan Komplain</h2>
    </div>

    <!-- TABLE CARD -->

    <div class="laporan-container">
    
        <p class="sub-title">Daftar komplain dari siswa</p>

            <table class="laporan-table">

                <tr>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Tanggal</th>
                    <th>Nama Guru</th>
                    <th>Status</th>
                    <th>Pesan</th>
                    <th>Aksi</th>
                    <th></th>
                </tr>
<tbody>
<?php 
$no = 1;
while($row = mysqli_fetch_assoc($query)) { 
?>

<tr>
    <td><?= $no++; ?></td>

<<<<<<< HEAD
    <td><?= htmlspecialchars($row['id_siswa']); ?></td>
=======
                <tr>
                    <td>
                        {nama siswa(pelapor)}
                    </td>
                    <td>
                        <?= htmlspecialchars($row['nama_kelas']); ?>
                    </td>
                    <td>
                        <?= date('d M Y', strtotime($row['tanggal'])); ?>
                    </td>
                    
                    <td>
                        <?= htmlspecialchars($row['nama_guru']); ?>
                    </td>
>>>>>>> 92388105e177605293f7682f5bdd01c53d988929

<td><?= htmlspecialchars($row['nama_siswa']); ?></td>

<<<<<<< HEAD
<td><?= htmlspecialchars($row['nama_kelas']); ?></td>
=======
        if($status == 'Hadir') {
            echo "<span class='badge bg-success'>Hadir</span>";
        } elseif($status == 'Izin') {
            echo "<span class='badge bg-warning'>Izin</span>";
        } elseif($status == 'Alpha') {
            echo "<span class='badge bg-danger'>Alpha</span>";
        } else {
            echo "<span class='badge bg-secondary'>Belum Absen</span>";
        }
        ?>
                    </td>
                    <td>
                        
                    </td>
                    <td>
                        {aksi(dropdown)}
                    </td>
                    <td>
                       <button class="update"><a class="update">Update</a></button>
                    </td>
                </tr>
>>>>>>> 92388105e177605293f7682f5bdd01c53d988929

<td><?= date('d M Y', strtotime($row['tanggal'])); ?></td>

<td><?= htmlspecialchars($row['nama_guru']); ?></td>

<td>
<?php
$status = $row['status_absensi'] ?? 'Belum Absen';

if($status == 'Hadir') {
    echo "<span class='badge bg-success'>Hadir</span>";
} elseif($status == 'Izin') {
    echo "<span class='badge bg-warning text-dark'>Izin</span>";
} elseif($status == 'Alpha') {
    echo "<span class='badge bg-danger'>Alpha</span>";
} else {
    echo "<span class='badge bg-secondary'>Belum Absen</span>";
}
?>
</td>

<td><?= htmlspecialchars($row['pesan']); ?></td>


    <td>
        <div class="dropdown">
            <button class="btn btn-sm btn-primary dropdown-toggle" 
                    type="button" 
                    data-bs-toggle="dropdown">
                Aksi
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a class="dropdown-item" 
                       href="detail_komplain.php?id=<?= $row['id_komplain']; ?>">
                       Lihat Detail
                    </a>
                </li>
                <li>
                    <a class="dropdown-item text-success" 
                       href="proses_komplain.php?id=<?= $row['id_komplain']; ?>&aksi=selesai">
                       Tandai Selesai
                    </a>
                </li>
                <li>
                    <a class="dropdown-item text-danger" 
                       href="hapus_komplain.php?id=<?= $row['id_komplain']; ?>"
                       onclick="return confirm('Yakin ingin menghapus?')">
                       Hapus
                    </a>
                </li>
            </ul>
        </div>
    </td>
</tr>

<?php } ?>
</tbody>

            </table>

        </div>

    </div>

</div>

<?php include "../templates/footer.php"; ?>
