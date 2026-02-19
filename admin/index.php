<?php
require "../config/config.php";  
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('admin');

require "../config/database.php";
require "../config/functions.php";

include "../templates/header.php";
include "../sidebar.php";
include "../header.php";    

/* =========================
   QUERY KOMPLAIN
========================= */
$query_komplain = mysqli_query($conn, "
    SELECT 
        komplain.*, 
        siswa.nama_siswa AS nama_siswa
    FROM komplain
    JOIN siswa ON komplain.id_siswa = siswa.id_siswa
    ORDER BY komplain.created_at DESC
    LIMIT 3
");

/* =========================
   QUERY JURNAL
========================= */
$query_jurnal = mysqli_query($conn, "
    SELECT 
        jurnal_mengajar.id_jurnal,
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

<link rel="alternate" href="atom.xml" type="application/atom+xml" title="Atom">

<!-- =========================
     MAIN CONTENT
========================= -->
<div class="main-content">

        <div class="dashboard-wrapper">

        <!-- =========================
            STAT CARDS
        ========================= -->
        <div class="stat-cards">

            <!-- Guru -->
            <div class="card">
                <div class="card-header">
                    <h5>Total Guru</h5>
                    <div class="icon-box-guru">
                        <i class="orang bi bi-person"></i>
                    </div>
                </div>

                <div class="card-total">
                    <h3><?= getUserByRole("guru", $conn); ?></h3>
                </div>
            </div>


            <!-- Jurnal -->
            <div class="card">
                <div class="card-header">
                    <h5>Pengumpulan Jurnal</h5>
                    <div class="icon-box-kelas">
                        <i class="buku bi bi-book"></i>
                    </div>
                </div>

                <div class="card-total">
                    <h3><?= getTotal("jurnal_mengajar", $conn); ?></h3>
                </div>
            </div>


            <!-- Siswa -->
            <div class="card">
                <div class="card-header">
                    <h5>Total Siswa</h5>
                    <div class="icon-box-siswa">
                        <i class="orang2 bi bi-people"></i>
                    </div>
                </div>

                <div class="card-total">
                    <h3><?= getTotal("siswa", $conn); ?></h3>
                </div>
            </div>


            <!-- Komplain -->
            <div class="card">
                <div class="card-header">
                    <h5>Laporan</h5>
                    <div class="icon-box-laporan">
                        <i class="bel bi bi-bell"></i>
                    </div>
                </div>

                <div class="card-total">
                    <h3><?= getTotal("komplain", $conn); ?></h3>
                </div>
            </div>

        </div>



        <!-- =========================
            KOMPLAIN + JURNAL
        ========================= -->
        <div class="dashboard-row">


        <!-- =========================
            KOMPLAIN
        ========================= -->
        <div class="card dashboard-card">

            <h3>Recent Complaints</h3>
            <p class="sub-title">Komplain terbaru siswa</p>

            <?php while($row = mysqli_fetch_assoc($query_komplain)) { ?>

                <div class="list-item">

                    <div>

                        <strong><?= $row['nama_siswa'] ?></strong>

                        <p><?= $row['pesan'] ?></p>

                        <small>
                            <?= date('d M Y', strtotime($row['created_at'])) ?>
                        </small>

                    </div>

                    <span class="badge pending">
                        pending
                    </span>

                </div>

            <?php } ?>

        </div>



        <!-- =========================
            JURNAL
        ========================= -->
        <div class="card dashboard-card">

            <h3>Recent Journal Submissions</h3>
            <p class="sub-title">Jurnal terbaru guru</p>

            <?php while($jurnal = mysqli_fetch_assoc($query_jurnal)) { ?>

                <div class="journal-item">

                    <div>

                        <div class="journal-nama">
                            <?= $jurnal['nama_guru'] ?>
                        </div>

                        <div class="journal-info">
                            <?= $jurnal['mapel'] ?> - <?= $jurnal['nama_kelas'] ?>
                        </div>

                        <div class="journal-date">
                            <?= date('d M Y', strtotime($jurnal['created_at'])) ?>
                        </div>

                    </div>

                    <span class="badge submitted">
                        submitted
                    </span>

                </div>

                <hr class="journal-divider">

            <?php } ?>

        </div>


</body>
</html>

<?php require "../templates/footer.php"; ?>
