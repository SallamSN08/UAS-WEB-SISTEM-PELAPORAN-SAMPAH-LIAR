<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (isUserLoggedIn()) {
    redirect(BASE_URL . '/user/dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username/email dan password wajib diisi';
    } elseif (loginUser($username, $password)) {
        setFlash('success', 'Selamat datang kembali, ' . $_SESSION['user_nama'] . '!');
        redirect(BASE_URL . '/user/dashboard.php');
    } else {
        $error = 'Username/email atau password salah';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SiPAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .login-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f0fdf4 0%, #d1fae5 100%); padding: 20px; }
        .login-card { background: white; border-radius: 24px; box-shadow: 0 20px 60px rgba(0,0,0,0.1); padding: 40px; width: 100%; max-width: 420px; }
        .login-logo { text-align: center; margin-bottom: 30px; }
        .login-logo .icon { width: 70px; height: 70px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 36px; color: white; box-shadow: 0 8px 20px rgba(16,185,129,0.3); }
        .login-logo h2 { font-weight: 800; color: #1e293b; font-size: 24px; }
        .login-logo p { color: #64748b; font-size: 14px; }
        .form-floating label { font-size: 14px; }
        .btn-login { width: 100%; padding: 14px; border-radius: 12px; font-weight: 700; font-size: 15px; background: linear-gradient(135deg, #10b981, #059669); border: none; color: white; box-shadow: 0 4px 14px rgba(16,185,129,0.4); transition: all 0.3s; }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(16,185,129,0.5); }
        .divider { display: flex; align-items: center; margin: 24px 0; color: #94a3b8; font-size: 13px; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }
        .divider span { padding: 0 12px; }
    </style>
</head>
<body>
    <div class="login-page">
        <div class="login-card animate-fade-in">
            <div class="login-logo">
                <div class="icon">🌿</div>
                <h2>SiPAL</h2>
                <p>Masuk ke akun warga Anda</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger" style="border-radius: 12px; font-size: 13px;">
                    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="username" name="username" placeholder="Username/Email" required style="border-radius: 12px; border: 2px solid #e2e8f0;">
                    <label for="username"><i class="fas fa-user me-2 text-gray"></i>Username atau Email</label>
                </div>
                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required style="border-radius: 12px; border: 2px solid #e2e8f0;">
                    <label for="password"><i class="fas fa-lock me-2 text-gray"></i>Password</label>
                </div>
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Masuk
                </button>
            </form>

            <div class="divider"><span>atau</span></div>

            <div class="text-center">
                <p style="font-size: 14px; color: #64748b;">Belum punya akun? <a href="register.php" style="color: #10b981; font-weight: 700; text-decoration: none;">Daftar sekarang</a></p>
                <a href="index.php" style="font-size: 13px; color: #94a3b8; text-decoration: none;"><i class="fas fa-arrow-left me-1"></i>Kembali ke Beranda</a>
            </div>
        </div>
    </div>
    <div class="toast-container" id="toastContainer"></div>
    <script src="assets/js/main.js"></script>
</body>
</html>
