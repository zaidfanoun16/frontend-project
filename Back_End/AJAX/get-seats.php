<?php
require_once "../db.php";
header("Content-Type: application/json");

$result = $conn->query("
    SELECT c.id, c.capacity,
           (SELECT COUNT(*) FROM registrations r WHERE r.course_id = c.id) AS enrolled
    FROM courses c
");

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[$row["id"]] = [
        "capacity" => (int)$row["capacity"],
        "enrolled" => (int)$row["enrolled"]
    ];
}

echo json_encode($data);
?>