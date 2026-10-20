<?php
session_start();
require_once "functions.inc";   // gives you e()

if (empty($_SESSION['apply_success'])) {
    header("Location: apply.php");
    exit();
}

$first_name = $_SESSION['first_name'] ?? "";
unset($_SESSION['apply_success'], $_SESSION['first_name']);

$page_title = "Success";
$page_description = "Community Connect is a not-for-profit supporting community programs, volunteer registration, events, donations and access to support resources.";
$current_page = "Success apply page";

$page_style = <<<'CSS'
  body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    min-height: 100dvh;   /* better on mobile browsers */
  }

  main {
    flex: 1 0 auto;       /* take all remaining height */
    width: 100%;          /* needed, see note below */
  }
CSS;

include "header.inc";
?>
<main id="main">
  <h1>Thank you, <?php echo e($first_name); ?>!</h1>
  <h2>Your application has been received.</h2>
  <div class="group">
    <a class="btn" href="index.php">Back to home</a>
  </div>
</main>
<?php include "footer.inc"; ?>