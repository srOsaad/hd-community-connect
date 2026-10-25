<?php
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
