<?php
session_start();
require "config/database.php"; // Sesuaikan path jika perlu

// 1. Cek Login
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// 2. Ambil semua notifikasi untuk user ini
$query = "SELECT * FROM notifikasi WHERE id_user = '$id_user' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);

include "header.php"; // Memanggil header agar muncul lonceng juga di sini
?>

<div class="main-content p-4" style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold"><i class="bi bi-bell-fill me-2 text-primary"></i> Riwayat Notifikasi</h4>
            <a href="baca_semua_notif.php" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-check2-all"></i> Tandai Semua Dibaca
            </a>
        </div>

        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="list-group list-group-flush">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <?php 
                            // Beri warna latar berbeda untuk yang belum dibaca
                            $bg_color = ($row['is_read'] == 0) ? 'background-color: #f0f7ff;' : '';
                            $bold_text = ($row['is_read'] == 0) ? 'fw-bold' : '';
                        ?>
                        <a href="detail_notif.php?id=<?= $row['id_notif'] ?>" 
                           class="list-group-item list-group-item-action p-3 border-start border-4 <?= ($row['is_read'] == 0) ? 'border-primary' : 'border-light' ?>" 
                           style="<?= $bg_color ?>">
                            
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <h6 class="mb-1 <?= $bold_text ?> text-dark"><?= htmlspecialchars($row['judul']) ?></h6>
                                <small class="text-muted">
                                    <?= date('d M Y, H:i', strtotime($row['created_at'])) ?>
                                </small>
                            </div>
                            <p class="mb-1 text-secondary small">
                                <?= substr(htmlspecialchars($row['pesan']), 0, 120) ?>...
                            </p>
                            <?php if ($row['is_read'] == 0): ?>
                                <span class="badge bg-primary rounded-pill" style="font-size: 10px;">Baru</span>
                            <?php endif; ?>
                        </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-bell-slash text-muted" style="font-size: 3rem;"></i>
                        <p class="mt-2 text-muted">Belum ada notifikasi untuk Anda.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="mt-3">
            <a href="javascript:history.back()" class="btn btn-secondary px-4">Kembali</a>
        </div>
    </div>
</div>

<style>
    .list-group-item {
        transition: all 0.2s;
    }
    .list-group-item:hover {
        background-color: #f8f9fa !important;
        transform: translateX(5px);
    }
</style>

<?php // include "footer.php"; ?>