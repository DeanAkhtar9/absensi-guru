<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('siswa');

require "../config/database.php";

include "../templates/header.php";
include "../templates/navbar.php";
include "../sidebar.php";

/* Ambil semua jadwal */
$jadwal = mysqli_query($conn,"
    SELECT jm.id_jadwal, u.nama, jm.mapel, k.nama_kelas, jm.hari, jm.jam_mulai, jm.jam_selesai
    FROM jadwal_mengajar jm
    JOIN users u ON jm.id_guru = u.id_user
    JOIN kelas k ON jm.id_kelas = k.id_kelas
    ORDER BY jm.hari, jm.jam_mulai
");
?>

<div class="container mt-4">
    <h3>Buat Laporan Kehadiran Guru</h3>

    <form method="POST" action="proses_laporan.php">

        <!-- Tanggal -->
        <div class="mb-3">
            <label class="form-label">Tanggal Kejadian</label>
            <input type="date" name="tanggal" class="form-control" required>
        </div>

        <!-- Pilih Jadwal -->
        <div class="mb-3">
            <label class="form-label">Pilih Jadwal</label>
            <select name="id_jadwal" class="form-control" required>
                <option value="">-- Pilih Jadwal --</option>
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

        <!-- Status Kehadiran -->
        <div class="mb-3">
            <label class="form-label">Status Kehadiran Guru</label>
            <select name="deskripsi" class="form-control" required>
                <option value="">-- Pilih Status --</option>
                <option value="Hadir">Hadir</option>
                <option value="Tidak Hadir">Tidak Hadir</option>
                <option value="Tidak Ada Keterangan">Tidak Ada Keterangan</option>
            </select>
        </div>

        <!-- Keterangan Tambahan -->
<div class="mb-3">
    <label class="form-label">Keterangan (Isi jika guru izin atau alasan tidak hadir)</label>
    <textarea name="keterangan" class="form-control" rows="3"
        placeholder="Contoh: Guru izin karena sakit / Guru memberi tugas di WA / Tidak ada pemberitahuan"></textarea>
    <small class="text-muted">
        Jika memilih <b>Tidak Hadir</b>, mohon isi apakah guru memberi izin atau tidak.
    </small>
</div>

        <!-- Tombol -->
        <button type="submit" class="btn btn-primary">Kirim Laporan</button>
        <a href="dashboard.php" class="btn btn-secondary">Kembali</a>

    </form>
</div>

<?php include "../templates/footer.php"; ?>