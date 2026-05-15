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
        $course_id = (int)$_POST["course_id"];
        $prereq_id = (int)$_POST["prerequisite_id"];

        if ($course_id === $prereq_id) {
            $message  = "A course cannot be its own prerequisite.";
            $msg_type = "warning";
        } else {
            $chk = $conn->prepare("SELECT id FROM course_prerequisites WHERE course_id = ? AND prerequisite_course_id = ?");
            $chk->bind_param("ii", $course_id, $prereq_id);
            $chk->execute();
            $chk->store_result();

            if ($chk->num_rows > 0) {
                $message  = "This relation already exists.";
                $msg_type = "warning";
            } else {
                $ins = $conn->prepare("INSERT INTO course_prerequisites (course_id, prerequisite_course_id) VALUES (?,?)");
                $ins->bind_param("ii", $course_id, $prereq_id);
                $ins->execute();
                $ins->close();
                $message  = "Prerequisite added.";
                $msg_type = "success";
            }
            $chk->close();
        }

    } elseif ($action === "delete") {
        $del_id = (int)$_POST["prereq_row_id"];
        $del = $conn->prepare("DELETE FROM course_prerequisites WHERE id = ?");
        $del->bind_param("i", $del_id);
        $del->execute();
        $del->close();
        $message  = "Prerequisite removed.";
        $msg_type = "success";
    }
}

$all_courses = $conn->query("SELECT id, course_code, title FROM courses ORDER BY course_code");
$courses_list = [];
while ($r = $all_courses->fetch_assoc()) {
    $courses_list[] = $r;
}

$relations = $conn->query("
    SELECT cp.id,
           c1.course_code AS course_code, c1.title AS course_title,
           c2.course_code AS prereq_code, c2.title AS prereq_title
    FROM course_prerequisites cp
    JOIN courses c1 ON c1.id = cp.course_id
    JOIN courses c2 ON c2.id = cp.prerequisite_course_id
    ORDER BY c1.course_code
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Prerequisites</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../Front_End/manage-prerequisites/manage-prerequisites.css">
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
  <h1>Manage Prerequisites </h1>

  <?php if ($message): ?>
    <div class="alert alert-<?= $msg_type ?>" style="padding:10px;margin:10px 0;border-radius:5px;">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <div class="form-box" style="margin:20px 0;">
    <form method="POST" action="manage-prerequisites.php">
      <input type="hidden" name="action" value="add">

      <select name="course_id" required style="padding:8px;border-radius:5px;border:1px solid #ccc;">
        <option value="">-- Select Course --</option>
        <?php foreach ($courses_list as $c): ?>
          <option value="<?= $c["id"] ?>"><?= htmlspecialchars($c["course_code"] . " - " . $c["title"]) ?></option>
        <?php endforeach; ?>
      </select>

      <select name="prerequisite_id" required style="padding:8px;border-radius:5px;border:1px solid #ccc;margin-left:10px;">
        <option value="">-- Requires (Prerequisite) --</option>
        <?php foreach ($courses_list as $c): ?>
          <option value="<?= $c["id"] ?>"><?= htmlspecialchars($c["course_code"] . " - " . $c["title"]) ?></option>
        <?php endforeach; ?>
      </select>

      <button type="submit" style="margin-left:10px;padding:8px 15px;background:#27ae60;color:white;border:none;border-radius:5px;cursor:pointer;">
        Add Relation
      </button>
    </form>
  </div>

  <table>
    <thead>
      <tr>
        <th>Course</th>
        <th>Requires</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($relations->num_rows === 0): ?>
        <tr><td colspan="3">No prerequisites defined.</td></tr>
      <?php else: ?>
        <?php while ($rel = $relations->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($rel["course_code"] . " - " . $rel["course_title"]) ?></td>
            <td><?= htmlspecialchars($rel["prereq_code"] . " - " . $rel["prereq_title"]) ?></td>
            <td>
              <form method="POST" action="manage-prerequisites.php" style="display:inline"
                    onsubmit="return confirm('Remove this prerequisite?')">
                <input type="hidden" name="action"        value="delete">
                <input type="hidden" name="prereq_row_id" value="<?= $rel["id"] ?>">
                <button type="submit" style="background:red;color:white;border:none;padding:5px 10px;border-radius:4px;cursor:pointer;">
                  Remove
                </button>
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
