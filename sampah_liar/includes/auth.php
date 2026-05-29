<?php
require_once 'config.php';

// ===================== USER AUTH =====================
function isUserLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireUser() {
    if (!isUserLoggedIn()) {
        setFlash('error', 'Silakan login terlebih dahulu');
        redirect(BASE_URL . '/login.php');
    }
}

function getUserData() {
    global $conn;
    if (!isUserLoggedIn()) return null;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function loginUser($username, $password) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_username'] = $user['username'];
            $_SESSION['user_nama'] = $user['nama_lengkap'];
            $_SESSION['user_email'] = $user['email'];
            return true;
        }
    }
    return false;
}

function registerUser($data) {
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $data['username'], $data['email']);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        return ['success' => false, 'message' => 'Username atau email sudah terdaftar'];
    }

    $hash = password_hash($data['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (nama_lengkap, username, email, password, no_telepon, alamat, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssssss", $data['nama_lengkap'], $data['username'], $data['email'], $hash, $data['no_telepon'], $data['alamat']);

    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Registrasi berhasil! Silakan login'];
    }
    return ['success' => false, 'message' => 'Registrasi gagal'];
}

function logoutUser() {
    unset($_SESSION['user_id'], $_SESSION['user_username'], $_SESSION['user_nama'], $_SESSION['user_email']);
}

// ===================== ADMIN AUTH =====================
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireAdmin() {
    if (!isAdminLoggedIn()) {
        setFlash('error', 'Silakan login sebagai admin');
        redirect(BASE_URL . '/admin/login.php');
    }
}

function requireAdminRole($roles) {
    requireAdmin();
    if (!in_array($_SESSION['admin_role'], (array)$roles)) {
        setFlash('error', 'Anda tidak memiliki akses');
        redirect(BASE_URL . '/admin/dashboard.php');
    }
}

function getAdminData() {
    global $conn;
    if (!isAdminLoggedIn()) return null;
    $stmt = $conn->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['admin_id']);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function loginAdmin($username, $password) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_nama'] = $admin['nama_lengkap'];
            $_SESSION['admin_role'] = $admin['role'];
            return true;
        }
    }
    return false;
}

function logoutAdmin() {
    unset($_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_nama'], $_SESSION['admin_role']);
}

// ===================== NOTIFIKASI =====================
function createNotification($data) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO notifikasi (user_id, admin_id, judul, pesan, tipe, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iisss", $data['user_id'], $data['admin_id'], $data['judul'], $data['pesan'], $data['tipe']);
    return $stmt->execute();
}

function getUnreadCount($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM notifikasi WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total'];
}
?>
