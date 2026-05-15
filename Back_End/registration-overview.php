<?php
require_once "db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit;
}

$registrations = $conn->query("
    SELECT s.student_number, s.name AS student_name, s.email,
           c.course_code, c.title AS course_title,
           r.registration_date
    FROM registrations r
    JOIN students s ON s.id = r.student_id
    JOIN courses  c ON c.id = r.course_id
    ORDER BY c.course_code, s.name
");

$summary = $conn->query("
    SELECT c.course_code, c.title, c.capacity,
           COUNT(r.id) AS enrolled
    FROM courses c
    LEFT JOIN registrations r ON r.course_id = c.id
    GROUP BY c.id
    ORDER BY c.course_code
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Registration Overview</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../Front_End/registration-overview/registration-overview.css">
</head>
<body>

<div class="navbar">
  <h2>Admin Panel </h2>
  <div class="nav-links">
    <a href="admin-dashboard.php">Dashboard</a>
    <a href="manage-courses.php">Courses</a>
    <a href="manage-prerequisites.php">Prerequisites</a>
    <a href="registration-overview.php">Overview</a>
    <a href="logout.php">Logout</a>
  </div>
</div>

<div class="container" style="width:85%;margin:auto;padding-top:30px;">
  <h1>Registration Overview </h1>

  <h4 class="mt-4">Course Summary</h4>
  <table>
    <thead>
      <tr>
        <th>Code</th>
        <th>Title</th>
        <th>Enrolled</th>
        <th>Capacity</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $summary->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row["course_code"]) ?></td>
          <td><?= htmlspecialchars($row["title"]) ?></td>
          <td><?= $row["enrolled"] ?></td>
          <td><?= $row["capacity"] ?></td>
          <td>
            <?php if ($row["enrolled"] >= $row["capacity"]): ?>
              <span style="color:red;font-weight:bold;">Full</span>
            <?php else: ?>
              <span style="color:green;font-weight:bold;">Available</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <h4 class="mt-5">All Registrations</h4>
  <table>
    <thead>
      <tr>
        <th>Student No.</th>
        <th>Student Name</th>
        <th>Email</th>
        <th>Course Code</th>
        <th>Course Title</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($registrations->num_rows === 0): ?>
        <tr><td colspan="6">No registrations yet.</td></tr>
      <?php else: ?>
        <?php while ($row = $registrations->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($row["student_number"]) ?></td>
            <td><?= htmlspecialchars($row["student_name"]) ?></td>
            <td><?= htmlspecialchars($row["email"]) ?></td>
            <td><?= htmlspecialchars($row["course_code"]) ?></td>
            <td><?= htmlspecialchars($row["course_title"]) ?></td>
            <td><?= $row["registration_date"] ?></td>
          </tr>
        <?php endwhile; ?>
      <?php endif; ?>
    </tbody>
  </table>

</div>
</body>
</html>
