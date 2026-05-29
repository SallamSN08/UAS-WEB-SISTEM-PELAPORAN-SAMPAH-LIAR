<?php
require_once 'config.php';

function uploadFoto($file, $subfolder = '') {
    $allowed = ['image/jpeg', 'image/jpg', 'image/png'];
    $maxSize = 5 * 1024 * 1024;
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Gagal upload file'];
    }
    
    if (!in_array($file['type'], $allowed)) {
        return ['success' => false, 'message' => 'Format file harus JPG, JPEG, atau PNG'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'Ukuran file maksimal 5MB'];
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . strtolower($ext);
    $folder = UPLOAD_DIR . ($subfolder ? $subfolder . '/' : '');
    
    if (!is_dir($folder)) mkdir($folder, 0777, true);
    
    $filepath = $folder . $filename;
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => ($subfolder ? $subfolder . '/' : '') . $filename];
    }
    return ['success' => false, 'message' => 'Gagal menyimpan file'];
}

function getStatsAdmin() {
    global $conn;
    $stats = ['total' => 0, 'menunggu' => 0, 'diproses' => 0, 'selesai' => 0, 'ditolak' => 0];
    $result = $conn->query("SELECT status, COUNT(*) as jumlah FROM laporan_sampah GROUP BY status");
    while ($row = $result->fetch_assoc()) {
        $stats[$row['status']] = $row['jumlah'];
        $stats['total'] += $row['jumlah'];
    }
    return $stats;
}

function getStatsUser($user_id) {
    global $conn;
    $stats = ['total' => 0, 'menunggu' => 0, 'diproses' => 0, 'selesai' => 0];
    $stmt = $conn->prepare("SELECT status, COUNT(*) as jumlah FROM laporan_sampah WHERE user_id = ? GROUP BY status");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $stats[$row['status']] = $row['jumlah'];
        $stats['total'] += $row['jumlah'];
    }
    return $stats;
}

function getLaporanById($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT l.*, u.nama_lengkap as nama_pelapor, u.no_telepon, a.nama_lengkap as nama_petugas FROM laporan_sampah l LEFT JOIN users u ON l.user_id = u.id LEFT JOIN admins a ON l.petugas_id = a.id WHERE l.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getTrackingByLaporan($laporan_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT t.*, a.nama_lengkap as nama_admin FROM tracking_pekerjaan t LEFT JOIN admins a ON t.admin_id = a.id WHERE t.laporan_id = ? ORDER BY t.created_at ASC");
    $stmt->bind_param("i", $laporan_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function updateStatusLaporan($laporan_id, $status_baru, $catatan, $admin_id, $foto_progress = null) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT status, user_id, judul FROM laporan_sampah WHERE id = ?");
    $stmt->bind_param("i", $laporan_id);
    $stmt->execute();
    $laporan = $stmt->get_result()->fetch_assoc();
    $status_lama = $laporan['status'];
    $user_id = $laporan['user_id'];
    $judul = $laporan['judul'];
    
    if ($foto_progress) {
        $stmt = $conn->prepare("UPDATE laporan_sampah SET status = ?, catatan_admin = ?, petugas_id = ?, foto_sesudah = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("ssisi", $status_baru, $catatan, $admin_id, $foto_progress, $laporan_id);
    } else {
        $stmt = $conn->prepare("UPDATE laporan_sampah SET status = ?, catatan_admin = ?, petugas_id = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("ssii", $status_baru, $catatan, $admin_id, $laporan_id);
    }
    $stmt->execute();
    
    $stmt = $conn->prepare("INSERT INTO tracking_pekerjaan (laporan_id, admin_id, status_sebelum, status_sesudah, keterangan, foto_progress, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iissss", $laporan_id, $admin_id, $status_lama, $status_baru, $catatan, $foto_progress);
    $stmt->execute();
    
    $pesan_status = [
        'menunggu' => 'Laporan Anda sedang menunggu verifikasi',
        'diproses' => 'Laporan Anda sedang diproses oleh petugas',
        'selesai' => 'Laporan Anda telah selesai ditangani',
        'ditolak' => 'Laporan Anda ditolak. Silakan cek catatan admin'
    ];
    
    createNotification([
        'user_id' => $user_id,
        'admin_id' => $admin_id,
        'judul' => 'Update Status: ' . ucfirst($status_baru),
        'pesan' => $pesan_status[$status_baru] . ' - ' . $judul,
        'tipe' => 'status_update'
    ]);
    
    return true;
}

function getLaporanPerBulan($tahun = null) {
    global $conn;
    $tahun = $tahun ?: date('Y');
    $data = array_fill(0, 12, 0);
    $stmt = $conn->prepare("SELECT MONTH(created_at) as bulan, COUNT(*) as jumlah FROM laporan_sampah WHERE YEAR(created_at) = ? GROUP BY MONTH(created_at)");
    $stmt->bind_param("i", $tahun);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[$row['bulan'] - 1] = (int)$row['jumlah'];
    }
    return $data;
}

function getLaporanPerKategori() {
    global $conn;
    $data = [];
    $result = $conn->query("SELECT kategori, COUNT(*) as jumlah FROM laporan_sampah GROUP BY kategori");
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

function getLaporanPerStatus() {
    global $conn;
    $data = [];
    $result = $conn->query("SELECT status, COUNT(*) as jumlah FROM laporan_sampah GROUP BY status");
    while ($row = $result->fetch_assoc()) {
        $data[$row['status']] = (int)$row['jumlah'];
    }
    return $data;
}

function getPetugasAktif() {
    global $conn;
    $stmt = $conn->prepare("SELECT a.id, a.nama_lengkap, a.username, a.role, COUNT(l.id) as jumlah_laporan FROM admins a LEFT JOIN laporan_sampah l ON a.id = l.petugas_id WHERE a.role IN ('admin', 'petugas') GROUP BY a.id ORDER BY jumlah_laporan DESC");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getTopLokasi($limit = 5) {
    global $conn;
    $stmt = $conn->prepare("SELECT alamat_lokasi, kelurahan, kecamatan, COUNT(*) as jumlah FROM laporan_sampah GROUP BY alamat_lokasi, kelurahan, kecamatan ORDER BY jumlah DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function rataRataPenyelesaian() {
    global $conn;
    $result = $conn->query("SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as rata_rata FROM laporan_sampah WHERE status = 'selesai'");
    $row = $result->fetch_assoc();
    return round($row['rata_rata'] ?? 0, 1);
}