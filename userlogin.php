<?php
session_start();
require_once 'db_connect.inc';

$error = '';
$max_attempts = 5;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $eoi_number = (int) trim((string) ($_POST['eoi_number'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!$conn) {
        $error = $db_error_message;
    } else {
        $stmt = mysqli_prepare($conn,
            "SELECT password_hash, failed_attempts, lockout_until, lockout_minutes FROM users WHERE eoi_number = ?");
        mysqli_stmt_bind_param($stmt, "i", $eoi_number);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $hash, $attempts, $lock_until, $lock_minutes);
        $found = mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        $is_locked = $found && $lock_until !== null && strtotime($lock_until) > time();

        if ($is_locked) {
            $minutes_left = ceil((strtotime($lock_until) - time()) / 60);
            $error = "Too many failed attempts. Try again in $minutes_left minute(s).";

        } elseif ($found && password_verify($password, $hash)) {
            // Correct: clear the failed count and lockout, then log in as a user
            $stmt = mysqli_prepare($conn,
                "UPDATE users SET failed_attempts = 0, lockout_until = NULL WHERE eoi_number = ?");
            mysqli_stmt_bind_param($stmt, "i", $eoi_number);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            session_regenerate_id(true);
            $_SESSION['eoi_id'] = $eoi_number;
            header('Location: applicant.php');
            exit();

        } else {
            $error = 'Incorrect application ID or password.';

            if ($found) {
                $attempts = $attempts + 1;
                $lock_time = null;

                if ($attempts >= $max_attempts) {
                    $lock_time = date('Y-m-d H:i:s', time() + $lock_minutes * 60);
                    $attempts = 0;
                    $error = "Too many failed attempts. Try again in $lock_minutes minutes.";
                }

                $stmt = mysqli_prepare($conn,
                    "UPDATE users SET failed_attempts = ?, lockout_until = ? WHERE eoi_number = ?");
                mysqli_stmt_bind_param($stmt, "isi", $attempts, $lock_time, $eoi_number);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }
}

$page_title = 'Check your application';
$page_description = 'Check the status of your Community Connect job application.';
$current_page = 'userlogin';
include 'header.inc';
?>

<main id="main">
  <h1>Check your application</h1>

  <?php if ($error != '') { ?>
    <p class="message message-error"><?php echo $error; ?></p>
  <?php } ?>

  <form method="post" action="userlogin.php">
    <label for="eoi_number">Application ID:</label>
    <input type="text" id="eoi_number" name="eoi_number" inputmode="numeric" pattern="[0-9]+" required><br>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required><br>

    <div class="group">
      <button type="submit" class="btn">Check application</button>
      <a class="btn-secondary" href="stafflogin.php">Staff login</a>
    </div>
  </form>
</main>

<?php include 'footer.inc'; ?>