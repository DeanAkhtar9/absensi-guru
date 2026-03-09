<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('guru');

include "../config/database.php";
include "../templates/header.php";
include "../sidebar.php";
include "../header.php";

$id_guru = $_SESSION['id_user'];
?>

<style>

/* area konten */
.content-area{
    padding:30px;
    max-width:1100px;
    margin:0 auto;
}

/* card form */
.form-card{
    background:white;
    border-radius:12px;
    padding:30px;
    box-shadow:0 4px 12px rgba(0,0,0,0.06);
    max-width:700px;
    max-height:700px;
    width:100%;
    margin-left: -350px;
}

/* tombol simpan */
.btn-simpan{
    width:100%;
    padding:12px;
    border-radius:10px;
    font-weight:500;
}

/* label */
.form-label{
    font-weight:500;
}

/* garis pemisah */
.form-card hr{
    opacity:0.1;
}

</style>


<div class="content-area">

<!-- Judul -->
<h4 class="fw-bold">Isi Jurnal Harian</h4>
<p class="text-muted">
Catat kegiatan pembelajaran hari ini untuk dokumentasi akademik.
</p>


<!-- FORM -->
<div class="d-flex justify-content-center mt-4">

<div class="form-card">

<form action="proses-simpan-jurnal.php" method="POST">

<!-- tanggal -->
<div class="mb-3">
<label class="form-label">Tanggal Pelaksanaan</label>
<input type="date" name="tanggal" class="form-control" required>
</div>


<!-- kegiatan -->
<div class="mb-3">
<label class="form-label">Kegiatan Pembelajaran</label>
<textarea 
    name="kegiatan"
    class="form-control"
    rows="4"
    placeholder="Tuliskan materi, tujuan, dan aktivitas pembelajaran hari ini secara detail..."
    required
></textarea>
</div>


<div class="row">

<!-- status guru -->
<div class="col-md-6 mb-3">
<label class="form-label">Status Kehadiran Guru</label>
<select name="status_guru" class="form-select" required>
<option value="hadir">Hadir</option>
<option value="izin">Izin</option>
<option value="sakit">Sakit</option>
</select>
</div>


<!-- kelas -->
<div class="col-md-6 mb-3">
<label class="form-label">Kelas</label>

<select name="kelas" class="form-select" required>

<?php
$kelas = mysqli_query($conn,"
    SELECT k.id_kelas, k.nama_kelas
    FROM kelas k
    JOIN jadwal_mengajar jm ON k.id_kelas = jm.id_kelas
    WHERE jm.id_guru='$id_guru'
    GROUP BY k.id_kelas
");

while($k = mysqli_fetch_assoc($kelas)){
    echo "<option value='{$k['id_kelas']}'>{$k['nama_kelas']}</option>";
}
?>

</select>

</div>

</div>


<!-- tombol -->
<button type="submit" class="btn btn-primary btn-simpan mt-3">
<i class="bi bi-floppy me-2"></i> Simpan Jurnal
</button>


</form>

<hr class="mt-4">

<!-- catatan -->
<div class="text-center text-muted small">
Jurnal yang telah disimpan akan otomatis masuk ke laporan bulanan kepala sekolah.
</div>

</div>

</div>

</div>

<?php include "../templates/footer.php"; ?>