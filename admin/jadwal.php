<?php
require "../config/config.php";
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

    $guru    = $_POST['id_guru'];
    $kelas   = $_POST['id_kelas'];
    $mapel   = $_POST['mapel']; // karena mapel sekarang varchar
    $hari    = $_POST['hari'];
    $mulai   = $_POST['jam_mulai'];
    $selesai = $_POST['jam_selesai'];

    mysqli_query($conn, "
        INSERT INTO jadwal_mengajar 
        (id_guru, id_kelas, mapel, hari, jam_mulai, jam_selesai)
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
$guru  = mysqli_query($conn, "SELECT * FROM users WHERE role='guru'");
$kelas = mysqli_query($conn, "SELECT * FROM kelas");

$data = mysqli_query($conn, "
    SELECT 
        j.*,
        u.nama AS nama_guru,
        k.nama_kelas
    FROM jadwal_mengajar j
    JOIN users u ON j.id_guru = u.id_user
    JOIN kelas k ON j.id_kelas = k.id_kelas
    ORDER BY 
        FIELD(j.hari,'Senin','Selasa','Rabu','Kamis','Jumat'),
        j.jam_mulai ASC
");

include "../templates/header.php";
include "../templates/navbar.php";
include "../sidebar.php";
include "../header.php";
?>

<div class="main-content">

    <div class="container-fluid">

        <!-- TITLE -->
        <div class="page-header">
            <h3>Jadwal Mengajar</h3>
            <p class="text-muted">Kelola jadwal mengajar guru</p>
        </div>

        <?php flash(); ?>

        <!-- FORM -->
        <div class="card mb-4">
            <div class="card-body">

                <form method="post" class="row g-3">

                    <div class="col-md-3">
                        <label class="form-label">Guru</label>
                        <select name="id_guru" class="form-control" required>
                            <option value="">Pilih Guru</option>
                            <?php while($g=mysqli_fetch_assoc($guru)) { ?>
                                <option value="<?= $g['id_user']; ?>">
                                    <?= $g['nama']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Kelas</label>
                        <select name="id_kelas" class="form-control" required>
                            <option value="">Pilih Kelas</option>
                            <?php while($k=mysqli_fetch_assoc($kelas)) { ?>
                                <option value="<?= $k['id_kelas']; ?>">
                                    <?= $k['nama_kelas']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Mapel</label>
                        <input type="text" name="mapel" class="form-control" placeholder="Contoh: Matematika" required>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Hari</label>
                        <select name="hari" class="form-control">
                            <option>Senin</option>
                            <option>Selasa</option>
                            <option>Rabu</option>
                            <option>Kamis</option>
                            <option>Jumat</option>
                        </select>
                    </div>

                    <div class="col-md-1">
                        <label class="form-label">Mulai</label>
                        <input type="time" name="jam_mulai" class="form-control">
                    </div>

                    <div class="col-md-1">
                        <label class="form-label">Selesai</label>
                        <input type="time" name="jam_selesai" class="form-control">
                    </div>

                    <div class="col-md-1 d-flex align-items-end">
                        <button name="tambah" class="btn btn-primary w-100">
                            Tambah
                        </button>
                    </div>

                </form>

            </div>
        </div>


        <!-- TABLE -->
        <div class="card">
            <div class="card-body">

                <table class="table table-hover">

                    <thead>
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
                            <td><?= $d['mapel']; ?></td>
                            <td><?= $d['hari']; ?></td>
                            <td><?= $d['jam_mulai']; ?> - <?= $d['jam_selesai']; ?></td>
                        </tr>

                        <?php } ?>

                    </tbody>

                </table>

            </div>
        </div>


    </div>

</div>

<?php include "../templates/footer.php"; ?>
