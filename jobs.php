<?php
require_once "session.inc";
require_once "db_connect.inc";   // gives you $conn
require_once "functions.inc";    // helpers that jobs.inc uses
require_once "error.inc";
require_once "jobs.inc";

if (!$conn) {
    show_database_error();
}

$jobs = find_jobs($conn); // retrieve all job listings

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

    .apply-help {
        width: 100%;
        max-width: 40rem;
        margin: 0 0 2rem;
        padding: 1.25rem 1.5rem;
        border: 1px solid var(--border-strong);
        border-left: 4px solid var(--brand);
        border-radius: var(--radius);
        background-color: var(--surface-alt);
    }

    .apply-help h2 {
        margin-top: 0;
        font-size: var(--step-1);
    }

    .apply-help ol {
        margin-bottom: 0;
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

        <!-- Check Application button, goes to the applicant login page -->
        <a href="userlogin.php" class="btn-secondary">Check Application</a>
    </div>

    <aside class="apply-help" aria-labelledby="apply-help-heading">
        <h2 id="apply-help-heading">How to Apply</h2>
        <ol>
            <li>Choose a job below and select Read More to see its responsibilities and requirements.</li>
            <li>Fill in the <a href="apply.php">application form</a>. You will be given an application ID.</li>
            <li>Use your application ID and password to <a href="userlogin.php">check your application</a> at any time.</li>
        </ol>
    </aside>

     <!-- group containing all job boxes -->
    <div class="group job-list">

        <?php
        if ($jobs) {
            foreach ($jobs as $row) {
                job_card($row);
            }
        } else {
            echo "<p>There are no jobs at the moment.</p>"; // the jobs table is empty
        }
        ?>

    </div>

  </main>

    <!-- footer from included file -->
    <?php include "footer.inc"; ?>