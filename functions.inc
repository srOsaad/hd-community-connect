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
    return mb_strlen(html_entity_decode((string) $value, ENT_QUOTES, "UTF-8"));
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

function lockout_message($minutes) {
    return "Too many failed attempts. Try again in " . $minutes . ($minutes === 1 ? " minute." : " minutes.");
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
