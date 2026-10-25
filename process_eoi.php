<?php
require_once "db_connect.inc";   // gives you $conn
require_once "functions.inc";    // gives you post_value() and validate_application()
require_once "error.inc";
require_once "application_form.inc";
require_once "session.inc";

function keep_form_values() {
    $_SESSION['apply_old'] = sent_form_values();
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: apply.php");
    exit();
}

$content_type = $_SERVER['CONTENT_TYPE'] ?? '';
$form_body = stripos($content_type, 'application/x-www-form-urlencoded') === 0 || stripos($content_type, 'multipart/form-data') === 0;
if (empty($_POST) && $form_body && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    show_application_error(["Your application was too large to send. Please shorten the other skills box and try again."], 413);
}

$required_fields = ['job_reference_number', 'first_name', 'last_name', 'DateOfBirth', 'streetaddress', 'suburb_town',
                    'state', 'postcode', 'email', 'phone', 'password', 'confirm_password'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field])) {
        header("Location: apply.php");
        exit();
    }
}

if (!csrf_ok()) {
    keep_form_values();
    show_application_error(["That request could not be verified. Please try again."]);
}

if (!$conn) {
    keep_form_values();
    show_database_error("apply.php", "Return to Application Form");
}

// Sanitise
$job_ref     = strtoupper(post_value('job_reference_number'));
$password    = is_string($_POST['password'] ?? null) ? $_POST['password'] : "";
$confirm     = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : "";
$form        = read_application_form();

// Validate
$errors = [];

// Job reference: exactly 6 alphanumeric characters
$job_exists = false;
if (preg_match('/^[A-Z0-9]{6}$/D', $job_ref)) {
    $job_stmt = mysqli_prepare($conn, "SELECT 1 FROM jobs WHERE reference = ?");
    if (!$job_stmt) {
        keep_form_values();
        show_error_page("Sorry, we could not process your application", ["Please try again later."], "apply.php", "Return to Application Form", 503);
    }
    mysqli_stmt_bind_param($job_stmt, "s", $job_ref);
    mysqli_stmt_execute($job_stmt);
    mysqli_stmt_store_result($job_stmt);
    $job_exists = mysqli_stmt_num_rows($job_stmt) > 0;
    mysqli_stmt_close($job_stmt);
}
if (!$job_exists) {
    $errors[] = "Please select a valid job.";
}

$errors = array_merge($errors, validate_application($form));

$password_error = validate_password($password, $confirm);
if ($password_error !== "") {
    $errors[] = $password_error;
}

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
    Email         VARCHAR(150) NOT NULL,
    Phone         VARCHAR(10)  NOT NULL,
    Skills        SET('Problem solving','Communication','Teamwork') NOT NULL,
    OtherSkills   TEXT NOT NULL,
    Status        ENUM('New','Current','Final') NOT NULL DEFAULT 'New',
    UNIQUE KEY job_email (JobReference, Email),
    INDEX (FirstName),
    INDEX (LastName),
    INDEX (Status),
    CONSTRAINT fk_eoi_job FOREIGN KEY (JobReference) REFERENCES jobs(reference) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$create_users_sql = "CREATE TABLE IF NOT EXISTS users (
    eoi_number       INT UNSIGNED NOT NULL PRIMARY KEY,
    password_hash    VARCHAR(255) NOT NULL,
    failed_attempts  INT UNSIGNED NOT NULL DEFAULT 0,
    lockout_until    DATETIME     NULL,
    FOREIGN KEY (eoi_number) REFERENCES eoi(EOInumber) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
if (!mysqli_query($conn, $create_sql) || !mysqli_query($conn, $create_users_sql)) {
    show_error_page("Sorry, we could not process your application", ["Please try again later."], "apply.php", "Return to Application Form", 503);
}

// Save the application
$skills_str = implode(",", $form["skills"]);   // SET needs commas with no spaces

$stmt = mysqli_prepare($conn,
    "INSERT INTO eoi (ApplicationID, JobReference, FirstName, LastName, DOB, Gender, StreetAddress, Suburb,
                      State, Postcode, Email, Phone, Skills, OtherSkills)
     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
if (!$stmt) {
    show_error_page("Sorry, we could not process your application", ["Please try again later."], "apply.php", "Return to Application Form", 503);
}

$application_id = "";
mysqli_stmt_bind_param($stmt, "ssssssssssssss",
    $application_id, $job_ref, $form["first_name"], $form["last_name"], $form["dob_sql"], $form["gender"],
    $form["street"], $form["suburb"], $form["state"], $form["postcode"], $form["email"], $form["phone"],
    $skills_str, $form["other_skills"]);

mysqli_begin_transaction($conn);

$dup_stmt = db_prepare($conn, "SELECT 1 FROM eoi WHERE JobReference = ? AND Email = ? FOR UPDATE");
mysqli_stmt_bind_param($dup_stmt, "ss", $job_ref, $form["email"]);
mysqli_stmt_execute($dup_stmt);
mysqli_stmt_store_result($dup_stmt);
$already_applied = mysqli_stmt_num_rows($dup_stmt) > 0;
mysqli_stmt_close($dup_stmt);

if ($already_applied) {
    mysqli_rollback($conn);
    keep_form_values();
    show_application_error(["You have already applied for this job with this email address."]);
}

$saved = false;
for ($try = 0; $try < 5 && !$saved; $try++) {
    $application_id = make_application_id();
    $saved = mysqli_stmt_execute($stmt);
    if (!$saved && mysqli_stmt_errno($stmt) === 1062 && strpos(mysqli_stmt_error($stmt), "job_email") !== false) {
        $already_applied = true;
        break;
    }
    if (!$saved && mysqli_stmt_errno($stmt) !== 1062) {
        break;
    }
    if (!$saved) {
        usleep(1000);
    }
}
if ($already_applied) {
    mysqli_rollback($conn);
    keep_form_values();
    show_application_error(["You have already applied for this job with this email address."]);
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

    session_regenerate_id(true);
    unset($_SESSION['apply_old']);
    $_SESSION['apply_success'] = true;
    $_SESSION['first_name'] = $form["first_name"];
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