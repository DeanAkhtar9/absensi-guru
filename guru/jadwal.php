<?php
require "../config/database.php";
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('guru');

include "../templates/header.php";
include "../templates/navbar.php";
include "../sidebar.php";

date_default_timezone_set('Asia/Jakarta');

include "../templates/header.php";
include "../templates/navbar.php";
include "../sidebar.php";

$id_guru = intval($_SESSION['id_user']);

/* Mapping hari */
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
$jam_sekarang = date('H:i');

/* Ambil jadwal hari ini */
$query = mysqli_query($conn, "
    SELECT 
        jm.id_jadwal,
        k.nama_kelas,
        jm.mapel,
        TIME_FORMAT(jm.jam_mulai, '%H:%i') as jam_mulai,
        TIME_FORMAT(jm.jam_selesai, '%H:%i') as jam_selesai
    FROM jadwal_mengajar jm
    JOIN kelas k ON jm.id_kelas = k.id_kelas
    WHERE jm.id_guru = $id_guru
      AND jm.hari = '$hari_ini'
    ORDER BY jm.jam_mulai
");

if (!$query) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<div class="container py-4">
    <h3 class="mb-3">Jadwal Mengajar Hari Ini</h3>

    <div class="mb-4">
        <span class="badge bg-primary">
            Hari: <?= $hari_ini ?>
        </span>
        <span class="badge bg-dark">
            Jam Sekarang: <?= date('H:i') ?>
        </span>
    </div>

    <?php if (mysqli_num_rows($query) == 0): ?>
        <div class="alert alert-secondary">
            Kamu tidak memiliki jadwal mengajar hari ini.
        </div>

    <?php else: ?>
        <div class="row">
            <?php while ($row = mysqli_fetch_assoc($query)): ?>

                <?php
                $now = strtotime($jam_sekarang);
                $mulai = strtotime($row['jam_mulai']);
                $selesai = strtotime($row['jam_selesai']);

                if ($now >= $mulai && $now <= $selesai) {
                    $status = "Sedang Mengajar";
                    $alertClass = "alert-success";
                    $buttonDisabled = "";
                } elseif ($now < $mulai) {
                    $status = "Belum Mulai";
                    $alertClass = "alert-info";
                    $buttonDisabled = "disabled";
                } else {
                    $status = "Selesai";
                    $alertClass = "alert-secondary";
                    $buttonDisabled = "disabled";
                }
                ?>

                <div class="col-md-4">
                    <div class="card mb-4 shadow-sm border-0">
                        <div class="card-body">

                            <h5 class="card-title mb-2">
                                <?= htmlspecialchars($row['mapel']) ?>
                            </h5>

                            <p class="mb-1">
                                Kelas:
                                <b><?= htmlspecialchars($row['nama_kelas']) ?></b>
                            </p>

                            <p class="text-muted mb-2">
                                <?= $row['jam_mulai'] ?>
                                -
                                <?= $row['jam_selesai'] ?>
                            </p>

                            <div class="alert <?= $alertClass ?> py-2 text-center">
                                <b><?= $status ?></b>
                            </div>

                            <a href="jurnal.php?id_jadwal=<?= $row['id_jadwal'] ?>"
                               class="btn btn-primary w-100"
                               <?= $buttonDisabled ?>>
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
