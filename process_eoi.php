<?php
require_once "db_connect.inc";   // gives you $conn
require_once "functions.inc";    // gives you clean_input() and e()
require_once "error.inc";
require_once "session.inc";

function keep_form_values() {
    require_once "session.inc";
    $fields = ['job_reference_number', 'first_name', 'last_name', 'DateOfBirth', 'Gender', 'streetaddress',
               'suburb_town', 'state', 'postcode', 'email', 'phone', 'Other_skill'];
    $old = [];
    foreach ($fields as $field) {
        if (isset($_POST[$field]) && is_string($_POST[$field])) {
            $old[$field] = $_POST[$field];
        }
    }
    $old['skills'] = allowed_skills($_POST['skills'] ?? []);
    $_SESSION['apply_old'] = $old;
}

function show_application_error($messages, $status = 400) {
    show_error_page("Please fix the following problems", $messages, "apply.php", "Return to Application Form", $status);
}

function make_application_id() {
    $now = new DateTime('now', new DateTimeZone('Australia/Melbourne'));
    $ms = str_pad((string) intdiv((int) $now->format('u'), 1000), 2, '0', STR_PAD_LEFT);
    return $ms . $now->format('s') . $now->format('i') . $now->format('H')
         . $now->format('d') . $now->format('m') . $now->format('Y');
}

if (!$conn) {
    show_database_error("apply.php", "Return to Application Form");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: apply.php");
    exit();
}

if (!csrf_ok()) {
    keep_form_values();
    show_application_error(["That request could not be verified. Please try again."]);
}

// Sanitise
$job_ref     = strtoupper(post_value('job_reference_number'));
$password    = is_string($_POST['password'] ?? null) ? $_POST['password'] : "";
$confirm     = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : "";
$first_name  = post_value('first_name');
$last_name   = post_value('last_name');
$DOB         = post_value('DateOfBirth');
$gender      = post_value('Gender');
$street      = post_value('streetaddress');
$suburb      = post_value('suburb_town');
$state       = strtoupper(post_value('state'));
$postcode    = post_value('postcode');
$email       = post_value('email');
$phone       = post_value('phone');
$other_skill = post_value('Other_skill');
$skills_arr  = allowed_skills($_POST['skills'] ?? []);

// Validate
$errors = [];

// Job reference: exactly 6 alphanumeric characters
$job_exists = false;
if (preg_match('/^[A-Z0-9]{6}$/', $job_ref)) {
    $job_stmt = mysqli_prepare($conn, "SELECT 1 FROM jobs WHERE reference = ?");
    mysqli_stmt_bind_param($job_stmt, "s", $job_ref);
    mysqli_stmt_execute($job_stmt);
    mysqli_stmt_store_result($job_stmt);
    $job_exists = mysqli_stmt_num_rows($job_stmt) > 0;
    mysqli_stmt_close($job_stmt);
}
if (!$job_exists) {
    $errors[] = "Please select a valid job.";
}

// DOB: dd/mm/yyyy, real date, age 15-80
$dob_date = null;
$dob_sql = null;
if (preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $DOB, $m) && checkdate((int) $m[2], (int) $m[1], (int) $m[3])) {
    $dob_sql = "$m[3]-$m[2]-$m[1]";
    $dob_date = DateTime::createFromFormat('!Y-m-d', $dob_sql);
}

$errors = array_merge($errors, validate_application([
    'first_name' => $first_name, 'last_name' => $last_name,
    'dob' => $dob_date, 'dob_message' => "Date of birth must be a valid date in dd/mm/yyyy format.",
    'gender' => $gender, 'street' => $street, 'suburb' => $suburb,
    'state' => $state, 'postcode' => $postcode, 'email' => $email, 'phone' => $phone,
    'skills' => $skills_arr, 'other_skills' => $other_skill,
]));

if (strlen($password) < 8)
    $errors[] = "Password must be at least 8 characters.";
elseif (strpos($password, "\0") !== false)
    $errors[] = "Password contains a character that is not allowed.";
elseif ($password !== $confirm)
    $errors[] = "Passwords do not match.";

if ($errors) {
    keep_form_values();
    show_application_error($errors);
}

// Database
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
    Email         VARCHAR(1000) NOT NULL,
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
    show_error_page("Sorry, we could not process your application", ["Please try again later."], "apply.php", "Return to Application Form", 503);
}

// Checking insert
$skills_str = implode(",", $skills_arr);   // SET needs commas with no spaces

$stmt = mysqli_prepare($conn,
    "INSERT INTO eoi (ApplicationID, JobReference, FirstName, LastName, DOB, Gender, StreetAddress, Suburb,
                      State, Postcode, Email, Phone, Skills, OtherSkills)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
if (!$stmt) {
    show_error_page("Sorry, we could not process your application", ["Please try again later."], "apply.php", "Return to Application Form", 503);
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

    require_once "session.inc";
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
    keep_form_values();
    show_error_page("Sorry, we could not process your application", ["Your application could not be saved. Please try again."], "apply.php", "Return to Application Form", 503);
}

?>