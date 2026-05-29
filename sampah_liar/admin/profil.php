<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireAdmin();
$admin = getAdminData();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_telepon = trim($_POST['no_telepon'] ?? '');

    if (empty($nama_lengkap) || empty($email)) {
        $error = 'Nama lengkap dan email wajib diisi';
    } else {
        $stmt = $conn->prepare("UPDATE admins SET nama_lengkap = ?, email = ?, no_telepon = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nama_lengkap, $email, $no_telepon, $admin['id']);
        if ($stmt->execute()) {
            $success = 'Profil berhasil diperbarui';
            $_SESSION['admin_nama'] = $nama_lengkap;
            $admin = getAdminData();
        } else {
            $error = 'Gagal memperbarui profil';
        }
    }

    if (!empty($_POST['password_baru'])) {
        if (strlen($_POST['password_baru']) < 6) {
            $error = 'Password baru minimal 6 karakter';
        } else {
            $hash = password_hash($_POST['password_baru'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hash, $admin['id']);
            $stmt->execute();
            $success = 'Password berhasil diperbarui';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Admin - SiPAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="admin-layout">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <div class="logo-icon">🛡️</div>
            <h3>Admin SiPAL</h3>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section-title">Menu Utama</div>
            <a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
            <a href="laporan/index.php" class="nav-link"><i class="fas fa-clipboard-list"></i><span>Laporan</span></a>
            <a href="laporan/peta.php" class="nav-link"><i class="fas fa-map-marked-alt"></i><span>Peta Laporan</span></a>
            <a href="tracking/index.php" class="nav-link"><i class="fas fa-tasks"></i><span>Tracking</span></a>
            <a href="laporan_grafik/index.php" class="nav-link"><i class="fas fa-chart-bar"></i><span>Grafik & Statistik</span></a>
            <div class="nav-section-title">Manajemen</div>
            <a href="users/index.php" class="nav-link"><i class="fas fa-users"></i><span>Data Warga</span></a>
            <a href="petugas/index.php" class="nav-link"><i class="fas fa-user-shield"></i><span>Data Petugas</span></a>
            <div class="nav-section-title">Akun</div>
            <a href="profil.php" class="nav-link active"><i class="fas fa-user-cog"></i><span>Profil</span></a>
            <a href="logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i><span>Keluar</span></a>
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
                <h2>Profil Admin</h2>
            </div>
        </div>
        <div class="admin-content">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card animate-fade-in">
                        <?php if ($error): ?><div class="alert alert-danger m-4 mb-0" style="border-radius: 12px;"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?></div><?php endif; ?>
                        <?php if ($success): ?><div class="alert alert-success m-4 mb-0" style="border-radius: 12px;"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?></div><?php endif; ?>

                        <div class="text-center p-4">
                            <div style="width: 100px; height: 100px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: white; font-size: 40px; font-weight: 700; border: 4px solid var(--primary-light);"><?= strtoupper(substr($admin['nama_lengkap'], 0, 1)) ?></div>
                            <h4 class="mt-3" style="font-weight: 700;"><?= htmlspecialchars($admin['nama_lengkap']) ?></h4>
                            <span class="badge" style="background: var(--primary-light); color: var(--primary-dark); padding: 6px 16px; border-radius: 20px; font-size: 12px; text-transform: uppercase; font-weight: 700;"><?= ucfirst($admin['role']) ?></span>
                        </div>

                        <div class="p-4 pt-0">
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group"><label class="form-label">Nama Lengkap</label><input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($admin['nama_lengkap']) ?>" required></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group"><label class="form-label">Username</label><input type="text" class="form-control" value="<?= htmlspecialchars($admin['username']) ?>" disabled></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin['email']) ?>" required></div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group"><label class="form-label">No. Telepon</label><input type="tel" name="no_telepon" class="form-control" value="<?= htmlspecialchars($admin['no_telepon'] ?: '') ?>"></div>
                                    </div>
                                </div>
                                <hr style="margin: 24px 0;">
                                <h6 style="font-weight: 700; margin-bottom: 16px;"><i class="fas fa-lock me-2 text-primary"></i>Ubah Password</h6>
                                <div class="form-group"><label class="form-label">Password Baru (kosongkan jika tidak ingin mengubah)</label><input type="password" name="password_baru" class="form-control" placeholder="Min. 6 karakter"></div>
                                <div class="d-flex gap-3">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Simpan Perubahan</button>
                                    <a href="dashboard.php" class="btn btn-outline">Batal</a>
                                </div>
                            </form>
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
        function toggleSidebar() { document.getElementById('adminSidebar').classList.toggle('collapsed'); document.getElementById('adminMain').classList.toggle('expanded'); }
        function toggleMobileSidebar() { document.getElementById('adminSidebar').classList.toggle('mobile-open'); document.getElementById('sidebarOverlay').classList.toggle('active'); }
    </script>
</body>
</html>
