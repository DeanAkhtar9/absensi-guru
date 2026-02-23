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
        komplain.id_jadwal,
        komplain.tanggal,
        komplain.pesan,
        komplain.created_at
    FROM komplain
    JOIN siswa ON komplain.id_siswa = siswa.id_siswa
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
                        <?= htmlspecialchars($row['Kelas']); ?>
                    </td>
                    
                    <td>
                        <?= htmlspecialchars($row['nama_guru']); ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($row['status']); ?>
                    </td>
                    
                    <td>
                        <?= htmlspecialchars($row['status']); ?>
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
