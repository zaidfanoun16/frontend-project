<?php
require_once "db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit;
}

$message  = "";
$msg_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"];

    if ($action === "add") {
        $code     = trim($_POST["course_code"]);
        $title    = trim($_POST["title"]);
        $desc     = trim($_POST["description"]);
        $credits  = (int)$_POST["credits"];
        $capacity = (int)$_POST["capacity"];

        $chk = $conn->prepare("SELECT id FROM courses WHERE course_code = ?");
        $chk->bind_param("s", $code);
        $chk->execute();
        $chk->store_result();

        if ($chk->num_rows > 0) {
            $message  = "Course code already exists.";
            $msg_type = "warning";
        } else {
            $ins = $conn->prepare("INSERT INTO courses (course_code, title, description, credits, capacity) VALUES (?,?,?,?,?)");
            $ins->bind_param("sssii", $code, $title, $desc, $credits, $capacity);
            $ins->execute();
            $ins->close();
            $message  = "Course added successfully.";
            $msg_type = "success";
        }
        $chk->close();

    } elseif ($action === "delete") {
        $del_id = (int)$_POST["course_id"];
        $del = $conn->prepare("DELETE FROM courses WHERE id = ?");
        $del->bind_param("i", $del_id);
        $del->execute();
        $del->close();
        $message  = "Course deleted.";
        $msg_type = "success";

    } elseif ($action === "edit") {
        $edit_id  = (int)$_POST["course_id"];
        $title    = trim($_POST["title"]);
        $desc     = trim($_POST["description"]);
        $credits  = (int)$_POST["credits"];
        $capacity = (int)$_POST["capacity"];

        $upd = $conn->prepare("UPDATE courses SET title=?, description=?, credits=?, capacity=? WHERE id=?");
        $upd->bind_param("ssiii", $title, $desc, $credits, $capacity, $edit_id);
        $upd->execute();
        $upd->close();
        $message  = "Course updated.";
        $msg_type = "success";
    }
}

$courses = $conn->query("
    SELECT c.id, c.course_code, c.title, c.credits, c.capacity,
           (SELECT COUNT(*) FROM registrations r WHERE r.course_id = c.id) AS enrolled
    FROM courses c ORDER BY c.course_code
");

$edit_course = null;
if (isset($_GET["edit_id"])) {
    $e_stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
    $e_stmt->bind_param("i", (int)$_GET["edit_id"]);
    $e_stmt->execute();
    $edit_course = $e_stmt->get_result()->fetch_assoc();
    $e_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Courses</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../Front_End/manage-courses/manage-courses.css">
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
  <h1>Manage Courses </h1>

  <?php if ($message): ?>
    <div class="alert alert-<?= $msg_type ?>" style="padding:10px;margin:10px 0;border-radius:5px;">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <div class="form-box">
    <?php if ($edit_course): ?>
      <form method="POST" action="manage-courses.php">
        <input type="hidden" name="action"    value="edit">
        <input type="hidden" name="course_id" value="<?= $edit_course["id"] ?>">
        <input type="text"   name="title"       placeholder="Title"       value="<?= htmlspecialchars($edit_course["title"]) ?>"       required>
        <input type="text"   name="description" placeholder="Description" value="<?= htmlspecialchars($edit_course["description"]) ?>">
        <input type="number" name="credits"     placeholder="Credits"     value="<?= $edit_course["credits"] ?>"  min="1" required>
        <input type="number" name="capacity"    placeholder="Capacity"    value="<?= $edit_course["capacity"] ?>" min="1" required>
        <button type="submit">Update Course</button>
        <a href="manage-courses.php" style="margin-left:10px;">Cancel</a>
      </form>
    <?php else: ?>
      <form method="POST" action="manage-courses.php">
        <input type="hidden" name="action" value="add">
        <input type="text"   name="course_code"  placeholder="Course Code (e.g. CS106)" required>
        <input type="text"   name="title"        placeholder="Title"                    required>
        <input type="text"   name="description"  placeholder="Description">
        <input type="number" name="credits"      placeholder="Credits"   min="1" value="3"  required>
        <input type="number" name="capacity"     placeholder="Capacity"  min="1" value="30" required>
        <button type="submit">Add Course</button>
      </form>
    <?php endif; ?>
  </div>

 <table class="table table-bordered table-hover table-striped align-middle mt-3">
  <thead class="table-dark">
    <tr>
      <th>Code</th>
      <th>Title</th>
      <th>Credits</th>
      <th>Enrolled / Capacity</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php while ($c = $courses->fetch_assoc()): ?>
      <?php
        $percent = ($c["capacity"] > 0) ? ($c["enrolled"] / $c["capacity"]) * 100 : 0;
        $bar_color = $percent >= 100 ? 'bg-danger' : ($percent >= 70 ? 'bg-warning' : 'bg-success');
      ?>
      <tr>
        <td><span class="badge bg-primary"><?= htmlspecialchars($c["course_code"]) ?></span></td>
        <td><?= htmlspecialchars($c["title"]) ?></td>
        <td class="text-center"><?= $c["credits"] ?></td>
        <td><?= $c["enrolled"] ?> / <?= $c["capacity"] ?></td>
        <td>
          <a href="manage-courses.php?edit_id=<?= $c["id"] ?>" class="btn btn-warning btn-sm me-1">
             Edit
          </a>
          <form method="POST" action="manage-courses.php" style="display:inline"
                onsubmit="return confirm('Delete this course?')">
            <input type="hidden" name="action"    value="delete">
            <input type="hidden" name="course_id" value="<?= $c["id"] ?>">
            <button type="submit" class="btn btn-danger btn-sm"> Delete</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>

</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
</body>
</html>
