<?php
session_start();
include "db.php";

function stars($rating) {
    $full = floor($rating);
    $half = ($rating - $full) >= 0.4 ? 1 : 0;
    return str_repeat("★", $full) . ($half ? "½" : "") . str_repeat("☆", 5 - $full - $half);
}

$favorites = [];
if (isset($_SESSION["user_id"])) {
    $stmt = $conn->prepare("SELECT r.*,
            COALESCE(ROUND(AVG(rv.rating), 1), 0) AS avg_rating,
            COUNT(rv.id) AS review_count
            FROM favorites f
            JOIN restaurants r ON r.id = f.restaurant_id
            LEFT JOIN reviews rv ON rv.restaurant_id = r.id
            WHERE f.user_id = ?
            GROUP BY r.id, f.id
            ORDER BY f.id DESC");
    $stmt->bind_param("i", $_SESSION["user_id"]);
    $stmt->execute();
    $favorites = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Favourites · Taste Trails</title>
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
        <li><a href="favorites.php" class="active">Favourites</a></li>
        <li><a href="reviews.php">Reviews</a></li>
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
      <p class="eyebrow">Your list</p>
      <h1>Favourited restaurants</h1>
      <p>Places you’ve saved for later — open details, leave a review, or remove them anytime.</p>
    </div>
  </section>

  <section class="section" style="padding-top:2rem">
    <div class="container">
      <?php if (!isset($_SESSION["user_id"])): ?>
        <div class="empty-state">
          <p>Log in to see your favourited restaurants.</p>
          <p style="margin-top:1rem">
            <a class="btn" href="login.php">Login</a>
            <a class="btn ghost" href="register.php" style="margin-left:.5rem">Register</a>
          </p>
        </div>

      <?php elseif (count($favorites) === 0): ?> 
        <div class="empty-state">
          <p>No favourites yet. Save restaurants from their detail page.</p>
          <p style="margin-top:1rem"><a class="btn" href="restaurants.php">Browse restaurants</a></p>
        </div>

      <?php else: ?>
        <div class="resto-grid">
          <?php foreach ($favorites as $r): ?>
          <article class="resto-card">
            <a class="resto-media" href="restaurant.php?id=<?php echo $r["id"]; ?>">
              <img src="<?php echo htmlspecialchars($r["image"]); ?>" alt="<?php echo htmlspecialchars($r["name"]); ?>" loading="lazy">
              <span class="price-badge">from KSh <?php echo number_format($r["price_min"]); ?></span>
            </a>
            <div class="resto-body">
              <div class="resto-top">
                <h3><a href="restaurant.php?id=<?php echo $r["id"]; ?>"><?php echo htmlspecialchars($r["name"]); ?></a></h3>
                <p class="resto-rating">
                  <?php if ($r["review_count"] > 0): ?>
                    <span><?php echo stars($r["avg_rating"]); ?></span> <?php echo $r["avg_rating"]; ?>
                  <?php else: ?>
                    <span>No reviews yet</span>
                  <?php endif; ?>
                </p>
              </div>
              <p class="resto-meta">📍 <?php echo htmlspecialchars($r["location"]); ?></p>
              <p class="resto-meta">🍴 <?php echo htmlspecialchars($r["cuisine"]); ?></p>
              <p class="resto-meta">💰 KSh <?php echo number_format($r["price_min"]); ?> – KSh <?php echo number_format($r["price_max"]); ?></p>
              <a class="btn btn-block" href="restaurant.php?id=<?php echo $r["id"]; ?>">View Details</a>
              <form method="POST" action="favorite.php">
                <input type="hidden" name="restaurant_id" value="<?php echo $r["id"]; ?>">
                <input type="hidden" name="back" value="favorites">
                <button class="btn btn-block ghost" type="submit">Remove from favourites</button>
              </form>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <footer class="site-footer">
    <div class="container">
      <div class="footer-bottom" style="border:0;padding-top:0">
        <span>© 2026 Taste Trails</span>
        <a href="restaurants.php">Browse more restaurants</a>
      </div>
    </div>
  </footer>

  <script src="js/data.js"></script>
  <script src="js/auth.js"></script>
  <script src="js/main.js"></script>
</body>
</html>
