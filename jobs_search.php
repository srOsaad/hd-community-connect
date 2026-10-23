<!DOCTYPE html>
<html lang="en-AU">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Jobs</title>
  <meta name="description" content="Results for jobs search.">

  <!-- Google Fonts — credited in the footer -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;600&amp;family=Source+Serif+4:wght@600;700&amp;display=swap">
  
  <link rel="icon" type="image/x-icon" href="assets/favicon.ico">
  <link rel="stylesheet" href="styles/styles.css">

  <style>
    .inline {
		display: inline;
	}
	
	.coloured {
		color: #1C3872; /*from style.css*/
		font-weight: bold;
	}
	
	.group {
		/*gap: 5rem;*/ /*overriding style.css*/
	}
	
	.outline {
		padding: 1.25rem 1.5rem;
		border: 1px solid var(--border-strong);
		border-radius: var(--radius);
		
		max-width: 20rem;
	}
  </style>
  
    <!-- php includes -->
	<?php
	session_start();
	require_once "db_connect.inc";   // gives you $conn
	require_once "functions.inc";    // gives you clean_input() and e()
	?>

</head>

<body>

	<!-- header from included file -->
  <?php include("header.inc"); ?>

  <main id="main">

	<h1>Search Jobs</h1>
	
	 <!-- group containing all job boxes -->
	<div>
	
		<?php
		
			if ($conn && isset($_GET["search"]))
			{
				$search = clean_input($_GET["search"]);
				$query = "SELECT * FROM jobs WHERE title LIKE '%$search%'";
				$result = mysqli_query($conn, $query);
				
				echo "<p class='intro'> Search results for &#34;" . $_GET["search"] . "&#34;</p>";
				
				if (mysqli_num_rows($result) > 0)
				{
					echo "<ol style='list-style: none;'>"; // inline css
					while ($row = mysqli_fetch_assoc($result))
					{
						echo "<section class='outline'>
							<h2>" . $row["title"] . "</h2>
							<p class='hint'> #" . $row["reference"] . "</p>
							<p class='coloured'>Salary: $" . number_format($row["salary"]) . "</p>
							<p>" . $row["description"] . "</p>
							<p>" . $row["reporting_line"] . "</p>";
							
							// button is a form so the job reference can by sent via get
							echo "<form method='get' action='job_full.php'>";
							echo "<input type='hidden' name='reference' value='" . $row["reference"] . "'>";
							echo "<input class='btn' type='submit' value='Read More'>";
							echo "</form>";
							
							echo "</section>";
					}
					echo "</ol>";
				}
			}
		?>
		
	</div>
	
	<aside style="float: right"> <!-- more inline css -->
		<p class="hint">See the apply page for information on how to apply.</p>
	</aside>

  </main>

	<!-- footer from included file -->
	<?php include("footer.inc"); ?>

</body>
</html>