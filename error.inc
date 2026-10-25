<?php
require_once "functions.inc";

function show_error_page($heading, $messages = [], $back_href = "index.php", $back_text = "Back to Home", $status = 500) {
    if (!headers_sent()) {
        http_response_code($status);
    }

    $page_title = $heading;
    $page_description = "Something went wrong on Community Connect.";
    $current_page = "";
    include "header.inc";
?>
  <main id="main">
    <h1><?php echo e($heading); ?></h1>
<?php if ($messages) { ?>
    <div class="message message-error" role="alert">
      <ul>
<?php foreach ($messages as $message) { ?>
        <li><?php echo e($message); ?></li>
<?php } ?>
      </ul>
    </div>
<?php } ?>
    <div class="group">
      <a class="btn" href="<?php echo e($back_href); ?>"><?php echo e($back_text); ?></a>
<?php if ($back_href !== "index.php") { ?>
      <a class="btn-secondary" href="index.php">Back to Home</a>
<?php } ?>
    </div>
  </main>

<?php
    include "footer.inc";
    exit();
}

function show_database_error($back_href = "index.php", $back_text = "Back to Home") {
    global $db_error_message;
    show_error_page("Something went wrong", [$db_error_message ?? "Sorry, we can't reach our database right now. Please try again later."], $back_href, $back_text, 503);
}
?>