<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require "config/database.php"; // Pastikan path benar

$role = $_SESSION['role'] ?? 'Guest';
$user_id = $_SESSION['id_user'] ?? 0; // Pastikan kamu simpan id_user di session saat login
$nama = $_SESSION['nama'] ?? 'User';
$tanggal = date("l, d F Y");

// Ambil jumlah notifikasi yang belum dibaca
$queryNotif = mysqli_query($conn, "SELECT * FROM notifikasi WHERE id_user = '$user_id' AND is_read = 0 ORDER BY created_at DESC");
$jmlNotif = mysqli_num_rows($queryNotif);


?>

<style>
/* --- NOTIFIKASI CSS --- */
.notif-container {
    position: relative;
    margin-right: 20px;
}
.notif-badge {
    position: absolute; top: -5px; right: -5px; background: red; color: white;
    font-size: 10px; padding: 2px 5px; border-radius: 50%; font-weight: bold;
}
.notif-dropdown {
    position: absolute; top: 42px; right: 0; width: 300px; background: #fff;
    border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); padding: 10px 0;
    display: none; z-index: 100; max-height: 400px; overflow-y: auto;
}
.notif-dropdown.show { display: block; }
.notif-item {
    padding: 10px 15px; border-bottom: 1px solid #eee; display: block; text-decoration: none; color: #333;
}
.notif-item:hover { background: #f9f9f9; }
.notif-item.unread { border-left: 4px solid #007bff; background: #f0f7ff; }
.notif-item small { color: #888; font-size: 11px; }
.notif-header { padding: 5px 15px 10px; font-weight: bold; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; }
.account-dropdown{
    position: absolute;
    top: 45px;
    right: 0;   /* ubah ke kiri */
    right: auto;
    z-index: 9999;
}
.account-container{
    position: relative;
}

.account-dropdown{
    position: absolute;
    top: 100%;   /* tepat di bawah tombol */
    margin-top: 8px; /* jarak dikit */
    left: 0;
    z-index: 9999;
}
.account-dropdown{
    padding: 15px 0; /* atas bawah lebih lega */
}

.account-info{
    padding: 10px 20px;
}

.account-dropdown a{
    padding: 10px 20px;
    display: block;
}

.logout:hover{
    background: #ffeaea;
    color: #b02a37;
}
.notif-header{
    padding: 5px 15px 10px;
    font-weight: bold;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: center; /* tengah */
    align-items: center;
    position: relative; /* biar tombol kanan tetap bisa diposisikan */
}
</style>

<div class="main-header">

    <div class="notif-container">
        <button class="notif-btn" onclick="toggleNotif()" aria-label="Notifications">
            <i class="bi bi-bell"></i>
            <?php if($jmlNotif > 0): ?>
                <span class="notif-badge"><?= $jmlNotif ?></span>
            <?php endif; ?>
        </button>

        <div class="notif-dropdown" id="notifDropdown">
            <div class="notif-header">
                <span>Notifikasi</span>
                <?php if($jmlNotif > 0): ?>
                    <a href="baca_semua_notif.php" style="font-size: 11px; color: #007bff;">Tandai semua dibaca</a>
                <?php endif; ?>
            </div>
            <div class="text-center border-top">
        <a href="/absensi-guru/riwayat_notifikasi.php" class="d-block p-2 small text-primary fw-bold text-decoration-none">
            Lihat Semua Riwayat
        </a>
    </div>
            <?php if($jmlNotif == 0): ?>
                <div class="p-3 text-center text-muted small">Tidak ada notifikasi baru</div>
            <?php else: ?>
                <?php while($n = mysqli_fetch_assoc($queryNotif)): ?>
                    <a href="detail_notifikasi.php?id=<?= $n['id_notif'] ?>" class="notif-item unread">
                        <div style="font-size: 13px; font-weight: bold;"><?= $n['judul'] ?></div>
                        <div style="font-size: 12px;"><?= substr($n['pesan'], 0, 50) ?>...</div>
                        <small><?= date('d M, H:i', strtotime($n['created_at'])) ?></small>
                    </a>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="account-container">
        <button class="account-btn" onclick="toggleDropdown()">
            <i class="bi bi-person"></i>
        </button>
        <div class="account-dropdown" id="accountDropdown">
            <div class="account-info">
                <strong><?= htmlspecialchars($nama) ?></strong>
                <span><?= htmlspecialchars($role) ?></span>
            </div>
            <hr>
            <a href="/absensi-guru/logout.php" class="logout" style="color: #ea2a2a;">Keluar</a>
        </div>
    </div>
    <div class="header-date"><?= $tanggal ?></div>
</div>

<script>
function toggleNotif() {
    document.getElementById('notifDropdown').classList.toggle('show');
    document.getElementById('accountDropdown').classList.remove('show');
}

function toggleDropdown() {
    document.getElementById('accountDropdown').classList.toggle('show');
    document.getElementById('notifDropdown').classList.remove('show');
}

// Klik luar tutup
document.addEventListener('click', function(e) {
    if (!document.querySelector('.notif-container').contains(e.target)) {
        document.getElementById('notifDropdown').classList.remove('show');
    }
    if (!document.querySelector('.account-container').contains(e.target)) {
        document.getElementById('accountDropdown').classList.remove('show');
    }
});
</script>