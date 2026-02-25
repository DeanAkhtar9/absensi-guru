<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('guru');

include "../templates/header.php";
include "../templates/navbar.php";
include "../sidebar.php";

$id_guru   = $_SESSION['id_user'];
$id_jadwal = isset($_GET['id_jadwal']) ? intval($_GET['id_jadwal']) : 0;
$tanggal   = date('Y-m-d');

/* ==============================
   CEK ID JADWAL VALID
   ============================== */
if ($id_jadwal == 0) {
    echo "<div class='container'><div class='alert alert-danger'>
            ID Jadwal tidak ditemukan.
          </div></div>";
    include "../templates/footer.php";
    exit;
}

/* Ambil jadwal, pastikan milik guru ini */
$jadwal = mysqli_query($conn, "
    SELECT 
        jm.id_jadwal,
        jm.id_kelas,
        jm.mapel,
        k.nama_kelas
    FROM jadwal_mengajar jm
    JOIN kelas k ON jm.id_kelas = k.id_kelas
    WHERE jm.id_jadwal = '$id_jadwal'
      AND jm.id_guru = '$id_guru'
    LIMIT 1
");

if (mysqli_num_rows($jadwal) == 0) {
    $check = mysqli_query($conn, "SELECT * FROM jadwal_mengajar WHERE id_jadwal = '$id_jadwal'");
    if (mysqli_num_rows($check) == 0) {
        die("<div class='container'><div class='alert alert-danger'>
                ID Jadwal tidak ditemukan di database.
            </div></div>");
    } else {
        die("<div class='container'><div class='alert alert-danger'>
                Jadwal ditemukan, tapi bukan milik guru ini.
            </div></div>");
    }
}

$data_jadwal = mysqli_fetch_assoc($jadwal);

/* ==============================
   CEK ATAU BUAT ABSENSI GURU
   ============================== */
$absensi = mysqli_query($conn, "
    SELECT * FROM absensi_guru
    WHERE id_jadwal = '$id_jadwal'
      AND tanggal = '$tanggal'
    LIMIT 1
");

if (mysqli_num_rows($absensi) == 0) {
    // Jika absensi guru belum ada, buat otomatis "hadir"
    mysqli_query($conn, "
        INSERT INTO absensi_guru (id_jadwal, tanggal, status, diinput_oleh)
        VALUES ('$id_jadwal', '$tanggal', 'hadir', '$id_guru')
    ");
    $id_absensi_guru = mysqli_insert_id($conn);
} else {
    $data_absensi = mysqli_fetch_assoc($absensi);
    $id_absensi_guru = $data_absensi['id_absensi_guru'];
}

/* ==============================
   PROSES SIMPAN JURNAL
   ============================== */
if (isset($_POST['simpan'])) {

    $materi  = mysqli_real_escape_string($conn, $_POST['materi']);
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);

    mysqli_query($conn, "
        INSERT INTO jurnal_mengajar
        (id_absensi_guru, materi, catatan, diisi_oleh)
        VALUES
        ('$id_absensi_guru','$materi','$catatan','$id_guru')
    ");

    $id_jurnal = mysqli_insert_id($conn);

    /* SIMPAN ABSENSI SISWA */
    if (isset($_POST['siswa']) && is_array($_POST['siswa'])) {
        foreach ($_POST['siswa'] as $id_siswa => $status) {
            mysqli_query($conn, "
                INSERT INTO absensi_siswa
                (id_jurnal, id_siswa, status)
                VALUES
                ('$id_jurnal', '$id_siswa', '$status')
            ");
        }
    }

    echo "<script>
        alert('Jurnal berhasil disimpan');
        window.location='rekap.php';
    </script>";
    exit;
}

/* ==============================
   AMBIL DATA SISWA
   ============================== */
$siswa = mysqli_query($conn, "
    SELECT s.id_siswa, u.nama AS nama_siswa
    FROM siswa s
    JOIN users u ON s.id_user = u.id_user
    WHERE s.id_kelas = '{$data_jadwal['id_kelas']}'
    ORDER BY u.nama
");
?>

<div class="container">
    <h3>Jurnal Mengajar</h3>

    <p>
        <b><?= htmlspecialchars($data_jadwal['mapel']) ?></b><br>
        Kelas: <?= htmlspecialchars($data_jadwal['nama_kelas']) ?><br>
        Tanggal: <?= date('d-m-Y') ?>
    </p>

    <form method="post">
        <div class="mb-3">
            <label>Materi</label>
            <textarea name="materi" class="form-control" required></textarea>
        </div>

        <div class="mb-3">
            <label>Catatan</label>
            <textarea name="catatan" class="form-control"></textarea>
        </div>

        <h5>Absensi Siswa</h5>

        <table class="table table-bordered">
            <tr>
                <th>Nama Siswa</th>
                <th>Status</th>
            </tr>
            <?php while ($s = mysqli_fetch_assoc($siswa)): ?>
            <tr>
                <td><?= htmlspecialchars($s['nama_siswa']) ?></td>
                <td>
                    <select name="siswa[<?= $s['id_siswa'] ?>]" class="form-select">
                        <option value="hadir">Hadir</option>
                        <option value="izin">Izin</option>
                        <option value="sakit">Sakit</option>
                        <option value="alpa">Alpa</option>
                    </select>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>

        <button name="simpan" class="btn btn-success">
            Simpan Jurnal
        </button>

    </form>
</div>

<?php include "../templates/footer.php"; ?>
