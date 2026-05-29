<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireUser();
$user = getUserData();

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Filter status
$filter_status = $_GET['status'] ?? '';
$where = "WHERE user_id = ?";
$params = [$user['id']];
$types = "i";

if ($filter_status) {
    $where .= " AND status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

// Count total
$count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM laporan_sampah $where");
$count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

// Get data
$stmt = $conn->prepare("SELECT * FROM laporan_sampah $where ORDER BY created_at DESC LIMIT ? OFFSET ?");
$types .= "ii";
$params[] = $limit;
$params[] = $offset;
$stmt->bind_param($types, ...$params);
$stmt->execute();
$laporan = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Saya - SiPAL</title>
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
                <a href="laporan_saya.php" class="user-nav-link active"><i class="fas fa-list"></i> Laporan Saya</a>
                <a href="notifikasi.php" class="user-nav-link"><i class="fas fa-bell"></i> Notifikasi</a>
                <a href="profil.php" class="user-nav-link"><i class="fas fa-user"></i> Profil</a>
                <a href="../logout.php" class="user-nav-link" style="color: #ef4444;"><i class="fas fa-sign-out-alt"></i> Keluar</a>
            </div>
        </div>
    </nav>

    <div class="user-dashboard">
        <div class="container">
            <div class="card animate-fade-in">
                <div class="card-header" style="border-bottom: 2px solid #f1f5f9;">
                    <h5 style="font-weight: 700; margin: 0;"><i class="fas fa-list me-2 text-primary"></i>Daftar Laporan Saya</h5>
                    <div class="d-flex gap-2">
                        <form method="GET" action="" class="d-flex gap-2">
                            <select name="status" class="form-control form-select" style="width: auto; min-width: 150px;" onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                <option value="menunggu" <?= $filter_status == 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                                <option value="diproses" <?= $filter_status == 'diproses' ? 'selected' : '' ?>>Diproses</option>
                                <option value="selesai" <?= $filter_status == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                <option value="ditolak" <?= $filter_status == 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                            </select>
                        </form>
                        <a href="laporan_baru.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Baru</a>
                    </div>
                </div>

                <div class="laporan-list p-3">
                    <?php if (empty($laporan)): ?>
                        <div class="text-center py-5">
                            <div style="font-size: 48px; opacity: 0.3;">📋</div>
                            <p class="text-gray mt-3">Belum ada laporan</p>
                            <a href="laporan_baru.php" class="btn btn-primary btn-sm mt-2">Buat Laporan Pertama</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($laporan as $lap): ?>
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
                                        <span><i class="fas fa-exclamation-circle text-gray"></i> <?= ucfirst($lap['tingkat_urgensi']) ?></span>
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

                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page-1 ?>&status=<?= $filter_status ?>"><i class="fas fa-chevron-left"></i></a>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?= $i ?>&status=<?= $filter_status ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?= $page+1 ?>&status=<?= $filter_status ?>"><i class="fas fa-chevron-right"></i></a>
                        <?php endif; ?>
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
