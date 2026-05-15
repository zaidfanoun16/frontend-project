<?php
require_once "db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit;
}

$student_id   = $_SESSION["user_id"];
$student_name = $_SESSION["user_name"];

$total_result  = $conn->query("SELECT COUNT(*) AS total FROM courses");
$total_courses = $total_result->fetch_assoc()["total"];

$my_stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM registrations WHERE student_id = ?");
$my_stmt->bind_param("i", $student_id);
$my_stmt->execute();
$my_courses_count = $my_stmt->get_result()->fetch_assoc()["cnt"];
$my_stmt->close();

$available_courses = $total_courses - $my_courses_count;

$activity_stmt = $conn->prepare("
    SELECT c.title, r.registration_date
    FROM registrations r
    JOIN courses c ON c.id = r.course_id
    WHERE r.student_id = ?
    ORDER BY r.registration_date DESC
    LIMIT 5
");
$activity_stmt->bind_param("i", $student_id);
$activity_stmt->execute();
$activities = $activity_stmt->get_result();
$activity_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../Front_End/student-dashboard/student-dashboard.css">
</head>
<body>

<div class="navbar">
  <h2>Student Panel </h2>
  <div class="nav-links">
    <a href="student-dashboard.php">Dashboard</a>
    <a href="courses.php">Courses</a>
    <a href="my-courses.php">My Courses</a>
    <a href="logout.php">Logout</a>
  </div>
</div>

<div class="container mt-5">

  <div class="mb-4">
    <h2>Welcome back, <?= htmlspecialchars($student_name) ?> </h2>
    <p>Manage your courses below.</p>
  </div>

  <div class="row g-4 mb-5">
    <div class="col-md-4">
      <div class="card card-box shadow p-3 text-center">
        <h5>Total Courses</h5>
        <h3><?= $total_courses ?></h3>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card card-box shadow p-3 text-center">
        <h5>My Courses</h5>
        <h3><?= $my_courses_count ?></h3>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card card-box shadow p-3 text-center">
        <h5>Available Courses</h5>
        <h3><?= $available_courses ?></h3>
      </div>
    </div>
  </div>

  <div class="mb-5">
    <h4>Quick Actions</h4>
    <a href="courses.php"    class="btn btn-main me-2">Browse Courses</a>
    <a href="my-courses.php" class="btn btn-outline-primary">My Courses</a>
  </div>

  <div>
    <h4>Recent Activity</h4>
    <ul class="list-group">
      <?php if ($activities->num_rows === 0): ?>
        <li class="list-group-item">No registrations yet.</li>
      <?php else: ?>
        <?php while ($row = $activities->fetch_assoc()): ?>
          <li class="list-group-item">
            ✅ Registered in <strong><?= htmlspecialchars($row["title"]) ?></strong>
            <span class="text-muted small ms-2"><?= $row["registration_date"] ?></span>
          </li>
        <?php endwhile; ?>
      <?php endif; ?>
    </ul>
  </div>

</div>
</body>
</html>
