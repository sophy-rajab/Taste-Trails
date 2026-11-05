<?php
session_start();
include "db.php";

$message = "";
$passwordError = false;
$name = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";
    $confirm = $_POST["confirm"] ?? "";

    if ($name === "" || $email === "" || $password === "") {
        $message = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
        $passwordError = true;
    } elseif ($password !== $confirm) {
        $message = "Passwords do not match. Please type them again.";
        $passwordError = true;
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "An account with this email already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $insert->bind_param("sss", $name, $email, $hash);

            if ($insert->execute()) {
                $_SESSION["user_id"] = $conn->insert_id;
                $_SESSION["user_name"] = $name;
                $_SESSION["user_role"] = "user";
                header("Location: index.php");
                exit;
            } else {
                $message = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register · Taste Trails</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,650&family=Great+Vibes&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="auth-page">
    <aside class="auth-visual">
      <div class="auth-visual-inner">
        <a href="index.php" class="logo-word">Taste Trails</a>
        <p>Join Kenya’s food discovery trail — reviews, ratings, and recommendations in one place.</p>
      </div>
    </aside>

    <main class="auth-panel">
      <div class="auth-card">
        <h1>Create account</h1>
        <p class="lead">Register to start exploring restaurants your way.</p>

        <form method="POST" action="register.php">
          <div class="form-group">
            <label for="name">Full name</label>
            <input id="name" name="name" type="text" autocomplete="name" required placeholder="Your name">
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" autocomplete="email" required placeholder="you@example.com">
          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required placeholder="At least 6 characters">
          </div>
          <div class="form-group">
            <label for="confirm">Confirm password</label>
            <input id="confirm" name="confirm" type="password" autocomplete="new-password" required placeholder="Repeat password">
          </div>
          <p class="form-msg"><?php echo htmlspecialchars($message); ?></p>
          <button class="btn btn-block" type="submit">Register</button>
        </form>

        <p class="auth-alt">Already have an account? <a href="login.php">Login</a></p>
      </div>
    </main>
  </div>

  <script src="js/auth.js"></script>
  <script src="js/main.js"></script>
</body>
</html>
