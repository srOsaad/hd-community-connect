<?php
// applicant.php - shows ONE applicant's own row from the eoi table.
//   Default view -> data shown as headings + paragraphs, with an Edit button
//                   (no button when Current or Final)
//   ?edit=1      -> editable form with Save / Cancel

session_start();

require_once 'db_connect.inc';   // $conn
require_once 'functions.inc';    // clean_input(), e()

    

$genders = ['Male', 'Female', 'Other'];
$states  = ['VIC', 'NSW', 'QLD', 'NT', 'WA', 'SA', 'TAS', 'ACT'];
$skill_options = ['Problem solving', 'Communication', 'Teamwork'];

$errors = [];
$notice = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

// ---------- Load this user's row ----------
$app = null;
$id  = (int) ($_SESSION['eoi_id'] ?? 0);

if ($id < 1) {
    header('Location: userlogin.php');
    exit;
}

if ($conn && $id > 0) {
    $stmt = mysqli_prepare($conn, 'SELECT * FROM eoi WHERE EOInumber = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $app = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: null;
    mysqli_stmt_close($stmt);
}

// Job title for the page heading (JobReference in eoi = reference in jobs)
$job_title = '';
if ($app) {
    $stmt = mysqli_prepare($conn, 'SELECT title FROM jobs WHERE reference = ?');
    $jn = $app['JobReference'];
    mysqli_stmt_bind_param($stmt, 's', $jn);
    mysqli_stmt_execute($stmt);
    $job = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    $job_title = $job['title'] ?? '';
}

$editing = false;

if ($app) {
    $locked = in_array($app['Status'], ['Current', 'Final'], true);    // only Current,Final can't be edited

    $form = $app;
    $form['Skills'] = $app['Skills'] === '' ? [] : explode(',', $app['Skills']);

    // Edit mode is only available when not locked
    if (!$locked && (isset($_GET['edit']) || $_SERVER['REQUEST_METHOD'] === 'POST')) {
        $editing = true;
    }

    // ---------- Save ----------
    if ($editing && $_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach (['FirstName','LastName','DOB','Gender','StreetAddress','Suburb',
                  'State','Postcode','Email','Phone','OtherSkills'] as $f) {
            $form[$f] = clean_input($_POST[$f] ?? '');
        }
        $form['Skills'] = array_values(array_intersect($skill_options, (array) ($_POST['Skills'] ?? [])));

        $d = DateTime::createFromFormat('Y-m-d', $form['DOB']);
        if (!preg_match("/^[\p{L}' -]{1,20}$/u", $form['FirstName'])) $errors[] = 'First name: letters only, max 20 characters.';
        if (!preg_match("/^[\p{L}' -]{1,20}$/u", $form['LastName']))  $errors[] = 'Last name: letters only, max 20 characters.';
        if (!$d || $d->format('Y-m-d') !== $form['DOB'] || $d > new DateTime('today')) $errors[] = 'Please enter a valid date of birth.';
        if (!in_array($form['Gender'], $genders, true)) $errors[] = 'Please choose a gender.';
        if ($form['StreetAddress'] === '' || mb_strlen($form['StreetAddress']) > 40) $errors[] = 'Street address is required (max 40 characters).';
        if ($form['Suburb'] === '' || mb_strlen($form['Suburb']) > 40) $errors[] = 'Suburb/town is required (max 40 characters).';
        if (!in_array($form['State'], $states, true)) $errors[] = 'Please choose a valid state.';
        if (!preg_match('/^\d{4}$/', $form['Postcode'])) $errors[] = 'Postcode must be exactly 4 digits.';
        if (!filter_var($form['Email'], FILTER_VALIDATE_EMAIL) || strlen($form['Email']) > 150) $errors[] = 'Please enter a valid email address.';
        if (!preg_match('/^\d{10}$/', $form['Phone'])) $errors[] = 'Phone must be exactly 10 digits.';
        if (mb_strlen($form['OtherSkills']) > 1000) $errors[] = 'Other skills: max 1000 characters.';

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
            mysqli_stmt_close($stmt);

            if ($ok) {
                $_SESSION['flash'] = 'Your application has been updated.';
                header('Location: applicant.php');
                exit;
            }
            $errors[] = 'Sorry, we could not save your changes. Please try again later.';
        }
    }
}

$page_title = 'Your Application';
$page_description = 'Check the status of your Community Connect job application.';
include 'header.inc';
?>
<style>
    html { height: 100%; }
    body { min-height: 100vh; display: flex; flex-direction: column; margin: 0; }
    main { flex: 1 0 auto; }
    footer { margin-top: auto; flex-shrink: 0; }

    /* Edit form: fields react on hover (Job Reference is never editable) */
    form input:not(.static):hover,
    form select:hover,
    form textarea:hover {
        border-color: #1a3a6e;
        box-shadow: 0 0 0 2px rgba(26, 58, 110, 0.2);
    }
    input.static { pointer-events: none; cursor: default; }
</style>
<main id="main">
    <h1>Your Application<?= $job_title !== '' ? ' for ' . e($job_title) : '' ?></h1>

<?php if (!$app): ?>

    <p>No application was found for this ID.</p>

