<?php

require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('siswa');

require "../config/database.php";

include "../templates/header.php";
include "../templates/navbar.php";
include "../sidebar.php";
include "../header.php";

/* Ambil semua jadwal */
$jadwal = mysqli_query($conn,"
    SELECT jm.id_jadwal, u.nama, jm.mapel, k.nama_kelas, jm.hari, jm.jam_mulai, jm.jam_selesai
    FROM jadwal_mengajar jm
    JOIN users u ON jm.id_guru = u.id_user
    JOIN kelas k ON jm.id_kelas = k.id_kelas
    ORDER BY jm.hari, jm.jam_mulai
");
?>
<div class="main-content">
<div class="content-area container-fluid">
    <main class="flex-1 overflow-y-auto bg-background-light dark:bg-background-dark p-8 max-w-7xl mx-auto space-y-8">
    
        <h2 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Buat Laporan</h2>
<!-- CARD FORM LAPORAN -->
<div class="card shadow-sm mb-4">

    <div class="card-body">
        <form method="POST" action="proses_laporan.php" enctype="multipart/form-data">

            <!-- Tanggal -->
            <div class="mb-3">
                <label class="form-label">Tanggal Laporan</label>
                <input type="date" name="tanggal" class="form-control" required>
            </div>

            <!-- Pilih Jadwal -->
            <div class="mb-3">
                <label class="form-label">Pilih Jadwal</label>
                <select name="id_jadwal" class="form-control" required>
                    <option value="">Pilih Jadwal</option>
                    <?php while($j = mysqli_fetch_assoc($jadwal)): ?>
                        <option value="<?= $j['id_jadwal'] ?>">
                            <?= $j['hari'] ?> |
                            <?= substr($j['jam_mulai'],0,5) ?> - <?= substr($j['jam_selesai'],0,5) ?> |
                            <?= $j['nama'] ?> |
                            <?= $j['mapel'] ?> (<?= $j['nama_kelas'] ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Jenis Laporan -->
            <div class="mb-3">
                <label class="form-label">Jenis Laporan</label>
                <select name="jenis_laporan" class="form-control" required>
                    <option value="">Pilih Jenis Laporan</option>
                    <option value="Hadir">Hadir</option>
                    <option value="Tidak Hadir">Tidak Hadir</option>
                    <option value="Izin">Izin</option>
                    <option value="Sakit">Sakit</option>
                </select>
            </div>

            <!-- Deskripsi / Keterangan -->
            <div class="mb-3">
                <label class="form-label">Deskripsi / Keterangan</label>
                <textarea name="deskripsi" class="form-control" rows="3"
                    placeholder="Isi keterangan tambahan jika diperlukan"></textarea>
            </div>
            <!-- Tombol -->
            <button type="submit" class="btn btn-primary">Kirim Laporan</button>
            <a href="dashboard.php" class="btn btn-secondary">Kembali</a>

        </form>
    </div>
</div>

</div>
</div>

<?php include "../templates/footer.php"; ?>

