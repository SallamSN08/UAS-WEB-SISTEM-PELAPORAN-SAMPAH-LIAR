-- SiPAL - Sistem Pelaporan Sampah Liar
-- Database: sampah_liar
-- Import ke phpMyAdmin

CREATE DATABASE IF NOT EXISTS sampah_liar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sampah_liar;

-- Tabel Users (Warga)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    no_telepon VARCHAR(20),
    alamat TEXT,
    foto_profil VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Admins
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('superadmin', 'admin', 'petugas') DEFAULT 'petugas',
    no_telepon VARCHAR(20),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Laporan Sampah
CREATE TABLE laporan_sampah (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_laporan VARCHAR(50) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    kategori VARCHAR(50) NOT NULL,
    tingkat_urgensi ENUM('rendah', 'sedang', 'tinggi', 'darurat') DEFAULT 'sedang',
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    alamat_lokasi TEXT NOT NULL,
    kelurahan VARCHAR(100),
    kecamatan VARCHAR(100),
    kota VARCHAR(100),
    foto_kondisi VARCHAR(255),
    foto_sesudah VARCHAR(255),
    status ENUM('menunggu', 'diproses', 'selesai', 'ditolak') DEFAULT 'menunggu',
    catatan_admin TEXT,
    petugas_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (petugas_id) REFERENCES admins(id) ON DELETE SET NULL
);

-- Tabel Tracking Pekerjaan
CREATE TABLE tracking_pekerjaan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    laporan_id INT NOT NULL,
    admin_id INT NOT NULL,
    status_sebelum VARCHAR(50),
    status_sesudah VARCHAR(50),
    keterangan TEXT,
    foto_progress VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (laporan_id) REFERENCES laporan_sampah(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
);

-- Tabel Notifikasi
CREATE TABLE notifikasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    admin_id INT,
    judul VARCHAR(200) NOT NULL,
    pesan TEXT NOT NULL,
    tipe VARCHAR(50) DEFAULT 'umum',
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
);

-- ============================================================
-- DATA DUMMY
-- ============================================================

-- Users (Password: password)
INSERT INTO users (nama_lengkap, username, email, password, no_telepon, alamat) VALUES
('Budi Santoso', 'budi', 'budi@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567890', 'Jl. Merdeka No. 1, Jakarta'),
('Ani Wijaya', 'ani', 'ani@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567891', 'Jl. Sudirman No. 5, Jakarta'),
('Dedi Kurniawan', 'dedi', 'dedi@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567892', 'Jl. Thamrin No. 10, Jakarta'),
('Siti Aminah', 'siti', 'siti@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567893', 'Jl. Gatot Subroto No. 15, Jakarta'),
('Rudi Hartono', 'rudi', 'rudi@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567894', 'Jl. Rasuna Said No. 20, Jakarta');

