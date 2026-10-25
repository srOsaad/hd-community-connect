<?php
require_once "session.inc";
require_once "db_connect.inc";   // gives you $conn
require_once "functions.inc";    // gives you clean_input()
require_once "error.inc";
require_once "jobs.inc";

if (!$conn) {
    show_database_error();
}

$page_title = "Search Jobs";
$page_description = "Search Community Connect job openings by title.";
$current_page = "jobs";

$page_style = <<<'CSS'
    .job-search {
        margin-bottom: 1.5rem;
    }
CSS;

include "header.inc";
?>

<main id="main">

    <h1>Search Jobs</h1>

    <?php
    $search = isset($_GET["search"]) ? clean_input($_GET["search"]) : "";
    ?>

    <?php job_search_form($search); ?>

    <?php
    if ($search === "") {
        echo "<p>Enter a job title to search.</p>";
    } else {
        $jobs = find_jobs($conn, $search); // retrieve job listings based on search from user

        echo "<p class='intro'> Search results for &#34;" . $search . "&#34;</p>";

        echo "<div class='group job-list'>"; // group containing all job boxes

        if ($jobs) {
            foreach ($jobs as $row) {
                job_card($row);
            }
        } else {
            echo "<p>No job listings found. Search for something else or check back later.</p>"; // if db connected, but no listings
        }

        echo "</div>";
    }
    ?>

</main>

    <!-- footer from included file -->
<?php include "footer.inc"; ?>