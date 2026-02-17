<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body>
    <div class="sidebar">

    <!-- HEADER -->
    <div class="sidebar-header">

        <div class="sidebar-brand">
            <div class="brand-icon">
                <img src="/absensi-guru/logo.png" alt="">
            </div>

            <div class="brand-text">
                <h2>School Portal</h2>
                <?php 
                    if(isset($_SESSION['role'])){
                        echo ucfirst($_SESSION['role']); 
                    }
                ?>
            </div>
        </div>

    </div>


    <!-- MENU -->
    <ul class="sidebar-menu">

        <li>
            <a href="/absensi-guru/admin/index.php">
                <i class="bi bi-grid"></i>
                Dashboard
            </a>
        </li>

        <li>
            <a href="/absensi-guru/admin/absensi.php">
                <i class="bi bi-calendar-check"></i>
                Absensi
            </a>
        </li>

        <li>
            <a href="/absensi-guru/admin/guru.php">
                <i class="bi bi-person-badge"></i>
                Data Guru
            </a>
        </li>

        <li>
            <a href="/absensi-guru/admin/siswa.php">
                <i class="bi bi-people"></i>
                Data Siswa
            </a>
        </li>

        <li>
            <a href="/absensi-guru/admin/laporan.php">
                <i class="bi bi-file-earmark-text"></i>
                Laporan
            </a>
        </li>

        <li class="logout">
            <a href="/absensi-guru/logout.php">
                <i class="bi bi-box-arrow-right"></i>
                Logout
            </a>
        </li>

    </ul>

</div>

</body>

</html>
