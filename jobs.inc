<?php
// Returns [ title => serial_number ]
function get_job_map($conn) {
    $map = [];
    $res = mysqli_query($conn, "SELECT title, serial_number FROM job_table");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $map[$row['title']] = (int)$row['serial_number'];
        }
    }
    return $map;
}
?>