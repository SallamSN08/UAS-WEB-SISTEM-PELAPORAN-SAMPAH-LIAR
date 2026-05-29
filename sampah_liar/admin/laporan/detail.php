<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireAdmin();
$admin = getAdminData();

$id = intval($_GET['id'] ?? 0);
$laporan = getLaporanById($id);

if (!$laporan) {
    setFlash('error', 'Laporan tidak ditemukan');
    redirect(BASE_URL . '/admin/laporan/index.php');
}

$tracking = getTrackingByLaporan($id);

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $status_baru = $_POST['status_baru'] ?? '';
    $catatan = trim($_POST['catatan'] ?? '');
    $petugas_id = !empty($_POST['petugas_id']) ? intval($_POST['petugas_id']) : $admin['id'];

    $foto_progress = null;
    if (isset($_FILES['foto_progress']) && $_FILES['foto_progress']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFoto($_FILES['foto_progress'], 'progress');
        if ($upload['success']) {
            $foto_progress = $upload['filename'];
        }
    }

    updateStatusLaporan($id, $status_baru, $catatan, $petugas_id, $foto_progress);

    // Update foto sesudah jika status selesai
    if ($status_baru == 'selesai' && isset($_FILES['foto_sesudah']) && $_FILES['foto_sesudah']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadFoto($_FILES['foto_sesudah'], 'sesudah');
        if ($upload['success']) {
            $stmt = $conn->prepare("UPDATE laporan_sampah SET foto_sesudah = ? WHERE id = ?");
            $stmt->bind_param("si", $upload['filename'], $id);
            $stmt->execute();
        }
    }

    setFlash('success', 'Status laporan berhasil diperbarui');
    redirect(BASE_URL . '/admin/laporan/detail.php?id=' . $id);
}

