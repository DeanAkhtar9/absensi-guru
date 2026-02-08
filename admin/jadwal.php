<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('admin');

require "../config/database.php";
require "../includes/flash.php";

/*
|--------------------------------------------------------------------------
| Tambah jadwal
|--------------------------------------------------------------------------
*/
if (isset($_POST['tambah'])) {
    $guru   = $_POST['id_guru'];
    $kelas  = $_POST['id_kelas'];
    $mapel  = $_POST['id_mapel'];
    $hari   = $_POST['hari'];
    $mulai  = $_POST['jam_mulai'];
    $selesai= $_POST['jam_selesai'];

    mysqli_query($conn, "
        INSERT INTO jadwal_mengajar 
        (id_guru, id_kelas, id_mapel, hari, jam_mulai, jam_selesai)
        VALUES 
        ('$guru','$kelas','$mapel','$hari','$mulai','$selesai')
    ");

    setFlash('success', 'Jadwal berhasil ditambahkan');
    header("Location: jadwal.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Data
|--------------------------------------------------------------------------
*/
$guru  = mysqli_query($conn, "SELECT * FROM guru");
$kelas = mysqli_query($conn, "SELECT * FROM kelas");
$mapel = mysqli_query($conn, "SELECT * FROM mapel");

$data = mysqli_query($conn, "
    SELECT j.*, g.nama_guru, k.nama_kelas, m.nama_mapel
    FROM jadwal_mengajar j
    JOIN guru g ON j.id_guru=g.id_guru
    JOIN kelas k ON j.id_kelas=k.id_kelas
    JOIN mapel m ON j.id_mapel=m.id_mapel
    ORDER BY j.hari, j.jam_mulai
");

include "../templates/header.php";
include "../templates/navbar.php";
?>

<div class="container">
    <h3>Jadwal Mengajar</h3>
    <?php flash(); ?>

    <form method="post" class="row g-2 mb-4">
        <div class="col">
            <select name="id_guru" class="form-control" required>
                <option value="">Guru</option>
                <?php while($g=mysqli_fetch_assoc($guru)) { ?>
                    <option value="<?= $g['id_guru']; ?>"><?= $g['nama_guru']; ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="col">
            <select name="id_kelas" class="form-control" required>
                <option value="">Kelas</option>
                <?php while($k=mysqli_fetch_assoc($kelas)) { ?>
                    <option value="<?= $k['id_kelas']; ?>"><?= $k['nama_kelas']; ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="col">
            <select name="id_mapel" class="form-control" required>
                <option value="">Mapel</option>
                <?php while($m=mysqli_fetch_assoc($mapel)) { ?>
                    <option value="<?= $m['id_mapel']; ?>"><?= $m['nama_mapel']; ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="col">
            <select name="hari" class="form-control">
                <option>Senin</option>
                <option>Selasa</option>
                <option>Rabu</option>
                <option>Kamis</option>
                <option>Jumat</option>
            </select>
        </div>

        <div class="col">
            <input type="time" name="jam_mulai" class="form-control">
        </div>

        <div class="col">
            <input type="time" name="jam_selesai" class="form-control">
        </div>

        <div class="col">
            <button name="tambah" class="btn btn-primary">Tambah</button>
        </div>
    </form>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Guru</th>
                <th>Kelas</th>
                <th>Mapel</th>
                <th>Hari</th>
                <th>Jam</th>
            </tr>
        </thead>
        <tbody>
            <?php while($d=mysqli_fetch_assoc($data)) { ?>
            <tr>
                <td><?= $d['nama_guru']; ?></td>
                <td><?= $d['nama_kelas']; ?></td>
                <td><?= $d['nama_mapel']; ?></td>
                <td><?= $d['hari']; ?></td>
                <td><?= $d['jam_mulai'].' - '.$d['jam_selesai']; ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php include "../templates/footer.php"; ?>
