<?php
require_once 'session.inc';
require_once 'db_connect.inc';
require_once 'error.inc';
require_once 'functions.inc';

if (!$conn) {
    show_database_error();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !csrf_ok()) {
    $error = 'That request could not be verified. Please try again.';
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = is_string($_POST['username'] ?? null) ? trim($_POST['username']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

    $found_username = check_login($conn,
        "SELECT username, password_hash, failed_attempts, lockout_until, lockout_minutes FROM staff WHERE username = ? FOR UPDATE",
        $username, "staff", "username", $password);

    if ($found_username !== null) {
        // Correct: log in as staff
        session_regenerate_id(true);
        $_SESSION['username'] = $found_username;
        $_SESSION['role'] = 'staff';
        $_SESSION['last_activity'] = time();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: manage.php');
        exit();
    }
    $error = failed_login_message('username');
}

$page_title = 'Staff Login';
$page_description = 'Staff login for Community Connect.';
$current_page = 'stafflogin';
include 'header.inc';
?>

<main id="main">
  <h1>Staff Login</h1>

  <?php if (isset($_GET['expired'])) { ?>
    <p class="message message-error">You were logged out after 30 minutes of inactivity. Please log in again.</p>
  <?php } ?>

  <?php if ($error != '') { ?>
    <p class="message message-error" role="alert"><?php echo e($error); ?></p>
  <?php } ?>

  <form method="post" action="stafflogin.php">
    <input type="hidden" name="token" value="<?php echo e(csrf_token()); ?>">
    <div class="field">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" autocomplete="username" required>
    </div>

    <div class="field">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" autocomplete="current-password" required>
    </div>

    <div class="group">
      <button type="submit" class="btn">Log In</button>
      <a class="btn-secondary" href="userlogin.php">Check Application</a>
    </div>
  </form>
</main>

<?php include 'footer.inc'; ?>
