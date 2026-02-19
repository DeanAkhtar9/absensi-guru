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

/* ambil seluruh jadwal hari ini */
$query = mysqli_query($conn, "
    SELECT 
        jm.id_jadwal,
        k.nama_kelas,
        jm.mapel,
        jm.jam_mulai,
        jm.jam_selesai
    FROM jadwal_mengajar jm
    JOIN kelas k ON jm.id_kelas = k.id_kelas
    WHERE jm.id_guru = '$id_guru'
      AND jm.hari = '$hari_ini'
    ORDER BY jm.jam_mulai
");

if (!$query) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<div class="container">
    <h3>Jadwal Mengajar Hari Ini</h3>

    <p>
        Hari: <b><?= $hari_ini ?></b> <br>
        Jam sekarang: <b><?= date('H:i') ?></b>
    </p>

    <?php if (mysqli_num_rows($query) == 0): ?>
        <div class="alert alert-secondary">
            Kamu tidak memiliki jadwal hari ini.
        </div>

    <?php else: ?>
        <div class="row">
            <?php while ($row = mysqli_fetch_assoc($query)): ?>
                <?php
                // Tentukan status mengajar
                if ($jam_sekarang >= $row['jam_mulai'] && $jam_sekarang <= $row['jam_selesai']) {
                    $status = "Sedang mengajar";
                    $alertClass = "alert-success";
                } elseif ($jam_sekarang < $row['jam_mulai']) {
                    $status = "Belum mulai";
                    $alertClass = "alert-info";
                } else {
                    $status = "Selesai";
                    $alertClass = "alert-secondary";
                }
                ?>
                <div class="col-md-4">
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <h5><?= $row['mapel'] ?></h5>
                            <p class="mb-1">
                                Kelas: <b><?= $row['nama_kelas'] ?></b>
                            </p>
                            <p class="text-muted">
                                <?= substr($row['jam_mulai'],0,5) ?>
                                -
                                <?= substr($row['jam_selesai'],0,5) ?>
                            </p>
                            <div class="alert <?= $alertClass ?> py-1 px-2">
                                Status: <b><?= $status ?></b>
                            </div>

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
