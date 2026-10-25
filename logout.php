<?php
require_once 'session.inc';
$was_staff = ($_SESSION['role'] ?? '') === 'staff';
$who = $_GET['who'] ?? ($was_staff ? 'staff' : 'applicant');

$token = (string) ($_SESSION['csrf_token'] ?? '');
$sent_token = is_string($_GET['token'] ?? null) ? $_GET['token'] : '';
if ($token !== '' && !hash_equals($token, $sent_token)) {
    header('Location: ' . ($was_staff ? 'manage.php' : (!empty($_SESSION['eoi_id']) ? 'applicant.php' : 'index.php')));
    exit();
}

if ($who === 'staff') {
    unset($_SESSION['username'], $_SESSION['role'], $_SESSION['last_activity']);
} else {
    unset($_SESSION['eoi_id'], $_SESSION['application_id'], $_SESSION['applicant_last_activity']);
}

if (empty($_SESSION['username']) && empty($_SESSION['eoi_id'])) {
    session_unset();
    session_destroy();
} else {
    session_regenerate_id(true);
}
header('Location: ' . ($who === 'staff' ? 'stafflogin.php' : 'index.php'));
exit();
?>