<?php
require_once "session.inc";
require_once "db_connect.inc";   // gives you $conn
require_once "functions.inc";    // gives you clean_input() and e()
require_once "error.inc";
require_once "jobs.inc";

if (!$conn)
{
    show_database_error();
}

$page_title = "Jobs";
$page_description = "Browse current job openings at Community Connect and find a role that suits you.";
$current_page = "jobs";

$page_style = <<<'CSS'
    .job-tools {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: end;
        justify-content: space-between;
        margin-bottom: 1.5rem;
    }

    .job-tools form {
        flex: 1 1 22rem;
    }
CSS;

include "header.inc";
?>

    <main id="main">

    <h1>Jobs</h1>
    <p class="intro">List of currently available positions at Community Connect.</p>

    <div class="job-tools">
        <!-- search bar -->
        <?php job_search_form(); ?>

        <!-- applied jobs button, goes to login page -->
        <a href="userlogin.php" class="btn-secondary">Check Application</a>
    </div>

     <!-- group containing all job boxes -->
    <div class="group job-list">

        <?php
        $jobs = find_jobs($conn); // retrieve all job listing

        if ($jobs)
        {
            foreach ($jobs as $row)
            {
                job_card($row);
            }
        }
        else
        {
            echo "<p>There are no jobs at the moment.</p>"; // if db connected, but no listings
        }
        ?>

    </div>

  </main>

    <!-- footer from included file -->
    <?php include "footer.inc"; ?>