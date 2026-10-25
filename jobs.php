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
        <form method="get" action="search.php" class="job-search">
            <label for="search">Search for a job title</label>
            <input type="text" name="search" id="search" required>
            <input class="btn" style="width: 8em" type="submit" value="Search">
        </form>

        <!-- applied jobs button, goes to login page -->
        <a href="userlogin.php" class="btn-secondary">Check Application</a>
    </div>

     <!-- group containing all job boxes -->
    <div class="group" style="gap: 2rem;/*overriding style.css*/">

        <?php
        $query = "SELECT * FROM jobs ORDER BY reference"; // retrieve all job listing
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0)
        {
            while ($row = mysqli_fetch_assoc($result))
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