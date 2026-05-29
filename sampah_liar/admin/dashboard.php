<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireAdmin();
$admin = getAdminData();
$stats = getStatsAdmin();

// Laporan terbaru
$stmt = $conn->prepare("SELECT l.*, u.nama_lengkap as nama_pelapor FROM laporan_sampah l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC LIMIT 5");
$stmt->execute();
$laporan_terbaru = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Petugas aktif
$petugas = getPetugasAktif();

// Chart data
$laporan_bulan = getLaporanPerBulan();
$laporan_kategori = getLaporanPerKategori();
$laporan_status = getLaporanPerStatus();

// Notifikasi baru (laporan menunggu)
$new_laporan = $stats['menunggu'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SiPAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <div class="logo-icon">🛡️</div>
            <h3>Admin SiPAL</h3>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section-title">Menu Utama</div>
            <a href="dashboard.php" class="nav-link active"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
            <a href="laporan/index.php" class="nav-link"><i class="fas fa-clipboard-list"></i><span>Laporan</span></a>
            <a href="laporan/peta.php" class="nav-link"><i class="fas fa-map-marked-alt"></i><span>Peta Laporan</span></a>
            <a href="tracking/index.php" class="nav-link"><i class="fas fa-tasks"></i><span>Tracking</span></a>
            <a href="laporan_grafik/index.php" class="nav-link"><i class="fas fa-chart-bar"></i><span>Grafik & Statistik</span></a>

            <div class="nav-section-title">Manajemen</div>
            <a href="users/index.php" class="nav-link"><i class="fas fa-users"></i><span>Data Warga</span></a>
            <a href="petugas/index.php" class="nav-link"><i class="fas fa-user-shield"></i><span>Data Petugas</span></a>

            <div class="nav-section-title">Akun</div>
            <a href="profil.php" class="nav-link"><i class="fas fa-user-cog"></i><span>Profil</span></a>
            <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i><span>Keluar</span></a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-info">
                <div style="width: 36px; height: 36px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px;">
                    <?= strtoupper(substr($admin['nama_lengkap'], 0, 1)) ?>
                </div>
                <div>
                    <div class="name"><?= htmlspecialchars($admin['nama_lengkap']) ?></div>
                    <div class="role"><?= ucfirst($admin['role']) ?></div>
                </div>
            </div>
            <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-chevron-left"></i> <span>Collapse</span></button>
        </div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleMobileSidebar()"></div>

    <!-- Main Content -->
    <main class="admin-main" id="adminMain">
        <div class="admin-topbar">
            <div class="topbar-title">
                <button class="mobile-toggle d-lg-none" onclick="toggleMobileSidebar()" style="background: none; border: none; font-size: 24px; color: var(--dark); margin-right: 12px;">☰</button>
                <h2>Dashboard</h2>
                <p>Selamat datang kembali, <?= htmlspecialchars($admin['nama_lengkap']) ?></p>
            </div>
            <div class="topbar-actions">
                <button class="topbar-btn" onclick="location.href='laporan/index.php'">
                    <i class="fas fa-bell"></i>
                    <?php if ($new_laporan > 0): ?><span class="badge-count"><?= $new_laporan ?></span><?php endif; ?>
                </button>
                <button class="topbar-btn" onclick="location.href='profil.php'">
                    <div style="width: 36px; height: 36px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px;">
                        <?= strtoupper(substr($admin['nama_lengkap'], 0, 1)) ?>
                    </div>
                </button>
            </div>
        </div>

        <div class="admin-content">
            <!-- Stats -->
            <div class="stats-row">
                <div class="admin-stat-card animate-fade-in">
                    <div class="icon" style="background: #f0fdf4; color: #059669;"><i class="fas fa-clipboard-list"></i></div>
                    <div class="info"><h3><?= $stats['total'] ?></h3><p>Total Laporan</p></div>
                </div>
                <div class="admin-stat-card animate-fade-in" style="animation-delay: 0.1s;">
                    <div class="icon" style="background: #fef3c7; color: #92400e;"><i class="fas fa-clock"></i></div>
                    <div class="info"><h3><?= $stats['menunggu'] ?></h3><p>Menunggu</p></div>
                </div>
                <div class="admin-stat-card animate-fade-in" style="animation-delay: 0.2s;">
                    <div class="icon" style="background: #dbeafe; color: #1e40af;"><i class="fas fa-spinner"></i></div>
                    <div class="info"><h3><?= $stats['diproses'] ?></h3><p>Diproses</p></div>
                </div>
                <div class="admin-stat-card animate-fade-in" style="animation-delay: 0.3s;">
                    <div class="icon" style="background: #dcfce7; color: #166534;"><i class="fas fa-check-circle"></i></div>
                    <div class="info"><h3><?= $stats['selesai'] ?></h3><p>Selesai</p></div>
                </div>
                <div class="admin-stat-card animate-fade-in" style="animation-delay: 0.4s;">
                    <div class="icon" style="background: #fee2e2; color: #991b1b;"><i class="fas fa-times-circle"></i></div>
                    <div class="info"><h3><?= $stats['ditolak'] ?></h3><p>Ditolak</p></div>
                </div>
            </div>

            <!-- Charts -->
            <div class="charts-grid">
                <div class="chart-card animate-fade-in" style="animation-delay: 0.2s;">
                    <h4><i class="fas fa-chart-bar text-primary"></i> Laporan per Bulan</h4>
                    <div class="chart-container"><canvas id="chartBulan"></canvas></div>
                </div>
                <div class="chart-card animate-fade-in" style="animation-delay: 0.3s;">
                    <h4><i class="fas fa-chart-pie text-primary"></i> Distribusi Status</h4>
                    <div class="chart-container"><canvas id="chartStatus"></canvas></div>
                </div>
                <div class="chart-card animate-fade-in" style="animation-delay: 0.4s;">
                    <h4><i class="fas fa-chart-line text-primary"></i> Tren Mingguan</h4>
                    <div class="chart-container"><canvas id="chartTrend"></canvas></div>
                </div>
                <div class="chart-card animate-fade-in" style="animation-delay: 0.5s;">
                    <h4><i class="fas fa-tags text-primary"></i> Laporan per Kategori</h4>
                    <div class="chart-container"><canvas id="chartKategori"></canvas></div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Laporan Terbaru -->
                <div class="col-lg-8 animate-fade-in" style="animation-delay: 0.4s;">
                    <div class="admin-table-card">
                        <div class="admin-table-header">
                            <h4><i class="fas fa-clock-rotate-left me-2 text-primary"></i>Laporan Terbaru</h4>
                            <a href="laporan/index.php" class="btn btn-sm btn-outline">Lihat Semua</a>
                        </div>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Judul</th>
                                        <th>Pelapor</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($laporan_terbaru as $lap): ?>
                                    <tr>
                                        <td><span style="font-size: 12px; font-weight: 600; color: #64748b;"><?= $lap['kode_laporan'] ?></span></td>
                                        <td><?= htmlspecialchars($lap['judul']) ?></td>
                                        <td><?= htmlspecialchars($lap['nama_pelapor']) ?></td>
                                        <td><?= statusBadge($lap['status']) ?></td>
                                        <td>
                                            <div class="quick-actions">
                                                <a href="laporan/detail.php?id=<?= $lap['id'] ?>" class="btn-action btn-view"><i class="fas fa-eye"></i></a>
                                                <?php if ($lap['status'] == 'menunggu'): ?>
                                                    <a href="laporan/detail.php?id=<?= $lap['id'] ?>&action=proses" class="btn-action btn-process"><i class="fas fa-play"></i></a>
                                                <?php elseif ($lap['status'] == 'diproses'): ?>
                                                    <a href="laporan/detail.php?id=<?= $lap['id'] ?>&action=selesai" class="btn-action btn-done"><i class="fas fa-check"></i></a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Petugas Aktif -->
                <div class="col-lg-4 animate-fade-in" style="animation-delay: 0.5s;">
                    <div class="admin-table-card">
                        <div class="admin-table-header">
                            <h4><i class="fas fa-user-hard-hat me-2 text-primary"></i>Petugas Aktif</h4>
                            <a href="petugas/index.php" class="btn btn-sm btn-outline">Lihat Semua</a>
                        </div>
                        <div class="p-3">
                            <?php foreach ($petugas as $p): ?>
                                <div class="petugas-card mb-2">
                                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px;">
                                        <?= strtoupper(substr($p['nama_lengkap'], 0, 1)) ?>
                                    </div>
                                    <div class="info">
                                        <h5><?= htmlspecialchars($p['nama_lengkap']) ?></h5>
                                        <p><?= $p['jumlah_laporan'] ?> laporan ditangani</p>
                                    </div>
                                    <span class="badge-role badge-<?= $p['role'] ?>"><?= ucfirst($p['role']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="toast-container" id="toastContainer"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // Sidebar toggle
        function toggleSidebar() {
            document.getElementById('adminSidebar').classList.toggle('collapsed');
            document.getElementById('adminMain').classList.toggle('expanded');
        }
        function toggleMobileSidebar() {
            document.getElementById('adminSidebar').classList.toggle('mobile-open');
            document.getElementById('sidebarOverlay').classList.toggle('active');
        }

        // Charts
        const bulanLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const bulanData = <?= json_encode($laporan_bulan) ?>;

        new Chart(document.getElementById('chartBulan'), {
            type: 'bar',
            data: {
                labels: bulanLabels,
                datasets: [{
                    label: 'Jumlah Laporan',
                    data: bulanData,
                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                    borderColor: '#10b981',
                    borderWidth: 2,
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

        const statusData = <?= json_encode(array_values($laporan_status)) ?>;
        const statusLabels = ['Menunggu', 'Diproses', 'Selesai', 'Ditolak'];
        const statusColors = ['#f59e0b', '#3b82f6', '#10b981', '#ef4444'];

        new Chart(document.getElementById('chartStatus'), {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: statusColors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // Trend mingguan (dummy data)
        new Chart(document.getElementById('chartTrend'), {
            type: 'line',
            data: {
                labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                datasets: [{
                    label: 'Laporan Minggu Ini',
                    data: [3, 5, 2, 8, 4, 6, 2],
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
                scales: { y: { beginAtZero: true } }
            }
        });

        const kategoriData = <?= json_encode($laporan_kategori) ?>;
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
    </script>
</body>
</html>
