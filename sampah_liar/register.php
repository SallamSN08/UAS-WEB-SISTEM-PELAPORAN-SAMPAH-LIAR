<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (isUserLoggedIn()) {
    redirect(BASE_URL . '/user/dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nama_lengkap' => trim($_POST['nama_lengkap'] ?? ''),
        'username' => trim($_POST['username'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'no_telepon' => trim($_POST['no_telepon'] ?? ''),
        'alamat' => trim($_POST['alamat'] ?? '')
    ];

    if (empty($data['nama_lengkap']) || empty($data['username']) || empty($data['email']) || empty($data['password'])) {
        $error = 'Semua field wajib diisi';
    } elseif (strlen($data['password']) < 6) {
        $error = 'Password minimal 6 karakter';
    } else {
        $result = registerUser($data);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - SiPAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .register-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f0fdf4 0%, #d1fae5 100%); padding: 20px; }
        .register-card { background: white; border-radius: 24px; box-shadow: 0 20px 60px rgba(0,0,0,0.1); padding: 40px; width: 100%; max-width: 480px; }
        .register-logo { text-align: center; margin-bottom: 30px; }
        .register-logo .icon { width: 70px; height: 70px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 36px; color: white; box-shadow: 0 8px 20px rgba(16,185,129,0.3); }
        .register-logo h2 { font-weight: 800; color: #1e293b; font-size: 24px; }
        .register-logo p { color: #64748b; font-size: 14px; }
        .form-floating label { font-size: 14px; }
        .btn-register { width: 100%; padding: 14px; border-radius: 12px; font-weight: 700; font-size: 15px; background: linear-gradient(135deg, #10b981, #059669); border: none; color: white; box-shadow: 0 4px 14px rgba(16,185,129,0.4); transition: all 0.3s; }
        .btn-register:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(16,185,129,0.5); }
    </style>
</head>
<body>
    <div class="register-page">
        <div class="register-card animate-fade-in">
            <div class="register-logo">
                <div class="icon">🌿</div>
                <h2>Buat Akun Baru</h2>
                <p>Bergabunglah untuk melaporkan sampah liar</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger" style="border-radius: 12px; font-size: 13px;">
                    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success" style="border-radius: 12px; font-size: 13px;">
                    <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?> <a href="login.php" style="font-weight: 700;">Login di sini</a>
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" placeholder="Nama Lengkap" required style="border-radius: 12px; border: 2px solid #e2e8f0;">
                        <label for="nama_lengkap"><i class="fas fa-user me-2 text-gray"></i>Nama Lengkap</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="username" name="username" placeholder="Username" required style="border-radius: 12px; border: 2px solid #e2e8f0;">
                        <label for="username"><i class="fas fa-at me-2 text-gray"></i>Username</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" required style="border-radius: 12px; border: 2px solid #e2e8f0;">
                        <label for="email"><i class="fas fa-envelope me-2 text-gray"></i>Email</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required minlength="6" style="border-radius: 12px; border: 2px solid #e2e8f0;">
                        <label for="password"><i class="fas fa-lock me-2 text-gray"></i>Password (min 6 karakter)</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="tel" class="form-control" id="no_telepon" name="no_telepon" placeholder="No Telepon" style="border-radius: 12px; border: 2px solid #e2e8f0;">
                        <label for="no_telepon"><i class="fas fa-phone me-2 text-gray"></i>No. Telepon</label>
                    </div>
                    <div class="form-floating mb-4">
                        <textarea class="form-control" id="alamat" name="alamat" placeholder="Alamat" style="height: 80px; border-radius: 12px; border: 2px solid #e2e8f0;"></textarea>
                        <label for="alamat"><i class="fas fa-map-marker-alt me-2 text-gray"></i>Alamat</label>
                    </div>
                    <button type="submit" class="btn-register">
                        <i class="fas fa-user-plus me-2"></i>Daftar
                    </button>
                </form>
            <?php endif; ?>

            <div class="text-center mt-4">
                <p style="font-size: 14px; color: #64748b;">Sudah punya akun? <a href="login.php" style="color: #10b981; font-weight: 700; text-decoration: none;">Masuk di sini</a></p>
                <a href="index.php" style="font-size: 13px; color: #94a3b8; text-decoration: none;"><i class="fas fa-arrow-left me-1"></i>Kembali ke Beranda</a>
            </div>
        </div>
    </div>
    <div class="toast-container" id="toastContainer"></div>
    <script src="assets/js/main.js"></script>
</body>
</html>
