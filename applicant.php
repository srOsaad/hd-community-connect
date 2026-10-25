<?php
// applicant.php - shows ONE applicant's own row from the eoi table.
//   Default view -> details list, with an Edit button
//                   (no button when Current or Final)
//   ?edit=1      -> editable form with Save / Cancel

require_once 'session.inc';

require_once 'db_connect.inc';   // $conn
require_once 'functions.inc';    // clean_input(), e()
require_once 'error.inc';

$genders = ['Male', 'Female', 'Other'];
$states  = ['VIC', 'NSW', 'QLD', 'NT', 'WA', 'SA', 'TAS', 'ACT'];
$skill_options = skill_options();

$errors = [];
$locked_notice = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['csrf_token'];
$notice = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

// Load this user's row
$app = null;
$id  = (int) ($_SESSION['eoi_id'] ?? 0);
$application_id = (string) ($_SESSION['application_id'] ?? '');

if ($id < 1 || $application_id === '') {
    unset($_SESSION['eoi_id'], $_SESSION['application_id'], $_SESSION['applicant_last_activity']);
    header('Location: userlogin.php');
    exit;
}

$applicant_timeout = 30 * 60;
if (isset($_SESSION['applicant_last_activity']) && time() - $_SESSION['applicant_last_activity'] > $applicant_timeout) {
    unset($_SESSION['eoi_id'], $_SESSION['application_id'], $_SESSION['applicant_last_activity']);
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
    unset($_SESSION['eoi_id'], $_SESSION['application_id'], $_SESSION['applicant_last_activity']);
    show_error_page('Application Not Found', ['We could not find this application. It may have been removed.'], 'userlogin.php', 'Check Application', 404);
}

// Job title for the page heading (JobReference in eoi = reference in jobs)
$job_title = '';
$stmt = mysqli_prepare($conn, 'SELECT title FROM jobs WHERE reference = ?');
$jn = $app['JobReference'];
mysqli_stmt_bind_param($stmt, 's', $jn);
mysqli_stmt_execute($stmt);
$job = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
$job_title = $job['title'] ?? '';

$locked = in_array($app['Status'], ['Current', 'Final'], true);    // only Current,Final can't be edited

$form = $app;
$form['Skills'] = $app['Skills'] === '' ? [] : explode(',', $app['Skills']);

// Edit mode is only available when not locked
$editing = !$locked && (isset($_GET['edit']) || $_SERVER['REQUEST_METHOD'] === 'POST');

if ($locked && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $locked_notice = 'Your changes were not saved because your application is now ' . strtolower($app['Status']) . '.';
}

// Save
if ($editing && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $sent_token = is_string($_POST['token'] ?? null) ? $_POST['token'] : '';
    if (!hash_equals($token, $sent_token)) {
        $errors[] = 'That request could not be verified. Please try again.';
    }

    foreach (['FirstName','LastName','DOB','Gender','StreetAddress','Suburb',
              'State','Postcode','Email','Phone','OtherSkills'] as $f) {
        $form[$f] = post_value($f);
    }
    $form['State'] = strtoupper($form['State']);
    $form['Skills'] = allowed_skills($_POST['Skills'] ?? []);

    $d = DateTime::createFromFormat('!Y-m-d', $form['DOB']);
    if ($d && $d->format('Y-m-d') !== $form['DOB']) {
        $d = null;
    }

    $errors = array_merge($errors, validate_application([
        'first_name' => $form['FirstName'], 'last_name' => $form['LastName'],
        'dob' => $d ?: null, 'dob_message' => 'Please enter a valid date of birth.',
        'gender' => $form['Gender'], 'street' => $form['StreetAddress'], 'suburb' => $form['Suburb'],
        'state' => $form['State'], 'postcode' => $form['Postcode'], 'email' => $form['Email'],
        'phone' => $form['Phone'], 'skills' => $form['Skills'], 'other_skills' => $form['OtherSkills'],
    ]));

    if (!$errors) {
        $skills = implode(',', $form['Skills']);
        // Status is re-checked in SQL, so a Current application can't be changed
        $stmt = mysqli_prepare($conn,
            "UPDATE eoi SET FirstName=?, LastName=?, DOB=?, Gender=?, StreetAddress=?, Suburb=?,
                    State=?, Postcode=?, Email=?, Phone=?, Skills=?, OtherSkills=?
             WHERE EOInumber=? AND Status NOT IN ('Current', 'Final')");
        mysqli_stmt_bind_param($stmt, 'ssssssssssssi',
            $form['FirstName'], $form['LastName'], $form['DOB'], $form['Gender'],
            $form['StreetAddress'], $form['Suburb'], $form['State'], $form['Postcode'],
            $form['Email'], $form['Phone'], $skills, $form['OtherSkills'], $id);
        $ok = mysqli_stmt_execute($stmt);
        $changed = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);

        if ($ok && $changed === 0) {
            $stmt = mysqli_prepare($conn, 'SELECT Status FROM eoi WHERE EOInumber = ?');
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            $current = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);

            if ($current && in_array($current['Status'], ['Current', 'Final'], true)) {
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
            $errors[] = 'Sorry, we could not save your changes. Please try again later.';
        }
    }
}

