<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('admin');

include "../config/database.php";
include "../templates/header.php";
include "../templates/navbar.php";
include "../sidebar.php";
include "../header.php"; 

$id_guru = $_SESSION['id_user'];

$query = mysqli_query($conn,"
    SELECT ag.tanggal,k.nama_kelas,m.nama_mapel,
           j.materi,j.catatan,j.diisi_oleh,j.created_at
    FROM jurnal_mengajar j
    JOIN absensi_guru ag ON j.id_absensi_guru=ag.id_absensi_guru
    JOIN jadwal_mengajar jm ON ag.id_jadwal=jm.id_jadwal
    JOIN kelas k ON jm.id_kelas=k.id_kelas
    JOIN mapel m ON jm.id_mapel=m.id_mapel
    WHERE jm.id_guru='$id_guru'
    ORDER BY ag.tanggal DESC
");
?>

<div class="container mt-4">
<h3>Riwayat Jurnal</h3>

<table class="table table-bordered">
<tr>
<th>Tanggal</th>
<th>Kelas</th>
<th>Mapel</th>
<th>Materi</th>
<th>Di isi oleh</th>
</tr>

<?php while($r=mysqli_fetch_assoc($query)): ?>
<tr>
<td><?= date('d-m-Y',strtotime($r['tanggal'])) ?></td>
<td><?= $r['nama_kelas'] ?></td>
<td><?= $r['nama_mapel'] ?></td>
<td><?= $r['materi'] ?></td>
<td><?= $r['created_at'] ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>

<?php include "../templates/footer.php"; ?>
