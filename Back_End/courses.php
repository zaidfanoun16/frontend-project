<?php
require_once "db.php";
 
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "student") {
    header("Location: login.php");
    exit;
}
 
$student_id = $_SESSION["user_id"];
$message    = "";
$msg_type   = "";
 
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["register_course_id"])) {
    $course_id = (int)$_POST["register_course_id"];
 
    $dup_stmt = $conn->prepare("SELECT id FROM registrations WHERE student_id = ? AND course_id = ?");
    $dup_stmt->bind_param("ii", $student_id, $course_id);
    $dup_stmt->execute();
    $dup_stmt->store_result();
 
    if ($dup_stmt->num_rows > 0) {
        $message  = "You are already registered in this course.";
        $msg_type = "warning";
    } else {
        $cap_stmt = $conn->prepare("
            SELECT c.capacity,
                   (SELECT COUNT(*) FROM registrations r WHERE r.course_id = c.id) AS enrolled
            FROM courses c WHERE c.id = ?
        ");
        $cap_stmt->bind_param("i", $course_id);
        $cap_stmt->execute();
        $cap_row = $cap_stmt->get_result()->fetch_assoc();
        $cap_stmt->close();
 
        if ($cap_row["enrolled"] >= $cap_row["capacity"]) {
            $message  = "This course is full.";
            $msg_type = "danger";
        } else {
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
                $message  = "Missing prerequisites: " . implode(", ", $missing_names);
                $msg_type = "danger";
            } else {
                $ins_stmt = $conn->prepare("INSERT INTO registrations (student_id, course_id) VALUES (?, ?)");
                $ins_stmt->bind_param("ii", $student_id, $course_id);
                $ins_stmt->execute();
                $ins_stmt->close();
                $message  = "Registered successfully!";
                $msg_type = "success";
            }
        }
    }
    $dup_stmt->close();
}
 
$search = isset($_GET["search"]) ? trim($_GET["search"]) : "";
$like   = "%" . $search . "%";
 
$list_stmt = $conn->prepare("
    SELECT c.id, c.course_code, c.title, c.credits, c.capacity,
           (SELECT COUNT(*) FROM registrations r WHERE r.course_id = c.id) AS enrolled,
           (SELECT COUNT(*) FROM registrations r2 WHERE r2.course_id = c.id AND r2.student_id = ?) AS is_registered
    FROM courses c
    WHERE c.course_code LIKE ? OR c.title LIKE ?
    ORDER BY c.course_code
");
$list_stmt->bind_param("iss", $student_id, $like, $like);
$list_stmt->execute();
$courses = $list_stmt->get_result();
$list_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Courses</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../Front_End/courses/courses.css">
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
  <h1 class="mt-3 mb-3">Available Courses </h1>
 
  <div id="alertBox">
    <?php if ($message): ?>
      <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>
  </div>
 
  <form method="GET" action="courses.php">
    <input type="text" name="search" id="search"
           placeholder="Search by code or title..."
           value="<?= htmlspecialchars($search) ?>">
  </form>
 
  <table>
    <thead>
      <tr>
        <th>Code</th>
        <th>Title</th>
        <th>Credits</th>
        <th>Seats Left</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($course = $courses->fetch_assoc()): ?>
        <?php
          $seats_left = $course["capacity"] - $course["enrolled"];
          $percent    = ($course["capacity"] > 0) ? ($course["enrolled"] / $course["capacity"]) * 100 : 0;
          $bar_color  = $percent >= 100 ? 'bg-danger' : ($percent >= 70 ? 'bg-warning' : 'bg-success');
        ?>
        <tr id="course-row-<?= $course["id"] ?>">
          <td><span class="badge bg-primary"><?= htmlspecialchars($course["course_code"]) ?></span></td>
          <td><?= htmlspecialchars($course["title"]) ?></td>
          <td><?= $course["credits"] ?></td>
 
          <td id="seats-<?= $course["id"] ?>">
            <small><?= $seats_left ?> / <?= $course["capacity"] ?></small>
          </td>
 
          <td id="action-<?= $course["id"] ?>">
            <?php if ($course["is_registered"]): ?>
              <span class="badge bg-success">✓ Registered</span>
            <?php elseif ($seats_left <= 0): ?>
              <span class="badge bg-danger">Full</span>
            <?php else: ?>
              <button class="register-btn"
                      onclick="registerCourse(<?= $course["id"] ?>, <?= $course["capacity"] ?>)">
                Register
              </button>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
 
</div>
 
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script>
 
function registerCourse(courseId, capacity) {
  $.ajax({
    url: "AJAX/register.php",
    type: "POST",
    data: { course_id: courseId },
    dataType: "json",
    success: function(response) {
      let alertClass = response.success ? "alert-success" : "alert-danger";
      $("#alertBox").html(
        '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
        response.message +
        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
        '</div>'
      );
      if (response.success) {
        $("#action-" + courseId).html('<span class="badge bg-success">✓ Registered</span>');
        let seatsLeft = response.seats_left;
        let percent   = Math.round(((capacity - seatsLeft) / capacity) * 100);
        let barColor  = percent >= 100 ? "bg-danger" : (percent >= 70 ? "bg-warning" : "bg-success");
        $("#seats-" + courseId).html(
          '<small>' + seatsLeft + ' / ' + capacity + '</small>'
        );
      }
      setTimeout(function() {
        $("#alertBox").fadeOut(500, function(){ $(this).html("").show(); });
      }, 4000);
    },
    error: function() {
      $("#alertBox").html('<div class="alert alert-danger">Server error. Please try again.</div>');
    }
  });
}
 

setInterval(function() {
  $.ajax({
    url: "AJAX/get-seats.php",
    type: "GET",
    dataType: "json",
    success: function(data) {
      $.each(data, function(courseId, info) {
        let seatsLeft = info.capacity - info.enrolled;
        let percent   = info.capacity > 0 ? Math.round((info.enrolled / info.capacity) * 100) : 0;
        let barColor  = percent >= 100 ? "bg-danger" : (percent >= 70 ? "bg-warning" : "bg-success");
 
        $("#seats-" + courseId).html(
          '<small>' + seatsLeft + ' / ' + info.capacity + '</small>'
        );
 
        if (seatsLeft <= 0) {
          if ($("#action-" + courseId + " button").length) {
            $("#action-" + courseId).html('<span class="badge bg-danger">Full</span>');
          }
        }
      });
    }
  });
}, 5000);
 
</script>
 
</body>
</html>
