<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('guru');

include "../templates/header.php";
include "../templates/navbar.php";
include "../sidebar.php";

$id_guru = $_SESSION['id_user'];

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
$jam_sekarang = date('H:i:s');

/* ambil jadwal aktif */
$query = mysqli_query($conn, "
    SELECT 
        jm.id_jadwal,
        k.nama_kelas,
        m.nama_mapel,
        jm.jam_mulai,
        jm.jam_selesai
    FROM jadwal_mengajar jm
    JOIN kelas k ON jm.id_kelas = k.id_kelas
    JOIN mapel m ON jm.id_mapel = m.id_mapel
    WHERE jm.id_guru = '$id_guru'
      AND jm.hari = '$hari_ini'
      AND '$jam_sekarang' BETWEEN jm.jam_mulai AND jm.jam_selesai
");

?>

<div class="container">
    <h3>Jadwal Mengajar Saat Ini</h3>

    <p>
        Hari: <b><?= $hari_ini ?></b> <br>
        Jam sekarang: <b><?= date('H:i') ?></b>
    </p>

    <?php if (mysqli_num_rows($query) == 0): ?>
        <div class="alert alert-secondary">
            Saat ini kamu <b>tidak sedang mengajar</b>.
        </div>

    <?php else: ?>
        <div class="row">
            <?php while ($row = mysqli_fetch_assoc($query)): ?>
                <div class="col-md-4">
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <h5><?= $row['nama_mapel'] ?></h5>
                            <p class="mb-1">
                                Kelas: <b><?= $row['nama_kelas'] ?></b>
                            </p>
                            <p class="text-muted">
                                <?= substr($row['jam_mulai'],0,5) ?>
                                -
                                <?= substr($row['jam_selesai'],0,5) ?>
                            </p>

                            <a href="jurnal.php?id_jadwal=<?= $row['id_jadwal'] ?>"
                               class="btn btn-primary btn-sm">
                                Isi Jurnal
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<?php include "../templates/footer.php"; ?>
