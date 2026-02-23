<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'] ?? 'Guest';
$username = $_SESSION['username'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    
<div class="main-header">

    <!-- LEFT -->
    <div class="header-left">
        <h2 class="header-title"><?= ucfirst($role) ?> Dashboard</h2>
        <span class="header-subtitle">
            Welcome back! Here's what's happening today.
        </span>
    </div>

    <!-- RIGHT -->
    <div class="header-right">
        <!-- ACCOUNT -->
        <div class="account-container">
            <button class="account-btn" onclick="toggleDropdown()">
                <i class="bi bi-person"></i>
            </button>

            <div class="account-dropdown" id="accountDropdown">
                <a href="/absensi-guru/logout.php">Logout</a>
            </div>
        </div>


    </div>

</div>

<script>
function toggleDropdown() {
    document.getElementById("accountDropdown").classList.toggle("show");
}
</script>


</body>
</html>
