<?php
/*
Helps with a safety check of data transfer between frontend and backend
*/

date_default_timezone_set("Australia/Melbourne");

// Clean user input (form or URL): strip slashes, trim, escape HTML.
function clean_input($data) {
    if (!is_string($data)) {
        return "";
    }
    $data = stripslashes($data);
    $data = trim($data);
    $data = htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
    return $data;
}

// Escape database text before printing it, for team: echo "<h2>" . e($row["title"]) . "</h2>";
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

// The form data was already escaped by clean_input() before saving,
// so we undo that first, otherwise "&" would show up as "&amp;" on the page
function show($value) {
    return e(html_entity_decode((string) $value, ENT_QUOTES, "UTF-8"));
}

// Reads one text value from the URL or a form. If it's missing (or someone sends an array), we just get ""
function field($source, $key) {
    return (isset($source[$key]) && is_string($source[$key])) ? clean_input($source[$key]) : "";
}

function post_value($key) {
    return field($_POST, $key);
}

function text_length($value) {
    return mb_strlen(str_replace("\r\n", "\n", html_entity_decode((string) $value, ENT_QUOTES, "UTF-8")));
}

function skill_options() {
    return ["Problem solving", "Communication", "Teamwork"];
}

function gender_options() {
    return ["Male", "Female", "Other"];
}

function state_options() {
    return ["VIC", "NSW", "QLD", "NT", "WA", "SA", "TAS", "ACT"];
}

function status_options() {
    return ["New", "Current", "Final"];
}

function locked_statuses() {
    return ["Current", "Final"];
}

function allowed_skills($values) {
    if (!is_array($values)) {
        return [];
    }
    return array_values(array_intersect(skill_options(), array_filter($values, "is_string")));
}

function like_pattern($text) {
    return "%" . addcslashes($text, "\\%_") . "%";
}

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
        mysqli_stmt_bind_param($stmt, "iss", $attempts, $lock_time, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    mysqli_commit($conn);

    return $correct ? $id : null;
}

function show_errors($messages) {
    if (!$messages) {
        return;
    }
    echo "<div class=\"message message-error\" role=\"alert\">\n<ul>\n";
    foreach ($messages as $message) {
        echo "<li>" . e($message) . "</li>\n";
    }
    echo "</ul>\n</div>\n";
}

function failed_login_message($what) {
    return "Incorrect " . $what . " or password. After " . MAX_LOGIN_ATTEMPTS . " failed attempts, logging in is paused for " . LOCKOUT_MINUTES . " minutes.";
}

function validate_password($password, $confirm) {
    if (strlen($password) < 8) {
        return "Password must be at least 8 characters.";
    }
    if (strlen($password) > 72) {
        return "Password must be 72 characters or fewer.";
    }
    if (strpos($password, "\0") !== false) {
        return "Password contains a character that is not allowed.";
    }
    if (trim($password) === "") {
        return "Password cannot be only spaces.";
    }
    if ($password !== $confirm) {
        return "Passwords do not match.";
    }
    return "";
}

function validate_application($f) {
    $errors = [];

    foreach (["first_name" => "First name", "last_name" => "Last name"] as $key => $label) {
        if (!preg_match('/^[A-Za-z ]{1,20}$/D', $f[$key]) || trim($f[$key]) === "") {
            $errors[] = "$label must be 1 to 20 characters using only letters and spaces.";
        }
    }

    if (!$f["dob"]) {
        $errors[] = $f["dob_message"];
    } else {
        $today = new DateTime("today");
        if ($f["dob"] > $today) {
            $errors[] = "Date of birth cannot be in the future.";
        } else {
            $age = $f["dob"]->diff($today)->y;
            if ($age < 15 || $age > 80) {
                $errors[] = "Applicants must be aged between 15 and 80.";
            }
        }
    }

    if (!in_array($f["gender"], gender_options(), true)) {
        $errors[] = "Please select a gender.";
    }

    $street = html_entity_decode($f["street"], ENT_QUOTES, "UTF-8");
    if ($street === "") {
        $errors[] = "Please enter your street address.";
    } elseif (!preg_match("/^[A-Za-z0-9 \\/,.'-]{3,40}$/D", $street)) {
        $errors[] = "Street address must be 3 to 40 characters using only letters, numbers, spaces and / , . ' -";
    }

    $suburb = html_entity_decode($f["suburb"], ENT_QUOTES, "UTF-8");
    if ($suburb === "") {
        $errors[] = "Please enter your suburb or town.";
    } elseif (!preg_match('/^[A-Za-z ]{3,40}$/D', $suburb)) {
        $errors[] = "Suburb or town must be 3 to 40 characters using only letters and spaces.";
    }

    $postcode_starts = [
        "VIC" => ["3", "8"], "NSW" => ["1", "2"], "QLD" => ["4", "9"], "NT" => ["0"],
        "WA"  => ["6"],      "SA"  => ["5"],      "TAS" => ["7"],      "ACT" => ["0", "2"],
    ];
    if (!in_array($f["state"], state_options(), true)) {
        $errors[] = "Please select a valid state.";
    }
    if (!preg_match('/^\d{4}$/D', $f["postcode"])) {
        $errors[] = "Postcode must be exactly 4 digits.";
    } elseif (isset($postcode_starts[$f["state"]]) && !in_array($f["postcode"][0], $postcode_starts[$f["state"]], true)) {
        $errors[] = "Postcode does not match the selected state.";
    }

    $email = html_entity_decode($f["email"], ENT_QUOTES, "UTF-8");
    if (!preg_match('/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/D', $email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
        $errors[] = "Please enter a valid email address.";
    }

    if (!preg_match('/^\d{10}$/D', $f["phone"])) {
        $errors[] = "Phone number must be exactly 10 digits.";
    }

    if (!$f["skills"] && $f["other_skills"] === "") {
        $errors[] = "Please select at least one skill, or describe your skills in the other skills box.";
    }
    if (text_length($f["other_skills"]) > 1000) {
        $errors[] = "Other skills must be 1000 characters or fewer.";
    }
    if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', html_entity_decode($f["other_skills"], ENT_QUOTES, "UTF-8"))) {
        $errors[] = "Other skills contains characters that are not allowed.";
    }

    return $errors;
}
?>