// Get all petugas for dropdown
$petugas_list = $conn->query("SELECT id, nama_lengkap, role FROM admins WHERE role IN ('admin', 'petugas') ORDER BY nama_lengkap");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Laporan - SiPAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body class="admin-layout">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header"><div class="logo-icon">🛡️</div><h3>Admin SiPAL</h3></div>
        <nav class="sidebar-nav">
            <div class="nav-section-title">Menu Utama</div>
            <a href="../dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
            <a href="index.php" class="nav-link active"><i class="fas fa-clipboard-list"></i><span>Laporan</span></a>
            <a href="peta.php" class="nav-link"><i class="fas fa-map-marked-alt"></i><span>Peta Laporan</span></a>
            <a href="../tracking/index.php" class="nav-link"><i class="fas fa-tasks"></i><span>Tracking</span></a>
            <a href="../laporan_grafik/index.php" class="nav-link"><i class="fas fa-chart-bar"></i><span>Grafik & Statistik</span></a>
            <div class="nav-section-title">Manajemen</div>
            <a href="../users/index.php" class="nav-link"><i class="fas fa-users"></i><span>Data Warga</span></a>
            <a href="../petugas/index.php" class="nav-link"><i class="fas fa-user-shield"></i><span>Data Petugas</span></a>
            <div class="nav-section-title">Akun</div>
            <a href="../profil.php" class="nav-link"><i class="fas fa-user-cog"></i><span>Profil</span></a>
            <a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i><span>Keluar</span></a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info">
                <div style="width: 36px; height: 36px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px;"><?= strtoupper(substr($admin['nama_lengkap'], 0, 1)) ?></div>
                <div><div class="name"><?= htmlspecialchars($admin['nama_lengkap']) ?></div><div class="role"><?= ucfirst($admin['role']) ?></div></div>
            </div>
            <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-chevron-left"></i> <span>Collapse</span></button>
        </div>
    </aside>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleMobileSidebar()"></div>

    <main class="admin-main" id="adminMain">
        <div class="admin-topbar">
            <div class="topbar-title">
                <button class="mobile-toggle d-lg-none" onclick="toggleMobileSidebar()" style="background: none; border: none; font-size: 24px; color: var(--dark); margin-right: 12px;">☰</button>
                <h2>Detail Laporan</h2>
            </div>
        </div>
        <div class="admin-content">
            <a href="index.php" class="btn btn-outline btn-sm mb-3"><i class="fas fa-arrow-left me-2"></i>Kembali</a>

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
                        <p style="font-size: 13px; color: #94a3b8;"><i class="fas fa-user me-1"></i><?= htmlspecialchars($laporan['nama_pelapor']) ?></p>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-8">
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

                    <div class="card animate-fade-in" style="animation-delay: 0.3s;">
                        <div class="card-header" style="border-bottom: 2px solid #f1f5f9;">
                            <h5 style="font-weight: 700; margin: 0;"><i class="fas fa-info-circle me-2 text-primary"></i>Informasi Laporan</h5>
                        </div>
                        <div class="p-4">
                            <div class="detail-info-grid">
                                <div class="detail-info-item"><label>Deskripsi</label><p><?= nl2br(htmlspecialchars($laporan['deskripsi'] ?: '-')) ?></p></div>
                                <div class="detail-info-item"><label>Alamat Lokasi</label><p><i class="fas fa-map-marker-alt me-1 text-primary"></i><?= htmlspecialchars($laporan['alamat_lokasi']) ?></p></div>
                                <div class="detail-info-item"><label>Kelurahan</label><p><?= htmlspecialchars($laporan['kelurahan'] ?: '-') ?></p></div>
                                <div class="detail-info-item"><label>Kecamatan</label><p><?= htmlspecialchars($laporan['kecamatan'] ?: '-') ?></p></div>
                                <div class="detail-info-item"><label>Kota</label><p><?= htmlspecialchars($laporan['kota'] ?: '-') ?></p></div>
                                <div class="detail-info-item"><label>Koordinat</label><p><i class="fas fa-globe me-1 text-primary"></i><?= $laporan['latitude'] ?>, <?= $laporan['longitude'] ?></p></div>
                                <?php if ($laporan['catatan_admin']): ?>
                                    <div class="detail-info-item" style="grid-column: 1 / -1; background: #fef3c7;">
                                        <label><i class="fas fa-comment-dots me-1 text-warning"></i>Catatan Admin</label>
                                        <p><?= nl2br(htmlspecialchars($laporan['catatan_admin'])) ?></p>
                                    </div>
                                <?php endif; ?>
                                <?php if ($laporan['nama_petugas']): ?>
                                    <div class="detail-info-item"><label>Petugas Penanganan</label><p><i class="fas fa-user-hard-hat me-1 text-primary"></i><?= htmlspecialchars($laporan['nama_petugas']) ?></p></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4 animate-fade-in" style="animation-delay: 0.4s;">
                        <div class="card-header" style="border-bottom: 2px solid #f1f5f9;">
                            <h5 style="font-weight: 700; margin: 0;"><i class="fas fa-map me-2 text-primary"></i>Lokasi di Peta</h5>
                        </div>
                        <div id="detailMap" style="height: 350px;"></div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Update Status Form -->
                    <div class="update-form mb-4 animate-fade-in" style="animation-delay: 0.3s;">
                        <h4><i class="fas fa-edit me-2 text-primary"></i>Update Status</h4>
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="form-group">
                                <label class="form-label">Status Baru</label>
                                <select name="status_baru" class="form-control form-select" required>
                                    <option value="menunggu" <?= $laporan['status']=='menunggu'?'selected':'' ?>>Menunggu</option>
                                    <option value="diproses" <?= $laporan['status']=='diproses'?'selected':'' ?>>Diproses</option>
                                    <option value="selesai" <?= $laporan['status']=='selesai'?'selected':'' ?>>Selesai</option>
                                    <option value="ditolak" <?= $laporan['status']=='ditolak'?'selected':'' ?>>Ditolak</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Petugas</label>
                                <select name="petugas_id" class="form-control form-select">
                                    <option value="">Pilih Petugas</option>
                                    <?php while ($p = $petugas_list->fetch_assoc()): ?>
                                        <option value="<?= $p['id'] ?>" <?= $laporan['petugas_id']==$p['id']?'selected':'' ?>><?= htmlspecialchars($p['nama_lengkap']) ?> (<?= ucfirst($p['role']) ?>)</option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Keterangan / Catatan</label>
                                <textarea name="catatan" class="form-control" rows="3" placeholder="Tambahkan keterangan..."></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Foto Progress</label>
                                <input type="file" name="foto_progress" class="form-control" accept="image/jpeg,image/jpg,image/png">
                            </div>
                            <?php if ($laporan['status'] != 'selesai'): ?>
                                <div class="form-group">
                                    <label class="form-label">Foto Sesudah (jika status Selesai)</label>
                                    <input type="file" name="foto_sesudah" class="form-control" accept="image/jpeg,image/jpg,image/png">
                                </div>
                            <?php endif; ?>
                            <button type="submit" name="update_status" class="btn btn-primary w-100"><i class="fas fa-save me-2"></i>Simpan Update</button>
                        </form>
                    </div>

                    <!-- Tracking Timeline -->
                    <div class="card animate-fade-in" style="animation-delay: 0.4s;">
                        <div class="card-header" style="border-bottom: 2px solid #f1f5f9;">
                            <h5 style="font-weight: 700; margin: 0;"><i class="fas fa-history me-2 text-primary"></i>Tracking Progress</h5>
                        </div>
                        <div class="p-4">
                            <?php if (empty($tracking)): ?>
                                <div class="text-center py-4"><span style="font-size: 32px; opacity: 0.3;">📋</span><p class="text-gray mt-2" style="font-size: 13px;">Belum ada tracking</p></div>
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
                                                <div style="font-weight: 700; font-size: 14px;"><?= statusIcon($track['status_sesudah']) ?> Status: <?= ucfirst($track['status_sesudah']) ?></div>
                                                <div class="timeline-date"><?= formatTanggal($track['created_at']) ?> oleh <?= htmlspecialchars($track['nama_admin']) ?></div>
                                                <?php if ($track['keterangan']): ?><div style="font-size: 13px; color: #64748b; margin-top: 4px;"><?= htmlspecialchars($track['keterangan']) ?></div><?php endif; ?>
                                                <?php if ($track['foto_progress']): ?><img src="<?= UPLOAD_URL . $track['foto_progress'] ?>" style="width: 100%; height: 120px; object-fit: cover; border-radius: 8px; margin-top: 8px;" alt="Progress"><?php endif; ?>
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
    </main>

    <div class="toast-container" id="toastContainer"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="../../assets/js/main.js"></script>
    <script src="../../assets/js/maps.js"></script>
    <script>
        function toggleSidebar() { document.getElementById('adminSidebar').classList.toggle('collapsed'); document.getElementById('adminMain').classList.toggle('expanded'); }
        function toggleMobileSidebar() { document.getElementById('adminSidebar').classList.toggle('mobile-open'); document.getElementById('sidebarOverlay').classList.toggle('active'); }
        initDetailMap(<?= $laporan['latitude'] ?>, <?= $laporan['longitude'] ?>, 'detailMap', '<?= $laporan['status'] ?>');
    </script>
</body>
</html>
