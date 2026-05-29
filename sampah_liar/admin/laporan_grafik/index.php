<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireAdmin();
$admin = getAdminData();

// Filters
$date_from = $_GET['date_from'] ?? date('Y-m-01');
$date_to = $_GET['date_to'] ?? date('Y-m-d');
$filter_kecamatan = $_GET['kecamatan'] ?? '';
$filter_kategori = $_GET['kategori'] ?? '';
$filter_status = $_GET['status'] ?? '';

$where = "WHERE DATE(l.created_at) BETWEEN ? AND ?";
$params = [$date_from, $date_to];
$types = "ss";

if ($filter_kecamatan) { $where .= " AND l.kecamatan = ?"; $params[] = $filter_kecamatan; $types .= "s"; }
if ($filter_kategori) { $where .= " AND l.kategori = ?"; $params[] = $filter_kategori; $types .= "s"; }
if ($filter_status) { $where .= " AND l.status = ?"; $params[] = $filter_status; $types .= "s"; }

// Stats
$stats = [];
$statuses = ['menunggu', 'diproses', 'selesai', 'ditolak'];
foreach ($statuses as $s) {
    $stmt = $conn->prepare("SELECT COUNT(*) as jumlah FROM laporan_sampah l $where AND l.status = ?");
    $stmt->bind_param($types . "s", ...array_merge($params, [$s]));
    $stmt->execute();
    $stats[$s] = $stmt->get_result()->fetch_assoc()['jumlah'];
}
$stats['total'] = array_sum($stats);

// Per kategori
$stmt = $conn->prepare("SELECT l.kategori, COUNT(*) as jumlah FROM laporan_sampah l $where GROUP BY l.kategori");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$kategori_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Per kecamatan
$stmt = $conn->prepare("SELECT l.kecamatan, COUNT(*) as jumlah FROM laporan_sampah l $where AND l.kecamatan IS NOT NULL AND l.kecamatan != '' GROUP BY l.kecamatan ORDER BY jumlah DESC LIMIT 5");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$kecamatan_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Tren harian
$stmt = $conn->prepare("SELECT DATE(l.created_at) as tanggal, COUNT(*) as jumlah FROM laporan_sampah l $where GROUP BY DATE(l.created_at) ORDER BY tanggal");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$tren_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Top lokasi
$top_lokasi = getTopLokasi(5);

// Rata-rata penyelesaian
$rata_rata = rataRataPenyelesaian();

// Bulan ini vs bulan lalu
$stmt = $conn->prepare("SELECT COUNT(*) as jumlah FROM laporan_sampah WHERE MONTH(created_at) = MONTH(CURRENT_DATE) AND YEAR(created_at) = YEAR(CURRENT_DATE)");
$stmt->execute();
$bulan_ini = $stmt->get_result()->fetch_assoc()['jumlah'];

$stmt = $conn->prepare("SELECT COUNT(*) as jumlah FROM laporan_sampah WHERE MONTH(created_at) = MONTH(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH))");
$stmt->execute();
$bulan_lalu = $stmt->get_result()->fetch_assoc()['jumlah'];

