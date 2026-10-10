<!DOCTYPE html>
<html lang="en-AU">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Apply for community</title>
  <meta name="description" content="One sentence about this page.">

  <!-- Google Fonts — credited in the footer -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;600&amp;family=Source+Serif+4:wght@600;700&amp;display=swap">

  <link rel="icon" type="image/x-icon" href="assets/favicon.ico">
  <link rel="stylesheet" href="css/styles.css">

  <style>
  .benefit{
    font-weight: bold;
    color: #007bff;
  }
  </style>

</head>

<body>
  <a class="skip-link" href="#main">Skip to main content</a>

  <!-- Header and nav -->
  <header class="site-header">
    <a class="brand" href="index.html">
      <img class="logo" src="assets/logo.png" alt="Community Connect logo" width="48" height="48">
      <span class="brand-name">Community Connect</span>
    </a>

    <nav class="site-nav" aria-label="Main navigation">
      <details>
        <summary aria-label="Menu"></summary>
      </details>
      <ul>
        <li><a href="index.html">Home</a></li>
        <li><a href="jobs.html">Jobs</a></li>
        <li><a href="apply.html" aria-current="page">Apply</a></li>
        <li><a href="about.html">About</a></li>
      </ul>
    </nav>
  </header>

  <main id="main">
    <!-- The title of the apply.html -->

    <h1><strong>Join the Community</strong></h1>
    <p class="intro"  >Why should you join our community? We have movie nights, workshops, and social events!</p>
    <h2><strong>Become our CSSO member today</strong></h2>
    <p><em>Fill in your details below to <span class="benefit">access advice</span>, <span class="benefit">support</span>, <span class="benefit">member benefits</span> <br> <span style="opacity:0.5">and more ...</span></em></p>
    <form method="post" action="https://mercury.swin.edu.au/it000000/formtest.php">
      <!-- Form fields for user information -->
      <fieldset class="field">
        <legend><strong>Who Are Youuuu?</strong></legend>
        <label for="job_reference_number"><strong>Job reference number:</strong></label>
        <input type="text" name="job_reference_number" id="job_reference_number" maxlength="6" pattern="[A-Za-z0-9]{6}"
          title="Please enter Exactly 6 alphanumeric characters" placeholder="Enter your job reference number" required>

        <label for="first_name"><strong>First name:</strong></label>
        <input type="text" name="first_name" id="first_name" pattern="[A-Za-z]{3,20}"
          title="Please enter your first name" placeholder="Enter your first name" required>
        <label for="last_name"><strong>Last name:</strong></label>
        <input type="text" name="last_name" id="last_name" pattern="[A-Za-z]{3,20}" title="Please enter your last name"
          placeholder="Enter your last name" required>

        <label for="DateOfBirth"><strong>Date of birth:</strong></label>
        <input type="date" name="DateOfBirth" id="DateOfBirth" required>
        <label><strong>Gender:</strong></label>

        <label for="Male" class="choice"><input type="radio" name="Gender" id="Male" value="Male"> Male </label>

        <label for="Female" class="choice"><input type="radio" name="Gender" id="Female" value="Female"> Female </label>

        <label for ="Other" class="choice"><input type="radio" name="Gender" value="Other" id="Other"> Other </label>
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
    <input type="tel" name="phone" id="phone" pattern="[0-9]{10,12}" maxlength="12" placeholder="Enter your phone number" title="Please enter your phone number" required>
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

  <!-- Footer : added all required things and credits -->
  <footer class="site-footer">
    <h2 class="visually-hidden">Site information</h2>

    <ul class="footer-links">
      <li><a href="https://hd-community-connect.atlassian.net/jira/software/projects/CSW/boards/3" target="_blank"
          rel="noopener noreferrer">Jira project board<span class="visually-hidden"> (opens in a new tab)</span></a>
      </li>
      <li><a href="https://github.com/srOsaad/hd-community-connect" target="_blank" rel="noopener noreferrer">GitHub
          repository<span class="visually-hidden"> (opens in a new tab)</span></a></li>

      <li><a href="mailto:106406031@student.swin.edu.au">106406031@student.swin.edu.au</a></li>
      <li><a href="mailto:106052926@student.swin.edu.au">106052926@student.swin.edu.au</a></li>
      <li><a href="mailto:106424716@student.swin.edu.au">106424716@student.swin.edu.au</a></li>
      <li><a href="mailto:106514329@student.swin.edu.au">106514329@student.swin.edu.au</a></li>
    </ul>

    <p class="footer-note">
      &copy; 2026 Community Connect. A student project by Group HD for
      Web Technology, Swinburne University of Technology.
      Community Connect is a fictional organisation created for assessment purposes.
    </p>

    <!-- add anything you didn't make yourself to this list -->
    <p class="footer-note">
      Acknowledgements: logo generated with Google Gemini.
      Colour scheme adapted from the Australian Services Union
      <a href="https://www.asu.org.au/ASU_NSW/About/community_sector.aspx" target="_blank"
        rel="noopener noreferrer">Social and
        Community Services<span class="visually-hidden"> (opens in a new tab)</span></a> page. Typefaces Source Serif 4
      and Source Sans 3 by
      Adobe, served via Google Fonts under the SIL Open Font License.
    </p>
  </footer>

</body>

</html>
