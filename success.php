<?php
require_once "session.inc";
require_once "functions.inc";   // gives you e()

if (empty($_SESSION['apply_success'])) {
    header("Location: " . (!empty($_SESSION['eoi_id']) ? "applicant.php" : "apply.php"));
    exit();
}

$first_name = $_SESSION['first_name'] ?? "";
$application_id = $_SESSION['application_id'] ?? "";
unset($_SESSION['apply_success'], $_SESSION['first_name']);

$page_title = "Success";
$page_description = "Your Community Connect job application has been received.";
$current_page = "apply";

include "header.inc";
?>
<main id="main">
  <h1>Thank you, <?php echo e($first_name); ?>!</h1>
  <h2>Your application has been received.</h2>
  <p>Your application ID is <strong><?php echo e($application_id); ?></strong>. Keep it with your password to check your application later.</p>
  <div class="group">
    <a class="btn" href="applicant.php">View your application</a>
    <a class="btn-secondary" href="index.php">Back to home</a>
  </div>
</main>
<?php include "footer.inc"; ?>