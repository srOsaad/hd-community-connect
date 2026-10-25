<?php
require_once 'session.inc';
$was_staff = ($_SESSION['role'] ?? '') === 'staff';

$token = (string) ($_SESSION['csrf_token'] ?? '');
$sent_token = is_string($_GET['token'] ?? null) ? $_GET['token'] : '';
if ($token !== '' && !hash_equals($token, $sent_token)) {
    header('Location: ' . ($was_staff ? 'manage.php' : (!empty($_SESSION['eoi_id']) ? 'applicant.php' : 'index.php')));
    exit();
}

session_unset();
session_destroy();
header('Location: ' . ($was_staff ? 'stafflogin.php' : 'index.php'));
exit();
?>