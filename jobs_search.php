<?php
session_start();
require_once "db_connect.inc";   // gives you $conn
require_once "functions.inc";    // gives you clean_input() and e()
require_once "error.inc";

if (!$conn)
{
	show_database_error();
}

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

	<h1>Search Jobs</h1>

	<?php

	$search = isset($_GET["search"]) ? clean_input($_GET["search"]) : "";

	if ($conn && $search === "")
	{
		echo "<p>Enter a job title in the search box on the <a href='jobs.php'>jobs page</a>.</p>";
	}
	elseif ($conn)
	{
		$like = like_pattern(html_entity_decode($search, ENT_QUOTES, "UTF-8"));
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
			else echo "<p>No job listings found. Search for something else or check back later.</p>"; // if db connected, but no listings

			echo "</div>";
			mysqli_stmt_close($stmt);
		}
		?>

	<aside style="float: right">
		<p class="hint">See the apply page for information on how to apply.</p>
	</aside>

	</main>

	<!-- footer from included file -->
	<?php include("footer.inc"); ?>