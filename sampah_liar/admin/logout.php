<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

logoutAdmin();
setFlash('success', 'Anda telah logout dari panel admin');
redirect(BASE_URL . '/admin/login.php');
?>
