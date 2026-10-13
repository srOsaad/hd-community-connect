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

</head>

<body>

	<!-- header from included file -->
  <?php include("header.inc"); ?>

  <main id="main">

	<h1>Jobs</h1>
	<p class="intro">List of currently avaliable positions at community Connect.</p>
	
	<div class="group" style="gap: 3rem; /*overriding style.css*/"> <!-- group containing all job boxes -->
	
		<!-- job 1 -->
		<section class="outline">
			<h2 class="inline">Community Organiser</h2>
			<p class="inline hint">#100001<p>
			<p>Community Organisers are in charge of planning and coordinating community events.</p>
			<p class="coloured">$70,000</p>
			<p>Reports to: Manager</p>
			<!-- key responsibilities -->
			<p class="coloured">Key Responsibilities</p>
			<ol style="list-style-type: square"> <!-- use inline here to remove numbers -->
				<li>Event Planning and Coordination</li>
				<li>Collaboration and Networking</li>
				<li>Public Speaking and Facilitation</li>
			</ol>
			<!-- essential requirements -->
			<p class="coloured">Essential Requirements</p>
			<ul>
				<li>Effective Communication</li>
				<li>Strategic Planning</li>
				<li>Legislative & Policy Knowledge</li>
			</ul>
			<!-- preferable requirements -->
			<p class="coloured">Preferable Requirements</p>
			<ul>
				<li>Problem-Solving</li>
				<li>Time Management</li>
				<li>Team Leadership</li>
			</ul>
		</section>
		
		<!-- job 2 -->
		<section class="outline">
			<h2 class="inline">Fundraiser</h2>
			<p class="inline hint">#100002<p>
			<p>Fundraisers work to organise and run fundraising events as well as managing funds raised.</p>
			<p class="coloured">$94,000</p>
			<p>Manager/Treasurer</p>
			<!-- key responsibilities -->
			<p class="coloured">Key Responsibilities</p>
			<ol style="list-style-type: square"> <!-- use inline here to remove numbers -->
				<li>Keep financial records</li>
				<li>Fundraising events</li>
				<li>Communicating with the public</li>
			</ol>
			<!-- essential requirements -->
			<p class="coloured">Essential Requirements</p>
			<ul>
				<li>Communication skills</li>
				<li>Budget Management</li>
				<li>Data Analysis and Fundraising</li>
			</ul>
			<!-- preferable requirements -->
			<p class="coloured">Preferable Requirements</p>
			<ul>
				<li>Networking</li>
				<li>Strategic thinking</li>
				<li>Persistence and resilience</li>
			</ul>
		</section>
		
		<!-- job 3 -->
		<section class="outline">
			<h2 class="inline">Media/PR</h2>
			<p class="inline hint">#100003<p>
			<p>PR is in charge of working with branding and social media.</p>
			<p class="coloured">$90,000</p>
			<p>Branding/management</p>
			<!-- key responsibilities -->
			<p class="coloured">Key Responsibilities</p>
			<ol style="list-style-type: square"> <!-- use inline here to remove numbers -->
				<li>Media Relations</li>
				<li>Brand Management</li>
				<li>Social Media Management</li>
			</ol>
			<!-- essential requirements -->
			<p class="coloured">Essential Requirements</p>
			<ul>
				<li>Effective Communication</li>
				<li>Communication</li>
				<li>Research and Analysis</li>
			</ul>
			<!-- preferable requirements -->
			<p class="coloured">Preferable Requirements</p>
			<ul>
				<li>Cultural Awareness</li>
				<li>Networking</li>
				<li>Graphic Design</li>
			</ul>
		</section>		
	</div>
	
	<a href="apply.html" class="btn">APPLY</a>
	<a href="index.html" class="btn-secondary">BACK</a>
	
	<aside style="float: right"> <!-- more inline css -->
		<p class="hint">See the apply page for information on how to apply.</p>
	</aside>

  </main>

	<!-- footer from included file -->
	<?php include("footer.inc"); ?>

</body>
</html>