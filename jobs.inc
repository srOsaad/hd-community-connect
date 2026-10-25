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
?>