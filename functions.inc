<?php
/*
Helps with a safety check of data transfer between frontend and backend
*/

date_default_timezone_set("Australia/Melbourne");

# Clean user input (form or URL): trim and escape HTML.
function clean_input($data) {
    if (!is_string($data)) {
        return "";
    }
    $data = trim($data);
    $data = htmlspecialchars($data, ENT_QUOTES, "UTF-8");
    return $data;
}

# Escape database text before printing it, for team: echo "<h2>" . e($row["title"]) . "</h2>";
function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

# The form data was already escaped by clean_input() before saving,
# so we undo that first, otherwise "&" would show up as "&amp;" on the page
function show($value) {
    return e(html_entity_decode((string) $value, ENT_QUOTES, "UTF-8"));
}

function post_value($key) {
    return isset($_POST[$key]) && is_string($_POST[$key]) ? clean_input($_POST[$key]) : "";
}

function text_length($value) {
    return mb_strlen(str_replace("\r\n", "\n", html_entity_decode((string) $value, ENT_QUOTES, "UTF-8")));
}

function skill_options() {
    return ["Problem solving", "Communication", "Teamwork"];
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

function check_login($conn, $find_sql, $find_value, $table, $id_column, $password) {
    $dummy_hash = '$2y$10$E4UH1PSby88iafe4viaiduqs8xxqYnEwXvd2IlIAk6LrRF5ilhA5e';
    $max_attempts = 5;

    mysqli_begin_transaction($conn);
    $stmt = mysqli_prepare($conn, $find_sql);
    $found = false;
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $find_value);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $id, $hash, $attempts, $lock_until, $lock_minutes);
        $found = (bool) mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
    }

    $is_locked = $found && $lock_until !== null && strtotime($lock_until) > time();
    $correct = password_verify($password, $found ? $hash : $dummy_hash) && $found && !$is_locked;

    if ($found && !$is_locked) {
        $attempts = $correct ? 0 : $attempts + 1;
        $lock_time = null;
        if ($attempts >= $max_attempts) {
            $lock_time = date("Y-m-d H:i:s", time() + $lock_minutes * 60);
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
    return "Incorrect " . $what . " or password. After 5 failed attempts, logging in is paused for 15 minutes.";
}

function validate_application($f) {
    $errors = [];

    foreach (["first_name" => "First name", "last_name" => "Last name"] as $key => $label) {
        if (!preg_match('/^[A-Za-z ]{1,20}$/', $f[$key]) || trim($f[$key]) === "") {
            $errors[] = "$label must be 1 to 20 letters.";
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

    if (!in_array($f["gender"], ["Male", "Female", "Other"], true)) {
        $errors[] = "Please select a gender.";
    }

    foreach (["street" => "Street address", "suburb" => "Suburb or town"] as $key => $label) {
        if ($f[$key] === "") {
            $errors[] = "Please enter your " . strtolower($label) . ".";
        } elseif (text_length($f[$key]) > 40) {
            $errors[] = "$label must be 40 characters or fewer.";
        }
    }

    $postcode_starts = [
        "VIC" => ["3", "8"], "NSW" => ["1", "2"], "QLD" => ["4", "9"], "NT" => ["0"],
        "WA"  => ["6"],      "SA"  => ["5"],      "TAS" => ["7"],      "ACT" => ["0", "2"],
    ];
    if (!isset($postcode_starts[$f["state"]])) {
        $errors[] = "Please select a valid state.";
    }
    if (!preg_match('/^\d{4}$/', $f["postcode"])) {
        $errors[] = "Postcode must be exactly 4 digits.";
    } elseif (isset($postcode_starts[$f["state"]]) && !in_array($f["postcode"][0], $postcode_starts[$f["state"]], true)) {
        $errors[] = "Postcode does not match the selected state.";
    }

    $email = html_entity_decode($f["email"], ENT_QUOTES, "UTF-8");
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
        $errors[] = "Please enter a valid email address.";
    }

    if (!preg_match('/^\d{10}$/', $f["phone"])) {
        $errors[] = "Phone number must be exactly 10 digits.";
    }

    if (!$f["skills"] && $f["other_skills"] === "") {
        $errors[] = "Please select at least one skill, or describe your skills in the other skills box.";
    }
    if (text_length($f["other_skills"]) > 1000) {
        $errors[] = "Other skills must be 1000 characters or fewer.";
    }

    return $errors;
}
?>
