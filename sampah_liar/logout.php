<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

logoutUser();
setFlash('success', 'Anda telah logout');
redirect(BASE_URL . '/login.php');
?>
