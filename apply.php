
<?php
/*
Using header.inc (with nav.inc inside) and footer.inc.
Page content is unchanged from Part 1 apart from links (.html to .php).
*/

$page_title = "Apply";
$page_description = "Community Connect is a not-for-profit supporting community programs, volunteer registration, events, donations and access to support resources.";
$current_page = "apply";

// Embedded CSS for this page (Part 1 requirement: embedded CSS on every page)
$page_style = <<<'CSS'
  .benefit{
    font-weight: bold;
    color: #007bff;
  }
CSS;

include "header.inc";
?>

  <main id="main">
    <!-- The title of the apply.html -->

    <h1><strong>Join the Community</strong></h1>
    <p class="intro"  >Why should you join our community? We have movie nights, workshops, and social events!</p>
    <h2><strong>Become our CSSO member today</strong></h2>
    <p><em>Fill in your details below to <span class="benefit">access advice</span>, <span class="benefit">support</span>, <span class="benefit">member benefits</span> <br> <span style="opacity:0.5">and more ...</span></em></p>
    <form action="process_eoi.php" method="post" novalidate="novalidate">
      <!-- Form fields for user information -->
      <fieldset class="field">
        <legend><strong>Who Are Youuuu?</strong></legend>
        <label for="job_reference_number"><strong>Job reference number:</strong></label>
        <input type="text" name="job_reference_number" id="job_reference_number" maxlength="6" pattern="[A-Za-z0-9]{6}"
          title="Please enter Exactly 6 alphanumeric characters" placeholder="Enter your job reference number" required>

        <label for="first_name"><strong>First name:</strong></label>
        <input type="text" name="first_name" id="first_name" pattern="[A-Za-z\s]{0,20}"
          title="Please enter your first name" placeholder="Enter your first name" required>
        <label for="last_name"><strong>Last name:</strong></label>
        <input type="text" name="last_name" id="last_name" pattern="[A-Za-z\s]{0,20}" title="Please enter your last name"
          placeholder="Enter your last name" required>

        <label for="DateOfBirth"><strong>Date of birth:</strong></label>
        <input type="input" pattern = "^\d{2}/\d{2}/\d{4}$" name="DateOfBirth" id="DateOfBirth" placeholder="Enter your date of birth (DD/MM/YYYY)" required>
      </fieldset>
      <!-- Form fields for user gender -->
      <fieldset class="field">
        <legend><strong>What is your gender?</strong></legend>
        <label><strong>Gender:</strong></label>

        <label for="Male" class="choice"><input type="radio" name="Gender" id="Male" value="Male" required> Male </label>

        <label for="Female" class="choice"><input type="radio" name="Gender" id="Female" value="Female" required> Female </label>

        <label for ="Other" class="choice"><input type="radio" name="Gender" value="Other" id="Other" required> Other </label>
        </label>
      </fieldset>

      <!-- Form fields for user address -->
      <fieldset class="field">
        <legend><strong>Where Do You Liveeee?</strong></legend>
    <label for="streetaddress"><strong>Street address:</strong></label>
    <input type="text" name="streetaddress" id="streetaddress" pattern="[A-Za-z0-9\s]{3,40}" style="width: 400px;" placeholder="Enter your street address" maxlength="40" title="Please enter your street address" required>
    <label for="suburb_town"><strong>Suburb/Town:</strong></label>
    <input type="text" name="suburb_town" id="suburb_town" pattern="[A-Za-z\s]{3,40}" placeholder="Enter your suburb or town" style="width: 400px;" maxlength="40" title="Please enter your suburb or town" required>
    <label for="state"><strong>State:</strong></label>
    <select id="state" name="state" required>
        <option value="">Please select state where you live</option>
        <option value="vic">VIC</option>
        <option value="nsw">NSW</option>
        <option value="qld">QLD</option>
        <option value="NT">NT</option>
        <option value="WA">WA</option>
        <option value="SA">SA</option>
        <option value="tas">TAS</option>
        <option value="ACT">ACT</option>
    </select>
      <label for="postcode"><strong>Postcode:</strong></label>
    <input type="text" name="postcode" id="postcode" pattern="[0-9]{4}" maxlength="4" placeholder="Enter your postcode" title="Please enter your postcode" required>
</fieldset>

<!-- Form fields for user contact information -->
<fieldset class="field">
    <legend><strong>Your contact informationnnn?</strong></legend>
    <label for="email"><strong>Email:</strong></label>
    <input type="email" name="email" id="email" style="width: 400px;" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}" maxlength="40" placeholder="Enter your email address" title="Please enter your email address" required>

    <label for="phone"><strong>Phone:</strong></label>
    <input type="tel" name="phone" id="phone" pattern="[0-9]{0,10}" maxlength="10" placeholder="Enter your phone number" title="Please enter your phone number" required>
</fieldset>
<!-- Form fields for user skills -->
<fieldset class="field">
  <legend><strong>What skills you have in this field?</strong></legend>
    <label class="choice" for="skill1">
    <input type="checkbox" name="skills" id="skill1" value="Problem solving"> Problem solving </label>
    
    <label class="choice" for="skill2">
    <input type="checkbox" name="skills" id="skill2" value="Communication"> Communication </label>

    <label class="choice" for="skill3">
    <input type="checkbox" name="skills" id="skill3" value="Teamwork"> Teamwork </label>
    
     <label class ="choice" for="Other_skill">Other skills:</label>
    <textarea name="Other_skill" id="Other_skill" rows="4" cols="50" placeholder="Please enter if you have other skills"></textarea>
</fieldset>
<!-- Form submission buttons -->
<div class="grid">
    <input class="btn" type="submit" value="Submit Application">
    <input class="btn-secondary" type="reset" value="Reset Form">
    </div>
</form>
  </main>

<?php include "footer.inc"; ?>
