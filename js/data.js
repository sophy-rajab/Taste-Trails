/* Taste Trails — seed data + localStorage store (swap for PHP/MySQL later) */
(function () {
  const RESTO_KEY = "tt_restaurants";
  const REVIEW_KEY = "tt_reviews";
  const SEED_VERSION = "tt_seed_v2";

  const defaultRestaurants = [
    {
      id: "java-house",
      name: "Java House",
      image: "images/javahouse.jpg",
      logo: "",
      rating: 4.8,
      reviews: 312,
      location: "Nairobi CBD",
      city: "Nairobi",
      cuisine: "Continental",
      priceTier: "mid",
      priceMin: 800,
      priceMax: 2500,
      tags: ["Breakfast", "Coffee", "Wi‑Fi"],
      description:
        "A Kenyan favourite for all-day brunch, specialty coffee, and continental plates in the heart of Nairobi.",
      hours: "6:30 AM – 10:00 PM",
      phone: "+254 700 000 101",
      featured: true,
    },
    {
      id: "artcaffe",
      name: "Artcaffé",
      image: "images/artcaffe.jpg",
      logo: "",
      rating: 4.7,
      reviews: 248,
      location: "Westlands",
      city: "Nairobi",
      cuisine: "Italian & Café",
      priceTier: "mid",
      priceMin: 900,
      priceMax: 2800,
      tags: ["Pastries", "Pasta", "Brunch"],
      description:
        "Elegant café dining with handmade pastries, Italian classics, and a calm Westlands atmosphere.",
      hours: "7:00 AM – 9:30 PM",
      phone: "+254 700 000 102",
      featured: true,
    },
    {
      id: "cjs",
      name: "CJ's",
      image: "images/cjs.jpg",
      logo: "",
      rating: 4.6,
      reviews: 189,
      location: "Kilimani",
      city: "Nairobi",
      cuisine: "American Grill",
      priceTier: "mid",
      priceMin: 1000,
      priceMax: 3200,
      tags: ["Burgers", "Grill", "Family"],
      description:
        "Casual American-style grill known for burgers, steaks, and a lively Kilimani crowd.",
      hours: "11:00 AM – 11:00 PM",
      phone: "+254 700 000 103",
      featured: true,
    },
    {
      id: "nyama-mama",
      name: "Nyama Mama",
      image: "images/nyamamama.jpg",
      logo: "",
      rating: 4.5,
      reviews: 167,
      location: "Lavington",
      city: "Nairobi",
      cuisine: "Kenyan",
      priceTier: "mid",
      priceMin: 700,
      priceMax: 2200,
      tags: ["Nyama Choma", "Local", "Share plates"],
      description:
        "Celebrating Kenyan flavours with modern plating — nyama choma, sukuma, and bold local sauces.",
      hours: "12:00 PM – 11:00 PM",
      phone: "+254 700 000 104",
      featured: false,
    },
    {
      id: "talisman",
      name: "Talisman",
      image: "images/talisman.jpg",
      logo: "",
      rating: 4.9,
      reviews: 421,
      location: "Karen",
      city: "Nairobi",
      cuisine: "Fusion",
      priceTier: "upscale",
      priceMin: 2500,
      priceMax: 6500,
      tags: ["Fine dining", "Garden", "Cocktails"],
      description:
        "Garden dining in Karen with inventive fusion dishes and one of Nairobi’s most loved atmospheres.",
      hours: "12:00 PM – 10:30 PM",
      phone: "+254 700 000 105",
      featured: false,
    },
    {
      id: "habesha",
      name: "Habesha Restaurant",
      image: "images/habesha.jpg",
      logo: "",
      rating: 4.4,
      reviews: 98,
      location: "South B",
      city: "Nairobi",
      cuisine: "Ethiopian",
      priceTier: "budget",
      priceMin: 500,
      priceMax: 1500,
      tags: ["Injera", "Spicy", "Shared"],
      description:
        "Authentic Ethiopian dining with injera platters, berbere spices, and warm hospitality.",
      hours: "11:00 AM – 10:00 PM",
      phone: "+254 700 000 106",
      featured: false,
    },
    {
      id: "carnivore",
      name: "Carnivore",
      image: "images/carnivore.jpg",
      logo: "",
      rating: 4.7,
      reviews: 540,
      location: "Langata",
      city: "Nairobi",
      cuisine: "Grill",
      priceTier: "upscale",
      priceMin: 3500,
      priceMax: 7500,
      tags: ["Nyama", "Buffet", "Tourist favourite"],
      description:
        "Iconic all-you-can-eat meat experience — game meats, grills, and a lively Nairobi classic.",
      hours: "12:00 PM – 11:00 PM",
      phone: "+254 700 000 107",
      featured: false,
    },
    {
      id: "about-thyme",
      name: "About Thyme",
      image: "images/aboutthyme.jpg",
      logo: "",
      rating: 4.6,
      reviews: 203,
      location: "Lavington",
      city: "Nairobi",
      cuisine: "Continental",
      priceTier: "mid",
      priceMin: 1200,
      priceMax: 3500,
      tags: ["Garden", "Brunch", "Date night"],
      description:
        "Leafy garden restaurant with thoughtful continental plates and a relaxed Lavington vibe.",
      hours: "8:00 AM – 10:00 PM",
      phone: "+254 700 000 108",
      featured: false,
    },
    {
      id: "mama-o",
      name: "Mama Oliech",
      image: "images/mamao.jpg",
      logo: "",
      rating: 4.5,
      reviews: 276,
      location: "Kilimani",
      city: "Nairobi",
      cuisine: "Kenyan",
      priceTier: "budget",
      priceMin: 400,
      priceMax: 1200,
      tags: ["Fish", "Local", "Casual"],
      description:
        "Famous for fresh fish and Kenyan comfort food — affordable, flavourful, and always busy.",
      hours: "11:00 AM – 10:30 PM",
      phone: "+254 700 000 109",
      featured: false,
    },
  ];

  const defaultReviews = [
    {
      id: 1,
      restaurantId: "java-house",
      restaurant: "Java House",
      user: "Amina K.",
      userId: null,
      rating: 5,
      food: 5,
      service: 5,
      price: 4,
      environment: 5,
      date: "2026-07-20",
      text: "Best breakfast in CBD. The avocado toast and pour-over coffee never miss.",
    },
    {
      id: 2,
      restaurantId: "artcaffe",
      restaurant: "Artcaffé",
      user: "Brian O.",
      userId: null,
      rating: 5,
      food: 5,
      service: 4,
      price: 4,
      environment: 5,
      date: "2026-07-18",
      text: "Quiet enough to work, food good enough to stay for lunch. Westlands gem.",
    },
    {
      id: 3,
      restaurantId: "cjs",
      restaurant: "CJ's",
      user: "Faith W.",
      userId: null,
      rating: 4,
      food: 4,
      service: 4,
      price: 4,
      environment: 4,
      date: "2026-07-15",
      text: "Burgers are solid and service is quick. Great for groups after work.",
    },
    {
      id: 4,
      restaurantId: "talisman",
      restaurant: "Talisman",
      user: "Daniel M.",
      userId: null,
      rating: 5,
      food: 5,
      service: 5,
      price: 3,
      environment: 5,
      date: "2026-07-12",
      text: "Special occasion done right. The garden setting alone is worth the trip to Karen.",
    },
    {
      id: 5,
      restaurantId: "nyama-mama",
      restaurant: "Nyama Mama",
      user: "Grace N.",
      userId: null,
      rating: 4,
      food: 5,
      service: 4,
      price: 4,
      environment: 4,
      date: "2026-07-10",
      text: "Proper Kenyan flavours with a modern twist. Portions are generous.",
    },
    {
      id: 6,
      restaurantId: "habesha",
      restaurant: "Habesha Restaurant",
      user: "Samuel T.",
      userId: null,
      rating: 5,
      food: 5,
      service: 5,
      price: 5,
      environment: 4,
      date: "2026-07-08",
      text: "Incredible injera and friendly staff. Affordable and authentic.",
    },
  ];

  const defaultCuisines = [
    { name: "Kenyan", count: 42, accent: "#c45c26" },
    { name: "Continental", count: 38, accent: "#d97706" },
    { name: "Italian", count: 27, accent: "#b45309" },
    { name: "Indian", count: 31, accent: "#ea580c" },
    { name: "Chinese", count: 22, accent: "#c2410c" },
    { name: "Ethiopian", count: 15, accent: "#9a3412" },
    { name: "Seafood", count: 19, accent: "#0e7490" },
    { name: "Grill", count: 34, accent: "#b91c1c" },
  ];

  function read(key, fallback) {
    try {
      const raw = localStorage.getItem(key);
      if (!raw) return null;
      return JSON.parse(raw);
    } catch {
      return fallback;
    }
  }

  function write(key, value) {
    localStorage.setItem(key, JSON.stringify(value));
  }

  function ensureSeed() {
    if (!localStorage.getItem(SEED_VERSION) || !localStorage.getItem(RESTO_KEY)) {
      write(RESTO_KEY, defaultRestaurants);
      write(REVIEW_KEY, defaultReviews);
      localStorage.setItem(SEED_VERSION, "1");
    }
  }

  function slugify(name) {
    return (
      name
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/(^-|-$)/g, "") || "restaurant"
    );
  }

  function recomputeRatings(restaurants, reviews) {
    return restaurants.map((r) => {
      const mine = reviews.filter((rev) => rev.restaurantId === r.id);
      if (!mine.length) {
        return { ...r, rating: Number(r.rating) || 0, reviews: 0 };
      }
      const avg =
        mine.reduce((sum, rev) => sum + Number(rev.rating), 0) / mine.length;
      return {
        ...r,
        rating: Math.round(avg * 10) / 10,
        reviews: mine.length,
      };
    });
  }

  function syncData() {
    ensureSeed();
    let restaurants = read(RESTO_KEY, defaultRestaurants) || defaultRestaurants;
    let reviews = read(REVIEW_KEY, defaultReviews) || defaultReviews;
    restaurants = recomputeRatings(restaurants, reviews);
    write(RESTO_KEY, restaurants);
    window.TT_DATA = {
      restaurants,
      reviews,
      cuisines: defaultCuisines,
    };
    return window.TT_DATA;
  }

  window.TTStore = {
    refresh: syncData,

    getRestaurants() {
      return syncData().restaurants;
    },

    getReviews() {
      return syncData().reviews;
    },

    getRestaurant(id) {
      return syncData().restaurants.find((r) => r.id === id);
    },

    saveRestaurant(payload) {
      const data = syncData();
      const tags = Array.isArray(payload.tags)
        ? payload.tags
        : String(payload.tags || "")
            .split(",")
            .map((t) => t.trim())
            .filter(Boolean);

      const next = {
        id: payload.id || slugify(payload.name) + "-" + Date.now(),
        name: payload.name.trim(),
        image: payload.image || "images/javahouse.jpg",
        logo: payload.logo || "",
        rating: Number(payload.rating) || 0,
        reviews: Number(payload.reviews) || 0,
        location: payload.location.trim(),
        city: payload.city.trim() || "Nairobi",
        cuisine: payload.cuisine.trim(),
        priceTier: payload.priceTier || "mid",
        priceMin: Number(payload.priceMin) || 0,
        priceMax: Number(payload.priceMax) || 0,
        tags,
        description: payload.description.trim(),
        hours: payload.hours.trim(),
        phone: payload.phone.trim(),
        featured: !!payload.featured,
      };

      const idx = data.restaurants.findIndex((r) => r.id === next.id);
      if (idx >= 0) {
        next.rating = data.restaurants[idx].rating;
        next.reviews = data.restaurants[idx].reviews;
        data.restaurants[idx] = next;
      } else {
        data.restaurants.unshift(next);
      }

      write(RESTO_KEY, data.restaurants);
      syncData();
      return next;
    },

    deleteRestaurant(id) {
      const data = syncData();
      write(
        RESTO_KEY,
        data.restaurants.filter((r) => r.id !== id)
      );
      write(
        REVIEW_KEY,
        data.reviews.filter((r) => r.restaurantId !== id)
      );
      syncData();
      return true;
    },

    addReview({ restaurantId, user, userId, rating, food, service, price, environment, text }) {
      const data = syncData();
      const resto = data.restaurants.find((r) => r.id === restaurantId);
      if (!resto) return { ok: false, message: "Restaurant not found." };
      if (!text || !text.trim()) return { ok: false, message: "Please write a review." };

      const overall = Number(rating) ||
        Math.round(
          ((Number(food) + Number(service) + Number(price) + Number(environment)) / 4) * 10
        ) / 10;

      const review = {
        id: Date.now(),
        restaurantId,
        restaurant: resto.name,
        user: user || "Guest",
        userId: userId || null,
        rating: Math.min(5, Math.max(1, overall)),
        food: Number(food) || overall,
        service: Number(service) || overall,
        price: Number(price) || overall,
        environment: Number(environment) || overall,
        date: new Date().toISOString().slice(0, 10),
        text: text.trim(),
      };

      data.reviews.unshift(review);
      write(REVIEW_KEY, data.reviews);
      syncData();
      return { ok: true, review };
    },

    deleteReview(id) {
      const data = syncData();
      write(
        REVIEW_KEY,
        data.reviews.filter((r) => String(r.id) !== String(id))
      );
      syncData();
      return true;
    },

    /** Compress image file to data URL for localStorage */
    fileToDataUrl(file, maxWidth = 900, quality = 0.72) {
      return new Promise((resolve, reject) => {
        if (!file) return resolve("");
        if (!file.type.startsWith("image/")) {
          return reject(new Error("Please choose an image file."));
        }
        if (file.size > 4 * 1024 * 1024) {
          return reject(new Error("Image must be under 4MB."));
        }

        const reader = new FileReader();
        reader.onerror = () => reject(new Error("Could not read image."));
        reader.onload = () => {
          const img = new Image();
          img.onload = () => {
            const scale = Math.min(1, maxWidth / img.width);
            const w = Math.round(img.width * scale);
            const h = Math.round(img.height * scale);
            const canvas = document.createElement("canvas");
            canvas.width = w;
            canvas.height = h;
            const ctx = canvas.getContext("2d");
            ctx.drawImage(img, 0, 0, w, h);
            resolve(canvas.toDataURL("image/jpeg", quality));
          };
          img.onerror = () => reject(new Error("Invalid image."));
          img.src = reader.result;
        };
        reader.readAsDataURL(file);
      });
    },
  };

  syncData();

  window.TT = {
    getRestaurant(id) {
      return window.TTStore.getRestaurant(id);
    },
    stars(rating) {
      const full = Math.floor(rating);
      const half = rating - full >= 0.4;
      return "★".repeat(full) + (half ? "½" : "") + "☆".repeat(5 - full - (half ? 1 : 0));
    },
    formatDate(iso) {
      return new Date(iso).toLocaleDateString("en-KE", {
        year: "numeric",
        month: "short",
        day: "numeric",
      });
    },
    formatKsh(amount) {
      return "KSh " + Number(amount).toLocaleString("en-KE");
    },
    priceRange(r) {
      return this.formatKsh(r.priceMin) + " – " + this.formatKsh(r.priceMax);
    },
    priceBadge(r) {
      return "from " + this.formatKsh(r.priceMin);
    },
    tierLabel(tier) {
      const map = { budget: "Budget", mid: "Moderate", upscale: "Upscale" };
      return map[tier] || tier;
    },
  };
})();
