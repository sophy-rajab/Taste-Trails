(function () {
  function qs(sel, root) {
    return (root || document).querySelector(sel);
  }
  function qsa(sel, root) {
    return Array.from((root || document).querySelectorAll(sel));
  }

  function initNavbar() {
    const navbar = qs(".navbar");
    if (!navbar) return;

    const onScroll = () => {
      navbar.classList.toggle("scrolled", window.scrollY > 40);
    };
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });

    const toggle = qs(".menu-toggle");
    const links = qs(".nav-links");
    if (toggle && links) {
      toggle.addEventListener("click", () => {
        const open = links.classList.toggle("is-open");
        toggle.setAttribute("aria-expanded", open ? "true" : "false");
        toggle.textContent = open ? "✕" : "☰";
      });

      qsa("a", links).forEach((a) => {
        a.addEventListener("click", () => {
          links.classList.remove("is-open");
          toggle.setAttribute("aria-expanded", "false");
          toggle.textContent = "☰";
        });
      });
    }
  }

  function initAuthUI() {
    const session = window.TTAuth ? window.TTAuth.getSession() : null;
    const authWrap = qs("[data-auth-slot]");
    if (!authWrap) return;

    // Show Favourites in main nav when logged in
    const navLinks = qs(".nav-links");
    if (navLinks && session && !qs('[href="favorites.html"]', navLinks)) {
      const li = document.createElement("li");
      const isFavPage = window.location.pathname.toLowerCase().includes("favorites");
      li.innerHTML = `<a href="favorites.html"${isFavPage ? ' class="active"' : ""}>Favourites</a>`;
      const reviews = qsa("a", navLinks).find((a) => a.getAttribute("href") === "reviews.html");
      if (reviews && reviews.parentElement) {
        navLinks.insertBefore(li, reviews.parentElement);
      } else {
        navLinks.appendChild(li);
      }
    }

    if (session) {
      const adminLink =
        session.role === "admin"
          ? `<a href="admin/index.html" class="nav-cta ghost">Admin</a>`
          : "";
      const favCount = window.TTFavorites ? window.TTFavorites.list().length : 0;
      authWrap.innerHTML = `
        <a href="favorites.html" class="nav-cta ghost" title="Favourites">♥ ${favCount}</a>
        <span class="user-chip" title="${session.email}">${session.name.split(" ")[0]}</span>
        ${adminLink}
        <button type="button" class="nav-cta outline" data-logout>Logout</button>
      `;
      const logoutBtn = qs("[data-logout]", authWrap);
      if (logoutBtn) {
        logoutBtn.addEventListener("click", () => {
          window.TTAuth.logout();
          window.location.href = "index.html";
        });
      }
    } else {
      authWrap.innerHTML = `
        <a href="login.html" class="nav-cta outline">Login</a>
        <a href="register.html" class="nav-cta solid">Register</a>
      `;
    }
  }

  function initAuthUIAdmin() {
    const session = window.TTAuth ? window.TTAuth.getSession() : null;
    const chip = qs("[data-admin-user]");
    if (chip && session) {
      chip.textContent = session.name;
    }

    const logout = qs("[data-admin-logout]");
    if (logout) {
      logout.addEventListener("click", (e) => {
        e.preventDefault();
        window.TTAuth.logout();
        window.location.href = "../login.html";
      });
    }
  }

  function requireAdmin() {
    if (!window.TTAuth || !window.TTAuth.isAdmin()) {
      window.location.href = "../login.html";
      return false;
    }
    return true;
  }

  function initReveal() {
    const items = qsa("[data-reveal]");
    if (!items.length) return;

    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-visible");
            io.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.15 }
    );

    items.forEach((el) => io.observe(el));
  }

  function initSmoothAnchors() {
    qsa('a[href^="#"]').forEach((a) => {
      a.addEventListener("click", (e) => {
        const id = a.getAttribute("href");
        if (!id || id === "#") return;
        const target = qs(id);
        if (!target) return;
        e.preventDefault();
        target.scrollIntoView({ behavior: "smooth", block: "start" });
      });
    });
  }

  function mediaSrc(path, base = "") {
    if (!path) return "";
    if (path.startsWith("data:") || path.startsWith("http") || path.startsWith("blob:")) {
      return path;
    }
    return base + path;
  }

  function restaurantCard(r, base = "", options = {}) {
    const logo = r.logo
      ? `<img class="resto-logo" src="${mediaSrc(r.logo, base)}" alt="">`
      : "";
    const unfav = options.showUnfav
      ? `<button type="button" class="btn btn-block ghost" data-unfav="${r.id}">Remove from favourites</button>`
      : "";
    return `
      <article class="resto-card" data-reveal>
        <a class="resto-media" href="${base}restaurant.html?id=${r.id}">
          <img src="${mediaSrc(r.image, base)}" alt="${r.name}" loading="lazy">
          <span class="price-badge">${window.TT.priceBadge(r)}</span>
          ${logo}
        </a>
        <div class="resto-body">
          <div class="resto-top">
            <h3><a href="${base}restaurant.html?id=${r.id}">${r.name}</a></h3>
            <p class="resto-rating" aria-label="Rating ${r.rating} out of 5">
              <span>${window.TT.stars(r.rating)}</span> ${r.rating}
            </p>
          </div>
          <p class="resto-meta">📍 ${r.location}</p>
          <p class="resto-meta">🍴 ${r.cuisine}</p>
          <p class="resto-meta">💰 ${window.TT.priceRange(r)}</p>
          <a class="btn btn-block" href="${base}restaurant.html?id=${r.id}">View Details</a>
          ${unfav}
        </div>
      </article>
    `;
  }

  function renderFeatured() {
    const mount = qs("[data-featured]");
    if (!mount || !window.TT_DATA) return;
    const featured = window.TT_DATA.restaurants.filter((r) => r.featured);
    mount.innerHTML = featured.map((r) => restaurantCard(r)).join("");
  }

  function renderCuisine() {
    const mount = qs("[data-cuisines]");
    if (!mount || !window.TT_DATA) return;

    const accents = {
      Kenyan: "#c45c26",
      Continental: "#d97706",
      Italian: "#b45309",
      Indian: "#ea580c",
      Chinese: "#c2410c",
      Ethiopian: "#9a3412",
      Seafood: "#0e7490",
      Grill: "#b91c1c",
      Fusion: "#a16207",
      American: "#b91c1c",
    };

    const counts = {};
    window.TT_DATA.restaurants.forEach((r) => {
      const name = r.cuisine || "Other";
      counts[name] = (counts[name] || 0) + 1;
    });

    const tiles = Object.entries(counts)
      .sort((a, b) => b[1] - a[1] || a[0].localeCompare(b[0]))
      .map(([name, count], i) => {
        const key = Object.keys(accents).find((k) =>
          name.toLowerCase().includes(k.toLowerCase())
        );
        const accent = accents[key] || "#c45c26";
        const label = count === 1 ? "1 place" : `${count} places`;
        return `
      <a class="cuisine-tile" href="restaurants.html?cuisine=${encodeURIComponent(name)}" data-reveal style="--delay:${i * 0.06}s; --accent:${accent}">
        <span class="cuisine-name">${name}</span>
        <span class="cuisine-count">${label}</span>
      </a>`;
      })
      .join("");

    mount.innerHTML = tiles || `<p class="empty-state">No cuisines available yet.</p>`;
  }

  function renderHomeReviews() {
    const mount = qs("[data-home-reviews]");
    if (!mount || !window.TT_DATA) return;
    mount.innerHTML = window.TT_DATA.reviews
      .slice(0, 3)
      .map(
        (rev) => `
      <article class="review-card" data-reveal>
        <div class="review-head">
          <div class="avatar">${rev.user.charAt(0)}</div>
          <div>
            <strong>${rev.user}</strong>
            <p>${rev.restaurant} · ${window.TT.formatDate(rev.date)}</p>
          </div>
        </div>
        <p class="review-stars">${window.TT.stars(rev.rating)}</p>
        <p class="review-text">“${rev.text}”</p>
      </article>`
      )
      .join("");
  }

  function initExplore() {
    const btn = qs("#explore-btn");
    const target = qs("#features");
    if (!btn || !target) return;
    btn.addEventListener("click", () => {
      target.scrollIntoView({ behavior: "smooth", block: "start" });
    });
  }

  function initFindTable() {
    const openBtn = qs("#find-table-btn");
    const modal = qs("#find-table-modal");
    const form = qs("#find-table-form");
    if (!openBtn || !modal || !form || !window.TT_DATA) return;

    const cuisineSelect = qs("#find-cuisine", form);
    const locationSelect = qs("#find-location", form);

    const cuisines = [...new Set(window.TT_DATA.restaurants.map((r) => r.cuisine))].sort();
    const locations = [...new Set(window.TT_DATA.restaurants.map((r) => r.location))].sort();

    cuisineSelect.innerHTML =
      `<option value="">Any cuisine</option>` +
      cuisines.map((c) => `<option value="${c}">${c}</option>`).join("");
    locationSelect.innerHTML =
      `<option value="">Anywhere in Nairobi</option>` +
      locations.map((l) => `<option value="${l}">${l}</option>`).join("");

    function openModal() {
      modal.hidden = false;
      document.body.classList.add("modal-open");
      cuisineSelect.focus();
    }

    function closeModal() {
      modal.hidden = true;
      document.body.classList.remove("modal-open");
    }

    openBtn.addEventListener("click", openModal);
    qsa("[data-close-modal]", modal).forEach((el) => {
      el.addEventListener("click", closeModal);
    });
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && !modal.hidden) closeModal();
    });

    form.addEventListener("submit", (e) => {
      e.preventDefault();
      const params = new URLSearchParams();
      if (form.cuisine.value) params.set("cuisine", form.cuisine.value);
      if (form.location.value) params.set("location", form.location.value);
      if (form.price.value) params.set("price", form.price.value);
      const query = params.toString();
      window.location.href = "restaurants.html" + (query ? "?" + query : "");
    });
  }

  function initLoginForm() {
    const form = qs("#login-form");
    if (!form) return;
    const msg = qs("[data-form-msg]");

    form.addEventListener("submit", (e) => {
      e.preventDefault();
      const email = form.email.value;
      const password = form.password.value;
      const result = window.TTAuth.login({ email, password });

      if (!result.ok) {
        msg.textContent = result.message;
        msg.className = "form-msg error";
        return;
      }

      msg.textContent = "Welcome back! Redirecting…";
      msg.className = "form-msg success";
      const dest =
        result.user.role === "admin" ? "admin/index.html" : "index.html";
      setTimeout(() => {
        window.location.href = dest;
      }, 600);
    });
  }

  function initRegisterForm() {
    const form = qs("#register-form");
    if (!form) return;
    const msg = qs("[data-form-msg]");

    form.addEventListener("submit", (e) => {
      e.preventDefault();
      const name = form.name.value;
      const email = form.email.value;
      const password = form.password.value;
      const confirm = form.confirm.value;

      if (password !== confirm) {
        msg.textContent = "Passwords do not match.";
        msg.className = "form-msg error";
        return;
      }

      const result = window.TTAuth.register({ name, email, password });
      if (!result.ok) {
        msg.textContent = result.message;
        msg.className = "form-msg error";
        return;
      }

      msg.textContent = "Account created! Taking you home…";
      msg.className = "form-msg success";
      setTimeout(() => {
        window.location.href = "index.html";
      }, 600);
    });
  }

  function initRestaurantsPage() {
    const mount = qs("[data-restaurant-grid]");
    if (!mount || !window.TT_DATA) return;

    const params = new URLSearchParams(window.location.search);
    const cuisineFilter = params.get("cuisine");
    const locationFilter = params.get("location");
    const priceFilter = params.get("price");
    const searchInput = qs("#resto-search");
    const cuisineSelect = qs("#resto-cuisine");
    const locationSelect = qs("#resto-location");
    const priceSelect = qs("#resto-price");
    const heading = qs("[data-resto-heading]");

    const cuisineOptions = [
      ...new Set(window.TT_DATA.restaurants.map((r) => r.cuisine)),
    ].sort();
    const locationOptions = [
      ...new Set(window.TT_DATA.restaurants.map((r) => r.location)),
    ].sort();

    if (cuisineSelect) {
      cuisineSelect.innerHTML =
        `<option value="">All cuisines</option>` +
        cuisineOptions.map((c) => `<option value="${c}">${c}</option>`).join("");
      if (cuisineFilter) {
        const match = cuisineOptions.find(
          (c) =>
            c.toLowerCase().includes(cuisineFilter.toLowerCase()) ||
            cuisineFilter.toLowerCase().includes(c.toLowerCase().split(" ")[0])
        );
        if (match) cuisineSelect.value = match;
      }
    }

    if (locationSelect) {
      locationSelect.innerHTML =
        `<option value="">All locations</option>` +
        locationOptions.map((l) => `<option value="${l}">${l}</option>`).join("");
      if (locationFilter) locationSelect.value = locationFilter;
    }

    if (priceSelect && priceFilter) {
      priceSelect.value = priceFilter;
    }

    if (heading && (cuisineFilter || locationFilter || priceFilter)) {
      const bits = [cuisineFilter, locationFilter].filter(Boolean);
      heading.textContent = bits.length
        ? bits.join(" · ") + " restaurants"
        : "Matching restaurants";
    }

    function apply() {
      const q = (searchInput?.value || "").trim().toLowerCase();
      const cuisine = cuisineSelect?.value || "";
      const location = locationSelect?.value || "";
      const price = priceSelect?.value || "";

      let list = [...window.TT_DATA.restaurants];
      if (q) {
        list = list.filter(
          (r) =>
            r.name.toLowerCase().includes(q) ||
            r.location.toLowerCase().includes(q) ||
            r.cuisine.toLowerCase().includes(q)
        );
      }
      if (cuisine) list = list.filter((r) => r.cuisine === cuisine);
      if (location) list = list.filter((r) => r.location === location);
      if (price) list = list.filter((r) => r.priceTier === price);

      if (!list.length) {
        mount.innerHTML = `<p class="empty-state">No restaurants match your filters. Try another search.</p>`;
        return;
      }
      mount.innerHTML = list.map((r) => restaurantCard(r)).join("");
      initReveal();
    }

    [searchInput, cuisineSelect, locationSelect, priceSelect].forEach((el) => {
      if (el) el.addEventListener("input", apply);
      if (el) el.addEventListener("change", apply);
    });
    apply();
  }

  function initRestaurantDetail() {
    const mount = qs("[data-restaurant-detail]");
    if (!mount || !window.TT_DATA) return;

    const id = new URLSearchParams(window.location.search).get("id");
    const r = window.TT.getRestaurant(id);
    if (!r) {
      mount.innerHTML = `<p class="empty-state">Restaurant not found. <a href="restaurants.html">Browse all</a></p>`;
      return;
    }

    document.title = `${r.name} · Taste Trails`;
    window.TTStore.refresh();
    const relatedReviews = window.TT_DATA.reviews.filter(
      (rev) => rev.restaurantId === r.id
    );
    const session = window.TTAuth.getSession();
    const tags = (r.tags || []).map((t) => `<span class="tag">${t}</span>`).join("");
    const logoBlock = r.logo
      ? `<img class="detail-logo" src="${mediaSrc(r.logo)}" alt="${r.name} logo">`
      : "";

    const reviewForm = session
      ? `
      <form class="review-form" id="leave-review-form">
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
        <p class="form-msg" data-review-msg></p>
        <button class="btn" type="submit">Submit review</button>
      </form>`
      : `<div class="review-login-prompt">
          <p>Want to leave a review? <a href="login.html">Log in</a> or <a href="register.html">create an account</a>.</p>
        </div>`;

    mount.innerHTML = `
      <div class="detail-hero">
        <img src="${mediaSrc(r.image)}" alt="${r.name}">
        <div class="detail-overlay">
          ${logoBlock}
          <p class="eyebrow">${r.cuisine} · ${window.TT.tierLabel(r.priceTier)}</p>
          <h1>${r.name}</h1>
          <p class="detail-rating">${window.TT.stars(r.rating)} ${r.rating} · ${r.reviews} reviews</p>
        </div>
      </div>
      <div class="detail-grid">
        <div class="detail-main">
          <h2>About</h2>
          <p>${r.description}</p>
          <div class="tag-row">${tags}</div>
          <h2>Write a review</h2>
          ${reviewForm}
          <h2>Recent reviews</h2>
          <div class="detail-reviews">
            ${
              relatedReviews.length
                ? relatedReviews
                    .map(
                      (rev) => `
              <article class="review-card compact">
                <div class="review-head">
                  <div class="avatar">${rev.user.charAt(0)}</div>
                  <div>
                    <strong>${rev.user}</strong>
                    <p>${window.TT.formatDate(rev.date)}</p>
                  </div>
                </div>
                <p class="review-stars">${window.TT.stars(rev.rating)}</p>
                <p class="review-breakdown muted">Food ${rev.food || "—"} · Service ${rev.service || "—"} · Price ${rev.price || "—"} · Atmosphere ${rev.environment || "—"}</p>
                <p class="review-text">“${rev.text}”</p>
              </article>`
                    )
                    .join("")
                : `<p class="muted">No reviews yet for this restaurant. Be the first!</p>`
            }
          </div>
        </div>
        <aside class="detail-aside">
          <div class="info-panel">
            <h3>Details</h3>
            <p><strong>Location</strong><br>${r.location}, ${r.city}</p>
            <p><strong>Hours</strong><br>${r.hours || "—"}</p>
            <p><strong>Phone</strong><br>${r.phone || "—"}</p>
            <p><strong>Price range</strong><br>${window.TT.priceRange(r)} <span class="muted">(${window.TT.tierLabel(r.priceTier)})</span></p>
            <button type="button" class="btn btn-block" data-favorite>${
              session && window.TTFavorites.has(r.id)
                ? "Saved ✓ · Remove"
                : "Save to favourites"
            }</button>
            <a class="btn btn-block ghost" href="favorites.html">View favourites</a>
            <a class="btn btn-block ghost" href="https://www.google.com/maps/search/${encodeURIComponent(r.name + " " + r.location)}" target="_blank" rel="noopener">View on map</a>
          </div>
        </aside>
      </div>
    `;

    const favBtn = qs("[data-favorite]", mount);
    if (favBtn) {
      favBtn.addEventListener("click", () => {
        if (!window.TTAuth.isLoggedIn()) {
          window.location.href = "login.html";
          return;
        }
        const result = window.TTFavorites.toggle(r.id);
        if (!result.ok) {
          window.location.href = "login.html";
          return;
        }
        favBtn.textContent = result.saved ? "Saved ✓ · Remove" : "Save to favourites";
        initAuthUI();
      });
    }

    const form = qs("#leave-review-form", mount);
    if (form) {
      form.addEventListener("submit", (e) => {
        e.preventDefault();
        const msg = qs("[data-review-msg]", form);
        const result = window.TTStore.addReview({
          restaurantId: r.id,
          user: session.name,
          userId: session.id,
          food: form.food.value,
          service: form.service.value,
          price: form.price.value,
          environment: form.environment.value,
          text: form.text.value,
        });
        if (!result.ok) {
          msg.textContent = result.message;
          msg.className = "form-msg error";
          return;
        }
        msg.textContent = "Thanks! Your review was posted.";
        msg.className = "form-msg success";
        setTimeout(() => initRestaurantDetail(), 500);
      });
    }
  }

  function initFavoritesPage() {
    const root = qs("[data-favorites-root]");
    if (!root) return;

    if (!window.TTAuth.isLoggedIn()) {
      root.innerHTML = `
        <div class="empty-state">
          <p>Log in to see your favourited restaurants.</p>
          <p style="margin-top:1rem">
            <a class="btn" href="login.html">Login</a>
            <a class="btn ghost" href="register.html" style="margin-left:.5rem">Register</a>
          </p>
        </div>`;
      return;
    }

    function render() {
      window.TTStore.refresh();
      const ids = window.TTFavorites.list();
      const list = ids
        .map((id) => window.TTStore.getRestaurant(id))
        .filter(Boolean);

      if (!list.length) {
        root.innerHTML = `
          <div class="empty-state">
            <p>No favourites yet. Save restaurants from their detail page.</p>
            <p style="margin-top:1rem"><a class="btn" href="restaurants.html">Browse restaurants</a></p>
          </div>`;
        return;
      }

      root.innerHTML = `
        <div class="resto-grid" data-favorites-grid>
          ${list.map((r) => restaurantCard(r, "", { showUnfav: true })).join("")}
        </div>`;

      initReveal();

      qsa("[data-unfav]", root).forEach((btn) => {
        btn.addEventListener("click", () => {
          window.TTFavorites.remove(btn.getAttribute("data-unfav"));
          initAuthUI();
          render();
        });
      });
    }

    render();
  }

  function initReviewsPage() {
    const mount = qs("[data-reviews-list]");
    if (!mount || !window.TTStore) return;

    window.TTStore.refresh();
    mount.innerHTML = window.TT_DATA.reviews
      .map(
        (rev) => `
      <article class="review-card wide" data-reveal>
        <div class="review-head">
          <div class="avatar">${rev.user.charAt(0)}</div>
          <div>
            <strong>${rev.user}</strong>
            <p><a href="restaurant.html?id=${rev.restaurantId}">${rev.restaurant}</a> · ${window.TT.formatDate(rev.date)}</p>
          </div>
        </div>
        <p class="review-stars">${window.TT.stars(rev.rating)}</p>
        <p class="review-text">“${rev.text}”</p>
      </article>`
      )
      .join("");
  }

  function initContactForm() {
    const form = qs("#contact-form");
    if (!form) return;
    const msg = qs("[data-form-msg]");
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      msg.textContent = "Thanks! Your message has been noted (demo).";
      msg.className = "form-msg success";
      form.reset();
    });
  }

  function thumbCell(r) {
    const src = mediaSrc(r.logo || r.image, "../");
    return `<td><img class="admin-thumb" src="${src}" alt=""></td>`;
  }

  function renderAdminDashboard() {
    window.TTStore.refresh();
    const restos = window.TT_DATA.restaurants;
    const reviews = window.TT_DATA.reviews;
    const users = window.TTAuth.listUsers();
    const avg =
      restos.reduce((s, r) => s + Number(r.rating), 0) / (restos.length || 1);

    const setText = (sel, val) => {
      const el = qs(sel);
      if (el) el.textContent = val;
    };

    setText("[data-stat-restaurants]", restos.length);
    setText("[data-stat-users]", users.length);
    setText("[data-stat-reviews]", reviews.length);
    setText("[data-stat-rating]", avg.toFixed(1));

    const restoRows = restos
      .map(
        (r) => `
        <tr>
          ${thumbCell(r)}
          <td><strong>${r.name}</strong></td>
          <td>${r.location}</td>
          <td>${r.cuisine}</td>
          <td>${r.rating}</td>
          <td>${window.TT.priceRange(r)}</td>
          <td><span class="badge ${r.featured ? "on" : ""}">${r.featured ? "Featured" : "Listed"}</span></td>
          <td class="actions-cell">
            <button type="button" class="btn tiny" data-edit-resto="${r.id}">Edit</button>
            <button type="button" class="btn tiny danger" data-del-resto="${r.id}">Delete</button>
          </td>
        </tr>`
      )
      .join("");

    const previewRows = restos
      .slice(0, 6)
      .map(
        (r) => `
        <tr>
          ${thumbCell(r)}
          <td><strong>${r.name}</strong></td>
          <td>${r.location}</td>
          <td>${r.cuisine}</td>
          <td>${r.rating}</td>
          <td>${window.TT.priceRange(r)}</td>
          <td><span class="badge ${r.featured ? "on" : ""}">${r.featured ? "Featured" : "Listed"}</span></td>
        </tr>`
      )
      .join("");

    const table = qs("[data-admin-restaurants]");
    if (table) {
      table.innerHTML = restoRows;
      qsa("[data-edit-resto]", table).forEach((btn) => {
        btn.addEventListener("click", () => fillRestoForm(btn.getAttribute("data-edit-resto")));
      });
      qsa("[data-del-resto]", table).forEach((btn) => {
        btn.addEventListener("click", () => {
          if (!confirm("Delete this restaurant and its reviews?")) return;
          window.TTStore.deleteRestaurant(btn.getAttribute("data-del-resto"));
          renderAdminDashboard();
        });
      });
    }

    const preview = qs("[data-admin-restaurants-preview]");
    if (preview) preview.innerHTML = previewRows;

    const reviewTable = qs("[data-admin-reviews]");
    if (reviewTable) {
      reviewTable.innerHTML = reviews
        .map(
          (r) => `
        <tr>
          <td>${r.user}</td>
          <td>${r.restaurant}</td>
          <td>${r.rating}</td>
          <td>${window.TT.formatDate(r.date)}</td>
          <td class="clip">${r.text}</td>
          <td><button type="button" class="btn tiny danger" data-del-review="${r.id}">Remove</button></td>
        </tr>`
        )
        .join("");

      qsa("[data-del-review]", reviewTable).forEach((btn) => {
        btn.addEventListener("click", () => {
          if (!confirm("Remove this review?")) return;
          window.TTStore.deleteReview(btn.getAttribute("data-del-review"));
          renderAdminDashboard();
        });
      });
    }

    const userTable = qs("[data-admin-users]");
    if (userTable) {
      userTable.innerHTML = users
        .map(
          (u) => `
        <tr>
          <td>${u.name}</td>
          <td>${u.email}</td>
          <td><span class="badge ${u.role === "admin" ? "on" : ""}">${u.role}</span></td>
          <td>
            ${
              u.role === "admin"
                ? "—"
                : `<button type="button" class="btn tiny danger" data-del-user="${u.id}">Remove</button>`
            }
          </td>
        </tr>`
        )
        .join("");

      qsa("[data-del-user]", userTable).forEach((btn) => {
        btn.addEventListener("click", () => {
          if (!confirm("Remove this user?")) return;
          window.TTAuth.deleteUser(btn.getAttribute("data-del-user"));
          renderAdminDashboard();
        });
      });
    }

    const cuisineChart = qs("[data-cuisine-bars]");
    if (cuisineChart) {
      const counts = {};
      restos.forEach((r) => {
        const key = r.cuisine.split(" ")[0];
        counts[key] = (counts[key] || 0) + 1;
      });
      const max = Math.max(...Object.values(counts), 1);
      cuisineChart.innerHTML = Object.entries(counts)
        .map(
          ([name, count]) => `
        <div class="bar-row">
          <span>${name}</span>
          <div class="bar-track"><div class="bar-fill" style="width:${(count / max) * 100}%"></div></div>
          <strong>${count}</strong>
        </div>`
        )
        .join("");
    }
  }

  function setPreview(el, src) {
    if (!el) return;
    el.innerHTML = src
      ? `<img src="${src}" alt="Preview">`
      : `<span class="muted">No image yet</span>`;
  }

  function fillRestoForm(id) {
    const form = qs("#admin-resto-form");
    if (!form) return;
    const r = window.TTStore.getRestaurant(id);
    if (!r) return;

    form.id.value = r.id;
    form.name.value = r.name;
    form.cuisine.value = r.cuisine;
    form.location.value = r.location;
    form.city.value = r.city || "Nairobi";
    form.phone.value = r.phone || "";
    form.hours.value = r.hours || "";
    form.priceTier.value = r.priceTier || "mid";
    form.priceMin.value = r.priceMin;
    form.priceMax.value = r.priceMax;
    form.tags.value = (r.tags || []).join(", ");
    form.description.value = r.description || "";
    form.featured.checked = !!r.featured;
    form.image.value = r.image || "";
    form.logo.value = r.logo || "";

    setPreview(qs("[data-cover-preview]"), mediaSrc(r.image, "../"));
    setPreview(qs("[data-logo-preview]"), r.logo ? mediaSrc(r.logo, "../") : "");

    const title = qs("[data-resto-form-title]");
    if (title) title.textContent = "Edit restaurant";
    const reset = qs("[data-resto-reset]");
    if (reset) reset.hidden = false;
    const submit = qs("[data-resto-submit]");
    if (submit) submit.textContent = "Update restaurant";

    // switch to restaurants tab
    const tabBtn = qs('[data-tab="restaurants"]');
    if (tabBtn) tabBtn.click();
    form.scrollIntoView({ behavior: "smooth", block: "start" });
  }

  function resetRestoForm() {
    const form = qs("#admin-resto-form");
    if (!form) return;
    form.reset();
    form.id.value = "";
    form.image.value = "";
    form.logo.value = "";
    form.city.value = "Nairobi";
    setPreview(qs("[data-cover-preview]"), "");
    setPreview(qs("[data-logo-preview]"), "");
    const title = qs("[data-resto-form-title]");
    if (title) title.textContent = "Add restaurant";
    const reset = qs("[data-resto-reset]");
    if (reset) reset.hidden = true;
    const submit = qs("[data-resto-submit]");
    if (submit) submit.textContent = "Save restaurant";
    const msg = qs("[data-resto-msg]");
    if (msg) {
      msg.textContent = "";
      msg.className = "form-msg";
    }
  }

  function initAdminRestaurantForm() {
    const form = qs("#admin-resto-form");
    if (!form) return;

    const msg = qs("[data-resto-msg]");
    const logoInput = form.logoFile;
    const coverInput = form.coverFile;

    logoInput.addEventListener("change", async () => {
      const file = logoInput.files[0];
      if (!file) return;
      try {
        const dataUrl = await window.TTStore.fileToDataUrl(file, 320, 0.8);
        form.logo.value = dataUrl;
        setPreview(qs("[data-logo-preview]"), dataUrl);
      } catch (err) {
        msg.textContent = err.message;
        msg.className = "form-msg error";
      }
    });

    coverInput.addEventListener("change", async () => {
      const file = coverInput.files[0];
      if (!file) return;
      try {
        const dataUrl = await window.TTStore.fileToDataUrl(file, 1000, 0.72);
        form.image.value = dataUrl;
        setPreview(qs("[data-cover-preview]"), dataUrl);
      } catch (err) {
        msg.textContent = err.message;
        msg.className = "form-msg error";
      }
    });

    qs("[data-resto-reset]")?.addEventListener("click", resetRestoForm);

    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      if (!form.name.value.trim() || !form.cuisine.value.trim() || !form.location.value.trim()) {
        msg.textContent = "Name, cuisine, and location are required.";
        msg.className = "form-msg error";
        return;
      }
      if (!form.description.value.trim()) {
        msg.textContent = "Please add a short description.";
        msg.className = "form-msg error";
        return;
      }

      const payload = {
        id: form.id.value || undefined,
        name: form.name.value,
        cuisine: form.cuisine.value,
        location: form.location.value,
        city: form.city.value,
        phone: form.phone.value,
        hours: form.hours.value,
        priceTier: form.priceTier.value,
        priceMin: form.priceMin.value,
        priceMax: form.priceMax.value,
        tags: form.tags.value,
        description: form.description.value,
        featured: form.featured.checked,
        image: form.image.value || "images/javahouse.jpg",
        logo: form.logo.value || "",
      };

      window.TTStore.saveRestaurant(payload);
      msg.textContent = form.id.value ? "Restaurant updated." : "Restaurant added.";
      msg.className = "form-msg success";
      resetRestoForm();
      renderAdminDashboard();
    });
  }

  function initAdminTabs() {
    const nav = qs("[data-admin-tabs]");
    if (!nav) return;

    const titles = {
      overview: "Dashboard",
      restaurants: "Restaurants",
      reviews: "Reviews",
      users: "Users",
    };

    function showTab(id) {
      qsa("[data-tab]", nav).forEach((btn) => {
        btn.classList.toggle("active", btn.getAttribute("data-tab") === id);
      });

      qsa("[data-tab-panel]").forEach((panel) => {
        const match = panel.getAttribute("data-tab-panel") === id;
        panel.classList.toggle("is-active", match);
        panel.hidden = !match;
      });

      const title = qs("[data-admin-title]");
      if (title) title.textContent = titles[id] || "Dashboard";
    }

    qsa("[data-tab]", nav).forEach((btn) => {
      btn.addEventListener("click", () => {
        showTab(btn.getAttribute("data-tab"));
      });
    });

    const hash = (window.location.hash || "").replace("#", "");
    if (titles[hash]) showTab(hash);

    window.TTAdminTabs = { showTab };
  }

  function initAdminDashboard() {
    if (!qs("[data-admin-root]")) return;
    if (!requireAdmin()) return;
    initAuthUIAdmin();
    initAdminTabs();
    initAdminRestaurantForm();
    renderAdminDashboard();
  }

  document.addEventListener("DOMContentLoaded", () => {
    initNavbar();
    initAuthUI();
    initSmoothAnchors();
    initExplore();
    initFindTable();
    renderFeatured();
    renderCuisine();
    renderHomeReviews();
    initReveal();
    initLoginForm();
    initRegisterForm();
    initRestaurantsPage();
    initRestaurantDetail();
    initFavoritesPage();
    initReviewsPage();
    initContactForm();
    initAdminDashboard();
  });
})();
