<?php
require_once "session.inc";
require_once "db_connect.inc";   // gives you $conn
require_once "functions.inc";    // gives you clean_input() and e()
require_once "error.inc";

$page_description = "Full job listing.";
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
		min-width: min(21rem, 100%);
	}

	.outline h2 {
		margin: 0 0 0.5rem;
		font-family: inherit;
		font-size: 1rem;
	}
CSS;

if (!$conn)
{
	show_database_error("jobs.php", "Back to jobs");
}

$reference = isset($_GET["reference"]) && is_string($_GET["reference"]) ? strtoupper(clean_input($_GET["reference"])) : "";
$row = null;

if (preg_match('/^[A-Z0-9]{6}$/', $reference))
{
	$stmt = mysqli_prepare($conn, "SELECT * FROM jobs WHERE reference = ?"); // get job listing from reference number
	mysqli_stmt_bind_param($stmt, "s", $reference);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);
	$row = $result ? mysqli_fetch_assoc($result) : null;
	mysqli_stmt_close($stmt);
}

if (!$row)
{
	show_error_page("Job not found", ["We couldn't find that job. It may have been filled or removed."], "jobs.php", "Back to jobs", 404);
}

$_SESSION["reference"] = $row["reference"]; // set the current job reference for the session, for other pages
$page_title = $row["title"];

include("header.inc");
?>

  <main id="main">
	<?php
		// display the first section of information for the job listing
		echo "<section>
			<h1>" . show($row["title"]) . "</h1>
			<p class='coloured'>Salary: $" . number_format((float) $row["salary"]) . "</p>
			<p>" . show($row["description"]) . "</p>
			<p>" . show($row["reporting_line"]) . "</p>";

		// this div contains two buttons, so they can be displayed spaced as the group class
		echo "<div class='group'>" .
			"<a class='btn' href='apply.php?reference=" . urlencode($row["reference"]) . "'>Apply Now</a>" .
			"<a class='btn-secondary' href='userlogin.php'>Check Application</a>" .
			"</div>";

		// the group containing the 3 lists
		echo "<section class='group'>";

		// responsibilities list
		echo "<div class='outline'>" .
			"<h2 class='coloured'>Key Responsibilities</h2>" .
			"<ul>";
		$separated = explode("\n", trim((string) $row["responsibilities"])); // separate text by newline
		for ($i = 0; $i < count($separated); $i++)
		{
			echo "<li>" . show($separated[$i]) . "</li>";
		}
		echo "</ul>" .
		"</div>";

		// essential requirements list
		echo "<div class='outline'>" .
			"<h2 class='coloured'>Essential Requirements</h2>" .
			"<ul>";
		$separated = explode("\n", trim((string) $row["essential"])); // separate text by newline
		for ($i = 0; $i < count($separated); $i++)
		{
			echo "<li>" . show($separated[$i]) . "</li>";
		}
		echo "</ul>" .
			"</div>";

		// preferred requirements list
		echo "<div class='outline'>" .
			"<h2 class='coloured'>Preferred Requirements</h2>" .
			"<ul>";
		$separated = explode("\n", trim((string) $row["preferred"])); // separate text by newline
		for ($i = 0; $i < count($separated); $i++)
		{
			echo "<li>" . show($separated[$i]) . "</li>";
		}
		echo "</ul>" .
			"</div>";

		echo "</section>
			</section>";
	?>

  </main>

	<!-- footer from included file -->
	<?php include("footer.inc"); ?>