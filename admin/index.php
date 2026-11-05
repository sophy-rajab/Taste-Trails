<?php
session_start();
include "../db.php";

// Only admins can open this page
if (!isset($_SESSION["user_id"]) || ($_SESSION["user_role"] ?? "") !== "admin") {
    header("Location: ../login.php");
    exit;
}

$totalRestaurants = $conn->query("SELECT COUNT(*) FROM restaurants")->fetch_row()[0];
$totalUsers       = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$totalReviews     = $conn->query("SELECT COUNT(*) FROM reviews")->fetch_row()[0];
$avgRating        = $conn->query("SELECT ROUND(AVG(rating), 1) FROM reviews")->fetch_row()[0];

$preview = $conn->query("SELECT r.*,
        COALESCE(ROUND(AVG(rv.rating), 1), 0) AS avg_rating,
        COUNT(rv.id) AS review_count
        FROM restaurants r
        LEFT JOIN reviews rv ON rv.restaurant_id = r.id
        GROUP BY r.id
        ORDER BY r.name")->fetch_all(MYSQLI_ASSOC);

$cuisineStats = $conn->query("SELECT cuisine, COUNT(*) AS total
        FROM restaurants
        GROUP BY cuisine
        ORDER BY total DESC, cuisine")->fetch_all(MYSQLI_ASSOC);

$adminReviews = $conn->query("SELECT rv.*, u.name AS user_name, r.name AS restaurant_name
        FROM reviews rv
        JOIN users u ON u.id = rv.user_id
        JOIN restaurants r ON r.id = rv.restaurant_id
        ORDER BY rv.created_at DESC")->fetch_all(MYSQLI_ASSOC);

$adminUsers = $conn->query("SELECT id, name, email, role FROM users ORDER BY id")->fetch_all(MYSQLI_ASSOC);

$editing = null;
if (isset($_GET["edit"])) {
    $e = $conn->prepare("SELECT * FROM restaurants WHERE id = ?");
    $editId = (int) $_GET["edit"];
    $e->bind_param("i", $editId);
    $e->execute();
    $editing = $e->get_result()->fetch_assoc();
}

$formMessage = "";
$formType = "";
if (isset($_GET["error"])) {
    $formMessage = $_GET["error"];
    $formType = "error";
} elseif (isset($_GET["saved"])) {
    $formMessage = "Restaurant saved.";
    $formType = "success";
} elseif (isset($_GET["deleted"])) {
    $formMessage = "Restaurant deleted.";
    $formType = "success";
}

$tierLabels = ["budget" => "Budget", "mid" => "Moderate", "upscale" => "Upscale"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard · Taste Trails</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,650&family=Great+Vibes&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../style.css">
</head>
<body class="admin-body">
  <aside class="admin-sidebar">
    <a href="../index.php" class="logo-word">Taste Trails</a>
    <p class="sub">Admin console</p>
    <nav class="admin-nav" data-admin-tabs>
      <button type="button" class="active" data-tab="overview">Dashboard</button>
      <button type="button" data-tab="restaurants">Restaurants</button>
      <button type="button" data-tab="reviews">Reviews</button>
      <button type="button" data-tab="users">Users</button>
      <a href="../index.php">← Back to site</a>
      <a href="../logout.php">Logout</a>
    </nav>
  </aside>

  <main class="admin-main">
    <div class="admin-top">
      <div>
        <p class="eyebrow" style="margin:0">Admin</p>
        <h1 data-admin-title>Dashboard</h1>
      </div>
      <div>
        <span class="user-chip"><?php echo htmlspecialchars($_SESSION["user_name"]); ?></span>
      </div>
    </div>

    <section class="admin-tab is-active" data-tab-panel="overview">
      <div class="stat-grid">
        <article class="stat-card">
          <span>Restaurants</span>
          <strong><?php echo $totalRestaurants; ?></strong>
        </article>
        <article class="stat-card">
          <span>Users</span>
          <strong><?php echo $totalUsers; ?></strong>
        </article>
        <article class="stat-card">
          <span>Reviews</span>
          <strong><?php echo $totalReviews; ?></strong>
        </article>
        <article class="stat-card">
          <span>Avg. rating</span>
          <strong><?php echo $avgRating ?: "—"; ?></strong>
        </article>
      </div>

      <div class="admin-panels">
        <section class="panel">
          <h2>Quick look — restaurants</h2>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th></th>
                  <th>Name</th>
                  <th>Location</th>
                  <th>Cuisine</th>
                  <th>Rating</th>
                  <th>Price</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($preview as $r): ?>
                <tr>
                  <td><img src="../<?php echo htmlspecialchars($r["image"]); ?>" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:8px"></td>
                  <td><?php echo htmlspecialchars($r["name"]); ?></td>
                  <td><?php echo htmlspecialchars($r["location"]); ?></td>
                  <td><?php echo htmlspecialchars($r["cuisine"]); ?></td>
                  <td><?php echo $r["review_count"] > 0 ? $r["avg_rating"] : "—"; ?></td>
                  <td><?php echo $tierLabels[$r["price_range"]] ?? ""; ?></td>
                  <td><?php echo $r["featured"] ? "Featured" : "Listed"; ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>

        <section class="panel">
          <h2>Cuisine popularity</h2>
          <div>
            <?php
            $max = $cuisineStats ? max(array_column($cuisineStats, "total")) : 1;
            foreach ($cuisineStats as $c):
              $pct = round($c["total"] / $max * 100);
            ?>
            <div style="margin-bottom:.8rem">
              <div style="display:flex;justify-content:space-between;font-size:.9rem">
                <span><?php echo htmlspecialchars($c["cuisine"]); ?></span>
                <span><?php echo $c["total"]; ?></span>
            </div>
            <div style="background:rgba(128,128,128,.25);border-radius:999px;height:8px">
              <div style="width:<?php echo $pct; ?>%;background:var(--ember);height:8px;border-radius:999px"></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        </section>
      </div>
    </section>

    <section class="admin-tab" data-tab-panel="restaurants" hidden>
      <section class="panel">
        <div class="panel-head-row">
          <div>
          <h2><?php echo $editing ? "Edit restaurant" : "Add restaurant"; ?></h2>
            <p class="muted">Fill in details, upload a logo and cover photo, then save.</p>
          </div>
          <?php if ($editing): ?>
          <a class="btn ghost tiny" href="index.php#restaurants">Cancel edit</a>
          <?php endif; ?>
        </div>

        <?php $tierSel = $editing["price_range"] ?? "mid"; ?>
        <form class="admin-form" method="POST" action="save-restaurant.php" enctype="multipart/form-data">
          <input type="hidden" name="id" value="<?php echo $editing["id"] ?? ""; ?>">
          <input type="hidden" name="image" value="<?php echo htmlspecialchars($editing["image"] ?? ""); ?>">

          <div class="form-grid">
            <div class="form-group">
              <label for="resto-name">Restaurant name *</label>
              <input id="resto-name" name="name" required placeholder="e.g. Java House" value="<?php echo htmlspecialchars($editing["name"] ?? ""); ?>">
          </div>
          <div class="form-group">
            <label for="resto-cuisine">Cuisine *</label>
            <input id="resto-cuisine" name="cuisine" required placeholder="e.g. Kenyan, Italian" value="<?php echo htmlspecialchars($editing["cuisine"] ?? ""); ?>">
          </div>
          <div class="form-group">
            <label for="resto-location">Location / area *</label>
            <input id="resto-location" name="location" required placeholder="e.g. Westlands" value="<?php echo htmlspecialchars($editing["location"] ?? ""); ?>">
          </div>
          <div class="form-group">
            <label for="resto-city">City</label>
            <input id="resto-city" name="city" placeholder="Nairobi" value="<?php echo htmlspecialchars($editing["city"] ?? "Nairobi"); ?>">
          </div>
          <div class="form-group">
            <label for="resto-phone">Phone</label>
            <input id="resto-phone" name="phone" placeholder="+254 7xx xxx xxx" value="<?php echo htmlspecialchars($editing["phone"] ?? ""); ?>">
          </div>
          <div class="form-group">
            <label for="resto-hours">Opening hours</label>
            <input id="resto-hours" name="hours" placeholder="11:00 AM – 10:00 PM" value="<?php echo htmlspecialchars($editing["hours"] ?? ""); ?>">
          </div>
          <div class="form-group">
            <label for="resto-tier">Price tier</label>
            <select id="resto-tier" name="priceTier">
              <option value="budget" <?php if ($tierSel === "budget") echo "selected"; ?>>Budget</option>
              <option value="mid" <?php if ($tierSel === "mid") echo "selected"; ?>>Moderate</option>
              <option value="upscale" <?php if ($tierSel === "upscale") echo "selected"; ?>>Upscale</option>
            </select>
          </div>
          <div class="form-group">
            <label for="resto-min">Min price (KSh)</label>
            <input id="resto-min" name="priceMin" type="number" min="0" step="50" placeholder="500" value="<?php echo htmlspecialchars($editing["price_min"] ?? ""); ?>">
          </div>
          <div class="form-group">
            <label for="resto-max">Max price (KSh)</label>
            <input id="resto-max" name="priceMax" type="number" min="0" step="50" placeholder="2500" value="<?php echo htmlspecialchars($editing["price_max"] ?? ""); ?>">
          </div>
          <div class="form-group">
            <label for="resto-tags">Tags (comma separated)</label>
            <input id="resto-tags" name="tags" placeholder="Wi-Fi, Family, Outdoor" value="<?php echo htmlspecialchars($editing["tags"] ?? ""); ?>">
          </div>
          <div class="form-group form-span-2">
            <label for="resto-desc">Description *</label>
            <textarea id="resto-desc" name="description" rows="3" required placeholder="Short description of the restaurant…"><?php echo htmlspecialchars($editing["description"] ?? ""); ?></textarea>
          </div>
          <div class="form-group">
            <label for="resto-cover">Cover photo</label>
            <input id="resto-cover" name="coverFile" type="file" accept="image/*">
            <?php if ($editing && $editing["image"]): ?>
            <div class="media-preview"><img src="../<?php echo htmlspecialchars($editing["image"]); ?>" alt="" style="max-width:160px;border-radius:8px;margin-top:.5rem"></div>
            <p class="muted">Leave empty to keep the current photo.</p>
            <?php endif; ?>
          </div>
          <div class="form-group form-check">
            <label>
              <input type="checkbox" name="featured" <?php if (!empty($editing["featured"])) echo "checked"; ?>>
              Show on Featured section
            </label>
          </div>
        </div>

        <p class="form-msg <?php echo $formType; ?>"><?php echo htmlspecialchars($formMessage); ?></p>
        <div class="form-actions">
          <button class="btn" type="submit"><?php echo $editing ? "Update restaurant" : "Save restaurant"; ?></button>
        </div>
      </form>
      </section>

      <section class="panel">
        <h2>All restaurants</h2>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th></th>
                <th>Name</th>
                <th>Location</th>
                <th>Cuisine</th>
                <th>Rating</th>
                <th>Price</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($preview as $r): ?>
              <tr>
                <td><img src="../<?php echo htmlspecialchars($r["image"]); ?>" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:8px"></td>
                <td><?php echo htmlspecialchars($r["name"]); ?></td>
                <td><?php echo htmlspecialchars($r["location"]); ?></td>
                <td><?php echo htmlspecialchars($r["cuisine"]); ?></td>
                <td><?php echo $r["review_count"] > 0 ? $r["avg_rating"] : "—"; ?></td>
                <td><?php echo $tierLabels[$r["price_range"]] ?? ""; ?></td>
                <td><?php echo $r["featured"] ? "Featured" : "Listed"; ?></td>
                <td>
                  <a class="btn ghost tiny" href="index.php?edit=<?php echo $r["id"]; ?>#restaurants">Edit</a>
                  <form method="POST" action="delete-restaurant.php" style="display:inline" onsubmit="return confirm('Delete this restaurant? Its reviews and favourites will be deleted too.');">
                    <input type="hidden" name="id" value="<?php echo $r["id"]; ?>">
                    <button class="btn ghost tiny" type="submit">Delete</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    </section>

    <section class="admin-tab" data-tab-panel="reviews" hidden>
      <section class="panel">
        <h2>Reviews</h2>
        <?php if (isset($_GET["rdeleted"])): ?>
        <p class="form-msg success">Review deleted.</p>
        <?php endif; ?>
        <p class="muted" style="margin-top:-0.5rem;margin-bottom:1rem">Customer feedback across the platform. You can remove inappropriate reviews.</p>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>User</th>
                <th>Restaurant</th>
                <th>Rating</th>
                <th>Date</th>
                <th>Comment</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($adminReviews as $rev): ?>
              <tr>
                <td><?php echo htmlspecialchars($rev["user_name"]); ?></td>
                <td><?php echo htmlspecialchars($rev["restaurant_name"]); ?></td>
                <td><?php echo $rev["rating"]; ?> ★</td>
                <td><?php echo date("M j, Y", strtotime($rev["created_at"])); ?></td>
                <td><?php echo htmlspecialchars(mb_strimwidth($rev["comment"], 0, 80, "…")); ?></td>
                <td>
                  <form method="POST" action="delete-review.php" style="display:inline" onsubmit="return confirm('Delete this review?');">
                    <input type="hidden" name="id" value="<?php echo $rev["id"]; ?>">
                    <button class="btn ghost tiny" type="submit">Delete</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    </section>

    <section class="admin-tab" data-tab-panel="users" hidden>
      <section class="panel">
        <h2>Users</h2>
        <?php if (isset($_GET["udeleted"])): ?>
        <p class="form-msg success">User deleted.</p>
        <?php endif; ?>
        <?php if (isset($_GET["uerror"])): ?>
        <p class="form-msg error"><?php echo htmlspecialchars($_GET["uerror"]); ?></p>
        <?php endif; ?>
        <p class="muted" style="margin-top:-0.5rem;margin-bottom:1rem">Registered accounts and roles.</p>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($adminUsers as $u): ?>
              <tr>
                <td><?php echo htmlspecialchars($u["name"]); ?></td>
                <td><?php echo htmlspecialchars($u["email"]); ?></td>
                <td><?php echo htmlspecialchars($u["role"]); ?></td>
                <td>
                  <?php if ((int) $u["id"] === (int) $_SESSION["user_id"]): ?>
                    <span class="muted">You</span>
                  <?php else: ?>
                  <form method="POST" action="delete-user.php" style="display:inline" onsubmit="return confirm('Delete this user? Their reviews and favourites will be deleted too.');">
                    <input type="hidden" name="id" value="<?php echo $u["id"]; ?>">
                    <button class="btn ghost tiny" type="submit">Delete</button>
                  </form>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    </section>
  </main>

  <script>
    const titles = { overview: "Dashboard", restaurants: "Restaurants", reviews: "Reviews", users: "Users" };

    function showTab(id) {
      document.querySelectorAll("[data-tab]").forEach(function (b) {
        b.classList.toggle("active", b.dataset.tab === id);
      });
      document.querySelectorAll("[data-tab-panel]").forEach(function (p) {
        var match = p.dataset.tabPanel === id;
        p.classList.toggle("is-active", match);
        p.hidden = !match;
      });
      document.querySelector("[data-admin-title]").textContent = titles[id] || "Dashboard";
      history.replaceState(null, "", "#" + id);
    }

    document.querySelectorAll("[data-tab]").forEach(function (b) {
      b.addEventListener("click", function () { showTab(b.dataset.tab); });
    });

    var hash = location.hash.replace("#", "");
    if (titles[hash]) showTab(hash);
  </script>
</body>
</html>
