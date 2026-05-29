<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$stats = getStatsAdmin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiPAL - Sistem Pelaporan Sampah Liar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <span style="font-size: 28px;">🌿</span>
                <span>SiPAL</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    <li class="nav-item"><a class="nav-link" href="#fitur">Fitur</a></li>
                    <li class="nav-item"><a class="nav-link" href="#cara-kerja">Cara Kerja</a></li>
                    <li class="nav-item"><a class="nav-link" href="#statistik">Statistik</a></li>
                    <?php if (isUserLoggedIn()): ?>
                        <li class="nav-item"><a class="btn btn-primary btn-sm" href="user/dashboard.php">Dashboard</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="btn btn-outline btn-sm" href="login.php">Masuk</a></li>
                        <li class="nav-item"><a class="btn btn-primary btn-sm" href="register.php">Daftar</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content animate-fade-in">
                    <h1>Laporkan Sampah Liar,<br>Bersihkan Kota Kita!</h1>
                    <p>SiPAL adalah platform pelaporan sampah liar dan TPS ilegal secara online. Bantu kami menjaga kebersihan lingkungan dengan melaporkan tumpukan sampah di sekitar Anda.</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <?php if (isUserLoggedIn()): ?>
                            <a href="user/laporan_baru.php" class="btn btn-lg" style="background: white; color: var(--primary-dark);">
                                <i class="fas fa-plus-circle"></i> Laporkan Sekarang
                            </a>
                        <?php else: ?>
                            <a href="register.php" class="btn btn-lg" style="background: white; color: var(--primary-dark);">
                                <i class="fas fa-user-plus"></i> Mulai Sekarang
                            </a>
                        <?php endif; ?>
                        <a href="#cara-kerja" class="btn btn-outline btn-lg" style="border-color: white; color: white;">
                            <i class="fas fa-info-circle"></i> Pelajari Lebih
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center animate-fade-in" style="animation-delay: 0.2s;">
                    <div style="font-size: 180px; line-height: 1; animation: float 4s ease-in-out infinite;">🗑️🌱</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-5" id="statistik" style="margin-top: -40px; position: relative; z-index: 10;">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3 col-6">
                    <div class="stat-card text-center animate-fade-in">
                        <div class="stat-icon primary mx-auto mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-clipboard-list" style="font-size: 28px;"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $stats['total'] ?></h3>
                            <p>Total Laporan</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card text-center animate-fade-in" style="animation-delay: 0.1s;">
                        <div class="stat-icon warning mx-auto mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-clock" style="font-size: 28px;"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $stats['menunggu'] ?></h3>
                            <p>Menunggu</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card text-center animate-fade-in" style="animation-delay: 0.2s;">
                        <div class="stat-icon info mx-auto mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-spinner" style="font-size: 28px;"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $stats['diproses'] ?></h3>
                            <p>Diproses</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card text-center animate-fade-in" style="animation-delay: 0.3s;">
                        <div class="stat-icon success mx-auto mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-check-circle" style="font-size: 28px;"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?= $stats['selesai'] ?></h3>
                            <p>Selesai</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Fitur Section -->
    <section class="py-5" id="fitur">
        <div class="container">
            <div class="text-center mb-5 animate-fade-in">
                <h2 style="font-weight: 800; color: var(--dark); font-size: 32px;">Fitur Unggulan</h2>
                <p class="text-gray">Berbagai fitur untuk memudahkan pelaporan sampah liar</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4 animate-fade-in">
                    <div class="card h-100 text-center p-4">
                        <div style="font-size: 60px; margin-bottom: 16px;">📍</div>
                        <h4 style="font-weight: 700; margin-bottom: 12px;">GPS Otomatis</h4>
                        <p class="text-gray">Lokasi tumpukan sampah tercatat otomatis dengan GPS. Anda juga bisa menyesuaikan pin lokasi di peta.</p>
                    </div>
                </div>
                <div class="col-md-4 animate-fade-in" style="animation-delay: 0.1s;">
                    <div class="card h-100 text-center p-4">
                        <div style="font-size: 60px; margin-bottom: 16px;">📷</div>
                        <h4 style="font-weight: 700; margin-bottom: 12px;">Foto Kondisi</h4>
                        <p class="text-gray">Lampirkan foto kondisi sampah saat ini. Admin akan upload foto sesudah penanganan.</p>
                    </div>
                </div>
                <div class="col-md-4 animate-fade-in" style="animation-delay: 0.2s;">
                    <div class="card h-100 text-center p-4">
                        <div style="font-size: 60px; margin-bottom: 16px;">📊</div>
                        <h4 style="font-weight: 700; margin-bottom: 12px;">Tracking Real-time</h4>
                        <p class="text-gray">Pantau progress penanganan laporan Anda secara real-time dengan timeline visual.</p>
                    </div>
                </div>
                <div class="col-md-4 animate-fade-in" style="animation-delay: 0.3s;">
                    <div class="card h-100 text-center p-4">
                        <div style="font-size: 60px; margin-bottom: 16px;">🔔</div>
                        <h4 style="font-weight: 700; margin-bottom: 12px;">Notifikasi</h4>
                        <p class="text-gray">Dapatkan notifikasi otomatis saat status laporan Anda berubah.</p>
                    </div>
                </div>
                <div class="col-md-4 animate-fade-in" style="animation-delay: 0.4s;">
                    <div class="card h-100 text-center p-4">
                        <div style="font-size: 60px; margin-bottom: 16px;">🗺️</div>
                        <h4 style="font-weight: 700; margin-bottom: 12px;">Peta Interaktif</h4>
                        <p class="text-gray">Lihat semua titik laporan sampah di peta interaktif dengan marker berwarna.</p>
                    </div>
                </div>
                <div class="col-md-4 animate-fade-in" style="animation-delay: 0.5s;">
                    <div class="card h-100 text-center p-4">
                        <div style="font-size: 60px; margin-bottom: 16px;">📈</div>
                        <h4 style="font-weight: 700; margin-bottom: 12px;">Grafik & Statistik</h4>
                        <p class="text-gray">Dashboard admin dilengkapi grafik dan statistik pelaporan yang komprehensif.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Cara Kerja -->
    <section class="py-5" id="cara-kerja" style="background: white;">
        <div class="container">
            <div class="text-center mb-5 animate-fade-in">
                <h2 style="font-weight: 800; color: var(--dark); font-size: 32px;">Cara Kerja</h2>
                <p class="text-gray">4 langkah mudah melaporkan sampah liar</p>
            </div>
            <div class="row g-4">
                <div class="col-md-3 text-center animate-fade-in">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: white; font-size: 32px; box-shadow: 0 8px 20px rgba(16,185,129,0.3);">1</div>
                    <h5 style="font-weight: 700;">Daftar Akun</h5>
                    <p class="text-gray" style="font-size: 14px;">Buat akun warga dengan data lengkap Anda</p>
                </div>
                <div class="col-md-3 text-center animate-fade-in" style="animation-delay: 0.15s;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: white; font-size: 32px; box-shadow: 0 8px 20px rgba(16,185,129,0.3);">2</div>
                    <h5 style="font-weight: 700;">Buat Laporan</h5>
                    <p class="text-gray" style="font-size: 14px;">Isi form dengan foto, lokasi, dan keterangan</p>
                </div>
                <div class="col-md-3 text-center animate-fade-in" style="animation-delay: 0.3s;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: white; font-size: 32px; box-shadow: 0 8px 20px rgba(16,185,129,0.3);">3</div>
                    <h5 style="font-weight: 700;">Tunggu Proses</h5>
                    <p class="text-gray" style="font-size: 14px;">Admin akan memproses laporan Anda</p>
                </div>
                <div class="col-md-3 text-center animate-fade-in" style="animation-delay: 0.45s;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: white; font-size: 32px; box-shadow: 0 8px 20px rgba(16,185,129,0.3);">4</div>
                    <h5 style="font-weight: 700;">Selesai!</h5>
                    <p class="text-gray" style="font-size: 14px;">Dapatkan notifikasi saat laporan selesai</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5">
        <div class="container">
            <div class="card text-center p-5 animate-fade-in" style="background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; border: none;">
                <h2 style="font-weight: 800; font-size: 32px; margin-bottom: 16px;">Siap Membuat Perubahan?</h2>
                <p style="font-size: 16px; opacity: 0.9; max-width: 600px; margin: 0 auto 24px;">Bergabunglah dengan ribuan warga yang peduli kebersihan. Laporkan sampah liar di sekitar Anda sekarang!</p>
                <?php if (!isUserLoggedIn()): ?>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="register.php" class="btn btn-lg" style="background: white; color: var(--primary-dark);">
                            <i class="fas fa-user-plus"></i> Daftar Sekarang
                        </a>
                        <a href="login.php" class="btn btn-outline btn-lg" style="border-color: white; color: white;">
                            <i class="fas fa-sign-in-alt"></i> Sudah Punya Akun?
                        </a>
                    </div>
                <?php else: ?>
                    <a href="user/laporan_baru.php" class="btn btn-lg" style="background: white; color: var(--primary-dark); display: inline-flex;">
                        <i class="fas fa-plus-circle"></i> Buat Laporan Baru
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h4 style="font-weight: 700; margin-bottom: 16px;">🌿 SiPAL</h4>
                    <p style="opacity: 0.7; font-size: 14px;">Sistem Pelaporan Sampah Liar untuk kota yang lebih bersih dan sehat.</p>
                </div>
                <div class="col-md-4">
                    <h5 style="font-weight: 600; margin-bottom: 16px;">Tautan Cepat</h5>
                    <ul style="list-style: none; padding: 0; opacity: 0.7; font-size: 14px;">
                        <li class="mb-2"><a href="login.php" style="color: white; text-decoration: none;">Login Warga</a></li>
                        <li class="mb-2"><a href="register.php" style="color: white; text-decoration: none;">Daftar Warga</a></li>
                        <li class="mb-2"><a href="admin/login.php" style="color: white; text-decoration: none;">Login Admin</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5 style="font-weight: 600; margin-bottom: 16px;">Kontak</h5>
                    <p style="opacity: 0.7; font-size: 14px;">
                        <i class="fas fa-envelope me-2"></i> info@sipal.id<br>
                        <i class="fas fa-phone me-2"></i> (021) 1234-5678
                    </p>
                </div>
            </div>
            <hr style="border-color: rgba(255,255,255,0.1); margin: 30px 0;">
            <div class="text-center" style="opacity: 0.5; font-size: 13px;">
                © 2026 SiPAL - Sistem Pelaporan Sampah Liar. All rights reserved.
            </div>
        </div>
    </footer>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
