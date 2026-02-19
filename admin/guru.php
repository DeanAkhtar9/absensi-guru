<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('admin');

require_once "../config/database.php";
include "../templates/header.php";
include "../templates/navbar.php";
include "../sidebar.php";
?>

<div class="container mt-4">
    <h3 class="mb-4">Data Guru</h3>

    <div class="card shadow-sm">
        <div class="card-body">

            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th>No</th>
                        <th>Nama Guru</th>
                        <th>Mata Pelajaran</th>
                        <th>Total Kehadiran</th>
                        <th>Jumlah Komplain</th>
                    </tr>
                </thead>
                <tbody>

                    <?php
                    $query = "
SELECT 
    u.id_user,
    u.nama,

    GROUP_CONCAT(DISTINCT jm.mapel SEPARATOR ', ') AS mapel,

    COUNT(DISTINCT CASE 
        WHEN ag.status = 'hadir' THEN ag.id_absensi_guru
    END) AS total_hadir,

    COUNT(DISTINCT k.id_komplain) AS total_komplain

FROM users u

LEFT JOIN jadwal_mengajar jm 
    ON u.id_user = jm.id_guru

LEFT JOIN absensi_guru ag 
    ON jm.id_jadwal = ag.id_jadwal

LEFT JOIN komplain k 
    ON jm.id_jadwal = k.id_jadwal

WHERE u.role = 'guru'

GROUP BY u.id_user
";



                    $result = mysqli_query($conn, $query);
                    $no = 1;

                    while ($row = mysqli_fetch_assoc($result)):
                        ?>

                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= $row['mapel'] ?? '-' ?></td>
                            <td class="text-center">
                                <span class="badge bg-success">
                                    <?= $row['total_hadir'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger">
                                    <?= $row['total_komplain'] ?>
                                </span>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                </tbody>
            </table>

        </div>
    </div>
</div>

<?php include "../templates/footer.php"; ?>