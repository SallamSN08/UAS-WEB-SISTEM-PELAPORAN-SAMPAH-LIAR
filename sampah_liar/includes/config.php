<?php
session_start();

// Konfigurasi Database
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'sampah_liar';

// Koneksi ke MySQL
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Konstanta
define('BASE_URL', 'http://localhost/sampah_liar');
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');
define('UPLOAD_URL', BASE_URL . '/assets/uploads/');

// Fungsi helper
function redirect($url) {
    header("Location: " . $url);
    exit;
}

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function formatTanggal($datetime) {
    $bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($datetime);
    return date('d', $t) . ' ' . $bulan[date('n', $t)] . ' ' . date('Y H:i', $t);
}

function statusBadge($status) {
    $badges = [
        'menunggu' => '<span class="badge badge-wait">Menunggu</span>',
        'diproses' => '<span class="badge badge-process">Diproses</span>',
        'selesai' => '<span class="badge badge-done">Selesai</span>',
        'ditolak' => '<span class="badge badge-reject">Ditolak</span>'
    ];
    return $badges[$status] ?? $status;
}

function statusIcon($status) {
    $icons = [
        'menunggu' => '⏳',
        'diproses' => '🔄',
        'selesai' => '✅',
        'ditolak' => '❌'
    ];
    return $icons[$status] ?? '❓';
}

function generateKode() {
    return 'SPL-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}
?>
