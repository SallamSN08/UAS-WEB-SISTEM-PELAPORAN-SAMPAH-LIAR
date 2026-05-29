<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireUser();
$user = getUserData();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_telepon = trim($_POST['no_telepon'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    if (empty($nama_lengkap) || empty($email)) {
        $error = 'Nama lengkap dan email wajib diisi';
    } else {
        $stmt = $conn->prepare("UPDATE users SET nama_lengkap = ?, email = ?, no_telepon = ?, alamat = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $nama_lengkap, $email, $no_telepon, $alamat, $user['id']);
        if ($stmt->execute()) {
            $success = 'Profil berhasil diperbarui';
            $_SESSION['user_nama'] = $nama_lengkap;
            $user = getUserData();
        } else {
            $error = 'Gagal memperbarui profil';
        }
    }

    // Update password
    if (!empty($_POST['password_baru'])) {
        if (strlen($_POST['password_baru']) < 6) {
            $error = 'Password baru minimal 6 karakter';
        } else {
            $hash = password_hash($_POST['password_baru'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hash, $user['id']);
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
    <title>Profil - SiPAL</title>
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
                <a href="laporan_saya.php" class="user-nav-link"><i class="fas fa-list"></i> Laporan Saya</a>
                <a href="notifikasi.php" class="user-nav-link"><i class="fas fa-bell"></i> Notifikasi</a>
                <a href="profil.php" class="user-nav-link active"><i class="fas fa-user"></i> Profil</a>
                <a href="../logout.php" class="user-nav-link" style="color: #ef4444;"><i class="fas fa-sign-out-alt"></i> Keluar</a>
            </div>
        </div>
    </nav>

    <div class="user-dashboard">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card animate-fade-in">
                        <div class="card-header" style="border-bottom: 2px solid #f1f5f9;">
                            <h5 style="font-weight: 700; margin: 0;"><i class="fas fa-user me-2 text-primary"></i>Profil Saya</h5>
                        </div>
                        <div class="p-4">
                            <?php if ($error): ?>
                                <div class="alert alert-danger mb-4" style="border-radius: 12px;"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?></div>
                            <?php endif; ?>
                            <?php if ($success): ?>
                                <div class="alert alert-success mb-4" style="border-radius: 12px;"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?></div>
                            <?php endif; ?>

                            <div class="text-center mb-4">
                                <div style="width: 100px; height: 100px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: white; font-size: 40px; font-weight: 700; border: 4px solid var(--primary-light);">
                                    <?= strtoupper(substr($user['nama_lengkap'], 0, 1)) ?>
                                </div>
                                <h4 class="mt-3" style="font-weight: 700;"><?= htmlspecialchars($user['nama_lengkap']) ?></h4>
                                <p class="text-gray">@<?= htmlspecialchars($user['username']) ?></p>
                            </div>

                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Nama Lengkap</label>
                                            <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($user['nama_lengkap']) ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Username</label>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Email</label>
                                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">No. Telepon</label>
                                            <input type="tel" name="no_telepon" class="form-control" value="<?= htmlspecialchars($user['no_telepon'] ?: '') ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Alamat</label>
                                    <textarea name="alamat" class="form-control" rows="3"><?= htmlspecialchars($user['alamat'] ?: '') ?></textarea>
                                </div>
                                <hr style="margin: 24px 0;">
                                <h6 style="font-weight: 700; margin-bottom: 16px;"><i class="fas fa-lock me-2 text-primary"></i>Ubah Password</h6>
                                <div class="form-group">
                                    <label class="form-label">Password Baru (kosongkan jika tidak ingin mengubah)</label>
                                    <input type="password" name="password_baru" class="form-control" placeholder="Min. 6 karakter">
                                </div>
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
    </div>

    <div class="toast-container" id="toastContainer"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
