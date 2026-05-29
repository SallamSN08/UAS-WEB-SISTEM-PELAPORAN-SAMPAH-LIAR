<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireAdmin();
$admin = getAdminData();

$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$filter_status = $_GET['status'] ?? '';
$filter_petugas = $_GET['petugas'] ?? '';

$where = "WHERE 1=1";
$params = [];
$types = "";

if ($filter_status) { $where .= " AND l.status = ?"; $params[] = $filter_status; $types .= "s"; }
if ($filter_petugas) { $where .= " AND l.petugas_id = ?"; $params[] = $filter_petugas; $types .= "i"; }

$count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM laporan_sampah l $where");
if ($types) $count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

$stmt = $conn->prepare("SELECT l.*, u.nama_lengkap as nama_pelapor, a.nama_lengkap as nama_petugas FROM laporan_sampah l LEFT JOIN users u ON l.user_id = u.id LEFT JOIN admins a ON l.petugas_id = a.id $where ORDER BY l.updated_at DESC LIMIT ? OFFSET ?");
$types .= "ii";
$params[] = $limit;
$params[] = $offset;
$stmt->bind_param($types, ...$params);
$stmt->execute();
$laporan = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$petugas_list = $conn->query("SELECT id, nama_lengkap FROM admins WHERE role IN ('admin', 'petugas') ORDER BY nama_lengkap");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Pekerjaan - SiPAL</title>
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
            <a href="../laporan/index.php" class="nav-link"><i class="fas fa-clipboard-list"></i><span>Laporan</span></a>
            <a href="../laporan/peta.php" class="nav-link"><i class="fas fa-map-marked-alt"></i><span>Peta Laporan</span></a>
            <a href="index.php" class="nav-link active"><i class="fas fa-tasks"></i><span>Tracking</span></a>
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
                <h2>Tracking Pekerjaan</h2>
            </div>
        </div>
        <div class="admin-content">
            <div class="admin-table-card animate-fade-in">
                <div class="admin-table-header">
                    <h4><i class="fas fa-tasks me-2 text-primary"></i>Semua Pekerjaan</h4>
                    <div class="search-filter">
                        <form method="GET" action="" class="d-flex gap-2 flex-wrap">
                            <select name="status" onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                <option value="menunggu" <?= $filter_status=='menunggu'?'selected':'' ?>>Menunggu</option>
                                <option value="diproses" <?= $filter_status=='diproses'?'selected':'' ?>>Diproses</option>
                                <option value="selesai" <?= $filter_status=='selesai'?'selected':'' ?>>Selesai</option>
                                <option value="ditolak" <?= $filter_status=='ditolak'?'selected':'' ?>>Ditolak</option>
                            </select>
                            <select name="petugas" onchange="this.form.submit()">
                                <option value="">Semua Petugas</option>
                                <?php while ($p = $petugas_list->fetch_assoc()): ?>
                                    <option value="<?= $p['id'] ?>" <?= $filter_petugas==$p['id']?'selected':'' ?>><?= htmlspecialchars($p['nama_lengkap']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </form>
                    </div>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr><th>Kode</th><th>Judul</th><th>Pelapor</th><th>Petugas</th><th>Status</th><th>Update</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($laporan as $lap): ?>
                            <tr>
                                <td><span style="font-size: 11px; font-weight: 700; color: #64748b;"><?= $lap['kode_laporan'] ?></span></td>
                                <td><?= htmlspecialchars($lap['judul']) ?></td>
                                <td><?= htmlspecialchars($lap['nama_pelapor']) ?></td>
                                <td><?= htmlspecialchars($lap['nama_petugas'] ?: '-') ?></td>
                                <td><?= statusBadge($lap['status']) ?></td>
                                <td style="font-size: 12px; color: #94a3b8;"><?= $lap['updated_at'] != $lap['created_at'] ? formatTanggal($lap['updated_at']) : '-' ?></td>
                                <td><a href="../laporan/detail.php?id=<?= $lap['id'] ?>" class="btn-action btn-view"><i class="fas fa-eye"></i></a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?><a href="?page=<?= $page-1 ?>&status=<?= $filter_status ?>&petugas=<?= $filter_petugas ?>"><i class="fas fa-chevron-left"></i></a><?php endif; ?>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?><a href="?page=<?= $i ?>&status=<?= $filter_status ?>&petugas=<?= $filter_petugas ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a><?php endfor; ?>
                    <?php if ($page < $total_pages): ?><a href="?page=<?= $page+1 ?>&status=<?= $filter_status ?>&petugas=<?= $filter_petugas ?>"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
                </div>
                <?php endif; ?>
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
