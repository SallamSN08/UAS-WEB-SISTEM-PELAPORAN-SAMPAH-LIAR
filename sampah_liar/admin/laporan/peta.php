<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireAdmin();
$admin = getAdminData();

$filter_status = $_GET['status'] ?? '';
$where = $filter_status ? "WHERE status = '$filter_status'" : "";

$result = $conn->query("SELECT id, judul, latitude, longitude, status, alamat_lokasi FROM laporan_sampah $where AND latitude IS NOT NULL");
$laporan_peta = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peta Laporan - SiPAL</title>
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
            <a href="index.php" class="nav-link"><i class="fas fa-clipboard-list"></i><span>Laporan</span></a>
            <a href="peta.php" class="nav-link active"><i class="fas fa-map-marked-alt"></i><span>Peta Laporan</span></a>
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
                <h2>Peta Laporan</h2>
            </div>
        </div>
        <div class="admin-content">
            <div class="grafik-filters animate-fade-in">
                <label>Filter Status:</label>
                <form method="GET" action="" class="d-flex gap-2">
                    <select name="status" onchange="this.form.submit()">
                        <option value="">Semua</option>
                        <option value="menunggu" <?= $filter_status=='menunggu'?'selected':'' ?>>Menunggu</option>
                        <option value="diproses" <?= $filter_status=='diproses'?'selected':'' ?>>Diproses</option>
                        <option value="selesai" <?= $filter_status=='selesai'?'selected':'' ?>>Selesai</option>
                        <option value="ditolak" <?= $filter_status=='ditolak'?'selected':'' ?>>Ditolak</option>
                    </select>
                </form>
                <span style="margin-left: auto; font-size: 13px; color: #64748b;"><i class="fas fa-map-marker-alt me-1"></i><?= count($laporan_peta) ?> titik laporan</span>
            </div>
            <div class="map-admin animate-fade-in" id="adminMap"></div>
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
        const laporanData = <?= json_encode($laporan_peta) ?>;
        initAdminMap(laporanData, 'adminMap');
    </script>
</body>
</html>
