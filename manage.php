<?php
/*
manage.php: the HR manager's page. Only logged-in staff can see it.
Shows every job application saved by process_eoi.php.
*/

require_once "auth.inc";          # keep this first, it sends anyone not logged in back to the login page
require_once "db_connect.inc";    # connects to the database ($conn)
require_once "functions.inc";     # our helpers: clean_input() and e()

# The form data was already escaped by clean_input() before saving,
# so we undo that first, otherwise "&" would show up as "&amp;" on the page
function show($value) {
    return e(html_entity_decode((string) $value, ENT_QUOTES, "UTF-8"));
}

# Reads one text value from the URL. If it's missing (or someone sends an array), we just get ""
function field($source, $key) {
    return (isset($source[$key]) && is_string($source[$key])) ? clean_input($source[$key]) : "";
}

$messages = [];   # error messages to show above the results

# What the manager is searching for (empty means "don't filter by this")
$filter_job   = strtoupper(field($_GET, "job_ref"));
$filter_first = field($_GET, "first_name");
$filter_last  = field($_GET, "last_name");

# Check the search values the same way the apply form checks them. Anything invalid is ignored
if ($filter_job !== "" && !preg_match('/^[A-Z0-9]{6}$/', $filter_job)) {
    $messages[] = "A job reference is exactly 6 letters or numbers.";
    $filter_job = "";
}
if ($filter_first !== "" && !preg_match('/^[A-Za-z ]{1,20}$/', $filter_first)) {
    $messages[] = "First name can only contain letters and spaces (up to 20).";
    $filter_first = "";
}
if ($filter_last !== "" && !preg_match('/^[A-Za-z ]{1,20}$/', $filter_last)) {
    $messages[] = "Last name can only contain letters and spaces (up to 20).";
    $filter_last = "";
}

$is_filtered = $filter_job !== "" || $filter_first !== "" || $filter_last !== "";

# Columns the manager can sort by.
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

$statuses = ["New", "Current", "Final"];

# CSRF token: forms that change data must send this back
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
$token = $_SESSION["csrf_token"];

$success = "";
if (isset($_SESSION["success"])) {
    $success = $_SESSION["success"];
    unset($_SESSION["success"]);
}

$page_url = "manage.php?" . http_build_query([
    "job_ref" => $filter_job, "first_name" => $filter_first, "last_name" => $filter_last,
    "sort" => $sort, "order" => $order,
]);

# The eoi table only exists after the first application is sent, so check for it first
$has_table = false;
if ($conn) {
    $check = mysqli_query($conn, "SHOW TABLES LIKE 'eoi'");
    $has_table = $check && mysqli_num_rows($check) > 0;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $conn && $has_table) {
    $eoi_number = (int) field($_POST, "eoi_number");
    $new_status = field($_POST, "status");

    if (!hash_equals($token, field($_POST, "token"))) {
        $messages[] = "That request could not be verified. Please try again.";
    } elseif (field($_POST, "action") !== "update_status" || $eoi_number < 1 || !in_array($new_status, $statuses, true)) {
        $messages[] = "Please choose a valid status.";
    } else {
        $stmt = $conn->prepare("SELECT Status FROM eoi WHERE EOInumber = ?");
        $stmt->bind_param("i", $eoi_number);
        $stmt->execute();
        $current = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$current) {
            $messages[] = "EOI $eoi_number doesn't exist.";
        } elseif ($current["Status"] === $new_status) {
            $_SESSION["success"] = "EOI $eoi_number already had the status $new_status.";
        } else {
            $stmt = $conn->prepare("UPDATE eoi SET Status = ? WHERE EOInumber = ?");
            $stmt->bind_param("si", $new_status, $eoi_number);
            if ($stmt->execute()) {
                $_SESSION["success"] = "EOI $eoi_number is now $new_status.";
            } else {
                error_log("Status update failed: " . $stmt->error);
                $messages[] = "The status could not be changed. Please try again.";
            }
            $stmt->close();
        }

        # Redirect so refreshing doesn't send the form again
        if (isset($_SESSION["success"])) {
            header("Location: " . $page_url);
            exit();
        }
    }
}

# Get the applications, oldest first. Each filled search box adds a condition,
# and the values go in as ? placeholders so they can't change the SQL
$eois = [];
if ($conn && $has_table) {
    $conditions = [];
    $types = "";
    $params = [];

    if ($filter_job !== "") {
        $conditions[] = "JobReference = ?";
        $types .= "s";
        $params[] = $filter_job;
    }
    if ($filter_first !== "") {
        $conditions[] = "FirstName LIKE ?";      # LIKE with % on both sides means "contains"
        $types .= "s";
        $params[] = "%" . $filter_first . "%";
    }
    if ($filter_last !== "") {
        $conditions[] = "LastName LIKE ?";
        $types .= "s";
        $params[] = "%" . $filter_last . "%";
    }

    $sql = "SELECT * FROM eoi";
    if ($conditions) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    $order_by = $sort === "State" ? "CAST(State AS CHAR)" : $sort;
    $sql .= " ORDER BY " . $order_by . " " . strtoupper($order) . ", EOInumber";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $eois = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        error_log("EOI list query failed: " . $conn->error);
        $messages[] = "Applications could not be loaded. Please try again later.";
    }
}

$page_title = "Manage applications";
$page_description = "HR page for viewing and managing job applications at Community Connect.";
$current_page = "manage";

# Embedded CSS for this page
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

    /* The table is wide, so on phones it scrolls inside this box instead of the whole page */
    .table-wrap {
      position: relative;
      overflow-x: auto;
      margin-bottom: 1rem;
    }

    .eoi-table td {
      font-size: var(--step--1);
    }

    .status-form {
      display: flex;
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
CSS;

include "header.inc";
?>
  <main id="main">
    <h1>Manage applications</h1>
    <p class="intro">Signed in as <strong><?php echo e($_SESSION["username"]); ?></strong>.</p>
 
<?php if ($success !== "") { ?>
    <p class="message message-success" role="status"><?php echo e($success); ?></p>
<?php } ?>

<?php foreach ($messages as $message) { ?>
    <p class="message message-error" role="alert"><?php echo e($message); ?></p>
<?php } ?>
 
<?php if (!$conn) { ?>
    <p class="message message-error"><?php echo e($db_error_message); ?></p>
 
<?php } elseif (!$has_table) { ?>
    <p>No applications have been submitted yet. They will appear here after the first one arrives.</p>
 
<?php } else { ?>
    <section aria-labelledby="search-heading">
      <h2 id="search-heading">Find and sort applications</h2>
      <form class="search-form" method="get" action="manage.php">
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
          <a class="btn-secondary" href="manage.php">Show all</a>
        </div>
      </form>
    </section>
 
    <section aria-labelledby="results-heading">
      <h2 id="results-heading"><?php echo $is_filtered ? "Search results" : "All applications"; ?></h2>
      <p><?php echo count($eois); ?> application(s) found.</p>
 
<?php if (count($eois) === 0 && $is_filtered) { ?>
      <p>No applications match your search.</p>
<?php } ?>
 
<?php if (count($eois) > 0) { ?>
      <div class="table-wrap">
        <table class="eoi-table">
          <caption>Expressions of interest, sorted by <?php echo $sort_fields[$sort]; ?> (<?php echo $order === "asc" ? "ascending" : "descending"; ?>)</caption>
          <thead>
            <tr>
              <th scope="col">EOI</th>
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
    </section>
<?php } ?>
  </main>
 
<?php include "footer.inc"; ?>