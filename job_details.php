<?php
require_once "session.inc";
require_once "db_connect.inc";   // gives you $conn
require_once "functions.inc";    // gives you clean_input() and e()
require_once "error.inc";
require_once "jobs.inc";

$page_description = "Full details of a job at Community Connect, including responsibilities and requirements.";
$current_page = "jobs";

$page_style = <<<'CSS'
    .lists {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(18rem, 100%), 1fr));
        gap: 2rem;
    }

    .outline h2 {
        margin: 0 0 0.5rem;
        font-family: inherit;
        font-size: 1rem;
    }
CSS;

if (!$conn)
{
    show_database_error("jobs.php", "Back to Jobs");
}

$reference = isset($_GET["reference"]) && is_string($_GET["reference"]) ? strtoupper(clean_input($_GET["reference"])) : "";
$row = null;

if (preg_match('/^[A-Z0-9]{6}$/D', $reference))
{
    $stmt = db_prepare($conn, "SELECT * FROM jobs WHERE reference = ?"); // get job listing from reference number
    mysqli_stmt_bind_param($stmt, "s", $reference);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);
}

if (!$row)
{
    show_error_page("Job Not Found", ["We couldn't find that job. It may have been filled or removed."], "jobs.php", "Back to Jobs", 404);
}

$page_title = $row["title"];
$lists = get_job_requirements($conn, (int) $row["job_id"]);

include "header.inc";
?>

  <main id="main">
    <?php
        // display the first section of information for the job listing
        echo "<section>
            <h1>" . show($row["title"]) . "</h1>
            " . job_facts($row);

        // this div contains three buttons, so they can be displayed spaced as the group class
        echo "<div class='group'>" .
            "<a class='btn' href='apply.php?reference=" . urlencode($row["reference"]) . "'>Apply Now</a>" .
            "<a class='btn-secondary' href='userlogin.php'>Check Application</a>" .
            "<a class='btn-secondary' href='jobs.php'>Back to Jobs</a>" .
            "</div>";

        // the group containing the 3 lists
        echo "<section class='group lists'>";

        // responsibilities list
        job_list("Key Responsibilities", $lists["responsibility"]);

        // essential requirements list
        job_list("Essential Requirements", $lists["essential"]);

        // preferred requirements list
        job_list("Preferred Requirements", $lists["preferred"]);

        echo "</section>
            </section>";
    ?>

  </main>

    <!-- footer from included file -->
    <?php include "footer.inc"; ?>