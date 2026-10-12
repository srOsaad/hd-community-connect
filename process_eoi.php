<?php
require_once "db_connect.inc";   // gives you $conn
require_once "functions.inc";    // gives you clean_input() and e()

if (!$conn) {
    echo "<p>" . $db_error_message . "</p>";   // database not reachable
} else {
    $required_fields = ['job_reference_number','first_name','last_name','DateOfBirth','Gender','streetaddress','suburb_town',
                    'state','postcode','email','phone'];

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: apply.php");
        exit();
}
    foreach ($required_fields as $f) {
        if (!isset($_POST[$f])) {          // field not even submitted => not from our form
            header("Location: apply.php");
            exit();
    }
}
}
$job_ref     = strtoupper(sanitise_input($_POST['job_reference_number']));
$first_name  = sanitise_input($_POST['first_name']);
$last_name   = sanitise_input($_POST['last_name']);
$DOB         = sanitise_input($_POST['DateOfBirth']);
$gender      = isset($_POST['Gender']) ? sanitise_input($_POST['Gender']) : "";
$street      = sanitise_input($_POST['streetaddress']);
$suburb      = sanitise_input($_POST['suburb_town']);
$state       = strtoupper(sanitise_input($_POST['state']));
$postcode    = sanitise_input($_POST['postcode']);
$email       = sanitise_input($_POST['email']);
$phone       = sanitise_input($_POST['phone']);
$other_skills = isset($_POST['other_skills']) ? sanitise_input($_POST['other_skills']) : "";

$skills_arr = [];
if (isset($_POST['skills']) && is_array($_POST['skills'])) {
    foreach ($_POST['skills'] as $s) $skills_arr[] = sanitise_input($s);
}

?>
