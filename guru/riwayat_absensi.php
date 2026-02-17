<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('guru');

include "../config/database.php";
include "../templates/header.php";
include "../templates/navbar.php";

$id_guru = $_SESSION['id_user'];

$query = mysqli_query($conn,"
SELECT ag.tanggal,k.nama_kelas,
COUNT(CASE WHEN asw.status='hadir' THEN 1 END) as hadir,
COUNT(CASE WHEN asw.status!='hadir' THEN 1 END) as tidak_hadir
FROM absensi_siswa asw
JOIN jurnal_mengajar j ON asw.id_jurnal=j.id_jurnal
JOIN absensi_guru ag ON j.id_absensi_guru=ag.id_absensi_guru
JOIN jadwal_mengajar jm ON ag.id_jadwal=jm.id_jadwal
JOIN kelas k ON jm.id_kelas=k.id_kelas
WHERE jm.id_guru='$id_guru'
GROUP BY ag.tanggal,k.nama_kelas
ORDER BY ag.tanggal DESC
");
?>

<div class="container mt-4">
<h3>Riwayat Absensi</h3>

<table class="table table-bordered">
<tr>
<th>Tanggal</th>
<th>Kelas</th>
<th>Hadir</th>
<th>Tidak Hadir</th>
</tr>

<?php while($r=mysqli_fetch_assoc($query)): ?>
<tr>
<td><?= date('d-m-Y',strtotime($r['tanggal'])) ?></td>
<td><?= $r['nama_kelas'] ?></td>
<td><?= $r['hadir'] ?></td>
<td><?= $r['tidak_hadir'] ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>

<?php include "../templates/footer.php"; ?>
