<?php
require_once "db_connect.inc";   // gives you $conn
require_once "functions.inc";    // gives you clean_input() and e()
require_once "error.inc";

function render_page($title, $heading, $messages, $is_error) {
    show_error_page($heading, $messages, "apply.php", "Return to application form", $title === "Database Error" ? 503 : 400);
}

function keep_form_values() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $fields = ['job_reference_number', 'first_name', 'last_name', 'DateOfBirth', 'Gender', 'streetaddress',
               'suburb_town', 'state', 'postcode', 'email', 'phone', 'Other_skill'];
    $old = [];
    foreach ($fields as $field) {
        if (isset($_POST[$field]) && is_string($_POST[$field])) {
            $old[$field] = $_POST[$field];
        }
    }
    $old['skills'] = isset($_POST['skills']) && is_array($_POST['skills']) ? array_values(array_filter($_POST['skills'], 'is_string')) : [];
    $_SESSION['apply_old'] = $old;
}

function make_application_id() {
    $now = new DateTime('now', new DateTimeZone('Australia/Melbourne'));
    $ms = str_pad((string) intdiv((int) $now->format('u'), 1000), 2, '0', STR_PAD_LEFT);
    return $ms . $now->format('s') . $now->format('i') . $now->format('H')
         . $now->format('d') . $now->format('m') . $now->format('Y');
}

if (!$conn) {
    show_database_error("apply.php", "Return to application form");
} else {
    $required_fields = ['job_reference_number','first_name','last_name','DateOfBirth','streetaddress','suburb_town',
                    'state','postcode','email','phone','password','confirm_password'];

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

/* ---------- Sanitise ---------- */
$job_ref     = strtoupper(clean_input($_POST['job_reference_number']));
$password    = (string) $_POST['password'];
$confirm     = (string) $_POST['confirm_password'];
$first_name  = clean_input($_POST['first_name']);
$last_name   = clean_input($_POST['last_name']);
$DOB         = clean_input($_POST['DateOfBirth']);
$gender      = isset($_POST['Gender']) ? clean_input($_POST['Gender']) : "";
$street      = clean_input($_POST['streetaddress']);
$suburb      = clean_input($_POST['suburb_town']);
$state       = strtoupper(clean_input($_POST['state']));
$postcode    = clean_input($_POST['postcode']);
$email       = clean_input($_POST['email']);
$phone       = clean_input($_POST['phone']);
$other_skill = isset($_POST['Other_skill']) ? clean_input($_POST['Other_skill']) : "";
$skills_arr = [];

if (isset($_POST['skills']) && is_array($_POST['skills'])) {
    foreach ($_POST['skills'] as $s) $skills_arr[] = clean_input($s);
}    



/* ---------- Validate ---------- */
$errors = [];
// Job reference: exactly 6 alphanumeric characters
if (!preg_match('/^[A-Za-z0-9]{6}$/', $job_ref))
    $errors[] = "Please select a valid job referrence.";

// Names: max 20 alpha characters (letters, hyphen, apostrophe, space allowed)
if ($first_name === "" || !preg_match("/^[A-Za-z\s]{1,20}$/", $first_name))
    $errors[] = "First name must be 1-20 letters only.";
if ($last_name === "" || !preg_match("/^[A-Za-z\s]{1,20}$/", $last_name))
    $errors[] = "Last name must be 1-20 letters only.";

// DOB: dd/mm/yyyy, real date, age 15-80
$dob_sql = null;
if (!preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $DOB, $m) || !checkdate((int)$m[2], (int)$m[1], (int)$m[3])) {
    $errors[] = "Date of birth must be a valid date in dd/mm/yyyy format.";
} else {
    $dob_sql = "$m[3]-$m[2]-$m[1]";
    $dob_date = new DateTime($dob_sql);
    $today    = new DateTime('today');

    if ($dob_date > $today) {
        $errors[] = "Date of birth is not valid.";
    } else {
        $age = $dob_date->diff($today)->y;
        if ($age < 15 || $age > 80) {
            $errors[] = "Applicants must be aged between 15 and 80.";
        }
    }
}

// Gender
$valid_genders = ['Male','Female','Other'];
if (!in_array($gender, $valid_genders, true))
    $errors[] = "Please select a gender option.";

// Address
if ($street === "") $errors[] = "Please enter your street address.";
elseif (text_length($street) > 40) $errors[] = "Street address must be 40 characters or fewer.";
if ($suburb === "") $errors[] = "Please enter your suburb or town.";
elseif (text_length($suburb) > 40) $errors[] = "Suburb or town must be 40 characters or fewer.";

// State + postcode (4 digits, first digit must match state)
$valid_states = ['VIC','NSW','QLD','NT','WA','SA','TAS','ACT'];
if (!in_array($state, $valid_states, true)) {
    $errors[] = "Please select a valid state.";
}
if (!preg_match('/^\d{4}$/', $postcode)) {
    $errors[] = "Postcode must be exactly 4 digits.";
} elseif (in_array($state, $valid_states, true)) {
    $first_digit_map = [
        'VIC' => ['3','8'], 'NSW' => ['1','2'], 'QLD' => ['4','9'], 'NT' => ['0'],
        'WA'  => ['6'],     'SA'  => ['5'],     'TAS' => ['7'],     'ACT' => ['0','2'],
    ];
    if (!in_array($postcode[0], $first_digit_map[$state], true))
        $errors[] = "Postcode does not match the selected state.";
}

// Email (check the raw value, since htmlspecialchars may alter odd characters)
if (!filter_var(html_entity_decode($email), FILTER_VALIDATE_EMAIL) || text_length($email) > 150)
    $errors[] = "Please enter a valid email address.";

// Phone: 10 digits
if (!preg_match('/^[0-9]{10}$/', $phone))
    $errors[] = "Phone number must be exactly 10 digits";

