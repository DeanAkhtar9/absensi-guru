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
        komplain.pesan,
        komplain.tanggal,
        komplain.created_at,

        kelas.nama_kelas,
        users.nama AS nama_guru,

        absensi_guru.status AS status_absensi

    FROM komplain

    JOIN jadwal_mengajar 
        ON komplain.id_jadwal = jadwal_mengajar.id_jadwal

    JOIN users 
        ON jadwal_mengajar.id_guru = users.id_user

    JOIN kelas
        ON jadwal_mengajar.id_kelas = kelas.id_kelas

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
        <div class="laporan-card">

            <table class="laporan-table">

                <tr>
                    <th>No</th>
                    <th>Kelas</th>
                    <th>Nama Guru</th>
                    <th>Status</th>
                    <th>Pesan</th>
                    <th>Tanggal</th>
                    <th>Dibuat</th>
                </tr>
<tbody>

                <?php 
                $no = 1;
                while($row = mysqli_fetch_assoc($query)) { 
                ?>

                <tr>
                    <td><?= $no++; ?></td>

                    <td>
                        <?= htmlspecialchars($row['nama_kelas']); ?>
                    </td>
                    
                    <td>
                        <?= htmlspecialchars($row['nama_guru']); ?>
                    </td>
                    <td>
        <?php
        $status = $row['status_absensi'] ?? 'Belum Absen';

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
                        <?= htmlspecialchars($row['pesan']); ?>
                    </td>

                    <td>
                        <?= date('d M Y', strtotime($row['tanggal'])); ?>
                    </td>

                    <td>
                        <?= date('d M Y H:i', strtotime($row['created_at'])); ?>
                    </td>
                </tr>

                <?php } ?>

            </tbody>
            </table>

        </div>

    </div>

</div>

<?php include "../templates/footer.php"; ?>
