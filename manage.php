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

# The eoi table only exists after the first application is sent, so check for it first
$has_table = false;
if ($conn) {
    $check = mysqli_query($conn, "SHOW TABLES LIKE 'eoi'");
    $has_table = $check && mysqli_num_rows($check) > 0;
}

# Get all applications, oldest first
$eois = [];
if ($conn && $has_table) {
    $result = mysqli_query($conn, "SELECT * FROM eoi ORDER BY EOInumber");
    if ($result) {
        $eois = mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        error_log("EOI list query failed: " . mysqli_error($conn));
    }
}

$page_title = "Manage applications";
$page_description = "HR page for viewing and managing job applications at Community Connect.";
$current_page = "manage";

# Embedded CSS for this page
$page_style = <<<'CSS'
    /* The table is wide, so on phones it scrolls inside this box instead of the whole page */
    .table-wrap {
      position: relative;
      overflow-x: auto;
      margin-bottom: 1rem;
    }

    .eoi-table td {
      font-size: var(--step--1);
    }
CSS;

include "header.inc";
?>
  <main id="main">
    <h1>Manage applications</h1>
    <p class="intro">Signed in as <strong><?php echo e($_SESSION["username"]); ?></strong>.</p>

<?php if (!$conn) { ?>
    <p class="message message-error"><?php echo e($db_error_message); ?></p>

<?php } elseif (!$has_table) { ?>
    <p>No applications have been submitted yet. They will appear here after the first one arrives.</p>

<?php } else { ?>
    <section aria-labelledby="results-heading">
      <h2 id="results-heading">All applications</h2>
      <p><?php echo count($eois); ?> application(s) found.</p>

<?php if (count($eois) > 0) { ?>
      <div class="table-wrap">
        <table class="eoi-table">
          <caption>Expressions of interest</caption>
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
              <td><?php echo show($row["Status"]); ?></td>
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