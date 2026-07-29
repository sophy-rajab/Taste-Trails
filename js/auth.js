(function () {
  const USERS_KEY = "tt_users";
  const SESSION_KEY = "tt_session";

  const defaultUsers = [
    {
      id: "admin-1",
      name: "Taste Trails Admin",
      email: "admin@tastetrails.co.ke",
      password: "admin123",
      role: "admin",
    },
    {
      id: "user-1",
      name: "Sophia Rajab",
      email: "sophia@example.com",
      password: "taste123",
      role: "user",
    },
  ];

  function readUsers() {
    try {
      const raw = localStorage.getItem(USERS_KEY);
      if (!raw) {
        localStorage.setItem(USERS_KEY, JSON.stringify(defaultUsers));
        return [...defaultUsers];
      }
      return JSON.parse(raw);
    } catch {
      return [...defaultUsers];
    }
  }

  function writeUsers(users) {
    localStorage.setItem(USERS_KEY, JSON.stringify(users));
  }

  function setSession(user) {
    const session = {
      id: user.id,
      name: user.name,
      email: user.email,
      role: user.role,
    };
    localStorage.setItem(SESSION_KEY, JSON.stringify(session));
    return session;
  }

  window.TTAuth = {
    getSession() {
      try {
        const raw = localStorage.getItem(SESSION_KEY);
        return raw ? JSON.parse(raw) : null;
      } catch {
        return null;
      }
    },

    isLoggedIn() {
      return !!this.getSession();
    },

    isAdmin() {
      const s = this.getSession();
      return !!(s && s.role === "admin");
    },

    register({ name, email, password }) {
      const users = readUsers();
      const normalized = email.trim().toLowerCase();

      if (!name.trim() || !normalized || !password) {
        return { ok: false, message: "Please fill in all fields." };
      }
      if (password.length < 6) {
        return { ok: false, message: "Password must be at least 6 characters." };
      }
      if (users.some((u) => u.email === normalized)) {
        return { ok: false, message: "An account with this email already exists." };
      }

      const user = {
        id: "user-" + Date.now(),
        name: name.trim(),
        email: normalized,
        password,
        role: "user",
      };
      users.push(user);
      writeUsers(users);
      setSession(user);
      return { ok: true, user: this.getSession() };
    },

    login({ email, password }) {
      const users = readUsers();
      const normalized = email.trim().toLowerCase();
      const user = users.find(
        (u) => u.email === normalized && u.password === password
      );

      if (!user) {
        return { ok: false, message: "Invalid email or password." };
      }

      setSession(user);
      return { ok: true, user: this.getSession() };
    },

    logout() {
      localStorage.removeItem(SESSION_KEY);
    },

    listUsers() {
      return readUsers().map(({ password, ...rest }) => rest);
    },

    deleteUser(id) {
      const session = this.getSession();
      if (!session || session.role !== "admin") return false;
      if (id === session.id) return false;
      const users = readUsers().filter((u) => u.id !== id);
      writeUsers(users);
      return true;
    },
  };

  const FAV_KEY = "tt_favorites_by_user";

  function readFavMap() {
    try {
      return JSON.parse(localStorage.getItem(FAV_KEY) || "{}");
    } catch {
      return {};
    }
  }

  function writeFavMap(map) {
    localStorage.setItem(FAV_KEY, JSON.stringify(map));
  }

  window.TTFavorites = {
    list() {
      const session = window.TTAuth.getSession();
      if (!session) return [];
      const map = readFavMap();
      return Array.isArray(map[session.id]) ? map[session.id] : [];
    },

    has(restaurantId) {
      return this.list().includes(restaurantId);
    },

    add(restaurantId) {
      const session = window.TTAuth.getSession();
      if (!session) return { ok: false, message: "Please log in first." };
      const map = readFavMap();
      const list = Array.isArray(map[session.id]) ? map[session.id] : [];
      if (!list.includes(restaurantId)) list.push(restaurantId);
      map[session.id] = list;
      writeFavMap(map);
      return { ok: true, list };
    },

    remove(restaurantId) {
      const session = window.TTAuth.getSession();
      if (!session) return { ok: false, message: "Please log in first." };
      const map = readFavMap();
      map[session.id] = (map[session.id] || []).filter((id) => id !== restaurantId);
      writeFavMap(map);
      return { ok: true, list: map[session.id] };
    },

    toggle(restaurantId) {
      if (this.has(restaurantId)) return { ...this.remove(restaurantId), saved: false };
      return { ...this.add(restaurantId), saved: true };
    },
  };
})();