-- Admins (Password: password)
INSERT INTO admins (nama_lengkap, username, email, password, role, no_telepon) VALUES
('Super Admin', 'admin', 'admin@sipal.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin', '081111111111'),
('Admin Kelurahan', 'adminkel', 'adminkel@sipal.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '081122222222'),
('Petugas 1', 'petugas1', 'petugas1@sipal.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'petugas', '081133333333'),
('Petugas 2', 'petugas2', 'petugas2@sipal.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'petugas', '081144444444'),
('Petugas 3', 'petugas3', 'petugas3@sipal.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'petugas', '081155555555');

-- Laporan Sampah
INSERT INTO laporan_sampah (kode_laporan, user_id, judul, deskripsi, kategori, tingkat_urgensi, latitude, longitude, alamat_lokasi, kelurahan, kecamatan, kota, foto_kondisi, status, catatan_admin, petugas_id, created_at) VALUES
('SPL-20260501-ABC123', 1, 'Tumpukan Sampah di Gang Mawar', 'Sampah menumpuk di gang mawar sudah 3 hari tidak diangkut, bau tidak sedap', 'sampah_domestik', 'tinggi', -6.2088, 106.8456, 'Gang Mawar No. 12, RT 03/RW 05', 'Menteng', 'Menteng', 'Jakarta Pusat', 'sampah1.jpg', 'selesai', 'Sudah ditangani dan dibersihkan', 3, '2026-05-01 08:30:00'),
('SPL-20260502-DEF456', 2, 'TPS Ilegal di Pinggir Kali', 'Ada TPS ilegal di pinggir kali yang mengganggu lingkungan', 'tps_ilegal', 'darurat', -6.2146, 106.8451, 'Jalan Kali Besar Barat No. 45', 'Pinangsia', 'Taman Sari', 'Jakarta Barat', 'sampah2.jpg', 'diproses', 'Sedang dalam proses penanganan', 3, '2026-05-02 10:15:00'),
('SPL-20260503-GHI789', 3, 'Sampah Plastik Menumpuk di Taman', 'Banyak sampah plastik berserakan di taman kota', 'sampah_plastik', 'sedang', -6.1754, 106.8272, 'Taman Monas, Area Timur', 'Gambir', 'Gambir', 'Jakarta Pusat', 'sampah3.jpg', 'menunggu', NULL, NULL, '2026-05-03 14:20:00'),
('SPL-20260504-JKL012', 1, 'Sampah Konstruksi di Jalan Utama', 'Puing konstruksi menutupi separuh jalan', 'sampah_konstruksi', 'tinggi', -6.2000, 106.8166, 'Jalan Sudirman Kav. 28-30', 'Setiabudi', 'Setiabudi', 'Jakarta Selatan', 'sampah4.jpg', 'diproses', 'Petugas sedang menuju lokasi', 4, '2026-05-04 09:00:00'),
('SPL-20260505-MNO345', 4, 'Sampah Organik di Pasar', 'Sampah sayur dan buah busuk menumpuk di belakang pasar', 'sampah_organik', 'darurat', -6.1865, 106.8340, 'Pasar Senen Blok III', 'Senen', 'Senen', 'Jakarta Pusat', 'sampah5.jpg', 'selesai', 'Sudah dibersihkan dan disemprot', 3, '2026-05-05 07:45:00'),
('SPL-20260506-PQR678', 5, 'Sampah Elektronik Dibuang Sembarangan', 'TV dan kulkas bekas dibuang di trotoar', 'sampah_elektronik', 'sedang', -6.2250, 106.8070, 'Jalan Panglima Polim No. 88', 'Melawai', 'Kebayoran Baru', 'Jakarta Selatan', 'sampah6.jpg', 'menunggu', NULL, NULL, '2026-05-06 16:30:00'),
('SPL-20260507-STU901', 2, 'Tumpukan Sampah di Perumahan', 'Sampah menumpuk di depan rumah warga', 'sampah_domestik', 'tinggi', -6.1900, 106.8800, 'Perumahan Griya Indah Blok C5', 'Cakung', 'Cakung', 'Jakarta Timur', 'sampah7.jpg', 'ditolak', 'Lokasi tidak valid, mohon kirim ulang dengan foto yang jelas', 2, '2026-05-07 11:00:00'),
('SPL-20260508-VWX234', 3, 'Sampah Medis Berserakan', 'Kemasan obat dan jarum suntik ditemukan di tempat sampah umum', 'sampah_medis', 'darurat', -6.2400, 106.8000, 'RSUD Cengkareng, Area Belakang', 'Cengkareng Barat', 'Cengkareng', 'Jakarta Barat', 'sampah8.jpg', 'diproses', 'Tim khusus sedang menangani', 5, '2026-05-08 13:15:00'),
('SPL-20260509-YZA567', 4, 'Sampah di Stasiun Kereta', 'Tong sampah penuh dan sampah berserakan di peron', 'sampah_domestik', 'sedang', -6.1760, 106.8300, 'Stasiun Gambir, Peron 3', 'Gambir', 'Gambir', 'Jakarta Pusat', 'sampah9.jpg', 'selesai', 'Sudah dibersihkan', 4, '2026-05-09 18:00:00'),
('SPL-20260510-BCD890', 5, 'Sampah Plastik di Pantai', 'Banyak botol plastik dan kantong plastik di area pantai', 'sampah_plastik', 'tinggi', -6.1300, 106.8000, 'Pantai Ancol, Area Timur', 'Ancol', 'Pademangan', 'Jakarta Utara', 'sampah10.jpg', 'menunggu', NULL, NULL, '2026-05-10 06:30:00'),
('SPL-20260511-EFG123', 1, 'Sampah Puing Gedung', 'Reruntuhan gedung tua menutupi jalan setapak', 'sampah_konstruksi', 'sedang', -6.2100, 106.8500, 'Jalan Kramat Raya No. 100', 'Kramat', 'Senen', 'Jakarta Pusat', 'sampah11.jpg', 'diproses', 'Sedang dikoordinasikan dengan Dinas PU', 3, '2026-05-11 08:00:00'),
('SPL-20260512-HIJ456', 2, 'Sampah Organik di Restoran', 'Sampah makanan busuk menumpuk di belakang restoran', 'sampah_organik', 'darurat', -6.2200, 106.8200, 'Jalan Sabang No. 15, Belakang Restoran', 'Kebon Sirih', 'Menteng', 'Jakarta Pusat', 'sampah12.jpg', 'selesai', 'Sudah ditangani dan diberi peringatan ke restoran', 5, '2026-05-12 20:00:00');

-- Tracking Pekerjaan
INSERT INTO tracking_pekerjaan (laporan_id, admin_id, status_sebelum, status_sesudah, keterangan, created_at) VALUES
(1, 3, 'menunggu', 'diproses', 'Petugas menuju lokasi untuk verifikasi', '2026-05-01 09:00:00'),
(1, 3, 'diproses', 'selesai', 'Sampah sudah diangkut dan area dibersihkan', '2026-05-01 14:30:00'),
(2, 3, 'menunggu', 'diproses', 'Tim sedang mempersiapkan alat berat', '2026-05-02 11:00:00'),
(5, 3, 'menunggu', 'diproses', 'Petugas tiba di lokasi', '2026-05-05 08:30:00'),
(5, 3, 'diproses', 'selesai', 'Sampah sudah diangkut, area disemprot disinfektan', '2026-05-05 12:00:00'),
(7, 2, 'menunggu', 'ditolak', 'Lokasi tidak valid, foto tidak jelas', '2026-05-07 12:00:00'),
(8, 5, 'menunggu', 'diproses', 'Tim khusus sampah medis sedang menuju lokasi', '2026-05-08 14:00:00'),
(9, 4, 'menunggu', 'diproses', 'Petugas kebersihan stasiun ditugaskan', '2026-05-09 19:00:00'),
(9, 4, 'diproses', 'selesai', 'Area peron sudah bersih', '2026-05-09 21:00:00'),
(12, 5, 'menunggu', 'diproses', 'Petugas tiba di lokasi', '2026-05-12 21:00:00'),
(12, 5, 'diproses', 'selesai', 'Sampah diangkut, restoran diberi surat peringatan', '2026-05-13 08:00:00');

-- Notifikasi
INSERT INTO notifikasi (user_id, admin_id, judul, pesan, tipe, is_read, created_at) VALUES
(1, 3, 'Update Status: Diproses', 'Laporan Anda sedang diproses oleh petugas - Tumpukan Sampah di Gang Mawar', 'status_update', 1, '2026-05-01 09:00:00'),
(1, 3, 'Update Status: Selesai', 'Laporan Anda telah selesai ditangani - Tumpukan Sampah di Gang Mawar', 'status_update', 1, '2026-05-01 14:30:00'),
(2, 3, 'Update Status: Diproses', 'Laporan Anda sedang diproses oleh petugas - TPS Ilegal di Pinggir Kali', 'status_update', 0, '2026-05-02 11:00:00'),
(4, 3, 'Update Status: Diproses', 'Laporan Anda sedang diproses oleh petugas - Sampah Organik di Pasar', 'status_update', 1, '2026-05-05 08:30:00'),
(4, 3, 'Update Status: Selesai', 'Laporan Anda telah selesai ditangani - Sampah Organik di Pasar', 'status_update', 1, '2026-05-05 12:00:00'),
(2, 2, 'Update Status: Ditolak', 'Laporan Anda ditolak. Silakan cek catatan admin - Tumpukan Sampah di Perumahan', 'status_update', 0, '2026-05-07 12:00:00'),
(3, 5, 'Update Status: Diproses', 'Laporan Anda sedang diproses oleh petugas - Sampah Medis Berserakan', 'status_update', 0, '2026-05-08 14:00:00'),
(4, 4, 'Update Status: Selesai', 'Laporan Anda telah selesai ditangani - Sampah di Stasiun Kereta', 'status_update', 1, '2026-05-09 21:00:00'),
(2, 5, 'Update Status: Selesai', 'Laporan Anda telah selesai ditangani - Sampah Organik di Restoran', 'status_update', 0, '2026-05-13 08:00:00');
