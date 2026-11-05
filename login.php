<?php
session_start();
include "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";

    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["user_name"] = $user["name"];
        $_SESSION["user_role"] = $user["role"];
        header("Location: index.php");
        exit;
    } else {
      $message = "PHP says: wrong email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login · Taste Trails</title>
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
        <p>Sign in to save favourites, leave reviews, and get recommendations that fit your taste.</p>
      </div>
    </aside>

    <main class="auth-panel">
      <div class="auth-card">
        <h1>Welcome back</h1>
        <p class="lead">Log in to your Taste Trails account.</p>

        <form method="POST" action="login.php">
          <div class="form-group">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" autocomplete="email" required placeholder="you@example.com">
          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required placeholder="••••••••">
          </div>
          <p class="form-msg"><?php echo htmlspecialchars($message); ?></p>
          <button class="btn btn-block" type="submit">Login</button>
        </form>

        <p class="auth-alt">New here? <a href="register.php">Create an account</a></p>
        <div class="demo-hint">
          Demo admin: <code>admin@tastetrails.co.ke</code> / <code>admin123</code><br>
          Demo user: <code>sophia@example.com</code> / <code>taste123</code>
        </div>
      </div>
    </main>
  </div>

  <script src="js/auth.js"></script>
  <script src="js/main.js"></script>
</body>
</html>
