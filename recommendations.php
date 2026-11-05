<?php
session_start();
include "db.php";

function stars($rating) {
    $full = floor($rating);
    $half = ($rating - $full) >= 0.4 ? 1 : 0;
    return str_repeat("★", $full) . ($half ? "½" : "") . str_repeat("☆", 5 - $full - $half);
}

$cuisine  = $_GET["cuisine"] ?? "";
$location = $_GET["location"] ?? "";
$price    = $_GET["price"] ?? "";
$searched = ($cuisine !== "" || $location !== "" || $price !== "");

$sql = "SELECT r.*,
        COALESCE(ROUND(AVG(rv.rating), 1), 0) AS avg_rating,
        COUNT(rv.id) AS review_count,
        (r.cuisine = ?) * 3 + (r.location = ?) * 2 + (r.price_range = ?) * 2 AS match_score
        FROM restaurants r
        LEFT JOIN reviews rv ON rv.restaurant_id = r.id
        GROUP BY r.id";

if ($searched) {
    $sql .= " HAVING match_score > 0";
}

$sql .= " ORDER BY match_score DESC, avg_rating DESC, review_count DESC, r.name LIMIT 6";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $cuisine, $location, $price);
$stmt->execute();
$restaurants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$cuisines  = $conn->query("SELECT DISTINCT cuisine FROM restaurants ORDER BY cuisine")->fetch_all(MYSQLI_NUM);
$locations = $conn->query("SELECT DISTINCT location FROM restaurants ORDER BY location")->fetch_all(MYSQLI_NUM);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recommended for you · Taste Trails</title>
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
        <li><a href="recommendations.php" class="active">Recommended</a></li>
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
      <p class="eyebrow">Discover</p>
      <h1 data-resto-heading>Recommended for you</h1>
      <p>Search by name, cuisine, location, or price — then open a place for full details and reviews.</p>
    </div>
  </section>

  <section class="section" style="padding-top:2rem">
    <div class="container">
    <form class="filters" method="GET" action="recommendations.php">
  <select name="cuisine" aria-label="Cuisine">
    <option value="">Any cuisine</option>
    <?php foreach ($cuisines as $c): ?>
    <option value="<?php echo htmlspecialchars($c[0]); ?>" <?php if ($cuisine === $c[0]) echo "selected"; ?>><?php echo htmlspecialchars($c[0]); ?></option>
    <?php endforeach; ?>
  </select>

  <select name="location" aria-label="Location">
    <option value="">Any location</option>
    <?php foreach ($locations as $l): ?>
    <option value="<?php echo htmlspecialchars($l[0]); ?>" <?php if ($location === $l[0]) echo "selected"; ?>><?php echo htmlspecialchars($l[0]); ?></option>
    <?php endforeach; ?>
  </select>

  <select name="price" aria-label="Budget">
    <option value="">Any budget</option>
    <option value="budget" <?php if ($price === "budget") echo "selected"; ?>>Budget · under KSh 1,500</option>
    <option value="mid" <?php if ($price === "mid") echo "selected"; ?>>Moderate · KSh 700–3,500</option>
    <option value="upscale" <?php if ($price === "upscale") echo "selected"; ?>>Upscale · from KSh 2,500</option>
  </select>

  <button class="btn" type="submit">Recommend</button>
</form>

<div class="resto-grid">
  <?php if (count($restaurants) === 0): ?>
    <p>No matches found. Try changing one of your choices.</p>
  <?php endif; ?>

  <?php foreach ($restaurants as $r): ?>
  <?php
    $why = [];
    if ($cuisine !== "" && $r["cuisine"] === $cuisine) $why[] = "Cuisine";
    if ($location !== "" && $r["location"] === $location) $why[] = "Location";
    if ($price !== "" && $r["price_range"] === $price) $why[] = "Budget";
  ?>
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
      <?php if (count($why) > 0): ?>
      <p class="resto-meta">✅ Matches your: <?php echo implode(", ", $why); ?></p>
      <?php endif; ?>
      <a class="btn btn-block" href="restaurant.php?id=<?php echo $r["id"]; ?>">View Details</a>
    </div>
  </article>
  <?php endforeach; ?>
</div>
    </div>
  </section>

  <footer class="site-footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand">
          <span class="logo-word">Taste Trails</span>
          <p>Restaurant discovery built for Kenya.</p>
        </div>
        <div class="footer-col">
          <h4>Explore</h4>
          <a href="restaurants.php">Restaurants</a>
          <a href="reviews.php">Reviews</a>
          <a href="about.html">About</a>
        </div>
        <div class="footer-col">
          <h4>Account</h4>
          <a href="login.php">Login</a>
          <a href="register.php">Register</a>
        </div>
        <div class="footer-col">
          <h4>Contact</h4>
          <a href="contact.html">Get in touch</a>
        </div>
      </div>
      <div class="footer-bottom">
        <span>© 2026 Taste Trails</span>
      </div>
    </div>
  </footer>

  <script src="js/data.js"></script>
  <script src="js/auth.js"></script>
  <script src="js/main.js"></script>
</body>
</html>