// List kecamatan for filter
$kecamatan_list = $conn->query("SELECT DISTINCT kecamatan FROM laporan_sampah WHERE kecamatan IS NOT NULL AND kecamatan != '' ORDER BY kecamatan");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grafik & Statistik - SiPAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <a href="index.php" class="nav-link active"><i class="fas fa-chart-bar"></i><span>Grafik & Statistik</span></a>
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
                <h2>Grafik & Statistik</h2>
            </div>
        </div>
        <div class="admin-content">
            <!-- Filters -->
            <div class="grafik-filters animate-fade-in">
                <form method="GET" action="" class="d-flex gap-2 flex-wrap align-items-center w-100">
                    <label>Dari:</label>
                    <input type="date" name="date_from" value="<?= $date_from ?>">
                    <label>Sampai:</label>
                    <input type="date" name="date_to" value="<?= $date_to ?>">
                    <select name="kecamatan">
                        <option value="">Semua Kecamatan</option>
                        <?php while ($k = $kecamatan_list->fetch_assoc()): ?>
                            <option value="<?= $k['kecamatan'] ?>" <?= $filter_kecamatan==$k['kecamatan']?'selected':'' ?>><?= $k['kecamatan'] ?></option>
                        <?php endwhile; ?>
                    </select>
                    <select name="kategori">
                        <option value="">Semua Kategori</option>
                        <option value="sampah_domestik" <?= $filter_kategori=='sampah_domestik'?'selected':'' ?>>Domestik</option>
                        <option value="sampah_organik" <?= $filter_kategori=='sampah_organik'?'selected':'' ?>>Organik</option>
                        <option value="sampah_plastik" <?= $filter_kategori=='sampah_plastik'?'selected':'' ?>>Plastik</option>
                        <option value="sampah_konstruksi" <?= $filter_kategori=='sampah_konstruksi'?'selected':'' ?>>Konstruksi</option>
                        <option value="sampah_elektronik" <?= $filter_kategori=='sampah_elektronik'?'selected':'' ?>>Elektronik</option>
                        <option value="sampah_medis" <?= $filter_kategori=='sampah_medis'?'selected':'' ?>>Medis</option>
                        <option value="tps_ilegal" <?= $filter_kategori=='tps_ilegal'?'selected':'' ?>>TPS Ilegal</option>
                    </select>
                    <select name="status">
                        <option value="">Semua Status</option>
                        <option value="menunggu" <?= $filter_status=='menunggu'?'selected':'' ?>>Menunggu</option>
                        <option value="diproses" <?= $filter_status=='diproses'?'selected':'' ?>>Diproses</option>
                        <option value="selesai" <?= $filter_status=='selesai'?'selected':'' ?>>Selesai</option>
                        <option value="ditolak" <?= $filter_status=='ditolak'?'selected':'' ?>>Ditolak</option>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i>Filter</button>
                    <a href="index.php" class="btn btn-outline btn-sm">Reset</a>
                </form>
            </div>

            <!-- Summary Stats -->
            <div class="stats-row mb-4">
                <div class="admin-stat-card animate-fade-in">
                    <div class="icon" style="background: #f0fdf4; color: #059669;"><i class="fas fa-clipboard-list"></i></div>
                    <div class="info"><h3><?= $stats['total'] ?></h3><p>Total Laporan</p></div>
                </div>
                <div class="admin-stat-card animate-fade-in" style="animation-delay: 0.1s;">
                    <div class="icon" style="background: #dcfce7; color: #166534;"><i class="fas fa-check-circle"></i></div>
                    <div class="info"><h3><?= $stats['selesai'] ?></h3><p>Selesai</p></div>
                </div>
                <div class="admin-stat-card animate-fade-in" style="animation-delay: 0.2s;">
                    <div class="icon" style="background: #dbeafe; color: #1e40af;"><i class="fas fa-clock"></i></div>
                    <div class="info"><h3><?= $rata_rata ?> jam</h3><p>Rata-rata Penyelesaian</p></div>
                </div>
                <div class="admin-stat-card animate-fade-in" style="animation-delay: 0.3s;">
                    <div class="icon" style="background: #fef3c7; color: #92400e;"><i class="fas fa-chart-line"></i></div>
                    <div class="info"><h3><?= $bulan_ini ?></h3><p>Bulan Ini <?= $bulan_lalu > 0 ? '(' . ($bulan_ini > $bulan_lalu ? '+' : '') . round((($bulan_ini - $bulan_lalu) / $bulan_lalu) * 100) . '%)' : '' ?></p></div>
                </div>
            </div>

            <!-- Charts -->
            <div class="charts-grid">
                <div class="chart-card animate-fade-in" style="animation-delay: 0.2s;">
                    <h4><i class="fas fa-chart-bar text-primary"></i> Laporan per Kategori</h4>
                    <div class="chart-container"><canvas id="chartKategori"></canvas></div>
                </div>
                <div class="chart-card animate-fade-in" style="animation-delay: 0.3s;">
                    <h4><i class="fas fa-chart-pie text-primary"></i> Distribusi Status</h4>
                    <div class="chart-container"><canvas id="chartStatus"></canvas></div>
                </div>
                <div class="chart-card animate-fade-in" style="animation-delay: 0.4s; grid-column: 1 / -1;">
                    <h4><i class="fas fa-chart-line text-primary"></i> Tren Harian</h4>
                    <div class="chart-container"><canvas id="chartTren"></canvas></div>
                </div>
            </div>

            <!-- Tables -->
            <div class="row g-4 mt-2">
                <div class="col-lg-6 animate-fade-in" style="animation-delay: 0.4s;">
                    <div class="admin-table-card">
                        <div class="admin-table-header">
                            <h4><i class="fas fa-map-marker-alt me-2 text-primary"></i>Top 5 Lokasi</h4>
                        </div>
                        <div class="table-container">
                            <table class="table">
                                <thead><tr><th>Lokasi</th><th>Kecamatan</th><th>Jumlah</th></tr></thead>
                                <tbody>
                                    <?php foreach ($top_lokasi as $loc): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($loc['alamat_lokasi']) ?></td>
                                        <td><?= htmlspecialchars($loc['kecamatan']) ?></td>
                                        <td><span style="font-weight: 700; color: var(--primary-dark);"><?= $loc['jumlah'] ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 animate-fade-in" style="animation-delay: 0.5s;">
                    <div class="admin-table-card">
                        <div class="admin-table-header">
                            <h4><i class="fas fa-building me-2 text-primary"></i>Top 5 Kecamatan</h4>
                        </div>
                        <div class="table-container">
                            <table class="table">
                                <thead><tr><th>Kecamatan</th><th>Jumlah Laporan</th></tr></thead>
                                <tbody>
                                    <?php foreach ($kecamatan_data as $k): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($k['kecamatan']) ?></td>
                                        <td><span style="font-weight: 700; color: var(--primary-dark);"><?= $k['jumlah'] ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
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

        const kategoriData = <?= json_encode($kategori_data) ?>;
        new Chart(document.getElementById('chartKategori'), {
            type: 'bar',
            data: {
                labels: kategoriData.map(k => k.kategori.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())),
                datasets: [{
                    label: 'Jumlah',
                    data: kategoriData.map(k => k.jumlah),
                    backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16'],
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });

        const statusData = <?= json_encode([$stats['menunggu'], $stats['diproses'], $stats['selesai'], $stats['ditolak']]) ?>;
        new Chart(document.getElementById('chartStatus'), {
            type: 'doughnut',
            data: {
                labels: ['Menunggu', 'Diproses', 'Selesai', 'Ditolak'],
                datasets: [{
                    data: statusData,
                    backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        const trenData = <?= json_encode($tren_data) ?>;
        new Chart(document.getElementById('chartTren'), {
            type: 'line',
            data: {
                labels: trenData.map(t => t.tanggal),
                datasets: [{
                    label: 'Jumlah Laporan',
                    data: trenData.map(t => t.jumlah),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointBackgroundColor: '#10b981'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    </script>
</body>
</html>
