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
	
	 <!-- group containing all job boxes -->
	<div>
	
		<?php
		
			if ($conn && isset($_GET["reference"]))
			{
				$search = $_GET["reference"]; // sanitise this
				$query = "SELECT * FROM jobs WHERE reference = " . $_GET["reference"];
				$result = mysqli_query($conn, $query);
				
				if (mysqli_num_rows($result) > 0)
				{
					echo "<ol style='list-style: none;'>"; // inline css
					while ($row = mysqli_fetch_assoc($result))
					{
						echo "<section class='outline'>
							<h1>" . $row["title"] . "</h1>
							<p class='hint'> #" . $row["reference"] . "</p>
							<p class='coloured'>Salary: $" . number_format($row["salary"]) . "</p>
							<p>" . $row["description"] . "</p>
							<p>" . $row["reporting_line"] . "</p>";
							
							// responsibilities
							echo "<p class='coloured'>Key Responsibilities:</p>
							<ul>";
							$separated = explode("\n", $row["responsibilities"]); // separate text by newline
							for ($i = 0; $i < count($separated); $i++)
							{
								echo "<li>" . $separated[$i] . "</li>";
							}
							echo "</ul>";
							
							// essential
							echo "<p class='coloured'>Essential Requirements:</p>
							<ul>";
							$separated = explode("\n", $row["essential"]); // separate text by newline
							for ($i = 0; $i < count($separated); $i++)
							{
								echo "<li>" . $separated[$i] . "</li>";
							}
							echo "</ul>";
							
							// preferred
							echo "<p class='coloured'>Prefered Requirements:</p>
							<ul>";
							$separated = explode("\n", $row["preferred"]); // separate text by newline
							for ($i = 0; $i < count($separated); $i++)
							{
								echo "<li>" . $separated[$i] . "</li>";
							}
							echo "</ul><br>";
							
						// button reused from jobs.php
						echo "<form method='get' action='apply.php'>";
						echo "<input type='hidden' name='reference' value='" . $row["reference"] . "'>";
						echo "<input class='btn' type='submit' value='Apply'>";
						echo "</form>";
						echo "</li><br>";
					}
					echo "</ol>";
				}
			}
		?>
		
	</div>

  </main>

	<!-- footer from included file -->
	<?php include("footer.inc"); ?>

</body>
</html>