<?php
// applicant.php - shows ONE applicant's own row from the eoi table.
//   Default view -> details list, with an Edit button
//                   (no button when Current or Final)
//   ?edit=1      -> editable form with Save / Cancel

require_once 'session.inc';

require_once 'db_connect.inc';   // $conn
require_once 'functions.inc';    // post_value(), e(), show()
require_once 'error.inc';
require_once 'application_form.inc';

$errors = [];
$locked_notice = '';

$token = csrf_token();
$notice = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

// Load this user's row
$app = null;
$id  = (int) ($_SESSION['eoi_id'] ?? 0);
$application_id = (string) ($_SESSION['application_id'] ?? '');

if ($id < 1 || $application_id === '') {
    end_applicant_session();
    header('Location: userlogin.php');
    exit;
}

if (isset($_SESSION['applicant_last_activity']) && time() - $_SESSION['applicant_last_activity'] > SESSION_TIMEOUT) {
    end_applicant_session();
    header('Location: userlogin.php?expired=1');
    exit;
}
$_SESSION['applicant_last_activity'] = time();

if (!$conn) {
    show_database_error('userlogin.php', 'Back to Check Application');
}

$stmt = mysqli_prepare($conn, 'SELECT * FROM eoi WHERE EOInumber = ? AND ApplicationID = ?');
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'is', $id, $application_id);
    mysqli_stmt_execute($stmt);
    $app = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: null;
    mysqli_stmt_close($stmt);
}

if (!$app) {
    end_applicant_session();
    show_error_page('Application Not Found', ['We could not find this application. It may have been removed.'], 'userlogin.php', 'Check Application', 404);
}

// Job title for the page heading (JobReference in eoi = reference in jobs)
$job_title = '';
$stmt = db_prepare($conn, 'SELECT title FROM jobs WHERE reference = ?');
$jn = $app['JobReference'];
mysqli_stmt_bind_param($stmt, 's', $jn);
mysqli_stmt_execute($stmt);
$job = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
$job_title = $job['title'] ?? '';

$locked = in_array($app['Status'], locked_statuses(), true);    // only Current,Final can't be edited

$values = [];
foreach (['first_name' => 'FirstName', 'last_name' => 'LastName', 'Gender' => 'Gender', 'streetaddress' => 'StreetAddress',
          'suburb_town' => 'Suburb', 'state' => 'State', 'postcode' => 'Postcode', 'email' => 'Email',
          'phone' => 'Phone', 'Other_skill' => 'OtherSkills'] as $key => $column) {
    $values[$key] = html_entity_decode((string) $app[$column], ENT_QUOTES, 'UTF-8');
}
$values['DateOfBirth'] = date('d/m/Y', strtotime($app['DOB']));
$values['skills'] = $app['Skills'] === '' ? [] : explode(',', $app['Skills']);

// Edit mode is only available when not locked
$editing = !$locked && (isset($_GET['edit']) || $_SERVER['REQUEST_METHOD'] === 'POST');

if ($locked && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $locked_notice = 'Your changes were not saved because your application is now ' . strtolower($app['Status']) . '.';
}

// Save
if ($editing && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = sent_form_values();
    $form = read_application_form();

    if (!csrf_ok()) {
        $errors[] = 'That request could not be verified. Please try again.';
    } else {
        $errors = validate_application($form);
    }

    if (!$errors) {
        $stmt = db_prepare($conn, 'SELECT 1 FROM eoi WHERE JobReference = ? AND Email = ? AND EOInumber <> ?');
        mysqli_stmt_bind_param($stmt, 'ssi', $app['JobReference'], $form['email'], $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $email_taken = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);

        if ($email_taken) {
            $errors[] = 'Another application for this job already uses this email address.';
        }
    }

    if (!$errors) {
        $skills = implode(',', $form['skills']);
        // Status is re-checked in SQL, so a Current application can't be changed
        $stmt = db_prepare($conn,
            "UPDATE eoi SET FirstName=?, LastName=?, DOB=?, Gender=?, StreetAddress=?, Suburb=?,
                    State=?, Postcode=?, Email=?, Phone=?, Skills=?, OtherSkills=?
             WHERE EOInumber=? AND Status NOT IN ('Current', 'Final')");
        mysqli_stmt_bind_param($stmt, 'ssssssssssssi',
            $form['first_name'], $form['last_name'], $form['dob_sql'], $form['gender'],
            $form['street'], $form['suburb'], $form['state'], $form['postcode'],
            $form['email'], $form['phone'], $skills, $form['other_skills'], $id);
        $ok = mysqli_stmt_execute($stmt);
        $duplicate_email = !$ok && mysqli_stmt_errno($stmt) === 1062;
        $changed = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);

        if ($ok && $changed === 0) {
            $stmt = db_prepare($conn, 'SELECT Status FROM eoi WHERE EOInumber = ?');
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            $current = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);

            if ($current && in_array($current['Status'], locked_statuses(), true)) {
                $ok = false;
                $locked = true;
                $editing = false;
                $app['Status'] = $current['Status'];
                $locked_notice = 'Your changes were not saved because your application is now ' . strtolower($current['Status']) . '.';
            }
        }

        if ($ok) {
            $_SESSION['flash'] = 'Your application has been updated.';
            header('Location: applicant.php');
            exit;
        }
        if (!$locked) {
            $errors[] = $duplicate_email ? 'Another application for this job already uses this email address.'
                                         : 'Sorry, we could not save your changes. Please try again later.';
        }
    }
}

