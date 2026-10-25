<?php
require_once 'session.inc';
require_once 'db_connect.inc';
require_once 'error.inc';

if (!$conn) {
    show_database_error();
}

$error = '';
$max_attempts = 5;
$dummy_hash = '$2y$10$E4UH1PSby88iafe4viaiduqs8xxqYnEwXvd2IlIAk6LrRF5ilhA5e';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = is_string($_POST['username'] ?? null) ? trim($_POST['username']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

    mysqli_begin_transaction($conn);
    $stmt = mysqli_prepare($conn,
        "SELECT password_hash, failed_attempts, lockout_until, lockout_minutes FROM staff WHERE username = ? FOR UPDATE");
    $found = false;
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $hash, $attempts, $lock_until, $lock_minutes);
        $found = (bool) mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
    }

    $is_locked = $found && $lock_until !== null && strtotime($lock_until) > time();

    if ($is_locked) {
        $error = lockout_message((int) ceil((strtotime($lock_until) - time()) / 60));

    } elseif (password_verify($password, $found ? $hash : $dummy_hash) && $found) {
        // Correct: clear the failed count and lockout, then log in as staff
        $stmt = mysqli_prepare($conn,
            "UPDATE staff SET failed_attempts = 0, lockout_until = NULL WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_commit($conn);

        session_regenerate_id(true);
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'staff';
        $_SESSION['last_activity'] = time();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: manage.php');
        exit();

    } else {
        $error = 'Incorrect username or password.';

        if ($found) {
            $attempts = $attempts + 1;
            $lock_time = null;

            if ($attempts >= $max_attempts) {
                $lock_time = date('Y-m-d H:i:s', time() + $lock_minutes * 60);
                $attempts = 0;
                $error = lockout_message((int) $lock_minutes);
            }

            $stmt = mysqli_prepare($conn,
                "UPDATE staff SET failed_attempts = ?, lockout_until = ? WHERE username = ?");
            mysqli_stmt_bind_param($stmt, "iss", $attempts, $lock_time, $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_commit($conn);
}

$page_title = 'Staff login';
$page_description = 'Staff login for Community Connect.';
$current_page = 'stafflogin';
include 'header.inc';
?>

<main id="main">
  <h1>Staff login</h1>

  <?php if (isset($_GET['expired'])) { ?>
    <p class="message message-error">You were logged out after 30 minutes of inactivity. Please log in again.</p>
  <?php } ?>

  <?php if ($error != '') { ?>
    <p class="message message-error" role="alert"><?php echo e($error); ?></p>
  <?php } ?>

  <form method="post" action="stafflogin.php">
    <div class="field">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" autocomplete="username" required>
    </div>

    <div class="field">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" autocomplete="current-password" required>
    </div>

    <div class="group">
      <button type="submit" class="btn">Log in</button>
      <a class="btn-secondary" href="userlogin.php">Check Application</a>
    </div>
  </form>
</main>

<?php include 'footer.inc'; ?>
