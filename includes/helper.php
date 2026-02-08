<?php

/*
|--------------------------------------------------------------------------
| Escape output (anti XSS)
|--------------------------------------------------------------------------
*/
function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/*
|--------------------------------------------------------------------------
| Cek login
|--------------------------------------------------------------------------
*/
function isLogin()
{
    return isset($_SESSION['id_user']);
}

/*
|--------------------------------------------------------------------------
| Cek role user
|--------------------------------------------------------------------------
*/
function isRole($role)
{
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/*
|--------------------------------------------------------------------------
| Redirect helper
|--------------------------------------------------------------------------
*/
function redirect($path)
{
    header("Location: $path");
    exit;
}

/*
|--------------------------------------------------------------------------
| Format tanggal Indonesia
|--------------------------------------------------------------------------
*/
function tanggalIndo($tanggal)
{
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    $pecah = explode('-', $tanggal);
    return $pecah[2] . ' ' . $bulan[(int)$pecah[1]] . ' ' . $pecah[0];
}

/*
|--------------------------------------------------------------------------
| Badge status hadir
|--------------------------------------------------------------------------
*/
function badgeHadir($status)
{
    if ($status === 'hadir') {
        return "<span class='badge bg-success'>Hadir</span>";
    }
    return "<span class='badge bg-danger'>Tidak Hadir</span>";
}
