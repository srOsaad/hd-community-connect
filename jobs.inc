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
    // only title, salary, job reference, description, and reporting line are shown on the card
    echo "<section class='outline job-card'>
        <h2>" . show($row["title"]) . "</h2>
        <p class='coloured'>Salary: $" . number_format((float) $row["salary"]) . "</p>
        <p>Job reference: " . show($row["reference"]) . "</p>
        <p>" . show($row["description"]) . "</p>
        <p>Reports to: " . show($row["reporting_line"]) . "</p>";

    // button is a form so the job reference can be sent via get
    echo "<form method='get' action='job_details.php'>" .
        "<input type='hidden' name='reference' value='" . show($row["reference"]) . "'>" .
        "<input class='btn' type='submit' value='Read More'>" .
        "</form>";

    echo "</section>";
}

function get_job_requirements($conn, $job_id) {
    $lists = ["responsibility" => [], "essential" => [], "preferred" => []];
    $stmt = mysqli_prepare($conn, "SELECT list_type, item_text FROM job_requirements WHERE job_id = ? ORDER BY list_type, sort_order");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $job_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($item = mysqli_fetch_assoc($result)) {
            $lists[$item["list_type"]][] = $item["item_text"];
        }
        mysqli_stmt_close($stmt);
    }
    return $lists;
}

function job_list($heading, $items) {
    echo "<div class='outline'>" .
        "<h2 class='coloured'>" . e($heading) . "</h2>" .
        "<ul>";
    foreach ($items as $item)
    {
        echo "<li>" . show($item) . "</li>";
    }
    echo "</ul>" .
        "</div>";
}
?>