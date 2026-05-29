<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireUser();
$user = getUserData();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $kategori = $_POST['kategori'] ?? '';
    $tingkat_urgensi = $_POST['tingkat_urgensi'] ?? 'sedang';
    $latitude = $_POST['latitude'] ?? '';
    $longitude = $_POST['longitude'] ?? '';
    $alamat_lokasi = trim($_POST['alamat_lokasi'] ?? '');
    $kelurahan = trim($_POST['kelurahan'] ?? '');
    $kecamatan = trim($_POST['kecamatan'] ?? '');
    $kota = trim($_POST['kota'] ?? '');

    if (empty($judul) || empty($kategori) || empty($latitude) || empty($longitude) || empty($alamat_lokasi)) {
        $error = 'Judul, kategori, lokasi, dan koordinat wajib diisi';
    } else {
        $foto_kondisi = null;
        if (isset($_FILES['foto_kondisi']) && $_FILES['foto_kondisi']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadFoto($_FILES['foto_kondisi']);
            if ($upload['success']) {
                $foto_kondisi = $upload['filename'];
            } else {
                $error = $upload['message'];
            }
        }

        if (empty($error)) {
            $kode = generateKode();
            $stmt = $conn->prepare("INSERT INTO laporan_sampah (kode_laporan, user_id, judul, deskripsi, kategori, tingkat_urgensi, latitude, longitude, alamat_lokasi, kelurahan, kecamatan, kota, foto_kondisi, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'menunggu', NOW())");
            $stmt->bind_param("sisssssssssss", $kode, $user['id'], $judul, $deskripsi, $kategori, $tingkat_urgensi, $latitude, $longitude, $alamat_lokasi, $kelurahan, $kecamatan, $kota, $foto_kondisi);

            if ($stmt->execute()) {
                setFlash('success', 'Laporan berhasil dikirim! Kode: ' . $kode);
                redirect(BASE_URL . '/user/laporan_saya.php');
            } else {
                $error = 'Gagal menyimpan laporan';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Laporan - SiPAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/user.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
                <a href="laporan_baru.php" class="user-nav-link active"><i class="fas fa-plus-circle"></i> Buat Laporan</a>
                <a href="laporan_saya.php" class="user-nav-link"><i class="fas fa-list"></i> Laporan Saya</a>
                <a href="notifikasi.php" class="user-nav-link"><i class="fas fa-bell"></i> Notifikasi</a>
                <a href="profil.php" class="user-nav-link"><i class="fas fa-user"></i> Profil</a>
                <a href="../logout.php" class="user-nav-link" style="color: #ef4444;"><i class="fas fa-sign-out-alt"></i> Keluar</a>
            </div>
        </div>
    </nav>

    <div class="user-dashboard">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="laporan-form-section animate-fade-in">
                        <h3><i class="fas fa-plus-circle text-primary"></i> Buat Laporan Baru</h3>

                        <?php if ($error): ?>
                            <div class="alert alert-danger" style="border-radius: 12px;"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <form method="POST" action="" enctype="multipart/form-data" id="formLaporan">
                            <div class="form-group">
                                <label class="form-label">Judul Laporan</label>
                                <input type="text" name="judul" class="form-control" placeholder="Contoh: Tumpukan Sampah di Gang Mawar" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" placeholder="Jelaskan kondisi sampah yang Anda temukan..."></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Kategori</label>
                                        <select name="kategori" class="form-control form-select" required>
                                            <option value="">Pilih Kategori</option>
                                            <option value="sampah_domestik">Sampah Domestik</option>
                                            <option value="sampah_organik">Sampah Organik</option>
                                            <option value="sampah_plastik">Sampah Plastik</option>
                                            <option value="sampah_konstruksi">Sampah Konstruksi</option>
                                            <option value="sampah_elektronik">Sampah Elektronik</option>
                                            <option value="sampah_medis">Sampah Medis</option>
                                            <option value="tps_ilegal">TPS Ilegal</option>
                                            <option value="lainnya">Lainnya</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Tingkat Urgensi</label>
                                        <select name="tingkat_urgensi" class="form-control form-select">
                                            <option value="rendah">Rendah</option>
                                            <option value="sedang" selected>Sedang</option>
                                            <option value="tinggi">Tinggi</option>
                                            <option value="darurat">Darurat</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label"><i class="fas fa-map-marker-alt me-1 text-primary"></i> Lokasi di Peta (Drag pin untuk koreksi)</label>
                                <div id="mapPicker" class="map-picker"></div>
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <input type="text" id="latitude" name="latitude" class="form-control" placeholder="Latitude" readonly required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" id="longitude" name="longitude" class="form-control" placeholder="Longitude" readonly required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Alamat Lokasi</label>
                                <input type="text" name="alamat_lokasi" class="form-control" placeholder="Jl. Contoh No. 123" required>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Kelurahan</label>
                                        <input type="text" name="kelurahan" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Kecamatan</label>
                                        <input type="text" name="kecamatan" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Kota</label>
                                        <input type="text" name="kota" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Foto Kondisi (Max 5MB, JPG/PNG)</label>
                                <div class="foto-preview" onclick="document.getElementById('foto_kondisi').click()" id="previewContainer">
                                    <div class="placeholder">
                                        <i class="fas fa-camera" style="font-size: 40px; opacity: 0.5;"></i>
                                        <span>Klik untuk upload foto</span>
                                    </div>
                                </div>
                                <input type="file" id="foto_kondisi" name="foto_kondisi" accept="image/jpeg,image/jpg,image/png" style="display: none;" onchange="previewFoto(this, 'previewContainer')">
                            </div>

                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-paper-plane me-2"></i>Kirim Laporan
                                </button>
                                <a href="dashboard.php" class="btn btn-outline btn-lg">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="loading-overlay" id="loadingOverlay" style="display: none;">
        <div class="spinner"></div>
    </div>
    <div class="toast-container" id="toastContainer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/maps.js"></script>
    <script>
        initMapPicker('latitude', 'longitude', 'mapPicker');

        document.getElementById('formLaporan').addEventListener('submit', function() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        });
    </script>
</body>
</html>
