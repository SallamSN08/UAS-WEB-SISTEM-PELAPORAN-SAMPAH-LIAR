<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireUser();
$user = getUserData();
$stats = getStatsUser($user['id']);
$unread = getUnreadCount($user['id']);

// Laporan terbaru user
$stmt = $conn->prepare("SELECT * FROM laporan_sampah WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$laporan_terbaru = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Notifikasi terbaru
$stmt = $conn->prepare("SELECT * FROM notifikasi WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$notif_terbaru = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Data peta mini
$stmt = $conn->prepare("SELECT id, judul, latitude, longitude, status FROM laporan_sampah WHERE user_id = ? AND latitude IS NOT NULL");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$peta_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SiPAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/user.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body class="user-layout">
    <!-- Navbar User -->
    <nav class="user-navbar">
        <div class="container">
            <a href="../index.php" class="user-nav-brand">
                <div class="logo-icon">🌿</div>
                <span>SiPAL</span>
            </a>
            <button class="mobile-toggle">☰</button>
            <div class="user-nav-menu">
                <a href="dashboard.php" class="user-nav-link active"><i class="fas fa-home"></i> Dashboard</a>
                <a href="laporan_baru.php" class="user-nav-link"><i class="fas fa-plus-circle"></i> Buat Laporan</a>
                <a href="laporan_saya.php" class="user-nav-link"><i class="fas fa-list"></i> Laporan Saya</a>
                <a href="notifikasi.php" class="user-nav-link">
                    <i class="fas fa-bell"></i> Notifikasi
                    <?php if ($unread > 0): ?><span class="notif-badge"><?= $unread ?></span><?php endif; ?>
                </a>
                <a href="profil.php" class="user-nav-link"><i class="fas fa-user"></i> Profil</a>
                <a href="../logout.php" class="user-nav-link" style="color: #ef4444;"><i class="fas fa-sign-out-alt"></i> Keluar</a>
            </div>
        </div>
    </nav>

    <div class="user-dashboard">
        <div class="container">
            <!-- Welcome -->
            <div class="welcome-section animate-fade-in">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h2>Halo, <?= htmlspecialchars($user['nama_lengkap']) ?>! 👋</h2>
                        <p>Selamat datang di dashboard SiPAL. Yuk, laporkan sampah liar di sekitar Anda untuk kota yang lebih bersih!</p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <a href="laporan_baru.php" class="btn-laporan-besar">
                            <i class="fas fa-plus-circle" style="font-size: 24px;"></i>
                            Laporkan Sekarang
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="stats-grid animate-fade-in" style="animation-delay: 0.1s;">
                <div class="stat-card-user">
                    <div class="icon" style="background: #f0fdf4; color: #059669;"><i class="fas fa-clipboard-list"></i></div>
                    <div class="info"><h4><?= $stats['total'] ?></h4><p>Total Laporan</p></div>
                </div>
                <div class="stat-card-user">
                    <div class="icon" style="background: #fef3c7; color: #92400e;"><i class="fas fa-clock"></i></div>
                    <div class="info"><h4><?= $stats['menunggu'] ?></h4><p>Menunggu</p></div>
                </div>
                <div class="stat-card-user">
                    <div class="icon" style="background: #dbeafe; color: #1e40af;"><i class="fas fa-spinner"></i></div>
                    <div class="info"><h4><?= $stats['diproses'] ?></h4><p>Diproses</p></div>
                </div>
                <div class="stat-card-user">
                    <div class="icon" style="background: #dcfce7; color: #166534;"><i class="fas fa-check-circle"></i></div>
                    <div class="info"><h4><?= $stats['selesai'] ?></h4><p>Selesai</p></div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Laporan Terbaru -->
                <div class="col-lg-8 animate-fade-in" style="animation-delay: 0.2s;">
                    <div class="card">
                        <div class="card-header" style="border-bottom: 2px solid #f1f5f9;">
                            <h5 style="font-weight: 700; margin: 0;"><i class="fas fa-list me-2 text-primary"></i>Laporan Terbaru</h5>
                            <a href="laporan_saya.php" class="btn btn-sm btn-outline">Lihat Semua</a>
                        </div>
                        <div class="laporan-list">
                            <?php if (empty($laporan_terbaru)): ?>
                                <div class="text-center py-5">
                                    <div style="font-size: 48px; opacity: 0.3;">📋</div>
                                    <p class="text-gray mt-3">Belum ada laporan. Yuk buat laporan pertama!</p>
                                    <a href="laporan_baru.php" class="btn btn-primary btn-sm mt-2">Buat Laporan</a>
                                </div>
                            <?php else: ?>
                                <?php foreach ($laporan_terbaru as $lap): ?>
                                    <a href="detail_laporan.php?id=<?= $lap['id'] ?>" class="laporan-item">
                                        <div style="width: 100px; height: 100px; border-radius: 12px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden;">
                                            <?php if ($lap['foto_kondisi']): ?>
                                                <img src="<?= UPLOAD_URL . $lap['foto_kondisi'] ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="">
                                            <?php else: ?>
                                                <span style="font-size: 32px;">🗑️</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="laporan-info">
                                            <h4><?= htmlspecialchars($lap['judul']) ?></h4>
                                            <div class="meta">
                                                <span><i class="fas fa-barcode text-gray"></i> <?= $lap['kode_laporan'] ?></span>
                                                <span><i class="fas fa-tag text-gray"></i> <?= ucwords(str_replace('_', ' ', $lap['kategori'])) ?></span>
                                            </div>
                                            <div class="alamat"><i class="fas fa-map-marker-alt text-gray"></i> <?= htmlspecialchars($lap['alamat_lokasi']) ?></div>
                                        </div>
                                        <div class="laporan-status">
                                            <?= statusBadge($lap['status']) ?>
                                            <span class="tanggal"><?= formatTanggal($lap['created_at']) ?></span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Peta Mini -->
                    <div class="card mb-4 animate-fade-in" style="animation-delay: 0.3s;">
                        <div class="card-header" style="border-bottom: 2px solid #f1f5f9;">
                            <h5 style="font-weight: 700; margin: 0;"><i class="fas fa-map me-2 text-primary"></i>Lokasi Laporan</h5>
                        </div>
                        <div id="miniMap" style="height: 250px; border-radius: 12px; overflow: hidden;"></div>
                    </div>

                    <!-- Notifikasi -->
                    <div class="card animate-fade-in" style="animation-delay: 0.4s;">
                        <div class="card-header" style="border-bottom: 2px solid #f1f5f9;">
                            <h5 style="font-weight: 700; margin: 0;"><i class="fas fa-bell me-2 text-primary"></i>Notifikasi</h5>
                            <a href="notifikasi.php" class="btn btn-sm btn-outline">Lihat Semua</a>
                        </div>
                        <div class="notif-list">
                            <?php if (empty($notif_terbaru)): ?>
                                <div class="text-center py-4">
                                    <span style="font-size: 32px; opacity: 0.3;">🔔</span>
                                    <p class="text-gray mt-2" style="font-size: 13px;">Belum ada notifikasi</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($notif_terbaru as $notif): ?>
                                    <div class="notif-item <?= $notif['is_read'] ? '' : 'unread' ?>">
                                        <div class="notif-icon" style="background: <?= $notif['is_read'] ? '#f1f5f9' : 'var(--primary-light)' ?>; color: <?= $notif['is_read'] ? '#94a3b8' : 'var(--primary-dark)' ?>;">
                                            <i class="fas fa-bell"></i>
                                        </div>
                                        <div class="notif-content">
                                            <h5><?= htmlspecialchars($notif['judul']) ?></h5>
                                            <p><?= htmlspecialchars($notif['pesan']) ?></p>
                                        </div>
                                        <span class="notif-time"><?= formatTanggal($notif['created_at']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading -->
    <div class="loading-overlay" id="loadingOverlay" style="display: none;">
        <div class="spinner"></div>
    </div>

    <div class="toast-container" id="toastContainer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/maps.js"></script>
    <script>
        const petaData = <?= json_encode($peta_data) ?>;
        if (petaData.length > 0) {
            initMiniMap(petaData, 'miniMap');
        } else {
            document.getElementById('miniMap').innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#94a3b8;font-size:14px;">Belum ada laporan dengan lokasi</div>';
        }
    </script>
</body>
</html>
