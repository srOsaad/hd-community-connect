<?php
session_start();
require_once "db_connect.inc";   // gives you $conn
require_once "functions.inc";    // gives you clean_input() and e()
require_once "error.inc";

if (!$conn)
{
	show_database_error();
}

$page_title = "Jobs";
$page_description = "The jobs listing page.";
$current_page = "jobs";

$page_style = <<<'CSS'
    .inline {
		display: inline;
	}

	.coloured {
		color: #1C3872; /*from style.css*/
		font-weight: bold;
	}

	.outline {
		padding: 1.25rem 1.5rem;
		border: 1px solid var(--border-strong);
		border-radius: var(--radius);
		max-width: 21rem;
		display: flex;
		flex-direction: column;
	}
	
	.outline form {
		margin-top: auto;
	}
CSS;

include("header.inc");
?>

	<main id="main">

	<h1>Jobs</h1>
	<p class="intro">List of currently available positions at Community Connect.</p>

	<!-- search bar -->
	<form method="get" action="jobs_search.php" class="inline">
		<label for="search">Search for a job listing</label>
		<input type="text" name="search" id="search" required>
		<input class="btn" style="width: 8em" type="submit" value="Search">
	</form>

	<!-- applied jobs button, goes to login page -->
	<a href="userlogin.php" class="inline btn-secondary" style="float: right;">Check Application</a>

	 <!-- group containing all job boxes -->
	<div class="group" style="gap: 2rem;/*overriding style.css*/">

		<?php
		if ($conn)
		{
			$query = "SELECT * FROM jobs ORDER BY reference"; // retrieve all job listing
			$result = mysqli_query($conn, $query);

			if ($result && mysqli_num_rows($result) > 0)
			{
				while ($row = mysqli_fetch_assoc($result))
				{
					// only title, reference number, salary, description, and reporting line are shown on this page
					echo "<section class='outline'>
						<h2>" . $row["title"] . "</h2>
						<p class='coloured'>Salary: $" . number_format($row["salary"]) . "</p>
						<p>" . $row["description"] . "</p>
						<p>" . $row["reporting_line"] . "</p>";

					// button is a form so the job reference can by sent via get
					echo "<form method='get' action='job_details.php'>" .
						"<input type='hidden' name='reference' value='" . e($row["reference"]) . "'>" .
						"<input class='btn' type='submit' value='Read More'>" .
						"</form>";

					echo "</section>";
				}
			}
			else echo "<p>There are no jobs at the moment...</p>"; // if db connected, but no listings
		}

		?>

	</div>

	<aside class="hint" style="float: right">
		<p>See the apply page for information on how to apply.</p>
	</aside>

  </main>

	<!-- footer from included file -->
	<?php include("footer.inc"); ?>