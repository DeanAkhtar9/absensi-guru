jadwal2:
<?php
require "../auth/auth_check.php";
require "../auth/role_check.php";
checkRole('admin');

date_default_timezone_set('Asia/Jakarta');


/* AMBIL LIST KELAS DARI SHEET MASTER */
$url_master = "https://docs.google.com/spreadsheets/d/e/2PACX-1vQZwSBy_K6b0qt6-4lN2RqJ2Q4zUkUL4sRO7dT7V6z9ChPMZXdo8GL0HIKF_W3vaZ8GbDiBxgAvfW38/pub?gid=799813071&single=true&output=csv";

$csv = file_get_contents($url_master);
$rows = array_map("str_getcsv", explode("\n", $csv));

$kelasList = [];

foreach ($rows as $row) {
    if (!empty($row[0])) {
        $kelasList[] = trim($row[0]);
    }
}

/* Search */
$search = isset($_GET['search']) ? strtolower($_GET['search']) : "";


include "../templates/header.php";
include "../sidebar.php";
include "../header.php";
?>

<style>

/* CARD */
.kelas-card {
    border: none;
    border-radius: 16px;
    padding: 20px;
    background: #ffffff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    transition: 0.3s;
}

.kelas-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
}

/* TITLE */
.kelas-title {
    font-weight: 600;
    font-size: 18px;
}

/* BUTTON LIHAT */
.btn-lihat {
    background: linear-gradient(135deg, #4e73df, #224abe);
    color: #fff;
    border-radius: 10px;
    padding: 10px;
    font-size: 14px;
    border: none;
    transition: 0.3s;
}

.btn-lihat:hover {
    background: linear-gradient(135deg, #224abe, #1a3aa8);
    color: #fff;
}

/* BUTTON TAMBAH */
.btn-custom {
    border-radius: 10px;
    padding: 8px 16px;
}

/* SEARCH BOX */
.search-box {
    border-radius: 10px;
    padding: 10px;
}

/* FILTER BUTTON */
.btn-filter {
    background: #4e73df;
    color: white;
    border-radius: 10px;
}

.btn-filter:hover {
    background: #2e59d9;
}


</style>

<!-- <link rel="stylesheet" href="../assets/css/bootstrap.min.css"> -->
<div class="main-content">
<div class="container py-4">

<!-- HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold">Daftar Jadwal Per Kelas</h4>

    <a href="tambah_kelas.php" class="btn btn-primary btn-custom">
        + Tambah Kelas
    </a>
</div>

<!-- SEARCH -->
<form method="GET" class="mb-4">
    <div class="row g-2">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control search-box"
                placeholder="Cari nama kelas..."
                value="<?= htmlspecialchars($search) ?>">
        </div>

        <div class="col-md-2">
            <button class="btn btn-filter w-100">Filter</button>
        </div>
    </div>
</form>

<!-- CARD LIST -->
<div class="row g-4">

<?php
foreach ($kelasList as $nama_kelas) {

    if ($search && strpos(strtolower($nama_kelas), $search) === false) {
        continue;
    }
?>

<div class="col-md-4">
    <div class="card kelas-card h-100">
        <div class="card-body text-center d-flex flex-column justify-content-center">

            <h5 class="kelas-title"><?= htmlspecialchars($nama_kelas) ?></h5>

            <a href="detail_jadwal.php?kelas=<?= urlencode($nama_kelas) ?>"
               class="btn btn-lihat mt-3">
               Lihat Jadwal
            </a>

        </div>
    </div>
</div>

<?php } ?>

</div>
</div>
</div>


<?php include "../templates/footer.php"; ?>