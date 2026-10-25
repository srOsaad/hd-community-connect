<?php
/*
header.inc: shared <head>, skip link and site header (includes nav.inc).

Set these before including this file:
  $page_title        page name shown in the browser tab
  $page_description  one sentence for the meta description
  $current_page      "index", "jobs", "apply", "about", "manage" or "stafflogin" (highlights the menu link)
  $page_style        optional: this page's embedded CSS
*/

// Every page needs the session so nav.inc can show the right staff links
// Pages that redirect start it earlier themselves if it has not started.
require_once "session.inc";

if (!headers_sent()) {
    header("X-Frame-Options: DENY");
}

// Safe defaults in case a page forgets to set these
$page_title       = $page_title ?? "Home";
$page_description = $page_description ?? "Community Connect, a not-for-profit community and social services organisation.";
?>
<!DOCTYPE html>
<html lang="en-AU">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Community Connect - <?php echo htmlspecialchars($page_title); ?></title>
  <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">

  <!-- Google Fonts: credited in the footer -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;600&amp;family=Source+Serif+4:wght@600;700&amp;display=swap">

  <link rel="icon" type="image/x-icon" href="images/favicon.ico">
  <link rel="stylesheet" href="styles/styles.css">
<?php if (!empty($page_style)) { ?>

  <!-- Embedded CSS for this page only -->
  <style>
<?php echo $page_style . "\n"; ?>
  </style>
<?php } ?>
</head>

<body>
  <a class="skip-link" href="#main">Skip to main content</a>

  <!-- Header and nav -->
  <header class="site-header">
    <a class="brand" href="index.php">
      <img class="logo" src="images/logo.png"
           alt="Community Connect logo" width="48" height="48">
      <span class="brand-name">Community Connect</span>
    </a>

<?php include "nav.inc"; ?>
  </header>
