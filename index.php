<?php
session_start();
include "db.php";

function stars($rating) {
    $full = floor($rating);
    $half = ($rating - $full) >= 0.4 ? 1 : 0;
    return str_repeat("★", $full) . ($half ? "½" : "") . str_repeat("☆", 5 - $full - $half);
}

$featured = $conn->query("SELECT r.*,
        COALESCE(ROUND(AVG(rv.rating), 1), 0) AS avg_rating,
        COUNT(rv.id) AS review_count
        FROM restaurants r
        LEFT JOIN reviews rv ON rv.restaurant_id = r.id
        WHERE r.featured = 1
        GROUP BY r.id
        ORDER BY r.name")->fetch_all(MYSQLI_ASSOC);

$latestReviews = $conn->query("SELECT rv.*, u.name AS user_name, r.name AS restaurant_name
        FROM reviews rv
        JOIN users u ON u.id = rv.user_id
        JOIN restaurants r ON r.id = rv.restaurant_id
        ORDER BY rv.created_at DESC
        LIMIT 3")->fetch_all(MYSQLI_ASSOC);

$cuisineList = $conn->query("SELECT cuisine, COUNT(*) AS total
        FROM restaurants
        GROUP BY cuisine
        ORDER BY total DESC, cuisine")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Taste Trails · Discover Kenya’s Best Restaurants</title>
  <meta name="description" content="Taste Trails helps you discover restaurants across Kenya by cuisine, budget, location, and real reviews.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,650&family=Great+Vibes&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body class="home">
  <header class="navbar">
    <div class="container nav-inner">
      <a class="logo" href="index.php" aria-label="Taste Trails home">
        <img src="images/logo.png" alt="">
        <span class="logo-word">Taste Trails</span>
      </a>

      <ul class="nav-links">
        <li><a href="index.php" class="active">Home</a></li>
        <li><a href="restaurants.php">Restaurants</a></li>
        <li><a href="recommendations.php">Recommended</a></li>
        <li><a href="reviews.php">Reviews</a></li>
        <li><a href="about.html">About</a></li>
        <li><a href="contact.html">Contact</a></li>
      </ul>

      <div class="auth-buttons">
        <?php if (isset($_SESSION["user_id"])): ?>
        <span>Hi, <?php echo htmlspecialchars($_SESSION["user_name"]); ?></span>
        <?php if ($_SESSION["user_role"] === "admin"): ?>
        <a href="admin/index.php">Admin</a>
        <?php endif; ?>
        <a href="logout.php">Logout</a>
        <?php else: ?>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
        <?php endif; ?>
      </div>
      <button class="menu-toggle" type="button" aria-label="Open menu" aria-expanded="false">☰</button>
    </div>
  </header>

  <section class="hero">
    <div class="hero-content">
      <h1 class="hero-brand">Taste Trails</h1>
      <p class="hero-tagline">Tempting tastes, timeless travels.</p>
      <div class="hero-actions">
        <button id="explore-btn" class="btn" type="button">Explore Now</button>
        <button id="find-table-btn" class="btn ghost" type="button">Find a table</button>
      </div>
    </div>
    <a class="scroll-cue" href="#features" aria-label="Scroll to features">
      <span></span>
    </a>
  </section>

  <div class="modal" id="find-table-modal" hidden>
    <div class="modal-backdrop" data-close-modal></div>
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="find-table-title">
      <button type="button" class="modal-close" data-close-modal aria-label="Close">✕</button>
      <p class="eyebrow">Quick search</p>
      <h2 id="find-table-title">Find a table</h2>
      <p class="muted">Pick what you’re craving, where, and your budget — we’ll show matching restaurants.</p>
      <form id="find-table-form" class="find-form">
        <div class="form-group">
          <label for="find-cuisine">Cuisine</label>
          <select id="find-cuisine" name="cuisine">
            <option value="">Any cuisine</option>
          </select>
        </div>
        <div class="form-group">
          <label for="find-location">Location</label>
          <select id="find-location" name="location">
            <option value="">Anywhere in Nairobi</option>
          </select>
        </div>
        <div class="form-group">
          <label for="find-price">Budget</label>
          <select id="find-price" name="price">
            <option value="">Any budget</option>
            <option value="budget">Budget · under KSh 1,500</option>
            <option value="mid">Moderate · KSh 700–3,500</option>
            <option value="upscale">Upscale · from KSh 2,500</option>
          </select>
        </div>
        <button class="btn btn-block" type="submit">Show restaurants</button>
      </form>
    </div>
  </div>

  <section id="features" class="section features">
    <div class="container">
      <div class="section-head" data-reveal>
        <p class="eyebrow">Why Taste Trails</p>
        <h2>Dining decisions, made simple</h2>
        <p>Match cuisine, budget in KSh, and location — then trust real reviews before you go.</p>
      </div>
      <div class="feature-grid">
        <article class="feature-card" data-reveal>
          <div class="feature-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 11h16M7 11V5a2 2 0 0 1 4 0v6M13 11V4a2 2 0 0 1 4 0v7M5 11v8h14v-8"/></svg>
          </div>
          <h3>Discover Restaurants</h3>
          <p>Browse places across Kenya by cuisine, neighbourhood, and what you’re willing to spend.</p>
        </article>
        <article class="feature-card" data-reveal style="--delay:.1s">
          <div class="feature-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="m12 3 2.4 4.9 5.4.8-3.9 3.8.9 5.4L12 15.9 7.2 18l.9-5.4L4.2 8.7l5.4-.8L12 3z"/></svg>
          </div>
          <h3>Honest Reviews</h3>
          <p>See ratings on food, service, price, and atmosphere from diners who’ve already been.</p>
        </article>
        <article class="feature-card" data-reveal style="--delay:.2s">
          <div class="feature-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 21s7-4.4 7-10a7 7 0 1 0-14 0c0 5.6 7 10 7 10z"/><circle cx="12" cy="11" r="2.5"/></svg>
          </div>
          <h3>Smart Recommendations</h3>
          <p>Get suggestions shaped around your tastes so the next great meal finds you faster.</p>
        </article>
      </div>
    </div>
  </section>

  <section class="section restaurants" id="featured">
    <div class="container">
      <div class="section-head" data-reveal>
        <p class="eyebrow">Handpicked</p>
        <h2>Featured Restaurants</h2>
        <p>Popular Nairobi spots — each with clear KSh price ranges and ratings.</p>
      </div>
      <div class="resto-grid">
  <?php foreach ($featured as $r): ?>
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
      <div class="section-cta">
        <a class="btn ghost" href="restaurants.php">View all restaurants</a>
      </div>
    </div>
  </section>

  <section class="section cuisine" id="cuisine">
    <div class="container">
      <div class="section-head" data-reveal>
        <p class="eyebrow">Cravings</p>
        <h2>Browse by Cuisine</h2>
        <p>Start with the flavour you’re hungry for tonight.</p>
      </div>
      <div class="cuisine-grid">
        <?php
        $accents = [
          "Kenyan" => "#c45c26", "Continental" => "#d97706", "Italian" => "#b45309",
          "Indian" => "#ea580c", "Chinese" => "#c2410c", "Ethiopian" => "#9a3412",
          "Seafood" => "#0e7490", "Grill" => "#b91c1c", "Fusion" => "#a16207", "American" => "#b91c1c"
        ];
        foreach ($cuisineList as $i => $c):
          $accent = "#c45c26";
          foreach ($accents as $key => $color) {
            if (stripos($c["cuisine"], $key) !== false) {
              $accent = $color;
              break;
            }
          }
      ?>
      <a class="cuisine-tile" href="restaurants.php?cuisine=<?php echo urlencode($c["cuisine"]); ?>" style="--delay:<?php echo $i * 0.06; ?>s; --accent:<?php echo $accent; ?>">
        <span class="cuisine-name"><?php echo htmlspecialchars($c["cuisine"]); ?></span>
        <span class="cuisine-count"><?php echo $c["total"] == 1 ? "1 place" : $c["total"] . " places"; ?></span>
      </a>
      <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section reviews-section" id="latest-reviews">
    <div class="container">
      <div class="section-head" data-reveal>
        <p class="eyebrow">Community</p>
        <h2>Latest Reviews</h2>
        <p>Fresh takes from diners exploring Kenya’s food scene.</p>
      </div>
      <div class="review-grid">
        <?php if (count($latestReviews) === 0): ?>
          <p class="empty-state">No reviews yet. Be the first to review a restaurant!</p>
        <?php endif; ?>

        <?php foreach ($latestReviews as $rev): ?>
        <article class="review-card">
          <div class="review-head">
            <div class="avatar"><?php echo htmlspecialchars(mb_substr($rev["user_name"], 0, 1)); ?></div>
            <div>
              <strong><?php echo htmlspecialchars($rev["user_name"]); ?></strong>
              <p><?php echo htmlspecialchars($rev["restaurant_name"]); ?> · <?php echo date("M j, Y", strtotime($rev["created_at"])); ?></p>
            </div>
          </div>
          <p class="review-stars"><?php echo stars($rev["rating"]); ?></p>
          <p class="review-text">“<?php echo htmlspecialchars($rev["comment"]); ?>”</p>
        </article>
        <?php endforeach; ?>
      </div>
      <div class="section-cta">
        <a class="btn ghost" href="reviews.php">Read more reviews</a>
      </div>
    </div>
  </section>

  <?php if (!isset($_SESSION["user_id"])): ?>
  <section class="cta-band">
    <div class="container cta-band-inner" data-reveal>
      <div>
        <h2>Ready to find your next favourite table?</h2>
        <p>Create a free account to save places, leave reviews, and get recommendations.</p>
      </div>
      <a class="btn" href="register.php">Join Taste Trails</a>
    </div>
  </section>
  <?php endif; ?>

  <footer class="site-footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand">
          <span class="logo-word">Taste Trails</span>
          <p>A restaurant recommendation and review platform built for food lovers in Kenya.</p>
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
          <a href="favorites.php">Favourites</a>
          <a href="admin/index.php">Admin</a>
        </div>
        <div class="footer-col">
          <h4>Contact</h4>
          <p>hello@tastetrails.co.ke</p>
          <p>Nairobi, Kenya</p>
          <a href="contact.html">Send a message</a>
        </div>
      </div>
      <div class="footer-bottom">
        <span>© 2026 Taste Trails. All rights reserved.</span>
        <span>CISY 401 · Kenya Methodist University</span>
      </div>
    </div>
  </footer>

  <script src="js/data.js"></script>
  <script src="js/auth.js"></script>
  <script src="js/main.js"></script>
</body>
</html>
