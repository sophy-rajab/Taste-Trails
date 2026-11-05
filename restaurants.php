<?php
session_start();
include "db.php";

function stars($rating) {
    $full = floor($rating);
    $half = ($rating - $full) >= 0.4 ? 1 : 0;
    return str_repeat("★", $full) . ($half ? "½" : "") . str_repeat("☆", 5 - $full - $half);
}

$search   = trim($_GET["search"] ?? "");
$cuisine  = $_GET["cuisine"] ?? "";
$location = $_GET["location"] ?? "";
$price    = $_GET["price"] ?? "";

$sql = "SELECT r.*,
        COALESCE(ROUND(AVG(rv.rating), 1), 0) AS avg_rating,
        COUNT(rv.id) AS review_count
        FROM restaurants r
        LEFT JOIN reviews rv ON rv.restaurant_id = r.id
        WHERE 1=1";
$types = "";
$params = [];

if ($search !== "") {
    $sql .= " AND (r.name LIKE ? OR r.location LIKE ? OR r.cuisine LIKE ?)";
    $like = "%" . $search . "%";
    $types .= "sss";
    array_push($params, $like, $like, $like);
}
if ($cuisine !== "") {
    $sql .= " AND r.cuisine = ?";
    $types .= "s";
    $params[] = $cuisine;
}
if ($location !== "") {
    $sql .= " AND r.location = ?";
    $types .= "s";
    $params[] = $location;
}
if ($price !== "") {
    $sql .= " AND r.price_range = ?";
    $types .= "s";
    $params[] = $price;
}

$sql .= " GROUP BY r.id ORDER BY r.name";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
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
  <title>Restaurants · Taste Trails</title>
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
        <li><a href="restaurants.php" class="active">Restaurants</a></li>
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
      <h1 data-resto-heading>Restaurants</h1>
      <p>Search by name, cuisine, location, or price — then open a place for full details and reviews.</p>
    </div>
  </section>

  <section class="section" style="padding-top:2rem">
    <div class="container">
    <form class="filters" method="GET" action="restaurants.php">
      <input id="resto-search" type="search" name="search" placeholder="Search name, location, cuisine…" value="<?php echo htmlspecialchars($search); ?>">

      <select name="cuisine" onchange="this.form.submit()" aria-label="Filter by cuisine">
        <option value="">All cuisines</option>
        <?php foreach ($cuisines as $c): ?>
        <option value="<?php echo htmlspecialchars($c[0]); ?>" <?php if ($cuisine === $c[0]) echo "selected"; ?>><?php echo htmlspecialchars($c[0]); ?></option>
        <?php endforeach; ?>
      </select>

      <select name="location" onchange="this.form.submit()" aria-label="Filter by location">
        <option value="">All locations</option>
        <?php foreach ($locations as $l): ?>
        <option value="<?php echo htmlspecialchars($l[0]); ?>" <?php if ($location === $l[0]) echo "selected"; ?>><?php echo htmlspecialchars($l[0]); ?></option>
        <?php endforeach; ?>
      </select>

      <select name="price" onchange="this.form.submit()" aria-label="Filter by price">
        <option value="">All prices</option>
        <option value="budget" <?php if ($price === "budget") echo "selected"; ?>>Budget · under KSh 1,500</option>
        <option value="mid" <?php if ($price === "mid") echo "selected"; ?>>Moderate · KSh 700–3,500</option>
        <option value="upscale" <?php if ($price === "upscale") echo "selected"; ?>>Upscale · from KSh 2,500</option>
      </select>

      <button class="btn" type="submit">Search</button>
    </form>

    <div class="resto-grid">
      <?php if (count($restaurants) === 0): ?>
       <p>No restaurants found. Try different filters.</p>
     <?php endif; ?>

      <?php foreach ($restaurants as $r): ?>
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
