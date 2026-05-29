<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireUser();
$user = getUserData();

$id = intval($_GET['id'] ?? 0);
$laporan = getLaporanById($id);

if (!$laporan || $laporan['user_id'] != $user['id']) {
    setFlash('error', 'Laporan tidak ditemukan');
    redirect(BASE_URL . '/user/laporan_saya.php');
}

$tracking = getTrackingByLaporan($id);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Laporan - SiPAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/user.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
                <a href="notifikasi.php" class="user-nav-link"><i class="fas fa-bell"></i> Notifikasi</a>
                <a href="profil.php" class="user-nav-link"><i class="fas fa-user"></i> Profil</a>
                <a href="../logout.php" class="user-nav-link" style="color: #ef4444;"><i class="fas fa-sign-out-alt"></i> Keluar</a>
            </div>
        </div>
    </nav>

    <div class="user-dashboard">
        <div class="container">
            <a href="laporan_saya.php" class="btn btn-outline btn-sm mb-3"><i class="fas fa-arrow-left me-2"></i>Kembali</a>

            <div class="detail-header animate-fade-in">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div>
                        <span style="font-size: 12px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;"><?= $laporan['kode_laporan'] ?></span>
                        <h2 style="font-weight: 800; color: var(--dark); margin: 4px 0;"><?= htmlspecialchars($laporan['judul']) ?></h2>
                        <div class="d-flex gap-3 flex-wrap mt-2">
                            <?= statusBadge($laporan['status']) ?>
                            <span class="badge" style="background: #fef3c7; color: #92400e;"><i class="fas fa-exclamation-circle me-1"></i><?= ucfirst($laporan['tingkat_urgensi']) ?></span>
                            <span class="badge" style="background: #f1f5f9; color: #64748b;"><i class="fas fa-tag me-1"></i><?= ucwords(str_replace('_', ' ', $laporan['kategori'])) ?></span>
                        </div>
                    </div>
                    <div class="text-end">
                        <p style="font-size: 13px; color: #94a3b8;"><i class="fas fa-calendar me-1"></i><?= formatTanggal($laporan['created_at']) ?></p>
                        <?php if ($laporan['updated_at'] != $laporan['created_at']): ?>
                            <p style="font-size: 12px; color: #94a3b8;"><i class="fas fa-sync me-1"></i>Update: <?= formatTanggal($laporan['updated_at']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
                    <!-- Foto -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="foto-box animate-fade-in" style="animation-delay: 0.1s;">
                                <label><i class="fas fa-camera me-2"></i>Foto Sebelum</label>
                                <?php if ($laporan['foto_kondisi']): ?>
                                    <img src="<?= UPLOAD_URL . $laporan['foto_kondisi'] ?>" alt="Foto Sebelum">
                                <?php else: ?>
                                    <div style="height: 250px; display: flex; align-items: center; justify-content: center; color: #94a3b8;">Tidak ada foto</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="foto-box animate-fade-in" style="animation-delay: 0.2s;">
                                <label><i class="fas fa-check-circle me-2"></i>Foto Sesudah</label>
                                <?php if ($laporan['foto_sesudah']): ?>
                                    <img src="<?= UPLOAD_URL . $laporan['foto_sesudah'] ?>" alt="Foto Sesudah">
                                <?php else: ?>
                                    <div style="height: 250px; display: flex; align-items: center; justify-content: center; color: #94a3b8;">Belum ada foto sesudah</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Info Detail -->
                    <div class="card animate-fade-in" style="animation-delay: 0.3s;">
                        <div class="card-header" style="border-bottom: 2px solid #f1f5f9;">
                            <h5 style="font-weight: 700; margin: 0;"><i class="fas fa-info-circle me-2 text-primary"></i>Informasi Laporan</h5>
                        </div>
                        <div class="p-4">
                            <div class="detail-info-grid">
                                <div class="detail-info-item">
                                    <label>Deskripsi</label>
                                    <p><?= nl2br(htmlspecialchars($laporan['deskripsi'] ?: '-')) ?></p>
                                </div>
                                <div class="detail-info-item">
                                    <label>Alamat Lokasi</label>
                                    <p><i class="fas fa-map-marker-alt me-1 text-primary"></i><?= htmlspecialchars($laporan['alamat_lokasi']) ?></p>
                                </div>
                                <div class="detail-info-item">
                                    <label>Kelurahan</label>
                                    <p><?= htmlspecialchars($laporan['kelurahan'] ?: '-') ?></p>
                                </div>
                                <div class="detail-info-item">
                                    <label>Kecamatan</label>
                                    <p><?= htmlspecialchars($laporan['kecamatan'] ?: '-') ?></p>
                                </div>
                                <div class="detail-info-item">
                                    <label>Kota</label>
                                    <p><?= htmlspecialchars($laporan['kota'] ?: '-') ?></p>
                                </div>
                                <div class="detail-info-item">
                                    <label>Koordinat</label>
                                    <p><i class="fas fa-globe me-1 text-primary"></i><?= $laporan['latitude'] ?>, <?= $laporan['longitude'] ?></p>
                                </div>
                                <?php if ($laporan['catatan_admin']): ?>
                                    <div class="detail-info-item" style="grid-column: 1 / -1; background: #fef3c7;">
                                        <label><i class="fas fa-comment-dots me-1 text-warning"></i>Catatan Admin</label>
                                        <p><?= nl2br(htmlspecialchars($laporan['catatan_admin'])) ?></p>
                                    </div>
                                <?php endif; ?>
                                <?php if ($laporan['nama_petugas']): ?>
                                    <div class="detail-info-item">
                                        <label>Petugas Penanganan</label>
                                        <p><i class="fas fa-user-hard-hat me-1 text-primary"></i><?= htmlspecialchars($laporan['nama_petugas']) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Peta -->
                    <div class="card mt-4 animate-fade-in" style="animation-delay: 0.4s;">
                        <div class="card-header" style="border-bottom: 2px solid #f1f5f9;">
                            <h5 style="font-weight: 700; margin: 0;"><i class="fas fa-map me-2 text-primary"></i>Lokasi di Peta</h5>
                        </div>
                        <div id="detailMap" style="height: 350px;"></div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Tracking Timeline -->
                    <div class="card animate-fade-in" style="animation-delay: 0.3s;">
                        <div class="card-header" style="border-bottom: 2px solid #f1f5f9;">
                            <h5 style="font-weight: 700; margin: 0;"><i class="fas fa-history me-2 text-primary"></i>Tracking Progress</h5>
                        </div>
                        <div class="p-4">
                            <?php if (empty($tracking)): ?>
                                <div class="text-center py-4">
                                    <span style="font-size: 32px; opacity: 0.3;">📋</span>
                                    <p class="text-gray mt-2" style="font-size: 13px;">Belum ada tracking</p>
                                </div>
                            <?php else: ?>
                                <div class="timeline">
                                    <div class="timeline-item">
                                        <div class="timeline-content">
                                            <div style="font-weight: 700; font-size: 14px;">Laporan Dibuat</div>
                                            <div class="timeline-date"><?= formatTanggal($laporan['created_at']) ?></div>
                                            <div style="font-size: 13px; color: #64748b; margin-top: 4px;">Status: Menunggu</div>
                                        </div>
                                    </div>
                                    <?php foreach ($tracking as $track): ?>
                                        <div class="timeline-item">
                                            <div class="timeline-content">
                                                <div style="font-weight: 700; font-size: 14px;">
                                                    <?= statusIcon($track['status_sesudah']) ?> 
                                                    Status: <?= ucfirst($track['status_sesudah']) ?>
                                                </div>
                                                <div class="timeline-date"><?= formatTanggal($track['created_at']) ?> oleh <?= htmlspecialchars($track['nama_admin']) ?></div>
                                                <?php if ($track['keterangan']): ?>
                                                    <div style="font-size: 13px; color: #64748b; margin-top: 4px;"><?= htmlspecialchars($track['keterangan']) ?></div>
                                                <?php endif; ?>
                                                <?php if ($track['foto_progress']): ?>
                                                    <img src="<?= UPLOAD_URL . $track['foto_progress'] ?>" style="width: 100%; height: 120px; object-fit: cover; border-radius: 8px; margin-top: 8px;" alt="Progress">
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container" id="toastContainer"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/maps.js"></script>
    <script>
        initDetailMap(<?= $laporan['latitude'] ?>, <?= $laporan['longitude'] ?>, 'detailMap', '<?= $laporan['status'] ?>');
    </script>
</body>
</html>
