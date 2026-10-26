<?php
// Returns [ reference => title ]
function get_job_map($conn) {
    $map = [];
    if (!$conn) {
        return $map;
    }
    $res = db_query($conn, "SELECT reference, title FROM jobs ORDER BY reference");
    while ($row = mysqli_fetch_assoc($res)) {
        $map[$row['reference']] = $row['title'];
    }
    return $map;
}

function find_jobs($conn, $search = "") {
    if ($search === "") {
        return mysqli_fetch_all(db_query($conn, "SELECT * FROM jobs ORDER BY reference"), MYSQLI_ASSOC);
    }

    $text = html_entity_decode($search, ENT_QUOTES, "UTF-8");
    $words = array_slice(preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY), 0, 10);
    $conditions = [];
    $title_conditions = [];
    $params = [];
    $title_params = [];
    foreach ($words as $word) {
        $conditions[] = "(title LIKE ? OR description LIKE ?)";
        $title_conditions[] = "title LIKE ?";
        $params[] = like_pattern($word);
        $params[] = like_pattern($word);
        $title_params[] = like_pattern($word);
    }
    $params[] = $text;
    $params = array_merge($params, $title_params);

    $stmt = db_prepare($conn, "SELECT * FROM jobs WHERE (" . implode(" AND ", $conditions) . ") OR reference = ?
                               ORDER BY (" . implode(" AND ", $title_conditions) . ") DESC, reference");
    mysqli_stmt_bind_param($stmt, str_repeat("s", count($params)), ...$params);
    mysqli_stmt_execute($stmt);
    $jobs = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $jobs;
}

function job_facts($row) {
    $salary = (float) $row["salary"];
    return "<p class='coloured'>Salary: $" . number_format($salary, floor($salary) == $salary ? 0 : 2) . "</p>
        <p>Job reference: " . show($row["reference"]) . "</p>
        <p>" . show($row["description"]) . "</p>
        <p>Reports to: " . show($row["reporting_line"]) . "</p>";
}

function job_search_form($value = "") {
    echo "<form method='get' action='search.php' class='job-search'>" .
        "<label for='search'>Search by job title, keyword or reference</label>" .
        "<input type='text' name='search' id='search' maxlength='128' value='" . show($value) . "' required>" .
        "<input class='btn' type='submit' value='Search'>" .
        "</form>";
}

function job_card($row) {
    // only title, salary, job reference, description, and reporting line are shown on the card
    echo "<section class='outline job-card'>
        <h2>" . show($row["title"]) . "</h2>
        " . job_facts($row);

    // button is a form so the job reference can be sent via get
    echo "<form method='get' action='job_details.php'>" .
        "<input type='hidden' name='reference' value='" . show($row["reference"]) . "'>" .
        "<input class='btn' type='submit' value='Read More'>" .
        "</form>";

    echo "</section>";
}

function get_job_requirements($conn, $job_id) {
    $lists = ["responsibility" => [], "essential" => [], "preferred" => []];
    $stmt = db_prepare($conn, "SELECT list_type, item_text FROM job_requirements WHERE job_id = ? ORDER BY list_type, sort_order");
    mysqli_stmt_bind_param($stmt, "i", $job_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($item = mysqli_fetch_assoc($result)) {
        $lists[$item["list_type"]][] = $item["item_text"];
    }
    mysqli_stmt_close($stmt);
    return $lists;
}

function job_list($heading, $items) {
    echo "<div class='outline'>" .
        "<h2 class='coloured'>" . e($heading) . "</h2>";
    if (!$items) {
        echo "<p>None listed.</p></div>";
        return;
    }
    echo "<ul>";
    foreach ($items as $item) {
        echo "<li>" . show($item) . "</li>";
    }
    echo "</ul>" .
        "</div>";
}
?>