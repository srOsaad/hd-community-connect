<?php
require_once "auth.inc";
require_once "db_connect.inc";
require_once "functions.inc";
require_once "error.inc";

if (!$conn) {
    show_database_error("manage.php", "Back to Manage");
}

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
$token = $_SESSION["csrf_token"];

$errors = [];
$success = "";
$new_username = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_username = is_string($_POST["username"] ?? null) ? trim($_POST["username"]) : "";
    $password = is_string($_POST["password"] ?? null) ? $_POST["password"] : "";
    $confirm = is_string($_POST["confirm_password"] ?? null) ? $_POST["confirm_password"] : "";
    $sent_token = is_string($_POST["token"] ?? null) ? $_POST["token"] : "";

    if (!hash_equals($token, $sent_token)) {
        $errors[] = "That request could not be verified. Please try again.";
    } else {
        if (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $new_username)) {
            $errors[] = "Username must be 3 to 20 letters, numbers or underscores.";
        }
        if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must be at least 8 characters and include at least one letter and one number.";
        } elseif (strpos($password, "\0") !== false) {
            $errors[] = "Password contains a character that is not allowed.";
        } elseif ($password !== $confirm) {
            $errors[] = "Passwords do not match.";
        }
    }

    if (!$errors) {
        $stmt = mysqli_prepare($conn, "SELECT 1 FROM staff WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $new_username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $taken = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);

        if ($taken) {
            $errors[] = "That username is already taken.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "INSERT INTO staff (username, password_hash) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt, "ss", $new_username, $hash);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Manager account \"" . $new_username . "\" has been created.";
                $new_username = "";
            } else {
                error_log("Staff register failed: " . mysqli_stmt_error($stmt));
                $errors[] = "The account could not be created. Please try again.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

$page_title = "Add a Manager";
$page_description = "Create a new HR manager account for Community Connect.";
$current_page = "manage";
include "header.inc";
?>
  <main id="main">
    <h1>Add a Manager</h1>
    <p class="intro">Create a login for another HR manager.</p>

<?php if ($success !== "") { ?>
    <p class="message message-success" role="status"><?php echo e($success); ?></p>
<?php } ?>

<?php if ($errors) { ?>
    <div class="message message-error" role="alert">
      <ul>
<?php foreach ($errors as $error) { ?>
        <li><?php echo e($error); ?></li>
<?php } ?>
      </ul>
    </div>
<?php } ?>

    <form method="post" action="staffregister.php" novalidate>
      <input type="hidden" name="token" value="<?php echo $token; ?>">
      <div class="field">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" maxlength="20" autocomplete="off" value="<?php echo e($new_username); ?>" required>
        <p class="hint">3 to 20 letters, numbers or underscores.</p>
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" minlength="8" autocomplete="new-password" required>
        <p class="hint">At least 8 characters, with at least one letter and one number.</p>
      </div>
      <div class="field">
        <label for="confirm_password">Confirm password</label>
        <input type="password" id="confirm_password" name="confirm_password" minlength="8" autocomplete="new-password" required>
      </div>
      <div class="group">
        <input class="btn" type="submit" value="Create Account">
        <a class="btn-secondary" href="manage.php">Back to Manage</a>
      </div>
    </form>
  </main>

<?php include "footer.inc"; ?>