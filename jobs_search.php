<?php
session_start();
require_once "db_connect.inc";   // gives you $conn
require_once "functions.inc";    // gives you clean_input() and e()

$page_title = "Search Jobs";
$page_description = "Results for jobs search.";
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
		max-width: 20rem;
	}
CSS;

include("header.inc");
?>

	<main id="main">

	<h1>Search Jobs</h1>

	<?php

	if ($conn && isset($_GET["search"]))
	{
		$search = clean_input($_GET["search"]);
		$like = "%" . $search . "%";
		$stmt = mysqli_prepare($conn, "SELECT * FROM jobs WHERE title LIKE ? ORDER BY reference"); // retrieve job listings based on search from user
		mysqli_stmt_bind_param($stmt, "s", $like);
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);

		echo "<p class='intro'> Search results for &#34;" . $search . "&#34;</p>";

		echo "<div class='group' style='gap: 2rem;/*overriding style.css*/'>"; // group containing all job boxes

		if ($result && mysqli_num_rows($result) > 0)
		{
			while ($row = mysqli_fetch_assoc($result))
			{
				// only title, reference number, salary, description, and reporting line are shown on this page
				echo "<section class='outline'>
					<h2>" . $row["title"] . "</h2>
					<p class='hint'> #" . $row["reference"] . "</p>
					<p class='coloured'>Salary: $" . number_format($row["salary"]) . "</p>
					<p>" . $row["description"] . "</p>
					<p>" . $row["reporting_line"] . "</p>";

					// button is a form so the job reference can by sent via get
					echo "<form method='get' action='job_full.php'>" .
						"<input type='hidden' name='reference' value='" . e($row["reference"]) . "'>" .
						"<input class='btn' type='submit' value='Read More'>" .
						"</form>";

					echo "</section>";
				}
			}
			else echo "<p>No job listings found. Search for something else or check back later.</p>"; // if db connected, but no listings

			echo "</div>";
			mysqli_stmt_close($stmt);
		}
		else echo "<p>There was an error connecting to the database...</p>"; // if failed to connect to db
		?>

	<aside style="float: right">
		<p class="hint">See the apply page for information on how to apply.</p>
	</aside>

	</main>

	<!-- footer from included file -->
	<?php include("footer.inc"); ?>