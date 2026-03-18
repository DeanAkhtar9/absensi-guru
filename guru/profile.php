<?php
session_start();

require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('guru');

include "../config/database.php";

/* =========================
   AMBIL DATA GURU
========================= */
$id_guru = $_SESSION['id_user'];

$query = mysqli_query($conn, "
    SELECT nama, email, no_telp 
    FROM users 
    WHERE id_user = '$id_guru'
");

$guru = mysqli_fetch_assoc($query);

/* =========================
   AMBIL DATA JADWAL DARI SHEET
========================= */

/* MASTER SHEET (mapping kelas -> gid) */
$url_master = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=799813071&single=true&output=csv";

$csv_master = file_get_contents($url_master);
$rows_master = array_map("str_getcsv", explode("\n", $csv_master));

$jadwalGuru = [];

/* LOOP SEMUA KELAS */
foreach ($rows_master as $row) {

    if (count($row) < 2) continue;

    $nama_kelas = trim($row[0]);
    $gid = trim($row[1]);

    if (!$gid) continue;

    $url = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=$gid&single=true&output=csv";

    $csv = @file_get_contents($url);
    if (!$csv) continue;

    $rows = array_map("str_getcsv", explode("\n", $csv));

    foreach ($rows as $i => $r) {

        if ($i == 0) continue;
        if (count($r) < 5) continue;

        $id = intval($r[0]);

        /* FILTER HANYA JADWAL MILIK GURU */
        if ($id == $id_guru) {

            $jadwalGuru[] = [
                'kelas' => $nama_kelas,
                'mapel' => $r[1],
                'hari' => $r[2],
                'jam' => $r[3] . " - " . $r[4]
            ];
        }
    }
}
?>

<?php include "../templates/header.php"; ?>
<?php include "../sidebar.php"; ?>
<?php include "../header.php"; ?>

<style>
.profile-card{
    border-radius:12px;
}

.jadwal-card{
    border-radius:12px;
}

.table thead{
    background:#eef4ff;
}

.table td, .table th{
    border:1px solid rgba(0,0,0,0.05);
}
.table tbody tr:hover{
    background:#f8f9fc;
}
.table thead{
    background:#eef4ff;
}


</style>

<link rel="stylesheet" href="../assets/css/bootstrap.min.css">

<div class="main-content p-4">

<h4 class="fw-bold mb-4">Profil Guru</h4>

<div class="row g-4">

<!-- =========================
     CARD PROFIL
========================= -->
<div class="col-12">

<div class="card shadow-sm" style="border-radius:12px;">
<div class="card-body text-center">

<img src="../assets/user.png" width="70" class="rounded-circle mb-3">



<h5 class="fw-bold mb-1">
<?= htmlspecialchars($guru['nama']) ?>
</h5>

<p class="text-muted mb-3">Guru</p>

<hr>

<div class="text-start mt-3">

<div class="mb-3">
<label class="text-muted small">Email</label>
<div class="fw-semibold">
<?= htmlspecialchars($guru['email'] ?? '-') ?>
</div>
</div>

<div class="mb-3">
<label class="text-muted small">No Telpon</label>
<div class="fw-semibold">
<?= htmlspecialchars($guru['no_telp'] ?? '-') ?>
</div>
</div>

</div>

</div>
</div>

</div>


<!-- =========================
     CARD JADWAL
========================= -->
<div class="col-12">

<div class="card shadow-sm" style="border-radius:12px;">
<div class="card-body">

<div class="d-flex justify-content-between align-items-center mb-3">
<h6 class="fw-bold mb-0">Jadwal Mengajar</h6>

<span class="badge bg-primary">
<?= count($jadwalGuru) ?> Jadwal
</span>
</div>

<div class="table-responsive">
<table class="table align-middle">

<thead style="background:#eef4ff;">
<tr>
<th>Kelas</th>
<th>Mapel</th>
<th>Hari</th>
<th>Jam</th>
</tr>
</thead>

<tbody>

<?php if (empty($jadwalGuru)) { ?>
<tr>
<td colspan="4" class="text-center text-muted">
Tidak ada jadwal ditemukan
</td>
</tr>
<?php } ?>

<?php foreach ($jadwalGuru as $j) { ?>
<tr>
<td><?= htmlspecialchars($j['kelas']) ?></td>
<td><?= htmlspecialchars($j['mapel']) ?></td>
<td><?= htmlspecialchars($j['hari']) ?></td>
<td><?= htmlspecialchars($j['jam']) ?></td>
</tr>
<?php } ?>

</tbody>

</table>
</div>

</div>
</div>

</div>

</div>

</div>



<?php include "../templates/footer.php"; ?>
