<?php
require_once "db.php";
 
$error = "";
 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST["email"]);
    $password = trim($_POST["password"]);
 
  
    $stmt = $conn->prepare("SELECT id, name, password FROM students WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
 
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $name, $db_password);
        $stmt->fetch();
        if (password_verify($password, $db_password)) {
            $_SESSION["user_id"]   = $id;
            $_SESSION["user_name"] = $name;
            $_SESSION["role"]      = "student";
            header("Location: student-dashboard.php");
            exit;
        } else {
            $error = "Wrong password or email.";
        }
    } else {
        $stmt->close();
  
        $stmt = $conn->prepare("SELECT id, name, password FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
 
        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $name, $db_password);
            $stmt->fetch();
            if (password_verify($password, $db_password)) {
                $_SESSION["user_id"]   = $id;
                $_SESSION["user_name"] = $name;
                $_SESSION["role"]      = "admin";
                header("Location: admin-dashboard.php");
                exit;
            } else {
                $error = "Wrong password or email.";
            }
        } else {
            $error = "Wrong password or email.";
        }
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Course System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../Front_End/login/login.css">
</head>
<body class="bg-light">
 
<div class="container d-flex justify-content-center align-items-center vh-100">
  <div class="card shadow p-4" style="width: 350px;">
 
    <h3 class="text-center mb-4">Login</h3>
 
    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
 
    <form method="POST" action="login.php">
      <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" placeholder="Enter email" required>
      </div>
      <div class="mb-3">
        <label>Password</label>
        <input type="password" name="password" class="form-control" placeholder="Enter password" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
 
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
</body>
</html>
 
