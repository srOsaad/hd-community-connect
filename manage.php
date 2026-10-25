<?php
/*
manage.php: the HR manager's page. Only logged-in staff can see it.
Shows every job application saved by process_eoi.php.
*/

require_once "auth.inc";          // keep this first, it sends anyone not logged in back to the login page
require_once "db_connect.inc";    // connects to the database ($conn)
require_once "functions.inc";     // our helpers: clean_input() and e()
require_once "error.inc";
require_once "manage.inc";

if (!$conn) {
    show_database_error();
}

$messages = [];   // error messages to show above the results

// What the manager is searching for (empty means "don't filter by this")
$filter_job   = strtoupper(field($_GET, "job_ref"));
$filter_first = field($_GET, "first_name");
$filter_last  = field($_GET, "last_name");
$filter_app   = field($_GET, "application_id");

// Check the search values the same way the apply form checks them. Anything invalid is ignored
if ($filter_job !== "" && !preg_match('/^[A-Z0-9]{6}$/D', $filter_job)) {
    $messages[] = "A job reference is exactly 6 letters or numbers.";
}
if ($filter_first !== "" && !preg_match('/^[A-Za-z ]{1,20}$/D', $filter_first)) {
    $messages[] = "First name can only contain letters and spaces (up to 20).";
}
if ($filter_last !== "" && !preg_match('/^[A-Za-z ]{1,20}$/D', $filter_last)) {
    $messages[] = "Last name can only contain letters and spaces (up to 20).";
}

if ($filter_app !== "" && !preg_match('/^[0-9]{16,17}$/D', $filter_app)) {
    $messages[] = "An application ID is 16 or 17 digits.";
}

$is_filtered = $filter_job !== "" || $filter_first !== "" || $filter_last !== "" || $filter_app !== "";

// Columns the manager can sort by.
$sort_fields = [
    "EOInumber"    => "EOI number",
    "JobReference" => "Job reference",
    "FirstName"    => "First name",
    "LastName"     => "Last name",
    "State"        => "State",
    "Status"       => "Status",
];

$sort = field($_GET, "sort");
if (!array_key_exists($sort, $sort_fields)) {
    $sort = "EOInumber";
}
$order = field($_GET, "order") === "desc" ? "desc" : "asc";

$statuses = status_options();

$filter_status = field($_GET, "status");
if ($filter_status !== "" && !in_array($filter_status, $statuses, true)) {
    $messages[] = "Please choose a valid status.";
}
$filter_invalid = count($messages) > 0;
$is_filtered = $is_filtered || $filter_status !== "" || $filter_invalid;

// CSRF token: forms that change data must send this back
$token = csrf_token();

$success = "";
if (isset($_SESSION["success"])) {
    $success = $_SESSION["success"];
    unset($_SESSION["success"]);
}

$page = max(1, (int) field($_GET, "page"));
$per_page = 20;

$list_query = [
    "job_ref" => $filter_job, "first_name" => $filter_first, "last_name" => $filter_last,
    "application_id" => $filter_app, "status" => $filter_status,
    "sort" => $sort, "order" => $order,
];
$page_url = "manage.php?" . http_build_query($list_query + ["page" => $page]);

$has_table = eoi_table_exists($conn);

if ($_SERVER["REQUEST_METHOD"] === "POST" && $has_table && field($_POST, "action") === "update_status") {
    $eoi_number = (int) field($_POST, "eoi_number");
    $new_status = field($_POST, "status");

    if (!csrf_ok()) {
        $messages[] = "That request could not be verified. Please try again.";
    } elseif ($eoi_number < 1 || !in_array($new_status, $statuses, true)) {
        $messages[] = "Please choose a valid status.";
    } else {
        $result = update_eoi_status($conn, $eoi_number, $new_status);

        // Redirect so refreshing doesn't send the form again
        if (isset($result["success"])) {
            $_SESSION["success"] = $result["success"];
            header("Location: " . $page_url);
            exit();
        }
        $messages[] = $result["error"];
    }
}

$delete_job = "";
$delete_count = 0;

