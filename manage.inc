<?php
// The eoi table only exists after the first application is sent, so check for it first
function eoi_table_exists($conn) {
    $check = mysqli_query($conn, "SHOW TABLES LIKE 'eoi'");
    return $check && mysqli_num_rows($check) > 0;
}

function update_eoi_status($conn, $eoi_number, $new_status) {
    $stmt = mysqli_prepare($conn, "SELECT Status FROM eoi WHERE EOInumber = ?");
    mysqli_stmt_bind_param($stmt, "i", $eoi_number);
    mysqli_stmt_execute($stmt);
    $current = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$current) {
        return ["error" => "EOI $eoi_number doesn't exist."];
    }
    if ($current["Status"] === $new_status) {
        return ["success" => "EOI $eoi_number already had the status $new_status."];
    }

    $stmt = mysqli_prepare($conn, "UPDATE eoi SET Status = ? WHERE EOInumber = ?");
    mysqli_stmt_bind_param($stmt, "si", $new_status, $eoi_number);
    $ok = mysqli_stmt_execute($stmt);
    if (!$ok) {
        error_log("Status update failed: " . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);

    return $ok ? ["success" => "EOI $eoi_number is now $new_status."]
               : ["error" => "The status could not be changed. Please try again."];
}

function count_job_eois($conn, $job_ref) {
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM eoi WHERE JobReference = ?");
    mysqli_stmt_bind_param($stmt, "s", $job_ref);
    mysqli_stmt_execute($stmt);
    $total = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))["total"];
    mysqli_stmt_close($stmt);
    return $total;
}

function delete_job_eois($conn, $job_ref) {
    $stmt = mysqli_prepare($conn, "DELETE FROM eoi WHERE JobReference = ?");
    mysqli_stmt_bind_param($stmt, "s", $job_ref);
    $deleted = null;
    if (mysqli_stmt_execute($stmt)) {
        $deleted = mysqli_stmt_affected_rows($stmt);
    } else {
        error_log("EOI delete failed: " . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);
    return $deleted;
}

function count_by_status($conn, $statuses) {
    $status_counts = array_fill_keys($statuses, 0);
    $result = mysqli_query($conn, "SELECT Status, COUNT(*) AS total FROM eoi GROUP BY Status");
    if ($result) {
        while ($count_row = mysqli_fetch_assoc($result)) {
            $status_counts[$count_row["Status"]] = (int) $count_row["total"];
        }
    }
    return $status_counts;
}

function eoi_conditions($filters) {
    $conditions = [];
    $types = "";
    $params = [];

    if ($filters["status"] !== "") {
        $conditions[] = "Status = ?";
        $types .= "s";
        $params[] = $filters["status"];
    }
    if ($filters["application_id"] !== "") {
        $conditions[] = "ApplicationID = ?";
        $types .= "s";
        $params[] = $filters["application_id"];
    }
    if ($filters["job_ref"] !== "") {
        $conditions[] = "JobReference = ?";
        $types .= "s";
        $params[] = $filters["job_ref"];
    }
    if ($filters["first_name"] !== "") {
        $conditions[] = "FirstName LIKE ?";      // LIKE with % on both sides means "contains"
        $types .= "s";
        $params[] = like_pattern($filters["first_name"]);
    }
    if ($filters["last_name"] !== "") {
        $conditions[] = "LastName LIKE ?";
        $types .= "s";
        $params[] = like_pattern($filters["last_name"]);
    }

    $where = $conditions ? " WHERE " . implode(" AND ", $conditions) : "";
    return [$where, $types, $params];
}

function count_eois($conn, $filters) {
    [$where, $types, $params] = eoi_conditions($filters);

    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM eoi" . $where);
    if (!$stmt) {
        error_log("EOI count query failed: " . mysqli_error($conn));
        return null;
    }
    if ($params) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $total = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))["total"];
    mysqli_stmt_close($stmt);
    return $total;
}

// Get the applications in the chosen order. Each filled search box adds a condition,
// and the values go in as ? placeholders so they can't change the SQL
function find_eois($conn, $filters, $sort, $order, $limit = null, $offset = 0) {
    [$where, $types, $params] = eoi_conditions($filters);

    $order_by = $sort === "State" ? "CAST(State AS CHAR)" : $sort;
    $sql = "SELECT * FROM eoi" . $where . " ORDER BY " . $order_by . " " . strtoupper($order) . ", EOInumber";

    if ($limit !== null) {
        $sql .= " LIMIT ? OFFSET ?";
        $types .= "ii";
        $params[] = $limit;
        $params[] = $offset;
    }

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        error_log("EOI list query failed: " . mysqli_error($conn));
        return null;
    }
    if ($params) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $eois = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $eois;
}
?>
