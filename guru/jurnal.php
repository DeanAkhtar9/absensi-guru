<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('guru');

include "../templates/header.php";
include "../templates/navbar.php";

$id_guru   = $_SESSION['id_user'];
$id_jadwal = isset($_GET['id_jadwal']) ? intval($_GET['id_jadwal']) : 0;
$tanggal   = date('Y-m-d');

/* VALIDASI JADWAL */
$jadwal = mysqli_query($conn, "
    SELECT 
        jm.id_jadwal,
        jm.id_kelas,
        k.nama_kelas,
        m.nama_mapel
    FROM jadwal_mengajar jm
    JOIN kelas k ON jm.id_kelas = k.id_kelas
    JOIN mapel m ON jm.id_mapel = m.id_mapel
    WHERE jm.id_jadwal = '$id_jadwal'
      AND jm.id_guru = '$id_guru'
    LIMIT 1
");

if (mysqli_num_rows($jadwal) == 0) {
    echo "<div class='container'><div class='alert alert-danger'>
            Jadwal tidak valid.
          </div></div>";
    include "../templates/footer.php";
    exit;
}

$data_jadwal = mysqli_fetch_assoc($jadwal);

/* CEK ABSENSI GURU */
$absensi = mysqli_query($conn, "
    SELECT * FROM absensi_guru
    WHERE id_jadwal = '$id_jadwal'
      AND tanggal = '$tanggal'
    LIMIT 1
");

if (mysqli_num_rows($absensi) == 0) {
    echo "<div class='container'><div class='alert alert-warning'>
            Absensi guru belum diisi oleh siswa.
          </div></div>";
    include "../templates/footer.php";
    exit;
}

$data_absensi = mysqli_fetch_assoc($absensi);
$id_absensi_guru = $data_absensi['id_absensi_guru'];

/* PROSES SIMPAN JURNAL */
if (isset($_POST['simpan'])) {
    $materi   = mysqli_real_escape_string($conn, $_POST['materi']);
    $catatan  = mysqli_real_escape_string($conn, $_POST['catatan']);

    mysqli_query($conn, "
        INSERT INTO jurnal_mengajar
        (id_absensi_guru, materi, catatan, diisi_oleh)
        VALUES
        ('$id_absensi_guru', '$materi', '$catatan', '$id_guru')
    ");

    $id_jurnal = mysqli_insert_id($conn);

    foreach ($_POST['siswa'] as $id_siswa => $status) {
        mysqli_query($conn, "
            INSERT INTO absensi_siswa
            (id_jurnal, id_siswa, status)
            VALUES
            ('$id_jurnal', '$id_siswa', '$status')
        ");
    }

    echo "<script>
        alert('Jurnal berhasil disimpan');
        window.location='rekap.php';
    </script>";
    exit;
}

/* AMBIL SISWA */
$siswa = mysqli_query($conn, "
    SELECT id_siswa, nama_siswa
    FROM siswa
    WHERE id_kelas = '{$data_jadwal['id_kelas']}'
    ORDER BY nama_siswa
");
?>

<div class="container">
    <h3>Jurnal Mengajar</h3>

    <p>
        <b><?= $data_jadwal['nama_mapel'] ?></b><br>
        Kelas: <?= $data_jadwal['nama_kelas'] ?><br>
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
                <td><?= $s['nama_siswa'] ?></td>
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
