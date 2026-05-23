<?php

require_once "../db.php";

header("Content-Type: application/json");


if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$student_id = $_SESSION["user_id"];
$course_id  = (int)($_POST["course_id"] ?? 0);

if ($course_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid course."]);
    exit;
}


$dup = $conn->prepare("SELECT id FROM registrations WHERE student_id = ? AND course_id = ?");
$dup->bind_param("ii", $student_id, $course_id);
$dup->execute();
$dup->store_result();
if ($dup->num_rows > 0) {
    $dup->close();
    echo json_encode(["success" => false, "message" => "You are already registered in this course."]);
    exit;
}
$dup->close();

$cap_stmt = $conn->prepare("
    SELECT c.capacity,
           (SELECT COUNT(*) FROM registrations r WHERE r.course_id = c.id) AS enrolled
    FROM courses c WHERE c.id = ?
");
$cap_stmt->bind_param("i", $course_id);
$cap_stmt->execute();
$cap_row = $cap_stmt->get_result()->fetch_assoc();
$cap_stmt->close();

if (!$cap_row) {
    echo json_encode(["success" => false, "message" => "Course not found."]);
    exit;
}

if ($cap_row["enrolled"] >= $cap_row["capacity"]) {
    echo json_encode(["success" => false, "message" => "This course is full."]);
    exit;
}


$pre_stmt = $conn->prepare("
    SELECT cp.prerequisite_course_id
    FROM course_prerequisites cp
    WHERE cp.course_id = ?
      AND cp.prerequisite_course_id NOT IN (
          SELECT course_id FROM completed_courses
          WHERE student_id = ? AND completion_status = 'completed'
      )
");
$pre_stmt->bind_param("ii", $course_id, $student_id);
$pre_stmt->execute();
$missing = $pre_stmt->get_result();
$pre_stmt->close();

if ($missing->num_rows > 0) {
    $missing_names = [];
    while ($m = $missing->fetch_assoc()) {
        $n_stmt = $conn->prepare("SELECT course_code, title FROM courses WHERE id = ?");
        $n_stmt->bind_param("i", $m["prerequisite_course_id"]);
        $n_stmt->execute();
        $n_row = $n_stmt->get_result()->fetch_assoc();
        $n_stmt->close();
        $missing_names[] = $n_row["course_code"] . " - " . $n_row["title"];
    }
    echo json_encode([
        "success" => false,
        "message" => "Missing prerequisites: " . implode(", ", $missing_names)
    ]);
    exit;
}

$ins = $conn->prepare("INSERT INTO registrations (student_id, course_id) VALUES (?, ?)");
$ins->bind_param("ii", $student_id, $course_id);
$ins->execute();
$ins->close();

$seats_stmt = $conn->prepare("
    SELECT c.capacity,
           (SELECT COUNT(*) FROM registrations r WHERE r.course_id = c.id) AS enrolled
    FROM courses c WHERE c.id = ?
");
$seats_stmt->bind_param("i", $course_id);
$seats_stmt->execute();
$seats_row = $seats_stmt->get_result()->fetch_assoc();
$seats_stmt->close();

$seats_left = $seats_row["capacity"] - $seats_row["enrolled"];

echo json_encode([
    "success"    => true,
    "message"    => "Registered successfully!",
    "seats_left" => $seats_left
]);
?>
