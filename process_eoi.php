<?php
require_once "db_connect.inc";   // gives you $conn
require_once "functions.inc";    // gives you clean_input() and e()

if (!$conn) {
    echo "<p>" . $db_error_message . "</p>";   // database not reachable
} else {
    $required_fields = ['job_ref','first_name','last_name','dob','street','suburb',
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
?>
