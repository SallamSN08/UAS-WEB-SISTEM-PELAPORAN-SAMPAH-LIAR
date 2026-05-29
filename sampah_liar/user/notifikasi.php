<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireUser();
$user = getUserData();

// Mark all as read
if (isset($_GET['read_all'])) {
    $stmt = $conn->prepare("UPDATE notifikasi SET is_read = 1 WHERE user_id = ?");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    redirect(BASE_URL . '/user/notifikasi.php');
}

// Mark single as read
if (isset($_GET['read_id'])) {
    $stmt = $conn->prepare("UPDATE notifikasi SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $_GET['read_id'], $user['id']);
    $stmt->execute();
}

$page = max(1, intval($_GET['page'] ?? 1));
$limit = 15;
$offset = ($page - 1) * $limit;

$stmt = $conn->prepare("SELECT * FROM notifikasi WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param("iii", $user['id'], $limit, $offset);
$stmt->execute();
$notifs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM notifikasi WHERE user_id = ?");
$count_stmt->bind_param("i", $user['id']);
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi - SiPAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/user.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="user-layout">
    <nav class="user-navbar">
        <div class="container">
            <a href="../index.php" class="user-nav-brand">
                <div class="logo-icon">🌿</div>
                <span>SiPAL</span>
            </a>
            <button class="mobile-toggle">☰</button>
            <div class="user-nav-menu">
                <a href="dashboard.php" class="user-nav-link"><i class="fas fa-home"></i> Dashboard</a>
                <a href="laporan_baru.php" class="user-nav-link"><i class="fas fa-plus-circle"></i> Buat Laporan</a>
                <a href="laporan_saya.php" class="user-nav-link"><i class="fas fa-list"></i> Laporan Saya</a>
                <a href="notifikasi.php" class="user-nav-link active"><i class="fas fa-bell"></i> Notifikasi</a>
                <a href="profil.php" class="user-nav-link"><i class="fas fa-user"></i> Profil</a>
                <a href="../logout.php" class="user-nav-link" style="color: #ef4444;"><i class="fas fa-sign-out-alt"></i> Keluar</a>
            </div>
        </div>
    </nav>

    <div class="user-dashboard">
        <div class="container">
            <div class="card animate-fade-in">
                <div class="card-header" style="border-bottom: 2px solid #f1f5f9;">
                    <h5 style="font-weight: 700; margin: 0;"><i class="fas fa-bell me-2 text-primary"></i>Notifikasi</h5>
                    <a href="?read_all=1" class="btn btn-sm btn-outline"><i class="fas fa-check-double me-1"></i>Tandai Semua Dibaca</a>
                </div>
                <div class="notif-list p-3">
                    <?php if (empty($notifs)): ?>
                        <div class="text-center py-5">
                            <div style="font-size: 48px; opacity: 0.3;">🔔</div>
                            <p class="text-gray mt-3">Belum ada notifikasi</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($notifs as $notif): ?>
                            <div class="notif-item <?= $notif['is_read'] ? '' : 'unread' ?>">
                                <div class="notif-icon" style="background: <?= $notif['is_read'] ? '#f1f5f9' : 'var(--primary-light)' ?>; color: <?= $notif['is_read'] ? '#94a3b8' : 'var(--primary-dark)' ?>;">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <div class="notif-content">
                                    <h5><?= htmlspecialchars($notif['judul']) ?></h5>
                                    <p><?= htmlspecialchars($notif['pesan']) ?></p>
                                </div>
                                <div class="text-end">
                                    <span class="notif-time"><?= formatTanggal($notif['created_at']) ?></span>
                                    <?php if (!$notif['is_read']): ?>
                                        <br><a href="?read_id=<?= $notif['id'] ?>" class="btn btn-sm btn-outline mt-2" style="font-size: 11px; padding: 4px 10px;">Tandai Dibaca</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?><a href="?page=<?= $page-1 ?>"><i class="fas fa-chevron-left"></i></a><?php endif; ?>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?><a href="?page=<?= $page+1 ?>"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="toast-container" id="toastContainer"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
