<?php
function form_value($values, $key) {
    return isset($values[$key]) && is_string($values[$key]) ? $values[$key] : "";
}

function read_application_form() {
    $form = [
        "first_name"   => post_value("first_name"),
        "last_name"    => post_value("last_name"),
        "gender"       => post_value("Gender"),
        "street"       => post_value("streetaddress"),
        "suburb"       => post_value("suburb_town"),
        "state"        => strtoupper(post_value("state")),
        "postcode"     => post_value("postcode"),
        "email"        => post_value("email"),
        "phone"        => post_value("phone"),
        "skills"       => allowed_skills($_POST["skills"] ?? []),
        "other_skills" => post_value("Other_skill"),
        "dob"          => null,
        "dob_sql"      => null,
        "dob_message"  => "Date of birth must be a valid date in the format DD/MM/YYYY.",
    ];

    // DOB: dd/mm/yyyy and a real date (the age is checked in validate_application)
    if (preg_match('#^(\d{2})/(\d{2})/(\d{4})$#D', post_value("DateOfBirth"), $m) && checkdate((int) $m[2], (int) $m[1], (int) $m[3])) {
        $form["dob_sql"] = "$m[3]-$m[2]-$m[1]";
        $form["dob"] = DateTime::createFromFormat("!Y-m-d", $form["dob_sql"]);
    }
    return $form;
}

function sent_form_values() {
    $values = [];
    foreach (["job_reference_number", "first_name", "last_name", "DateOfBirth", "Gender", "streetaddress", "suburb_town",
              "state", "postcode", "email", "phone", "Other_skill"] as $key) {
        if (isset($_POST[$key]) && is_string($_POST[$key])) {
            $values[$key] = $_POST[$key];
        }
    }
    $values["skills"] = allowed_skills($_POST["skills"] ?? []);
    return $values;
}

function application_fields($values) {
?>
      <!-- Form fields for user information -->
      <fieldset class="field">
        <legend>Personal details</legend>
        <label for="first_name">First name</label>
        <input type="text" name="first_name" id="first_name" pattern="[A-Za-z\s]{1,20}"
          title="Please enter your first name" placeholder="Enter your first name" value="<?= e(form_value($values, 'first_name')) ?>" required>
        <label for="last_name">Last name</label>
        <input type="text" name="last_name" id="last_name" pattern="[A-Za-z\s]{1,20}" title="Please enter your last name"
          placeholder="Enter your last name" value="<?= e(form_value($values, 'last_name')) ?>" required>

        <label for="DateOfBirth">Date of birth</label>
        <input type="text" pattern="^\d{2}/\d{2}/\d{4}$" name="DateOfBirth" id="DateOfBirth" placeholder="DD/MM/YYYY" value="<?= e(form_value($values, 'DateOfBirth')) ?>" aria-describedby="dob_hint" required>
        <p class="hint" id="dob_hint">Use the format DD/MM/YYYY, for example 05/09/2001.</p>
      </fieldset>
      <!-- Form fields for user gender -->
      <fieldset class="field">
        <legend>Gender</legend>

        <?php foreach (gender_options() as $gender): ?>
        <label for="<?= $gender ?>" class="choice"><input type="radio" name="Gender" id="<?= $gender ?>" value="<?= $gender ?>"<?= form_value($values, 'Gender') === $gender ? ' checked' : '' ?> required> <?= $gender ?> </label>
        <?php endforeach; ?>
      </fieldset>

      <!-- Form fields for user address -->
      <fieldset class="field">
        <legend>Address</legend>
        <label for="streetaddress">Street address</label>
        <input type="text" name="streetaddress" id="streetaddress" pattern="[A-Za-z0-9\s\/,.'\-]{3,40}" placeholder="Enter your street address" maxlength="40" title="Please enter your street address" value="<?= e(form_value($values, 'streetaddress')) ?>" required>
        <label for="suburb_town">Suburb or town</label>
        <input type="text" name="suburb_town" id="suburb_town" pattern="[A-Za-z\s]{3,40}" placeholder="Enter your suburb or town" maxlength="40" title="Please enter your suburb or town" value="<?= e(form_value($values, 'suburb_town')) ?>" required>
        <label for="state">State</label>
        <select id="state" name="state" required>
          <option value="">Please select your state</option>
          <?php foreach (state_options() as $state): ?>
          <option value="<?= $state ?>"<?= strtoupper(form_value($values, 'state')) === $state ? ' selected' : '' ?>><?= $state ?></option>
          <?php endforeach; ?>
        </select>
        <label for="postcode">Postcode</label>
        <input type="text" name="postcode" id="postcode" pattern="[0-9]{4}" maxlength="4" placeholder="Enter your postcode" title="Please enter your postcode" value="<?= e(form_value($values, 'postcode')) ?>" required>
      </fieldset>

      <!-- Form fields for user contact information -->
      <fieldset class="field">
        <legend>Contact details</legend>
        <label for="email">Email</label>
        <input type="email" name="email" id="email" pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}" maxlength="150" placeholder="Enter your email address" title="Please enter your email address" value="<?= e(form_value($values, 'email')) ?>" required>

        <label for="phone">Phone</label>
        <input type="tel" name="phone" id="phone" pattern="[0-9]{8,12}" maxlength="12" placeholder="Enter your phone number" title="Please enter your phone number" value="<?= e(form_value($values, 'phone')) ?>" aria-describedby="phone_hint" required>
        <p class="hint" id="phone_hint">8 to 12 digits, for example 0412345678.</p>
      </fieldset>
<?php
}

function skill_fields($values) {
    $chosen = isset($values["skills"]) && is_array($values["skills"]) ? $values["skills"] : [];
?>
      <!-- Form fields for user skills -->
      <fieldset class="field">
        <legend>Skills</legend>
        <?php foreach (skill_options() as $i => $skill): ?>
        <label class="choice" for="skill<?= $i + 1 ?>">
          <input type="checkbox" name="skills[]" id="skill<?= $i + 1 ?>" value="<?= e($skill) ?>"<?= in_array($skill, $chosen, true) ? ' checked' : '' ?>> <?= e($skill) ?> </label>
        <?php endforeach; ?>

        <label for="Other_skill">Other skills</label>
        <textarea name="Other_skill" id="Other_skill" rows="4" cols="50" placeholder="List any other relevant skills"><?= e(form_value($values, 'Other_skill')) ?></textarea>
      </fieldset>
<?php
}
?>
