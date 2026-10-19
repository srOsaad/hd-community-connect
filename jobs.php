<!DOCTYPE html>
<html lang="en-AU">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Jobs</title>
  <meta name="description" content="The jobs listing page.">

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
	require_once "db_connect.inc";   // gives you $conn
	require_once "functions.inc";    // gives you clean_input() and e()
	?>

</head>

<body>

	<!-- header from included file -->
  <?php include("header.inc"); ?>

  <main id="main">

	<h1>Jobs</h1>
	<p class="intro">List of currently avaliable positions at community Connect.</p>
	
	<!-- search bar -->
	<form method="get" action="jobs_search.php">
		<label for="search">Search for a job listing</label>
		<input type="text" name="search" id="search" required>
		<input type="submit" value="Search">
	</form>
	
	 <!-- group containing all job boxes -->
	<div class="group" style="gap: 3rem; /*overriding style.css*/">
	
		<?php
			
			if ($conn)
			{
				$query = "SELECT * FROM jobs";
				$result = mysqli_query($conn, $query);
				
				if (mysqli_num_rows($result) > 0)
				{
					while ($row = mysqli_fetch_assoc($result))
					{
						echo "<section class='outline'>
							<h2 class='inline'>" . $row["title"] . "</h2>
							<p class='inline hint'> #" . $row["reference"] . "</p>
							<p>" . $row["description"] . "</p>
							<p class='coloured'> $" . number_format($row["salary"]) . "</p>
							<p>" . $row["reporting_line"] . "</p>";
							
							// button is a form so the job reference can by sent via get
							echo "<form method='get' action='apply.php'>";
							echo "<input type='hidden' name='reference' value='100001'>";
							echo "<input type='submit' value='Apply'>";
							echo "</form>";
							//echo "<a href=apply.php' class='btn'>APPLY</a>";
							
							echo "</section>";
							

					}
				}
				else echo "<p>There are no jobs at the moment...</p>";
			}
			else echo "<p>There was an error connecting to the database...</p>"

			
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