<?php elseif (!$editing): ?>

    <!-- ===== VIEW: headings + paragraphs ===== -->
    <?php if ($notice): ?><p class="success"><?= e($notice) ?></p><?php endif; ?>

    <h2>Application ID</h2>
    <p><?= e($app['ApplicationID']) ?></p>

    <h2>Status</h2>
    <p><strong><?= e($app['Status']) ?></strong></p>

    <?php if ($locked): ?>
        <p>Your application is <?= e(strtolower($app['Status'])) ?></p>
    <?php endif; ?>

    <h2>Job Reference</h2>
    <p><?= e($app['JobReference']) ?></p>

    <h2>First Name</h2>
    <p><?= e($app['FirstName']) ?></p>

    <h2>Last Name</h2>
    <p><?= e($app['LastName']) ?></p>

    <h2>Date of Birth</h2>
    <p><?= e(date('d/m/Y', strtotime($app['DOB']))) ?></p>

    <h2>Gender</h2>
    <p><?= e($app['Gender']) ?></p>

    <h2>Street Address</h2>
    <p><?= e($app['StreetAddress']) ?></p>

    <h2>Suburb/Town</h2>
    <p><?= e($app['Suburb']) ?></p>

    <h2>State</h2>
    <p><?= e($app['State']) ?></p>

    <h2>Postcode</h2>
    <p><?= e($app['Postcode']) ?></p>

    <h2>Email</h2>
    <p><?= e($app['Email']) ?></p>

    <h2>Phone</h2>
    <p><?= e($app['Phone']) ?></p>

    <h2>Skills</h2>
    <p><?= $app['Skills'] === '' ? '-' : e(str_replace(',', ', ', $app['Skills'])) ?></p>

    <h2>Other Skills</h2>
    <p><?= trim((string) $app['OtherSkills']) === '' ? '-' : nl2br(e($app['OtherSkills'])) ?></p>

    <div class="group">
    <?php if (!$locked): ?>
        <a class="btn" href="applicant.php?edit=1">Edit</a>
    <?php endif; ?>
        <a class="btn-secondary" href="logout.php">Log out</a>
    </div>

<?php else: ?>

    <!-- ===== EDIT FORM ===== -->
    <p>Status: <strong><?= e($app['Status']) ?></strong></p>

    <?php if ($errors): ?>
        <ul class="errors"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    <?php endif; ?>

    <form method="post" action="applicant.php" novalidate="novalidate">

        <!-- Never editable -->
        <p>
            <label for="JobReference">Job Reference</label>
            <input type="text" id="JobReference" class="static" value="<?= e($app['JobReference']) ?>" readonly>
        </p>
        <p>
            <label for="FirstName">First Name</label>
            <input type="text" id="FirstName" name="FirstName" maxlength="20" value="<?= e($form['FirstName']) ?>">
        </p>
        <p>
            <label for="LastName">Last Name</label>
            <input type="text" id="LastName" name="LastName" maxlength="20" value="<?= e($form['LastName']) ?>">
        </p>
        <p>
            <label for="DOB">Date of Birth</label>
            <input type="date" id="DOB" name="DOB" value="<?= e($form['DOB']) ?>">
        </p>
        <p>
            <label for="Gender">Gender</label>
            <select id="Gender" name="Gender">
                <?php foreach ($genders as $g): ?>
                    <option value="<?= $g ?>"<?= $form['Gender'] === $g ? ' selected' : '' ?>><?= $g ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="StreetAddress">Street Address</label>
            <input type="text" id="StreetAddress" name="StreetAddress" maxlength="40" value="<?= e($form['StreetAddress']) ?>">
        </p>
        <p>
            <label for="Suburb">Suburb/Town</label>
            <input type="text" id="Suburb" name="Suburb" maxlength="40" value="<?= e($form['Suburb']) ?>">
        </p>
        <p>
            <label for="State">State</label>
            <select id="State" name="State">
                <?php foreach ($states as $s): ?>
                    <option value="<?= $s ?>"<?= $form['State'] === $s ? ' selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="Postcode">Postcode</label>
            <input type="text" id="Postcode" name="Postcode" maxlength="4" value="<?= e($form['Postcode']) ?>">
        </p>
        <p>
            <label for="Email">Email</label>
            <input type="text" id="Email" name="Email" maxlength="150" value="<?= e($form['Email']) ?>">
        </p>
        <p>
            <label for="Phone">Phone</label>
            <input type="text" id="Phone" name="Phone" maxlength="10" value="<?= e($form['Phone']) ?>">
        </p>

        <fieldset>
            <legend>Skills</legend>
            <?php foreach ($skill_options as $opt): ?>
                <label>
                    <input type="checkbox" name="Skills[]" value="<?= e($opt) ?>"
                        <?= in_array($opt, $form['Skills'], true) ? 'checked' : '' ?>>
                    <?= e($opt) ?>
                </label><br>
            <?php endforeach; ?>
        </fieldset>

        <p>
            <label for="OtherSkills">Other Skills</label>
            <textarea id="OtherSkills" name="OtherSkills" rows="4" cols="40"><?= e((string) $form['OtherSkills']) ?></textarea>
        </p>

        <p>
            <input class="btn" type="submit" value="Save">
            <a href="applicant.php">Cancel</a>
        </p>
    </form>

<?php endif; ?>
</main>
<?php include 'footer.inc'; ?>