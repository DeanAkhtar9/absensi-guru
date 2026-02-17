<?php

// Hitung semua data dari tabel
function getTotal($table, $conn) {
    $q = mysqli_query($conn, "SELECT COUNT(*) AS total FROM $table");
    $d = mysqli_fetch_assoc($q);
    return $d['total'] ?? 0;
}


// Hitung user berdasarkan role
function getUserByRole($role, $conn) {
    $q = mysqli_query($conn,
        "SELECT COUNT(*) AS total FROM users WHERE role='$role'"
    );

    $d = mysqli_fetch_assoc($q);
    return $d['total'] ?? 0;
}
