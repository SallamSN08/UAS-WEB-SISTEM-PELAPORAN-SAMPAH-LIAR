<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireAdminRole(['superadmin', 'admin']);
$admin = getAdminData();

$page = max(1, intval($_GET['page'] ?? 1));
$limit = 15;
$offset = ($page - 1) * $limit;

$count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM admins WHERE role IN ('admin', 'petugas', 'superadmin')");
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

$stmt = $conn->prepare("SELECT a.*, COUNT(l.id) as jumlah_laporan FROM admins a LEFT JOIN laporan_sampah l ON a.id = l.petugas_id WHERE a.role IN ('admin', 'petugas', 'superadmin') GROUP BY a.id ORDER BY a.role DESC, a.nama_lengkap LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$petugas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Petugas - SiPAL</title>
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
            <a href="../tracking/index.php" class="nav-link"><i class="fas fa-tasks"></i><span>Tracking</span></a>
            <a href="../laporan_grafik/index.php" class="nav-link"><i class="fas fa-chart-bar"></i><span>Grafik & Statistik</span></a>
            <div class="nav-section-title">Manajemen</div>
            <a href="../users/index.php" class="nav-link"><i class="fas fa-users"></i><span>Data Warga</span></a>
            <a href="index.php" class="nav-link active"><i class="fas fa-user-shield"></i><span>Data Petugas</span></a>
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
                <h2>Data Petugas</h2>
            </div>
        </div>
        <div class="admin-content">
            <div class="admin-table-card animate-fade-in">
                <div class="admin-table-header">
                    <h4><i class="fas fa-user-shield me-2 text-primary"></i>Daftar Petugas & Admin</h4>
                </div>
                <div class="table-container">
                    <table class="table" id="tablePetugas">
                        <thead>
                            <tr><th>No</th><th>Nama</th><th>Username</th><th>Email</th><th>Role</th><th>Telepon</th><th>Laporan Ditangani</th></tr>
                        </thead>
                        <tbody>
                            <?php $no = $offset + 1; foreach ($petugas as $p): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong><?= htmlspecialchars($p['nama_lengkap']) ?></strong></td>
                                <td><?= htmlspecialchars($p['username']) ?></td>
                                <td><?= htmlspecialchars($p['email']) ?></td>
                                <td><span class="badge-role badge-<?= $p['role'] ?>"><?= ucfirst($p['role']) ?></span></td>
                                <td><?= htmlspecialchars($p['no_telepon'] ?: '-') ?></td>
                                <td><span style="font-weight: 700; color: var(--primary-dark);"><?= $p['jumlah_laporan'] ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?><a href="?page=<?= $page-1 ?>"><i class="fas fa-chevron-left"></i></a><?php endif; ?>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?><a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a><?php endfor; ?>
                    <?php if ($page < $total_pages): ?><a href="?page=<?= $page+1 ?>"><i class="fas fa-chevron-right"></i></a><?php endif; ?>
                </div>
                <?php endif; ?>
                <div class="p-3 text-end">
                    <button onclick="exportTableToCSV('tablePetugas', 'data_petugas.csv')" class="btn btn-sm btn-outline"><i class="fas fa-download me-1"></i>Export CSV</button>
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
