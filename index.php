<?php
require "config/config.php";

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

switch ($_SESSION['role']) {
    case 'siswa':
        header("Location: siswa/");
        break;
    case 'guru':
        header("Location: guru/");
        break;
    case 'walikelas':
        header("Location: walikelas/");
        break;
    default:
        header("Location: login.php");
}
exit;