if ($errors) {
    http_response_code(400);
}

$page_title = 'Your Application';
$page_description = 'Check the status of your Community Connect job application.';
$page_style = <<<'CSS'
    /* Edit form: fields react on hover (Job Reference is never editable) */
    form input:not(.static):hover,
    form select:hover,
    form textarea:hover {
        border-color: var(--brand);
        box-shadow: 0 0 0 2px rgba(26, 58, 110, 0.2);
    }
    input.static { pointer-events: none; cursor: default; background-color: var(--surface-alt); color: var(--ink-muted); }

    .details {
        display: grid;
        grid-template-columns: 8.5rem 1fr;
        gap: 0.6rem 1.5rem;
        max-width: var(--measure);
        margin: 0 0 1.75rem;
    }
    .details dt { font-weight: 600; color: var(--brand); }
    .details dd { margin: 0; }

    @media (max-width: 30em) {
        .details { grid-template-columns: 1fr; gap: 0.15rem; }
        .details dd { margin-bottom: 0.75rem; }
    }
CSS;
include 'header.inc';
?>
<main id="main">
    <h1>Your Application<?= $job_title !== '' ? ' for ' . e($job_title) : '' ?></h1>

<?php if (!$editing): ?>

    <!-- View mode: details list -->
    <?php if ($notice): ?><p class="message message-success" role="status"><?= e($notice) ?></p><?php endif; ?>
    <?php if ($locked_notice): ?><p class="message message-error" role="alert"><?= e($locked_notice) ?></p><?php endif; ?>

    <h2>Application</h2>
    <dl class="details">
        <dt>Application ID</dt>
        <dd><?= show($app['ApplicationID']) ?></dd>

        <dt>Status</dt>
        <dd><strong><?= show($app['Status']) ?></strong><?php if ($locked): ?> (your application is <?= e(strtolower($app['Status'])) ?> and can no longer be edited)<?php endif; ?></dd>

        <dt>Job</dt>
        <dd><?= $job_title !== '' ? e($job_title) . ' (' . show($app['JobReference']) . ')' : show($app['JobReference']) ?></dd>
    </dl>

    <h2>Your details</h2>
    <dl class="details">
        <dt>Name</dt>
        <dd><?= show($app['FirstName'] . ' ' . $app['LastName']) ?></dd>

        <dt>Date of birth</dt>
        <dd><?= e(date('d/m/Y', strtotime($app['DOB']))) ?></dd>

        <dt>Gender</dt>
        <dd><?= show($app['Gender']) ?></dd>

        <dt>Address</dt>
        <dd><?= show($app['StreetAddress']) ?><br><?= show($app['Suburb'] . ' ' . $app['State'] . ' ' . $app['Postcode']) ?></dd>

        <dt>Email</dt>
        <dd><?= show($app['Email']) ?></dd>

        <dt>Phone</dt>
        <dd><?= show($app['Phone']) ?></dd>

        <dt>Skills</dt>
        <dd><?= $app['Skills'] === '' ? '-' : show(str_replace(',', ', ', $app['Skills'])) ?></dd>

        <dt>Other skills</dt>
        <dd><?= trim((string) $app['OtherSkills']) === '' ? '-' : nl2br(show($app['OtherSkills'])) ?></dd>
    </dl>

    <div class="group">
    <?php if (!$locked): ?>
        <a class="btn" href="applicant.php?edit=1">Edit</a>
    <?php endif; ?>
        <a class="btn-secondary" href="logout.php?who=applicant&amp;token=<?= urlencode($token) ?>">Log Out</a>
    </div>

<?php else: ?>

    <!-- Edit mode: form -->
    <p>Status: <strong><?= show($app['Status']) ?></strong></p>

    <?php show_errors($errors); ?>

    <form method="post" action="applicant.php" novalidate="novalidate">
        <input type="hidden" name="token" value="<?= e($token) ?>">

        <!-- Never editable -->
        <div class="field">
            <label for="JobReference">Job reference</label>
            <input type="text" id="JobReference" class="static" value="<?= show($app['JobReference']) ?>" readonly>
        </div>
        <?php application_fields($values); ?>
        <?php skill_fields($values); ?>

        <div class="group">
            <input class="btn" type="submit" value="Save">
            <a class="btn-secondary" href="applicant.php">Cancel</a>
        </div>
    </form>

<?php endif; ?>
</main>
<?php include 'footer.inc'; ?>