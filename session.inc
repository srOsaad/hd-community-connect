<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(["httponly" => true, "samesite" => "Lax"]);
    session_start();
}

const SESSION_TIMEOUT = 30 * 60; // 30 minutes, in seconds

function is_staff_logged_in() {
    return isset($_SESSION["username"]) && ($_SESSION["role"] ?? "") === "staff"
        && time() - ($_SESSION["last_activity"] ?? 0) <= SESSION_TIMEOUT;
}

function end_staff_session() {
    unset($_SESSION["username"], $_SESSION["role"], $_SESSION["last_activity"]);
}

function end_applicant_session() {
    unset($_SESSION["eoi_id"], $_SESSION["application_id"], $_SESSION["applicant_last_activity"]);
}
?>
