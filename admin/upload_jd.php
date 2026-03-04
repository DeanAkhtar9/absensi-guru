<?php
require "../auth/auth_check.php";
require "../config/database.php";
include "../templates/header.php";
include "../sidebar.php";
include "../header.php"; 




if(isset($_POST['import'])) {

    $file = $_FILES['file']['tmp_name'];

    if(($handle = fopen($file, "r")) !== FALSE) {

        // Optional: hapus jadwal lama
        mysqli_query($conn, "TRUNCATE TABLE jadwal_mengajar");

        $rowNumber = 0;

        while(($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

            if($rowNumber == 0) {
                $rowNumber++;
                continue; // skip header
            }

            $id_guru     = $data[0];
            $id_kelas    = $data[1];
            $mapel       = $data[2];
            $hari        = $data[3];
            $jam_mulai   = $data[4];
            $jam_selesai = $data[5];

            mysqli_query($conn, "
                INSERT INTO jadwal_mengajar
                (id_guru, id_kelas, mapel, hari, jam_mulai, jam_selesai)
                VALUES
                ('$id_guru','$id_kelas','$mapel','$hari','$jam_mulai','$jam_selesai')
            ");

            $rowNumber++;
        }

        fclose($handle);

        echo "<script>alert('Jadwal berhasil diimport');</script>";
    }
}

?>

<div class="main-content">
<div class="container py-4">

<h3 class="mb-4">Upload Jadwal Pelajaran (CSV)</h3>

<div class="card shadow-sm">
<div class="card-body">

<form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
        <label class="form-label">Upload File CSV</label>
        <input type="file" name="file" class="form-control" accept=".csv" required>
    </div>

    <button type="submit" name="import" class="btn btn-primary">
        Upload & Import
    </button>
</form>

</div>
</div>

</div>
</div>
