<?php
/*
Using header.inc (with nav.inc inside) and footer.inc.
Job list comes from the jobs table; entered values come back after a validation error.
*/
require_once "db_connect.inc";
require_once "functions.inc";
require_once "jobs.inc";
require_once "error.inc";

if (!$conn) {
    show_database_error();
}
$job_map = get_job_map($conn);

require_once "session.inc";
$old = isset($_SESSION['apply_old']) && is_array($_SESSION['apply_old']) ? $_SESSION['apply_old'] : [];
unset($_SESSION['apply_old']);

function old_value($key) {
    global $old;
    return isset($old[$key]) && is_string($old[$key]) ? $old[$key] : '';
}

$old_skills = isset($old['skills']) && is_array($old['skills']) ? $old['skills'] : [];

if (isset($_GET['reference']) && is_string($_GET['reference'])) {
    $selected_job = strtoupper($_GET['reference']);
} else {
    $selected_job = strtoupper(old_value('job_reference_number'));
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

  fieldset > label:not(.choice) {
    margin-top: 1rem;
  }

  fieldset > label:first-of-type {
    margin-top: 0;
  }
CSS;

include "header.inc";


?>

  <main id="main">
    <!-- The title of the apply page -->

    <h1>Apply for a Position</h1>
    <p class="intro"  >Join our team and help us support community programs, volunteers and local services.</p>
    <h2>Application form</h2>
    <p><em>Fill in your details below. When you submit, you will receive an <span class="benefit">application ID</span> so you can <span class="benefit">check your application</span> later.</em></p>
    <form action="process_eoi.php" method="post" novalidate="novalidate">
      <input type="hidden" name="token" value="<?= e(csrf_token()) ?>">
      <!-- Form fields for user information -->
      <fieldset class="field">
        <legend>Personal details</legend>
        <label for="first_name">First name</label>
        <input type="text" name="first_name" id="first_name" pattern="[A-Za-z\s]{1,20}"
          title="Please enter your first name" placeholder="Enter your first name" value="<?= e(old_value('first_name')) ?>" required>
        <label for="last_name">Last name</label>
        <input type="text" name="last_name" id="last_name" pattern="[A-Za-z\s]{1,20}" title="Please enter your last name"
          placeholder="Enter your last name" value="<?= e(old_value('last_name')) ?>" required>

        <label for="DateOfBirth">Date of birth</label>
        <input type="text" pattern="^\d{2}/\d{2}/\d{4}$" name="DateOfBirth" id="DateOfBirth" placeholder="DD/MM/YYYY" value="<?= e(old_value('DateOfBirth')) ?>" aria-describedby="dob_hint" required>
        <p class="hint" id="dob_hint">Use the format DD/MM/YYYY, for example 05/09/2001.</p>
      </fieldset>
      <!-- Form fields for user gender -->
      <fieldset class="field">
        <legend>Gender</legend>

        <label for="Male" class="choice"><input type="radio" name="Gender" id="Male" value="Male"<?= old_value('Gender') === 'Male' ? ' checked' : '' ?> required> Male </label>

        <label for="Female" class="choice"><input type="radio" name="Gender" id="Female" value="Female"<?= old_value('Gender') === 'Female' ? ' checked' : '' ?> required> Female </label>

        <label for="Other" class="choice"><input type="radio" name="Gender" value="Other" id="Other"<?= old_value('Gender') === 'Other' ? ' checked' : '' ?> required> Other </label>
      </fieldset>

      <!-- Form fields for user address -->
      <fieldset class="field">
        <legend>Address</legend>
    <label for="streetaddress">Street address</label>
    <input type="text" name="streetaddress" id="streetaddress" pattern="[A-Za-z0-9\s\/,.'\-]{3,40}" placeholder="Enter your street address" maxlength="40" title="Please enter your street address" value="<?= e(old_value('streetaddress')) ?>" required>
    <label for="suburb_town">Suburb or town</label>
    <input type="text" name="suburb_town" id="suburb_town" pattern="[A-Za-z\s]{3,40}" placeholder="Enter your suburb or town" maxlength="40" title="Please enter your suburb or town" value="<?= e(old_value('suburb_town')) ?>" required>
    <label for="state">State</label>
    <select id="state" name="state" required>
        <option value="">Please select your state</option>
        <option value="vic"<?= strtoupper(old_value('state')) === 'VIC' ? ' selected' : '' ?>>VIC</option>
        <option value="nsw"<?= strtoupper(old_value('state')) === 'NSW' ? ' selected' : '' ?>>NSW</option>
        <option value="qld"<?= strtoupper(old_value('state')) === 'QLD' ? ' selected' : '' ?>>QLD</option>
        <option value="NT"<?= strtoupper(old_value('state')) === 'NT' ? ' selected' : '' ?>>NT</option>
        <option value="WA"<?= strtoupper(old_value('state')) === 'WA' ? ' selected' : '' ?>>WA</option>
        <option value="SA"<?= strtoupper(old_value('state')) === 'SA' ? ' selected' : '' ?>>SA</option>
        <option value="tas"<?= strtoupper(old_value('state')) === 'TAS' ? ' selected' : '' ?>>TAS</option>
        <option value="ACT"<?= strtoupper(old_value('state')) === 'ACT' ? ' selected' : '' ?>>ACT</option>
    </select>
      <label for="postcode">Postcode</label>
    <input type="text" name="postcode" id="postcode" pattern="[0-9]{4}" maxlength="4" placeholder="Enter your postcode" title="Please enter your postcode" value="<?= e(old_value('postcode')) ?>" required>
</fieldset>

<!-- Form fields for user contact information -->
<fieldset class="field">
    <legend>Contact details</legend>
    <label for="email">Email</label>
    <input type="email" name="email" id="email" pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}" maxlength="150" placeholder="Enter your email address" title="Please enter your email address" value="<?= e(old_value('email')) ?>" required>

    <label for="phone">Phone</label>
    <input type="tel" name="phone" id="phone" pattern="[0-9]{10}" maxlength="10" placeholder="Enter your phone number" title="Please enter your phone number" value="<?= e(old_value('phone')) ?>" required>
</fieldset>
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
<!-- Form fields for user skills -->
<fieldset class="field">
  <legend>Skills</legend>
    <label class="choice" for="skill1">
    <input type="checkbox" name="skills[]" id="skill1" value="Problem solving"<?= in_array('Problem solving', $old_skills, true) ? ' checked' : '' ?>> Problem solving </label>
    
    <label class="choice" for="skill2">
    <input type="checkbox" name="skills[]" id="skill2" value="Communication"<?= in_array('Communication', $old_skills, true) ? ' checked' : '' ?>> Communication </label>

    <label class="choice" for="skill3">
    <input type="checkbox" name="skills[]" id="skill3" value="Teamwork"<?= in_array('Teamwork', $old_skills, true) ? ' checked' : '' ?>> Teamwork </label>
    
     <label for="Other_skill">Other skills</label>
    <textarea name="Other_skill" id="Other_skill" rows="4" cols="50" placeholder="List any other relevant skills"><?= e(old_value('Other_skill')) ?></textarea>
</fieldset>
<fieldset class="field">
<legend>Create a password</legend>
    <label for="password">Password</label>
    <input type="password" id="password" name="password" minlength="8" autocomplete="new-password" aria-describedby="password_hint" required>
    <p class="hint" id="password_hint">At least 8 characters. You will use it with your application ID to check your application.</p>
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
