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

$page_title = "Search Jobs";
$page_description = "Results for jobs search.";
$current_page = "jobs";

$page_style = <<<'CSS'
    .search-again {
		display: flex;
		flex-wrap: wrap;
		gap: 0.5rem;
		align-items: end;
		margin-bottom: 1.5rem;
	}

	.search-again label {
		flex-basis: 100%;
	}

	.search-again input[type='text'] {
		flex: 1 1 14rem;
	}

	.coloured {
		color: var(--brand);
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

	<h1>Search Jobs</h1>

	<?php
	$search = isset($_GET["search"]) ? clean_input($_GET["search"]) : "";
	?>

	<form method="get" action="jobs_search.php" class="search-again">
		<label for="search">Search for a job title</label>
		<input type="text" name="search" id="search" value="<?php echo $search; ?>" required>
		<input class="btn" style="width: 8em" type="submit" value="Search">
	</form>

	<?php
	if ($search === "")
	{
		echo "<p>Enter a job title to search.</p>";
	}
	else
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
				job_card($row);
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