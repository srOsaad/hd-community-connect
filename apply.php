<?php
/*
Using header.inc (with nav.inc inside) and footer.inc.
Job list comes from the jobs table; entered values come back after a validation error.
*/
require_once "db_connect.inc";
require_once "functions.inc";
require_once "jobs.inc";
require_once "application_form.inc";
require_once "error.inc";

if (!$conn) {
    show_database_error();
}
$job_map = get_job_map($conn);

require_once "session.inc";
$old = isset($_SESSION['apply_old']) && is_array($_SESSION['apply_old']) ? $_SESSION['apply_old'] : [];
unset($_SESSION['apply_old']);

if (isset($_GET['reference']) && is_string($_GET['reference'])) {
    $selected_job = strtoupper($_GET['reference']);
} else {
    $selected_job = strtoupper(form_value($old, 'job_reference_number'));
}


$page_title = "Apply";
$page_description = "Apply for a position at Community Connect.";
$current_page = "apply";

// Embedded CSS for this page
$page_style = <<<'CSS'
  .benefit{
    font-weight: bold;
    color: var(--accent);
  }
CSS;

include "header.inc";


?>

  <main id="main">
    <!-- The title of the apply page -->

    <h1>Apply for a Position</h1>
    <p class="intro">Join our team and help us support community programs, volunteers and local services.</p>
    <h2>Application Form</h2>
    <p><em>Fill in your details below. When you submit, you will receive an <span class="benefit">application ID</span> so you can <a href="userlogin.php">check your application</a> later.</em></p>
    <form action="process_eoi.php" method="post" novalidate="novalidate">
      <input type="hidden" name="token" value="<?= e(csrf_token()) ?>">
      <?php application_fields($old); ?>
      <!-- Form fields for job apply -->
      <fieldset class="field">
        <legend>Position</legend>
        <label for="job_reference_number">Job</label>
        <select id="job_reference_number" name="job_reference_number" required>
          <option value="">Please select a position</option>
          <?php foreach ($job_map as $reference => $title): ?>
            <option value="<?= e($reference) ?>"<?= (string) $reference === $selected_job ? ' selected' : '' ?>><?= e($title) ?></option>
          <?php endforeach; ?>
        </select>
      </fieldset>
      <?php skill_fields($old); ?>
      <fieldset class="field">
        <legend>Create a password</legend>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" minlength="8" maxlength="72" autocomplete="new-password" aria-describedby="password_hint" required>
        <p class="hint" id="password_hint">8 to 72 characters. You will use it with your application ID to check your application.</p>
        <label for="confirm_password">Confirm password</label>
        <input type="password" id="confirm_password" name="confirm_password" minlength="8" autocomplete="new-password" required>
      </fieldset>
      <!-- Form submission buttons -->
      <div class="group">
        <input class="btn" type="submit" value="Submit Application">
        <a class="btn-secondary" href="apply.php">Reset Form</a>
      </div>
    </form>
  </main>

<?php include "footer.inc"; ?>
