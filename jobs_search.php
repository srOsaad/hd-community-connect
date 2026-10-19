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
		
		/*min-width: 20rem;*/
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

	<h1>Search Jobs</h1>
	
	 <!-- group containing all job boxes -->
	<div>
	
		<?php
		
			if ($conn && isset($_GET["search"]))
			{
				$search = $_GET["search"]; // sanitise this
				$query = "SELECT * FROM jobs WHERE title LIKE '%$search%'";
				$result = mysqli_query($conn, $query);
				
				echo "<p class='intro'> Search results for &#34;" . $_GET["search"] . "&#34;</p>";
				
				if (mysqli_num_rows($result) > 0)
				{
					echo "<ol style='list-style: none;'>"; // inline css
					while ($row = mysqli_fetch_assoc($result))
					{
						echo "<li class='outline'> <h2 class='inline'>" . $row["title"] . "</h2>" // make wide
						. "<p class='inline hint'> #" . $row["reference"] . "</p>";
						echo "<br><br><p>" . $row["description"] . "</p>";
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
	
	<aside style="float: right"> <!-- more inline css -->
		<p class="hint">See the apply page for information on how to apply.</p>
	</aside>

  </main>

	<!-- footer from included file -->
	<?php include("footer.inc"); ?>

</body>
</html>