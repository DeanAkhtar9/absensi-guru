<?php
session_start();
require "config/database.php"; // Tanpa ../ karena file ini sudah di root

$id_notif = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['id_user'] ?? 0;

if($id_notif == 0) {
    header("Location: index.php");
    exit;
}

// 1. Tandai sudah dibaca (Hanya jika milik user yang sedang login)
mysqli_query($conn, "UPDATE notifikasi SET is_read = 1 WHERE id_notif = '$id_notif' AND id_user = '$user_id'");

// 2. Ambil detail pesan
$res = mysqli_query($conn, "SELECT * FROM notifikasi WHERE id_notif = '$id_notif' AND id_user = '$user_id'");
$data = mysqli_fetch_assoc($res);

if(!$data) {
    die("Notifikasi tidak ditemukan atau Anda tidak memiliki akses.");
}

include "header.php"; // Panggil header yang ada di root
?>

<div class="main-content p-4" style="font-family: Arial, sans-serif;">
    <div class="card shadow-sm border-0" style="max-width: 700px; margin: auto; border-radius: 15px; overflow: hidden;">
        <div class="card-header bg-primary text-white p-3">
            <h5 class="mb-0"><i class="bi bi-envelope-open me-2"></i> Detail Notifikasi</h5>
        </div>
        <div class="card-body p-4">
            <h4 class="fw-bold text-dark"><?= htmlspecialchars($data['judul']) ?></h4>
            <small class="text-muted d-block mb-3">
                <i class="bi bi-clock me-1"></i> <?= date('d F Y, H:i', strtotime($data['created_at'])) ?>
            </small>
            <hr>
            <div class="py-2" style="line-height: 1.6; color: #444; font-size: 16px;">
                <?= nl2br(htmlspecialchars($data['pesan'])) ?>
            </div>
        </div>
        <div class="card-footer bg-light p-3 text-end">
            <a href="javascript:history.back()" class="btn btn-secondary px-4">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>
</div>

<?php // include "footer.php"; ?>