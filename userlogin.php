<?php
session_start();
require_once 'db_connect.inc';

$error = '';
$max_attempts = 5;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!$conn) {
        $error = $db_error_message;
    } else {
        $stmt = mysqli_prepare($conn,
            "SELECT password_hash, failed_attempts, lockout_until, lockout_minutes FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $hash, $attempts, $lock_until, $lock_minutes);
        $found = mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        $is_locked = $found && $lock_until !== null && strtotime($lock_until) > time();

        if ($is_locked) {
            $minutes_left = ceil((strtotime($lock_until) - time()) / 60);
            $error = "Too many failed attempts. Try again in $minutes_left minute(s).";

        } elseif ($found && ($password == $hash)) {
            // Correct: clear the failed count and lockout, then log in as a user
            $stmt = mysqli_prepare($conn,
                "UPDATE users SET failed_attempts = 0, lockout_until = NULL WHERE username = ?");
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            session_regenerate_id(true);
            $_SESSION['username'] = $username;
            $_SESSION['role'] = 'user';
            $_SESSION['last_activity'] = time();
            header('Location: index.php');
            exit();

        } else {
            $error = 'Incorrect username or password.';

            if ($found) {
                $attempts = $attempts + 1;
                $lock_time = null;

                if ($attempts >= $max_attempts) {
                    $lock_time = date('Y-m-d H:i:s', time() + $lock_minutes * 60);
                    $attempts = 0;
                    $error = "Too many failed attempts. Try again in $lock_minutes minutes.";
                }

                $stmt = mysqli_prepare($conn,
                    "UPDATE users SET failed_attempts = ?, lockout_until = ? WHERE username = ?");
                mysqli_stmt_bind_param($stmt, "iss", $attempts, $lock_time, $username);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }
}

$page_title = 'User login';
$page_description = 'User login for Community Connect.';
$current_page = 'userlogin';
include 'header.inc';
?>

<main id="main">
  <h1>User login</h1>

  <?php if ($error != '') { ?>
    <p class="message message-error"><?php echo $error; ?></p>
  <?php } ?>

  <form method="post" action="userlogin.php">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required><br>

    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required><br>

    <div class="group">
      <button type="submit" class="btn">Login</button>
      <a class="btn-secondary" href="stafflogin.php">Staff login</a>
    </div>
  </form>
</main>

<?php include 'footer.inc'; ?>