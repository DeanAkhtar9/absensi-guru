<?php
require "../config/config.php";  
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('admin');

require "../config/database.php";
require "../config/functions.php";

include "../templates/header.php";
include "../templates/navbar.php";
include "../sidebar.php";
include "../header.php";    

$query_komplain = mysqli_query($conn, "
    SELECT 
        komplain.*, 
        siswa.id_siswa AS id_siswa
    FROM komplain
    JOIN siswa ON komplain.id_siswa = siswa.id_siswa
    ORDER BY komplain.created_at DESC
    LIMIT 3
");

$query_jurnal = mysqli_query($conn, "
    SELECT 
        jurnal_mengajar.id_jurnal,
        jurnal_mengajar.materi,
        jurnal_mengajar.created_at,

        users.nama AS nama_guru,
        kelas.nama_kelas,
        jadwal_mengajar.mapel

    FROM jurnal_mengajar

    JOIN absensi_guru 
        ON jurnal_mengajar.id_absensi_guru = absensi_guru.id_absensi_guru

    JOIN jadwal_mengajar 
        ON absensi_guru.id_jadwal = jadwal_mengajar.id_jadwal

    JOIN users 
        ON jadwal_mengajar.id_guru = users.id_user

    JOIN kelas
        ON jadwal_mengajar.id_kelas = kelas.id_kelas

    ORDER BY jurnal_mengajar.created_at DESC
    LIMIT 3
");


?>



<div class="container">
    <div class="container dashboard-wrapper">
    <div class="dashboard-header">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    </div>

    <!-- STATISTICS CARDS -->
    <div class="stat-cards">

        <div class="card">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Total Guru</h5>
                <div class="icon-box-guru">
                    <i class="orang bi bi-person"></i>
                </div>
            </div>
            <div class="card-total">
                <h3><?= getUserByRole("guru", $conn); ?></h3>
            </div>
        </div>

        <div class="card">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Pengumpulan Jurnal</h5>
                <div class="icon-box-kelas">
                    <i class="buku bi bi-book"></i>
                </div>
            </div>
            <div class="card-total">
                <h3><?= getTotal("kelas", $conn); ?></h3>
            </div>
        </div>

        <div class="card">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Total Siswa</h5>
                <div class="icon-box-siswa">
                    <i class="orang2 bi bi-people"></i>
                </div>
            </div>
                <div class="card-total">
                    <h3><?= getTotal("siswa", $conn); ?></h3>
                </div>
        </div>
        
        <div class="card">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Laporan</h5>
            <div class="icon-box-laporan">
                <i class="bel bi bi-bell"></i>
            </div>
        </div>
            <div class="card-total">
                <h3><?= getTotal("komplain", $conn); ?></h3>
            </div>
        </div>

    </div>


 <div class="dashboard-row">

    <!-- COMPLAINT -->
    <div class="card dashboard-card">
        <h3>Recent Complaints</h3>
        <p class="sub-title">Komplain terbaru siswa</p>

        <?php while($row = mysqli_fetch_assoc($query_komplain)) { ?>
            
            <div class="list-item">

                <div>
                    <strong><?= $row['id_siswa'] ?></strong>
                    <p><?= $row['pesan'] ?></p>
                    <small><?= date('d M Y', strtotime($row['tanggal'])) ?></small>
                </div>

                <span class="badge pending">pending</span>

            </div>

        <?php } ?>

    </div>


    <!-- JOURNAL -->
    <div class="card dashboard-card">
        <h3>Recent Journal Submissions</h3>
        <p class="sub-title">Jurnal terbaru guru</p>

        <?php while($jurnal = mysqli_fetch_assoc($query_jurnal)) { ?>

        <div class="journal-item">

                <div class="journal-left">

                    <div class="journal-nama">
                        <?= $jurnal['nama_guru'] ?>
                    </div>

                    <div class="journal-info">
                        <?= $jurnal['mapel'] ?> - <?= $jurnal['nama_kelas'] ?>
                    </div>

                    <div class="journal-date">
                        <?= date('Y-m-d', strtotime($jurnal['created_at'])) ?>
                    </div>

                </div>

                <div class="journal-right">
                    <span class="badge submitted">submitted</span>
                </div>

            </div>

            <hr class="journal-divider">

        <?php } ?>

    </div>


</div>

</div>

<?php require "../templates/footer.php"; ?>
