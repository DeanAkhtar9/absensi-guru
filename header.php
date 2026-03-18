<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ambil data dari session
$role = $_SESSION['role'] ?? 'Guest';
$username = $_SESSION['username'] ?? 'User';
$email = $_SESSION['email'] ?? '';
$nama = $_SESSION['nama'] ?? $username;
$tanggal = date("l, d F Y");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Header</title>

<!-- Bootstrap Icon CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<style>
/* --- HEADER --- */
.main-header {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    padding: 12px 20px;
    background-color: #f5f5f5;
    border-bottom: 1px solid #ddd;
    font-family: Arial, sans-serif;
    position: relative;
}

.header-date {
    margin-right: 15px;
    font-size: 14px;
    color: #555;
}

.account-container {
    position: relative;
}

.account-btn {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #333;
}

.account-dropdown {
    position: absolute;
    top: 42px;
    right: 0;
    width: 240px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    padding: 15px;
    display: none;
    z-index: 10;
}

.account-dropdown.show {
    display: block;
}

.account-info strong {
    font-size: 16px;
    display: block;
    color: #222;
    margin-bottom: 3px;
}

.account-info span,
.account-info em {
    font-size: 13px;
    color: #666;
    display: block;
    margin-bottom: 5px;
}

.account-dropdown hr {
    border: 0;
    border-top: 1px solid #eee;
    margin: 8px 0;
}

.account-dropdown a {
    display: block;
    padding: 6px 0;
    text-decoration: none;
    color: #333;
    border-radius: 6px;
    transition: background 0.2s;
}

.account-dropdown a:hover {
    background-color: #f0f0f0;
}

.account-dropdown a.logout {
    color: #d9534f;
    font-weight: 600;
}
</style>
</head>

<body>

<div class="main-header">

    <div class="header-date"><?= $tanggal ?></div>

    <div class="account-container">
        <button class="account-btn" onclick="toggleDropdown()" aria-label="Account Menu">
            <i class="bi bi-person"></i>
        </button>

        <div class="account-dropdown" id="accountDropdown">
            <div class="account-info">
                <strong><?= htmlspecialchars($nama) ?></strong>
                <span><?= htmlspecialchars($email) ?></span>
            </div>
            <hr>

            <a href="/absensi-guru/logout.php" class="logout">Keluar</a>
        </div>
    </div>

</div>

<script>
// Toggle dropdown
function toggleDropdown() {
    const dropdown = document.getElementById('accountDropdown');
    dropdown.classList.toggle('show');
}

// Tutup dropdown jika klik di luar
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('accountDropdown');
    const btn = document.querySelector('.account-btn');
    if (!dropdown.contains(event.target) && !btn.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});
</script>

</body>
</html>