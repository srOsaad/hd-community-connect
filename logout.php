<?php
session_start();
$was_staff = ($_SESSION['role'] ?? '') === 'staff';
session_unset();
session_destroy();
header('Location: ' . ($was_staff ? 'stafflogin.php' : 'index.php'));
exit();
?>