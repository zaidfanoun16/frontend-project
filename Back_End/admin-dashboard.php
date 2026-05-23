<?php
require_once "db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit;
}

$admin_name          = $_SESSION["user_name"];
$total_courses       = $conn->query("SELECT COUNT(*) AS c FROM courses")->fetch_assoc()["c"];
$total_students      = $conn->query("SELECT COUNT(*) AS c FROM students")->fetch_assoc()["c"];
$total_registrations = $conn->query("SELECT COUNT(*) AS c FROM registrations")->fetch_assoc()["c"];
$full_courses        = $conn->query("
    SELECT COUNT(*) AS c FROM courses c2
    WHERE (SELECT COUNT(*) FROM registrations r WHERE r.course_id = c2.id) >= c2.capacity
")->fetch_assoc()["c"];


$available_courses = $total_courses - $full_courses;


$report = $conn->query("
    SELECT c.course_code, c.title, c.capacity,
           COUNT(r.id) AS registrations
    FROM courses c
    LEFT JOIN registrations r ON r.course_id = c.id
    GROUP BY c.id
    ORDER BY registrations DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../Front_End/admin-dashboard/admin-dashboard.css">
</head>
<body>

<div class="navbar">
  <h2>Admin Panel</h2>
  <div class="nav-links">
    <a href="admin-dashboard.php">Dashboard</a>
    <a href="manage-courses.php">Courses</a>
    <a href="manage-prerequisites.php">Prerequisites</a>
    <a href="registration-overview.php">Overview</a>
    <a href="logout.php">Logout</a>
  </div>
</div>

<div class="container mt-4">
  <h1>Admin Dashboard</h1>
  <p>Welcome, <?= htmlspecialchars($admin_name) ?></p>

  <div class="row g-4 mb-4">
    <div class="col-md-3">
      <div class="card shadow p-3 text-center">
        <h5>Total Courses</h5>
        <h3><?= $total_courses ?></h3>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow p-3 text-center">
        <h5>Total Students</h5>
        <h3><?= $total_students ?></h3>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow p-3 text-center">
        <h5>Registrations</h5>
        <h3><?= $total_registrations ?></h3>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow p-3 text-center" style="background:#e74c3c;color:white;">
        <h5>Full Courses</h5>
        <h3><?= $full_courses ?></h3>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card shadow p-3 text-center" style="background:#27ae60;color:white;">
        <h5>Available Courses</h5>
        <h3><?= $available_courses ?></h3>
      </div>
    </div>
  </div>

  <div class="dashboard-box">
    <button onclick="location.href='manage-courses.php'">Manage Courses</button>
    <button onclick="location.href='manage-prerequisites.php'">Manage Prerequisites</button>
    <button onclick="location.href='registration-overview.php'">Registration Overview</button>
  </div>

  <h4 class="mt-5 mb-3">Registrations Report </h4>
  <table class="table table-bordered table-hover table-striped align-middle">
    <thead class="table-dark">
      <tr>
        <th>Course Code</th>
        <th>Title</th>
        <th>Registrations</th>
        <th>Capacity</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $report->fetch_assoc()): ?>
        <?php
          $percent   = ($row["capacity"] > 0) ? ($row["registrations"] / $row["capacity"]) * 100 : 0;
          $bar_color = $percent >= 100 ? 'bg-danger' : ($percent >= 70 ? 'bg-warning' : 'bg-success');
        ?>
        <tr>
          <td><span class="badge bg-primary"><?= htmlspecialchars($row["course_code"]) ?></span></td>
          <td><?= htmlspecialchars($row["title"]) ?></td>
          <td><?= $row["registrations"] ?></td>
          <td><?= $row["capacity"] ?></td>
          <td>
            <?php if ($row["registrations"] >= $row["capacity"]): ?>
              <span class="badge bg-danger">Full</span>
            <?php elseif ($row["registrations"] == 0): ?>
              <span class="badge bg-secondary">No Registrations</span>
            <?php else: ?>
              <span class="badge bg-success">Available</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
</body>
</html>
