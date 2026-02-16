<?php
// auth/redirect_role.php

switch ($_SESSION['role']) {
    case 'siswa':
        header("Location: " . BASE_URL . "/siswa");
        break;
    case 'guru':
        header("Location: " . BASE_URL . "/guru");
        break;
    case 'walikelas':
        header("Location: " . BASE_URL . "/
        ");
        break;
    default:
        header("Location: " . BASE_URL . "/login.php");
}
exit;