$action = field($_POST, "action");
if ($_SERVER["REQUEST_METHOD"] === "POST" && $has_table && ($action === "confirm_delete" || $action === "delete")) {
    $job_ref = strtoupper(field($_POST, "job_ref"));

    if (!csrf_ok()) {
        $messages[] = "That request could not be verified. Please try again.";
    } elseif (!preg_match('/^[A-Z0-9]{6}$/D', $job_ref)) {
        $messages[] = "A job reference is exactly 6 letters or numbers.";
    } elseif ($action === "confirm_delete") {
        $delete_count = count_job_eois($conn, $job_ref);

        if ($delete_count === 0) {
            $messages[] = "There are no applications for job $job_ref.";
        } else {
            $delete_job = $job_ref;
        }
    } else {
        $deleted = delete_job_eois($conn, $job_ref);

        if ($deleted !== null) {
            $_SESSION["success"] = "Deleted " . $deleted . " application(s) for job $job_ref.";
            header("Location: " . $page_url);
            exit();
        }
        $messages[] = "The applications could not be deleted. Please try again.";
    }
}

$status_counts = $has_table ? count_by_status($conn, $statuses) : array_fill_keys($statuses, 0);
$total_count = array_sum($status_counts);

$eois = [];
$match_count = 0;
$page_count = 1;
if ($has_table && !$filter_invalid) {
    $filters = [
        "status" => $filter_status, "application_id" => $filter_app, "job_ref" => $filter_job,
        "first_name" => $filter_first, "last_name" => $filter_last,
    ];
    $match_count = count_eois($conn, $filters);

    if ($match_count !== null && $match_count <= $per_page) {
        $page = 1;
        $eois = find_eois($conn, $filters, $sort, $order);
    } elseif ($match_count !== null) {
        $page_count = (int) ceil($match_count / $per_page);
        $page = min($page, $page_count);
        $eois = find_eois($conn, $filters, $sort, $order, $per_page, ($page - 1) * $per_page);
    }

    if ($match_count === null || $eois === null) {
        $eois = [];
        $match_count = 0;
        $messages[] = "Applications could not be loaded. Please try again later.";
    }
}

$page_title = "Manage Applications";
$page_description = "HR page for viewing and managing job applications at Community Connect.";
$current_page = "manage";

// Embedded CSS for this page
$page_style = <<<'CSS'
    /* Search boxes sit side by side on wide screens and stack on phones */
    .search-form {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(11rem, 1fr));
      gap: 0 1rem;
      align-items: end;
    }

    .search-form .group {
      grid-column: 1 / -1;
      margin: 0.25rem 0 0;
    }

    .eoi-table th,
    .eoi-table td {
      padding: 0.5rem 0.6rem;
    }

    .eoi-table td {
      font-size: var(--step--1);
    }

    .pager {
      align-items: center;
    }

    .pager p {
      margin: 0;
    }

    .status-form {
      display: flex;
      flex-wrap: wrap;
      gap: 0.4rem;
    }

    .status-form select {
      width: auto;
      padding: 0.3rem 0.4rem;
    }

    .btn-small {
      min-height: 36px;
      padding: 0.3rem 0.8rem;
      font-size: var(--step--1);
    }

    .btn-danger,
    .btn-danger:hover {
      border-color: var(--danger);
      background-color: var(--danger);
    }

    .confirm-box {
      max-width: var(--measure);
      margin-bottom: 1.5rem;
      padding: 1rem 1.25rem;
      border: 2px solid var(--danger);
      border-radius: var(--radius);
    }

    .delete-form {
      display: flex;
      flex-wrap: wrap;
      gap: 0 1rem;
      align-items: end;
    }

    .delete-form .btn {
      margin-bottom: 1.25rem;
    }

    .delete-form .btn,
    .confirm-box .btn {
      width: auto;
      margin-right: 0.5rem;
    }
CSS;

include "header.inc";
?>
  <main id="main">
    <h1>Manage Applications</h1>
    <p class="intro">Signed in as <strong><?php echo e($_SESSION["username"]); ?></strong>.
      <a href="staffregister.php">Add a manager</a></p>

<?php if ($success !== "") { ?>
    <p class="message message-success" role="status"><?php echo e($success); ?></p>
<?php } ?>

