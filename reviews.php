<?php
session_start();
include "db.php";

function stars($rating) {
    $full = floor($rating);
    $half = ($rating - $full) >= 0.4 ? 1 : 0;
    return str_repeat("★", $full) . ($half ? "½" : "") . str_repeat("☆", 5 - $full - $half);
}

$reviews = $conn->query("SELECT rv.*, u.name AS user_name, r.name AS restaurant_name
        FROM reviews rv
        JOIN users u ON u.id = rv.user_id
        JOIN restaurants r ON r.id = rv.restaurant_id
        ORDER BY rv.created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reviews · Taste Trails</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,650&family=Great+Vibes&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="navbar scrolled">
    <div class="container nav-inner">
      <a class="logo" href="index.php" aria-label="Taste Trails home">
        <img src="images/logo.png" alt="">
        <span class="logo-word">Taste Trails</span>
      </a>
      <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="restaurants.php">Restaurants</a></li>
        <li><a href="reviews.php" class="active">Reviews</a></li>
        <li><a href="about.html">About</a></li>
        <li><a href="contact.html">Contact</a></li>
      </ul>
      <div class="auth-buttons">
        <?php if (isset($_SESSION["user_id"])): ?>
        <span class="user-chip"><?php echo htmlspecialchars(explode(" ", $_SESSION["user_name"])[0]); ?></span>
        <?php if ($_SESSION["user_role"] === "admin"): ?>
        <a href="admin/index.php" class="nav-cta ghost">Admin</a>
        <?php endif; ?>
        <a href="logout.php" class="nav-cta outline">Logout</a>
        <?php else: ?>
        <a href="login.php" class="nav-cta outline">Login</a>
        <a href="register.php" class="nav-cta solid">Register</a>
        <?php endif; ?>
      </div>
      <button class="menu-toggle" type="button" aria-label="Open menu" aria-expanded="false">☰</button>
    </div>
  </header>

  <section class="page-hero">
    <div class="container">
      <p class="eyebrow">Community voice</p>
      <h1>Latest Reviews</h1>
      <p>Real diner feedback on food quality, service, price, and atmosphere.</p>
    </div>
  </section>

  <section class="section" style="padding-top:2rem">
  <div class="container">

  <?php if (count($reviews) === 0): ?>
    <p class="empty-state">No reviews yet. Be the first to review a restaurant!</p>
  <?php endif; ?>

  <?php foreach ($reviews as $rev): ?>
  <article class="review-card wide">
    <div class="review-head">
      <div class="avatar"><?php echo htmlspecialchars(mb_substr($rev["user_name"], 0, 1)); ?></div>
        <div>
          <strong><?php echo htmlspecialchars($rev["user_name"]); ?></strong>
          <p><a href="restaurant.php?id=<?php echo $rev["restaurant_id"]; ?>"><?php echo htmlspecialchars($rev["restaurant_name"]); ?></a> · <?php echo date("M j, Y", strtotime($rev["created_at"])); ?></p>
        </div>
      </div>
      <p class="review-stars"><?php echo stars($rev["rating"]); ?></p>
      <p class="review-text">“<?php echo htmlspecialchars($rev["comment"]); ?>”</p>
    </article>
    <?php endforeach; ?>

    </div>
  </section>

  <footer class="site-footer">
    <div class="container">
      <div class="footer-bottom" style="border:0;padding-top:0">
        <span>© 2026 Taste Trails</span>
        <a href="register.php">Join to leave a review</a>
      </div>
    </div>
  </footer>

  <script src="js/data.js"></script>
  <script src="js/auth.js"></script>
  <script src="js/main.js"></script>
</body>
</html>
