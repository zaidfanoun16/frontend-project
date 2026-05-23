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


$chk = $conn->prepare("SELECT id FROM registrations WHERE student_id = ? AND course_id = ?");
$chk->bind_param("ii", $student_id, $course_id);
$chk->execute();
$chk->store_result();

if ($chk->num_rows === 0) {
    $chk->close();
    echo json_encode(["success" => false, "message" => "You are not registered in this course."]);
    exit;
}
$chk->close();


$del = $conn->prepare("DELETE FROM registrations WHERE student_id = ? AND course_id = ?");
$del->bind_param("ii", $student_id, $course_id);
$del->execute();
$del->close();

echo json_encode([
    "success" => true,
    "message" => "Course dropped successfully."
]);
?>
