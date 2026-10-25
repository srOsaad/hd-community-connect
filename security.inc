<?php
function csrf_token() {
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}

function csrf_ok() {
    $sent = isset($_POST["token"]) && is_string($_POST["token"]) ? $_POST["token"] : "";
    return hash_equals(csrf_token(), $sent);
}

const MAX_LOGIN_ATTEMPTS = 5;
const LOCKOUT_MINUTES = 15;

function check_login($conn, $find_sql, $find_value, $table, $id_column, $password) {
    $dummy_hash = '$2y$10$E4UH1PSby88iafe4viaiduqs8xxqYnEwXvd2IlIAk6LrRF5ilhA5e';

    mysqli_begin_transaction($conn);
    $stmt = mysqli_prepare($conn, $find_sql);
    $found = false;
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $find_value);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $id, $hash, $attempts, $lock_until);
        $found = (bool) mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
    }

    $is_locked = $found && $lock_until !== null && strtotime($lock_until) > time();
    $correct = password_verify($password, $found ? $hash : $dummy_hash) && $found && !$is_locked;

    if ($found && !$is_locked) {
        $attempts = $correct ? 0 : $attempts + 1;
        $lock_time = null;
        if ($attempts >= MAX_LOGIN_ATTEMPTS) {
            $lock_time = date("Y-m-d H:i:s", time() + LOCKOUT_MINUTES * 60);
            $attempts = 0;
        }
        $stmt = mysqli_prepare($conn, "UPDATE $table SET failed_attempts = ?, lockout_until = ? WHERE $id_column = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "iss", $attempts, $lock_time, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_commit($conn);

    return $correct ? $id : null;
}

function failed_login_message($what) {
    return "Incorrect " . $what . " or password. After " . MAX_LOGIN_ATTEMPTS . " failed attempts, logging in is paused for " . LOCKOUT_MINUTES . " minutes.";
}
?>
