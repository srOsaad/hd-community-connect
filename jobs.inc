<?php
// Returns [ reference => title ]
function get_job_map($conn) {
    $map = [];
    if (!$conn) {
        return $map;
    }
    $res = mysqli_query($conn, "SELECT reference, title FROM jobs ORDER BY reference");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $map[$row['reference']] = $row['title'];
        }
    }
    return $map;
}

function job_card($row) {
	// only title, salary, description, and reporting line are shown on the card
	echo "<section class='outline'>
		<h2>" . show($row["title"]) . "</h2>
		<p class='coloured'>Salary: $" . number_format((float) $row["salary"]) . "</p>
		<p>" . show($row["description"]) . "</p>
		<p>" . show($row["reporting_line"]) . "</p>";

	// button is a form so the job reference can be sent via get
	echo "<form method='get' action='job_details.php'>" .
		"<input type='hidden' name='reference' value='" . show($row["reference"]) . "'>" .
		"<input class='btn' type='submit' value='Read More'>" .
		"</form>";

	echo "</section>";
}
?>