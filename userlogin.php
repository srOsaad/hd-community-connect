<?php
require_once 'session.inc';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !empty($_SESSION['eoi_id'])) {
    header('Location: applicant.php');
    exit();
}

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
    $application_id = is_string($_POST['application_id'] ?? null) ? trim($_POST['application_id']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

    $eoi_number = check_login($conn,
        "SELECT u.eoi_number, u.password_hash, u.failed_attempts, u.lockout_until, u.lockout_minutes
         FROM users u JOIN eoi e ON e.EOInumber = u.eoi_number WHERE e.ApplicationID = ? FOR UPDATE",
        $application_id, "users", "eoi_number", $password);

    if ($eoi_number !== null) {
        // Correct: log in as a user
        session_regenerate_id(true);
        $_SESSION['eoi_id'] = $eoi_number;
        $_SESSION['application_id'] = $application_id;
        $_SESSION['applicant_last_activity'] = time();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: applicant.php');
        exit();
    }
    $error = failed_login_message('application ID');
}

$page_title = 'Check Your Application';
$page_description = 'Check the status of your Community Connect job application.';
$current_page = 'userlogin';
include 'header.inc';
?>

<main id="main">
  <h1>Check Your Application</h1>

  <?php if (isset($_GET['expired'])) { ?>
    <p class="message message-error">You were logged out after 30 minutes of inactivity. Please log in again.</p>
  <?php } ?>

  <?php if ($error != '') { ?>
    <p class="message message-error" role="alert"><?php echo e($error); ?></p>
  <?php } ?>

  <form method="post" action="userlogin.php">
    <input type="hidden" name="token" value="<?php echo e(csrf_token()); ?>">
    <div class="field">
      <label for="application_id">Application ID</label>
      <input type="text" id="application_id" name="application_id" inputmode="numeric" pattern="[0-9]{16,17}" maxlength="17" required>
      <p class="hint">The 16 or 17 digit number shown after you applied.</p>
    </div>

    <div class="field">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" autocomplete="current-password" required>
    </div>

    <div class="group">
      <button type="submit" class="btn">Check Application</button>
      <a class="btn-secondary" href="stafflogin.php">Staff Login</a>
    </div>
  </form>
</main>

<?php include 'footer.inc'; ?>