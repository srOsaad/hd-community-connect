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
$max_attempts = 5;
$dummy_hash = '$2y$10$E4UH1PSby88iafe4viaiduqs8xxqYnEwXvd2IlIAk6LrRF5ilhA5e';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !csrf_ok()) {
    $error = 'That request could not be verified. Please try again.';
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $application_id = is_string($_POST['application_id'] ?? null) ? trim($_POST['application_id']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

    mysqli_begin_transaction($conn);
    $stmt = mysqli_prepare($conn,
        "SELECT u.eoi_number, u.password_hash, u.failed_attempts, u.lockout_until, u.lockout_minutes
         FROM users u JOIN eoi e ON e.EOInumber = u.eoi_number WHERE e.ApplicationID = ? FOR UPDATE");
    $found = false;
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $application_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $eoi_number, $hash, $attempts, $lock_until, $lock_minutes);
        $found = (bool) mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
    }

    $is_locked = $found && $lock_until !== null && strtotime($lock_until) > time();

    if ($is_locked) {
        $error = failed_login_message('application ID');

    } elseif (password_verify($password, $found ? $hash : $dummy_hash) && $found) {
        // Correct: clear the failed count and lockout, then log in as a user
        $stmt = mysqli_prepare($conn,
            "UPDATE users SET failed_attempts = 0, lockout_until = NULL WHERE eoi_number = ?");
        mysqli_stmt_bind_param($stmt, "i", $eoi_number);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_commit($conn);

        session_regenerate_id(true);
        $_SESSION['eoi_id'] = $eoi_number;
        $_SESSION['application_id'] = $application_id;
        $_SESSION['applicant_last_activity'] = time();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: applicant.php');
        exit();

    } else {
        $error = failed_login_message('application ID');

        if ($found) {
            $attempts = $attempts + 1;
            $lock_time = null;

            if ($attempts >= $max_attempts) {
                $lock_time = date('Y-m-d H:i:s', time() + $lock_minutes * 60);
                $attempts = 0;
            }

            $stmt = mysqli_prepare($conn,
                "UPDATE users SET failed_attempts = ?, lockout_until = ? WHERE eoi_number = ?");
            mysqli_stmt_bind_param($stmt, "isi", $attempts, $lock_time, $eoi_number);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_commit($conn);
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