$page_title = 'Your Application';
$page_description = 'Check the status of your Community Connect job application.';
$page_style = <<<'CSS'
    /* Edit form: fields react on hover (Job Reference is never editable) */
    form input:not(.static):hover,
    form select:hover,
    form textarea:hover {
        border-color: #1a3a6e;
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

    <?php if ($errors): ?>
        <div class="message message-error" role="alert">
            <ul>
            <?php foreach ($errors as $err): ?>
                <li><?= e($err) ?></li>
            <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="applicant.php" novalidate="novalidate">
        <input type="hidden" name="token" value="<?= e($token) ?>">

        <!-- Never editable -->
        <div class="field">
            <label for="JobReference">Job reference</label>
            <input type="text" id="JobReference" class="static" value="<?= show($app['JobReference']) ?>" readonly>
        </div>
        <div class="field">
            <label for="FirstName">First name</label>
            <input type="text" id="FirstName" name="FirstName" maxlength="20" value="<?= show($form['FirstName']) ?>">
        </div>
        <div class="field">
            <label for="LastName">Last name</label>
            <input type="text" id="LastName" name="LastName" maxlength="20" value="<?= show($form['LastName']) ?>">
        </div>
        <div class="field">
            <label for="DOB">Date of birth</label>
            <input type="date" id="DOB" name="DOB" value="<?= show($form['DOB']) ?>">
        </div>
        <div class="field">
            <label for="Gender">Gender</label>
            <select id="Gender" name="Gender">
                <?php foreach ($genders as $g): ?>
                    <option value="<?= $g ?>"<?= $form['Gender'] === $g ? ' selected' : '' ?>><?= $g ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="StreetAddress">Street address</label>
            <input type="text" id="StreetAddress" name="StreetAddress" maxlength="40" value="<?= show($form['StreetAddress']) ?>">
        </div>
        <div class="field">
            <label for="Suburb">Suburb or town</label>
            <input type="text" id="Suburb" name="Suburb" maxlength="40" value="<?= show($form['Suburb']) ?>">
        </div>
        <div class="field">
            <label for="State">State</label>
            <select id="State" name="State">
                <?php foreach ($states as $s): ?>
                    <option value="<?= $s ?>"<?= $form['State'] === $s ? ' selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="Postcode">Postcode</label>
            <input type="text" id="Postcode" name="Postcode" maxlength="4" value="<?= show($form['Postcode']) ?>">
        </div>
        <div class="field">
            <label for="Email">Email</label>
            <input type="text" id="Email" name="Email" maxlength="150" value="<?= show($form['Email']) ?>">
        </div>
        <div class="field">
            <label for="Phone">Phone</label>
            <input type="text" id="Phone" name="Phone" maxlength="10" value="<?= show($form['Phone']) ?>">
        </div>

        <fieldset>
            <legend>Skills</legend>
            <?php foreach ($skill_options as $i => $opt): ?>
                <label class="choice" for="skill<?= $i ?>">
                    <input type="checkbox" id="skill<?= $i ?>" name="Skills[]" value="<?= e($opt) ?>"<?= in_array($opt, $form['Skills'], true) ? ' checked' : '' ?>>
                    <?= e($opt) ?>
                </label>
            <?php endforeach; ?>
        </fieldset>

        <div class="field">
            <label for="OtherSkills">Other skills</label>
            <textarea id="OtherSkills" name="OtherSkills" rows="4" cols="40"><?= show($form['OtherSkills']) ?></textarea>
        </div>

        <div class="group">
            <input class="btn" type="submit" value="Save">
            <a class="btn-secondary" href="applicant.php">Cancel</a>
        </div>
    </form>

<?php endif; ?>
</main>
<?php include 'footer.inc'; ?>