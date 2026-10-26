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
<?php show_errors($messages); ?>
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

function db_prepare($conn, $sql) {
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log("Query failed: " . mysqli_error($conn));
        show_database_error();
    }
    return $stmt;
}

function db_query($conn, $sql) {
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        error_log("Query failed: " . mysqli_error($conn));
        show_database_error();
    }
    return $result;
}

function show_database_error($back_href = "index.php", $back_text = "Back to Home") {
    global $db_error_message;
    show_error_page("Something Went Wrong", [$db_error_message ?? "Sorry, we can't reach our database right now. Please try again later."], $back_href, $back_text, 503);
}
?>