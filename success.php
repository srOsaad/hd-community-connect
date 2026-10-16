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
  .benefit{
    font-weight: bold;
    color: #007bff;
  }
CSS;

include "header.inc";
?>
<main id="main">
<h1>Thank you, <?php echo e($first_name); ?>!</h1>
<h2>Your application has been received.</h2>
<form>
<div class="grid">
    <input  href='index.php' class="btn" type="submit" value="Back to home">
    </div>
</form>

</main>
<?php include "footer.inc"; ?>