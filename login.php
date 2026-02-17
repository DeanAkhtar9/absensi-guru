<?php
session_start(); // WAJIB

require "config/config.php";
require "config/database.php";

if (isset($_SESSION['id_user']) && isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: admin/index.php");
            exit;
        case 'guru':
            header("Location: guru/index.php");
            exit;
        case 'siswa':
            header("Location: siswa/index.php");
            exit;
        case 'walikelas':
            header("Location: walikelas/index.php");
            exit;
    }
}


$error = "";

if (isset($_POST['login'])) {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $query = mysqli_query($conn,
        "SELECT * FROM users WHERE username='$username' LIMIT 1"
    );

    if (mysqli_num_rows($query) === 1) {

        $user = mysqli_fetch_assoc($query);

        if (password_verify($password, $user['password'])) {

            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['role']    = $user['role'];

            switch ($user['role']) {
                case 'admin':
                    header("Location: admin/index.php");
                    exit;
                case 'guru':
                    header("Location: guru/index.php");
                    exit;
                case 'siswa':
                    header("Location: siswa/index.php");
                    exit;
                case 'walikelas':
                    header("Location: walikelas/index.php");
                    exit;
            }
        }
    }

    $error = "Username atau password salah";
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body">
                    <h4 class="text-center mb-3">Login</h4>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="mb-3">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <button name="login" class="btn btn-primary w-100">
                            Login
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
