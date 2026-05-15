<?php
require_once "db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION["user_id"];
$message    = "";
$msg_type   = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["drop_course_id"])) {
    $course_id = (int)$_POST["drop_course_id"];
    $drop_stmt = $conn->prepare("DELETE FROM registrations WHERE student_id = ? AND course_id = ?");
    $drop_stmt->bind_param("ii", $student_id, $course_id);
    $drop_stmt->execute();
    $drop_stmt->close();
    $message  = "Course dropped successfully.";
    $msg_type = "success";
}

$stmt = $conn->prepare("
    SELECT c.id, c.course_code, c.title, c.credits, r.registration_date
    FROM registrations r
    JOIN courses c ON c.id = r.course_id
    WHERE r.student_id = ?
    ORDER BY r.registration_date DESC
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$my_courses = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Courses</title>
  <link rel="stylesheet" href="../Front_End/my-courses/my-courses.css">
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

<div class="container">
  <h1>My Courses </h1>

  <?php if ($message): ?>
    <div class="alert alert-<?= $msg_type ?>" style="padding:10px;margin:10px 0;border-radius:5px;">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <table>
    <thead>
      <tr>
        <th>Code</th>
        <th>Title</th>
        <th>Credits</th>
        <th>Registration Date</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($my_courses->num_rows === 0): ?>
        <tr><td colspan="5">You have no registered courses.</td></tr>
      <?php else: ?>
        <?php while ($course = $my_courses->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($course["course_code"]) ?></td>
            <td><?= htmlspecialchars($course["title"]) ?></td>
            <td><?= $course["credits"] ?></td>
            <td><?= $course["registration_date"] ?></td>
            <td>
              <form method="POST" action="my-courses.php"
                    onsubmit="return confirm('Drop this course?')">
                <input type="hidden" name="drop_course_id" value="<?= $course["id"] ?>">
                <button type="submit">Drop</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php endif; ?>
    </tbody>
  </table>

</div>
</body>
</html>
