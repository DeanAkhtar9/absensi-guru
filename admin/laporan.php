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
        siswa.nama_siswa
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
        <p class="sub-title">Daftar komplain dari siswa</p>
    </div>

    <!-- TABLE CARD -->

    <div class="laporan-container">

        <div class="laporan-card">

            <table class="laporan-table">

                <tr>
                    <th>No</th>
                    <th>Nama Siswa</th>
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
                        <?= htmlspecialchars($row['nama_siswa']); ?>
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
