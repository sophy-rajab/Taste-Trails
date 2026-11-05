<?php
session_start();
include "db.php";

function stars($rating) {
    $full = floor($rating);
    $half = ($rating - $full) >= 0.4 ? 1 : 0;
    return str_repeat("★", $full) . ($half ? "½" : "") . str_repeat("☆", 5 - $full - $half);
}

$id = (int) ($_GET["id"] ?? 0);

$stmt = $conn->prepare("SELECT r.*,
        COALESCE(ROUND(AVG(rv.rating), 1), 0) AS avg_rating,
        COUNT(rv.id) AS review_count
        FROM restaurants r
        LEFT JOIN reviews rv ON rv.restaurant_id = r.id
        WHERE r.id = ?
        GROUP BY r.id");
$stmt->bind_param("i", $id);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
$message = "";
$messageType = "";

if ($r && $_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit;
    }

    $food        = (int) ($_POST["food"] ?? 0);
    $service     = (int) ($_POST["service"] ?? 0);
    $price       = (int) ($_POST["price"] ?? 0);
    $environment = (int) ($_POST["environment"] ?? 0);
    $text        = trim($_POST["text"] ?? "");

    $scores = [$food, $service, $price, $environment];
    $valid = true;
    foreach ($scores as $s) {
        if ($s < 1 || $s > 5) {
            $valid = false;
        }
    }

    if (!$valid) {
        $message = "Each rating must be between 1 and 5.";
        $messageType = "error";
    } elseif ($text === "") {
        $message = "Please write a review.";
        $messageType = "error";
    } else {
        $rating = (int) round(array_sum($scores) / 4);

        $ins = $conn->prepare("INSERT INTO reviews
            (user_id, restaurant_id, rating, food, service, price, environment, comment)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $ins->bind_param("iiiiiiis", $_SESSION["user_id"], $id, $rating, $food, $service, $price, $environment, $text);
        $ins->execute();

        header("Location: restaurant.php?id=" . $id . "&reviewed=1");
        exit;
    }
}

if (isset($_GET["reviewed"])) {
    $message = "Thanks! Your review was posted.";
    $messageType = "success";
}

$reviews = [];
if ($r) {
    $rs = $conn->prepare("SELECT rv.*, u.name AS user_name
            FROM reviews rv
            JOIN users u ON u.id = rv.user_id
            WHERE rv.restaurant_id = ?
            ORDER BY rv.created_at DESC");
    $rs->bind_param("i", $id);
    $rs->execute();
    $reviews = $rs->get_result()->fetch_all(MYSQLI_ASSOC);
}

$isFav = false;
if ($r && isset($_SESSION["user_id"])) {
    $fav = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND restaurant_id = ?");
    $fav->bind_param("ii", $_SESSION["user_id"], $id);
    $fav->execute();
    $fav->store_result();
    $isFav = $fav->num_rows > 0;
}

$tierLabels = ["budget" => "Budget", "mid" => "Moderate", "upscale" => "Upscale"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Restaurant · Taste Trails</title>
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

  <main class="container" style="padding-top: calc(var(--nav-h) + 0.5rem)">
  <?php if (!$r): ?>
    <p class="empty-state">Restaurant not found. <a href="restaurants.php">Browse all</a></p>
  <?php else: ?>
    <?php $tags = array_filter(array_map("trim", explode(",", (string) $r["tags"]))); ?>

  <div class="detail-hero">
    <img src="<?php echo htmlspecialchars($r["image"]); ?>" alt="<?php echo htmlspecialchars($r["name"]); ?>">
    <div class="detail-overlay">
      <p class="eyebrow"><?php echo htmlspecialchars($r["cuisine"]); ?> · <?php echo $tierLabels[$r["price_range"]] ?? ""; ?></p>
      <h1><?php echo htmlspecialchars($r["name"]); ?></h1>
      <p class="detail-rating">
        <?php if ($r["review_count"] > 0): ?>
          <?php echo stars($r["avg_rating"]); ?> <?php echo $r["avg_rating"]; ?> · <?php echo $r["review_count"]; ?> reviews
        <?php else: ?>
          No reviews yet
        <?php endif; ?>
      </p>
    </div>
  </div>

  <div class="detail-grid">
    <div class="detail-main">
      <h2>About</h2>
      <p><?php echo htmlspecialchars($r["description"]); ?></p>
      <div class="tag-row">
        <?php foreach ($tags as $t): ?>
          <span class="tag"><?php echo htmlspecialchars($t); ?></span>
        <?php endforeach; ?>
      </div>
      
      <h2>Write a review</h2>
      <?php if (isset($_SESSION["user_id"])): ?>
        <form class="review-form" method="POST" action="restaurant.php?id=<?php echo $r["id"]; ?>">
          <h3>Leave a review</h3>
          <p class="muted">Rate food, service, price &amp; environment (1–5).</p>
          <div class="rating-grid">
            <label>Food <input name="food" type="number" min="1" max="5" value="5" required></label>
            <label>Service <input name="service" type="number" min="1" max="5" value="5" required></label>
            <label>Price <input name="price" type="number" min="1" max="5" value="4" required></label>
            <label>Environment <input name="environment" type="number" min="1" max="5" value="5" required></label>
          </div>
          <div class="form-group">
            <label for="review-text">Your review</label>
            <textarea id="review-text" name="text" rows="4" required placeholder="Share your experience…"></textarea>
          </div>
          <p class="form-msg <?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></p>
          <button class="btn" type="submit">Submit review</button>
        </form>
      <?php else: ?>
        <div class="review-login-prompt">
          <p>Want to leave a review? <a href="login.php">Log in</a> or <a href="register.php">create an account</a>.</p>
        </div>
      <?php endif; ?>

      <h2>Recent reviews</h2>
      <div class="detail-reviews">
        <?php if (count($reviews) === 0): ?>
          <p class="muted">No reviews yet for this restaurant. Be the first!</p>
        <?php endif; ?>

        <?php foreach ($reviews as $rev): ?>
        <article class="review-card compact">
          <div class="review-head">
            <div class="avatar"><?php echo htmlspecialchars(mb_substr($rev["user_name"], 0, 1)); ?></div>
            <div>
                <strong><?php echo htmlspecialchars($rev["user_name"]); ?></strong>
                <p><?php echo date("M j, Y", strtotime($rev["created_at"])); ?></p>
              </div>
            </div>
            <p class="review-stars"><?php echo stars($rev["rating"]); ?></p>
            <p class="review-breakdown muted">Food <?php echo $rev["food"] ?? "—"; ?> · Service <?php echo $rev["service"] ?? "—"; ?> · Price <?php echo $rev["price"] ?? "—"; ?> · Atmosphere <?php echo $rev["environment"] ?? "—"; ?></p>
            <p class="review-text">“<?php echo htmlspecialchars($rev["comment"]); ?>”</p>
          </article>
          <?php endforeach; ?>
        </div>
      </div>

      <aside class="detail-aside">
        <div class="info-panel">
          <h3>Details</h3>
          <p><strong>Location</strong><br><?php echo htmlspecialchars($r["location"] . ", " . $r["city"]); ?></p>
          <p><strong>Hours</strong><br><?php echo htmlspecialchars($r["hours"] ?: "—"); ?></p>
          <p><strong>Phone</strong><br><?php echo htmlspecialchars($r["phone"] ?: "—"); ?></p>
          <p><strong>Price range</strong><br>KSh <?php echo number_format($r["price_min"]); ?> – KSh <?php echo number_format($r["price_max"]); ?> <span class="muted">(<?php echo $tierLabels[$r["price_range"]] ?? ""; ?>)</span></p>
          <?php if (isset($_SESSION["user_id"])): ?>
          <form method="POST" action="favorite.php">
            <input type="hidden" name="restaurant_id" value="<?php echo $r["id"]; ?>">
            <button class="btn btn-block" type="submit"><?php echo $isFav ? "Saved ✓ · Remove" : "Save to favourites"; ?></button>
          </form>
          <?php else: ?>
          <a class="btn btn-block" href="login.php">Log in to save</a>
          <?php endif; ?>
          <a class="btn btn-block ghost" href="https://www.google.com/maps/search/<?php echo urlencode($r["name"] . " " . $r["location"]); ?>" target="_blank" rel="noopener">View on map</a>
        </div>
      </aside>
    </div>
  <?php endif; ?>
  </main>

  <footer class="site-footer">
    <div class="container">
      <div class="footer-bottom" style="border:0;padding-top:0">
        <span>© 2026 Taste Trails</span>
        <a href="restaurants.php">← Back to restaurants</a>
      </div>
    </div>
  </footer>

  <script src="js/data.js"></script>
  <script src="js/auth.js"></script>
  <script src="js/main.js"></script>
</body>
</html>
