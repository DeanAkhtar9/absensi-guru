<?php
// auth/role_check.php

function checkRole($role_yang_diizinkan)
{
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role_yang_diizinkan) {
        header("Location: " . BASE_URL . "/login.php");
        exit;
    }
}