<?php show_errors($messages); ?>

<?php if (!$has_table) { ?>
    <p>No applications have been submitted yet. They will appear here after the first one arrives.</p>

<?php } else { ?>
    <p><strong><?php echo $total_count; ?></strong> application(s) in total:
<?php foreach ($status_counts as $status => $count) { ?>
      <strong><?php echo $count; ?></strong> <?php echo $status; ?><?php echo $status === "Final" ? "." : ","; ?>
<?php } ?>
    </p>
<?php if ($delete_job !== "") { ?>
    <section class="confirm-box" aria-labelledby="confirm-heading">
      <h2 id="confirm-heading">Delete applications for job <?php echo e($delete_job); ?>?</h2>
      <p>This will permanently delete <strong><?php echo $delete_count; ?></strong> application(s). It can't be undone.</p>
      <form method="post" action="<?php echo e($page_url); ?>">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="token" value="<?php echo $token; ?>">
        <input type="hidden" name="job_ref" value="<?php echo e($delete_job); ?>">
        <input class="btn btn-danger" type="submit" value="Yes, Delete Them">
        <a class="btn-secondary" href="<?php echo e($page_url); ?>">Cancel</a>
      </form>
    </section>
<?php } ?>

    <section aria-labelledby="search-heading">
      <h2 id="search-heading">Find and sort applications</h2>
      <form class="search-form" method="get" action="manage.php">
        <div class="field">
          <label for="application_id">Application ID</label>
          <input type="text" id="application_id" name="application_id" inputmode="numeric" maxlength="17" value="<?php echo e($filter_app); ?>">
        </div>
        <div class="field">
          <label for="job_ref">Job reference</label>
          <input type="text" id="job_ref" name="job_ref" maxlength="6" value="<?php echo e($filter_job); ?>">
        </div>
        <div class="field">
          <label for="first_name">First name</label>
          <input type="text" id="first_name" name="first_name" maxlength="20" value="<?php echo e($filter_first); ?>">
        </div>
        <div class="field">
          <label for="last_name">Last name</label>
          <input type="text" id="last_name" name="last_name" maxlength="20" value="<?php echo e($filter_last); ?>">
        </div>
        <div class="field">
          <label for="status_filter">Status</label>
          <select id="status_filter" name="status">
            <option value="">Any status</option>
<?php foreach ($statuses as $status) { ?>
            <option<?php if ($status === $filter_status) { echo " selected"; } ?>><?php echo $status; ?></option>
<?php } ?>
          </select>
        </div>
        <div class="field">
          <label for="sort">Sort by</label>
          <select id="sort" name="sort">
<?php foreach ($sort_fields as $column => $label) { ?>
            <option value="<?php echo $column; ?>"<?php if ($column === $sort) { echo " selected"; } ?>><?php echo $label; ?></option>
<?php } ?>
          </select>
        </div>
        <div class="field">
          <label for="order">Order</label>
          <select id="order" name="order">
            <option value="asc"<?php if ($order === "asc") { echo " selected"; } ?>>Ascending</option>
            <option value="desc"<?php if ($order === "desc") { echo " selected"; } ?>>Descending</option>
          </select>
        </div>
        <div class="group">
          <input class="btn" type="submit" value="Search">
          <a class="btn-secondary" href="manage.php">Show All</a>
        </div>
      </form>
    </section>

    <section aria-labelledby="delete-heading">
      <h2 id="delete-heading">Delete applications for a job</h2>
      <form class="delete-form" method="post" action="<?php echo e($page_url); ?>">
        <input type="hidden" name="action" value="confirm_delete">
        <input type="hidden" name="token" value="<?php echo $token; ?>">
        <div class="field">
          <label for="delete_ref">Job reference</label>
          <input type="text" id="delete_ref" name="job_ref" maxlength="6" required>
        </div>
        <input class="btn btn-danger" type="submit" value="Delete">
      </form>
    </section>

    <section aria-labelledby="results-heading">
      <h2 id="results-heading"><?php echo $is_filtered ? "Search results" : "All applications"; ?></h2>
<?php if (!$filter_invalid) { ?>
      <p><?php echo $match_count; ?> application(s) found.<?php if ($page_count > 1) { ?> Showing <?php echo ($page - 1) * $per_page + 1; ?> to <?php echo ($page - 1) * $per_page + count($eois); ?>.<?php } ?></p>
<?php } ?>

<?php if ($filter_invalid) { ?>
      <p>Fix the search above to see results.</p>
<?php } elseif (count($eois) === 0 && $is_filtered) { ?>
      <p>No applications match your search.</p>
<?php } ?>

<?php if (count($eois) > 0) { ?>
      <div class="table-wrap">
        <table class="eoi-table">
          <caption>Expressions of interest, sorted by <?php echo $sort_fields[$sort]; ?> (<?php echo $order === "asc" ? "ascending" : "descending"; ?>)</caption>
          <thead>
            <tr>
              <th scope="col">EOI</th>
              <th scope="col">Application ID</th>
              <th scope="col">Job</th>
              <th scope="col">Name</th>
              <th scope="col">Date of birth</th>
              <th scope="col">Contact</th>
              <th scope="col">Address</th>
              <th scope="col">Skills</th>
              <th scope="col">Status</th>
            </tr>
          </thead>
          <tbody>
<?php foreach ($eois as $row) {
    $skills = str_replace(",", ", ", (string) $row["Skills"]);
?>
            <tr>
              <th scope="row"><?php echo (int) $row["EOInumber"]; ?></th>
              <td><?php echo show($row["ApplicationID"]); ?></td>
              <td><?php echo show($row["JobReference"]); ?></td>
              <td><?php echo show($row["FirstName"] . " " . $row["LastName"]); ?><br><?php echo show($row["Gender"]); ?></td>
              <td><?php echo date("d/m/Y", strtotime($row["DOB"])); ?></td>
              <td><?php echo show($row["Email"]); ?><br><?php echo show($row["Phone"]); ?></td>
              <td><?php echo show($row["StreetAddress"]); ?><br><?php echo show($row["Suburb"] . " " . $row["State"] . " " . $row["Postcode"]); ?></td>
              <td>
                <?php echo $skills !== "" ? show($skills) : "None selected"; ?>
<?php if (trim((string) $row["OtherSkills"]) !== "") { ?>
                <br>Other: <?php echo show($row["OtherSkills"]); ?>
<?php } ?>
              </td>
              <td>
                <form class="status-form" method="post" action="<?php echo e($page_url); ?>">
                  <input type="hidden" name="action" value="update_status">
                  <input type="hidden" name="token" value="<?php echo $token; ?>">
                  <input type="hidden" name="eoi_number" value="<?php echo (int) $row["EOInumber"]; ?>">
                  <label class="visually-hidden" for="status-<?php echo (int) $row["EOInumber"]; ?>">Status for EOI <?php echo (int) $row["EOInumber"]; ?></label>
                  <select id="status-<?php echo (int) $row["EOInumber"]; ?>" name="status">
<?php foreach ($statuses as $status) { ?>
                    <option<?php if ($status === $row["Status"]) { echo " selected"; } ?>><?php echo $status; ?></option>
<?php } ?>
                  </select>
                  <input class="btn btn-small" type="submit" value="Update">
                </form>
              </td>
            </tr>
<?php } ?>
          </tbody>
        </table>
      </div>
<?php } ?>

<?php if ($page_count > 1) { ?>
      <nav class="group pager" aria-label="Pages of applications">
<?php if ($page > 1) { ?>
        <a class="btn-secondary" href="<?php echo e("manage.php?" . http_build_query($list_query + ["page" => $page - 1])); ?>">Previous</a>
<?php } ?>
        <p>Page <?php echo $page; ?> of <?php echo $page_count; ?></p>
<?php if ($page < $page_count) { ?>
        <a class="btn-secondary" href="<?php echo e("manage.php?" . http_build_query($list_query + ["page" => $page + 1])); ?>">Next</a>
<?php } ?>
      </nav>
<?php } ?>
    </section>
<?php } ?>
  </main>

<?php include "footer.inc"; ?>