<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireAdmin();
$admin = getAdminData();

$page = max(1, intval($_GET['page'] ?? 1));
$limit = 15;
$offset = ($page - 1) * $limit;

$filter_status = $_GET['status'] ?? '';
$filter_kategori = $_GET['kategori'] ?? '';
$search = trim($_GET['search'] ?? '');

$where = "WHERE 1=1";
$params = [];
$types = "";

if ($filter_status) { $where .= " AND l.status = ?"; $params[] = $filter_status; $types .= "s"; }
if ($filter_kategori) { $where .= " AND l.kategori = ?"; $params[] = $filter_kategori; $types .= "s"; }
if ($search) { $where .= " AND (l.judul LIKE ? OR l.kode_laporan LIKE ? OR u.nama_lengkap LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; $types .= "sss"; }

$count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM laporan_sampah l LEFT JOIN users u ON l.user_id = u.id $where");
if ($types) $count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

$stmt = $conn->prepare("SELECT l.*, u.nama_lengkap as nama_pelapor FROM laporan_sampah l LEFT JOIN users u ON l.user_id = u.id $where ORDER BY l.created_at DESC LIMIT ? OFFSET ?");
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
    <title>Data Laporan - SiPAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
                <h2>Data Laporan</h2>
            </div>
        </div>
        <div class="admin-content">
            <div class="admin-table-card animate-fade-in">
                <div class="admin-table-header">
                    <h4><i class="fas fa-clipboard-list me-2 text-primary"></i>Semua Laporan</h4>
                    <div class="search-filter">
                        <form method="GET" action="" class="d-flex gap-2 flex-wrap">
                            <input type="text" name="search" placeholder="Cari laporan..." value="<?= htmlspecialchars($search) ?>">
                            <select name="status" onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                <option value="menunggu" <?= $filter_status=='menunggu'?'selected':'' ?>>Menunggu</option>
                                <option value="diproses" <?= $filter_status=='diproses'?'selected':'' ?>>Diproses</option>
                                <option value="selesai" <?= $filter_status=='selesai'?'selected':'' ?>>Selesai</option>
                                <option value="ditolak" <?= $filter_status=='ditolak'?'selected':'' ?>>Ditolak</option>
                            </select>
                            <select name="kategori" onchange="this.form.submit()">
                                <option value="">Semua Kategori</option>
                                <option value="sampah_domestik" <?= $filter_kategori=='sampah_domestik'?'selected':'' ?>>Domestik</option>
                                <option value="sampah_organik" <?= $filter_kategori=='sampah_organik'?'selected':'' ?>>Organik</option>
                                <option value="sampah_plastik" <?= $filter_kategori=='sampah_plastik'?'selected':'' ?>>Plastik</option>
                                <option value="sampah_konstruksi" <?= $filter_kategori=='sampah_konstruksi'?'selected':'' ?>>Konstruksi</option>
                                <option value="sampah_elektronik" <?= $filter_kategori=='sampah_elektronik'?'selected':'' ?>>Elektronik</option>
                                <option value="sampah_medis" <?= $filter_kategori=='sampah_medis'?'selected':'' ?>>Medis</option>
                                <option value="tps_ilegal" <?= $filter_kategori=='tps_ilegal'?'selected':'' ?>>TPS Ilegal</option>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
                        </form>
                    </div>
                </div>
                <div class="table-container">
                    <table class="table" id="tableLaporan">
                        <thead>
                            <tr><th>Kode</th><th>Judul</th><th>Pelapor</th><th>Kategori</th><th>Urgensi</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($laporan as $lap): ?>
                            <tr>
                                <td><span style="font-size: 11px; font-weight: 700; color: #64748b;"><?= $lap['kode_laporan'] ?></span></td>
                                <td><?= htmlspecialchars($lap['judul']) ?></td>
                                <td><?= htmlspecialchars($lap['nama_pelapor']) ?></td>
                                <td><span style="font-size: 11px; text-transform: capitalize;"><?= str_replace('_', ' ', $lap['kategori']) ?></span></td>
                                <td><span style="font-size: 11px; font-weight: 600; color: <?= $lap['tingkat_urgensi']=='darurat'?'#ef4444':($lap['tingkat_urgensi']=='tinggi'?'#f59e0b':'#64748b') ?>;"><?= ucfirst($lap['tingkat_urgensi']) ?></span></td>
                                <td><?= statusBadge($lap['status']) ?></td>
                                <td style="font-size: 12px; color: #94a3b8;"><?= formatTanggal($lap['created_at']) ?></td>
                                <td>
                                    <div class="quick-actions">
                                        <a href="detail.php?id=<?= $lap['id'] ?>" class="btn-action btn-view"><i class="fas fa-eye"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?><a href="?page=<?= $page-1 ?>&status=<?= $filter_status ?>&kategori=<?= $filter_kategori ?>&search=<?= urlencode($search) ?>"><i class="fas fa-chevron-left"></i></a><?php endif; ?>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?><a href="?page=<?= $i ?>&status=<?= $filter_status ?>&kategori=<?= $filter_kategori ?>&search=<?= urlencode($search) ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a><?php endfor; ?>
                    <?php if ($page < $total_pages): ?><a href="?page=<?= $page+1 ?>&status=<?= $filter_status ?>&kategori=<?= $filter_kategori ?>&search=<?= urlencode($search) ?>"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
                </div>
                <?php endif; ?>
                <div class="p-3 text-end">
                    <button onclick="exportTableToCSV('tableLaporan', 'laporan_sampah.csv')" class="btn btn-sm btn-outline"><i class="fas fa-download me-1"></i>Export CSV</button>
                </div>
            </div>
        </div>
    </main>

    <div class="toast-container" id="toastContainer"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/main.js"></script>
    <script>
        function toggleSidebar() { document.getElementById('adminSidebar').classList.toggle('collapsed'); document.getElementById('adminMain').classList.toggle('expanded'); }
        function toggleMobileSidebar() { document.getElementById('adminSidebar').classList.toggle('mobile-open'); document.getElementById('sidebarOverlay').classList.toggle('active'); }
    </script>
</body>
</html>
