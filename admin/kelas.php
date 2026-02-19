<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('admin');

require "../config/database.php";
require "../includes/flash.php";

/*
|--------------------------------------------------------------------------
| Tambah kelas
|--------------------------------------------------------------------------
*/
if (isset($_POST['tambah'])) {
    $nama = trim($_POST['nama_kelas']);

    if ($nama === '') {
        setFlash('danger', 'Nama kelas tidak boleh kosong');
    } else {
        mysqli_query($conn, "INSERT INTO kelas (nama_kelas) VALUES ('$nama')");
        setFlash('success', 'Kelas berhasil ditambahkan');
    }

    header("Location: kelas.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Hapus kelas
|--------------------------------------------------------------------------
*/
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM kelas WHERE id_kelas='$id'");
    setFlash('success', 'Kelas berhasil dihapus');
    header("Location: kelas.php");
    exit;
}

$data = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas ASC");

include "../templates/header.php";
include "../templates/navbar.php";
include "../sidebar.php";
?>

<div class="container">
    <h3>Data Kelas</h3>

    <?php flash(); ?>

    <div class="row">
        <div class="col-md-4">
            <form method="post">
                <div class="mb-3">
                    <label>Nama Kelas</label>
                    <input type="text" name="nama_kelas" class="form-control" placeholder="X RPL 1">
                </div>
                <button name="tambah" class="btn btn-primary">Tambah</button>
            </form>
        </div>

        <div class="col-md-8">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama Kelas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no=1; while ($k = mysqli_fetch_assoc($data)) { ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= htmlspecialchars($k['nama_kelas']); ?></td>
                        <td>
                            <a href="?hapus=<?= $k['id_kelas']; ?>"
                               onclick="return confirm('Hapus kelas ini?')"
                               class="btn btn-danger btn-sm">
                               Hapus
                            </a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include "../templates/footer.php"; ?>