// Skills: at least one, or something in other skills
if (count($skills_arr) === 0 && $other_skill === "")
    $errors[] = "Please select at least one skill, or describe your skills in the other skills box.";
if (text_length($other_skill) > 1000)
    $errors[] = "Other skills must be 1000 characters or fewer.";

if (strlen($password) < 8)
    $errors[] = "Password must be at least 8 characters.";
elseif ($password !== $confirm)
    $errors[] = "Passwords do not match.";
if ($errors) {
    keep_form_values();
    render_page("Application Error", "Please fix the following problems", $errors, true);
    exit();
}
mysqli_report(MYSQLI_REPORT_OFF);
$conn = @mysqli_connect($host, $user, $pwd, $sql_db);
if (!$conn) {
    render_page("Database Error", "Sorry, we could not process your application", ["Database connection failed. Please try again later."], true);
    exit();
}

$job_stmt = mysqli_prepare($conn, "SELECT 1 FROM jobs WHERE reference = ?");
mysqli_stmt_bind_param($job_stmt, "s", $job_ref);
mysqli_stmt_execute($job_stmt);
mysqli_stmt_store_result($job_stmt);
$job_exists = mysqli_stmt_num_rows($job_stmt) > 0;
mysqli_stmt_close($job_stmt);

if (!$job_exists) {
    keep_form_values();
    render_page("Application Error", "Please fix the following problems",
        ["Please select a valid job."], true);
    mysqli_close($conn);
    exit();
}

 /* DATA BASE */
$create_sql = "CREATE TABLE IF NOT EXISTS eoi (
    EOInumber     INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ApplicationID VARCHAR(17)  NOT NULL UNIQUE,
    JobReference  CHAR(6)      NOT NULL,
    FirstName     VARCHAR(20)  NOT NULL,
    LastName      VARCHAR(20)  NOT NULL,
    DOB           DATE         NOT NULL,
    Gender        ENUM('Male','Female','Other') NOT NULL,
    StreetAddress VARCHAR(255) NOT NULL,
    Suburb        VARCHAR(255) NOT NULL,
    State         ENUM('VIC','NSW','QLD','NT','WA','SA','TAS','ACT') NOT NULL,
    Postcode      CHAR(4)      NOT NULL,
    Email         VARCHAR(255)  NOT NULL,
    Phone         VARCHAR(10)  NOT NULL,
    Skills        SET('Problem solving','Communication','Teamwork') NOT NULL,
    OtherSkills   TEXT NULL,
    Status        ENUM('New','Current','Final') NOT NULL DEFAULT 'New',
    FOREIGN KEY (JobReference) REFERENCES jobs(reference) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$create_users_sql = "CREATE TABLE IF NOT EXISTS users (
    eoi_number       INT UNSIGNED NOT NULL PRIMARY KEY,
    password_hash    VARCHAR(255) NOT NULL,
    failed_attempts  INT UNSIGNED NOT NULL DEFAULT 0,
    lockout_until    DATETIME     NULL,
    lockout_minutes  INT UNSIGNED NOT NULL DEFAULT 15,
    FOREIGN KEY (eoi_number) REFERENCES eoi(EOInumber) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
if (!mysqli_query($conn, $create_sql) || !mysqli_query($conn, $create_users_sql)) {
    render_page("Database Error", "Sorry, we could not process your application",
        ["Could not prepare the database table."], true);
    mysqli_close($conn);
    exit();
}

/* ---------- Checking insert ---------- */
$skills_str = implode(",", $skills_arr);   // SET needs commas with no spaces

$stmt = mysqli_prepare($conn,
    "INSERT INTO eoi (ApplicationID, JobReference, FirstName, LastName, DOB, Gender, StreetAddress, Suburb,
                      State, Postcode, Email, Phone, Skills, OtherSkills)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
if (!$stmt) {
    render_page("Database Error", "Sorry, we could not process your application",
        ["Could not prepare the statement."], true);
    mysqli_close($conn);
    exit();
}

$application_id = "";
mysqli_stmt_bind_param($stmt, "ssssssssssssss",
    $application_id, $job_ref, $first_name, $last_name, $dob_sql, $gender, $street, $suburb,
    $state, $postcode, $email, $phone, $skills_str, $other_skill);

mysqli_begin_transaction($conn);
$saved = false;
for ($try = 0; $try < 5 && !$saved; $try++) {
    $application_id = make_application_id();
    $saved = mysqli_stmt_execute($stmt);
    if (!$saved && mysqli_stmt_errno($stmt) !== 1062) {
        break;
    }
    if (!$saved) {
        usleep(1000);
    }
}
$new_eoi_id = $saved ? mysqli_insert_id($conn) : 0;

if ($saved) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $user_stmt = mysqli_prepare($conn, "INSERT INTO users (eoi_number, password_hash) VALUES (?, ?)");
    $saved = $user_stmt && mysqli_stmt_bind_param($user_stmt, "is", $new_eoi_id, $password_hash)
        && mysqli_stmt_execute($user_stmt);
    if ($user_stmt) {
        mysqli_stmt_close($user_stmt);
    }
}

if ($saved) {
    mysqli_commit($conn);
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    session_start();
    session_regenerate_id(true);
    unset($_SESSION['apply_old']);
    $_SESSION['apply_success'] = true;
    $_SESSION['first_name'] = $first_name;
    $_SESSION['eoi_id'] = $new_eoi_id;
    $_SESSION['applicant_last_activity'] = time();
    $_SESSION['application_id'] = $application_id;

    header("Location: success.php");
    exit();
} else {
    mysqli_rollback($conn);
    render_page("Database Error", "Sorry, we could not process your application",
        ["Your application could not be saved. Please try again."], true);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

?>