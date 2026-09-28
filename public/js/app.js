/* Trinetraa Optician — shared client behavior.
 * Ports CartContext, CompareContext, CustomerAuthContext + PublicLayout
 * interactivity from the Next.js app to vanilla JS. */
(function () {
  "use strict";

  // ── CSRF token helper ───────────────────────────────────────────────────
  function getCsrf() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  // ── Fetch wrapper that always sends CSRF + JSON headers ─────────────────
  function apiFetch(url, opts) {
    opts = opts || {};
    var headers = { 'X-CSRF-TOKEN': getCsrf(), 'X-Requested-With': 'XMLHttpRequest' };
    if (opts.body !== undefined && !(opts.body instanceof FormData)) {
      headers['Content-Type'] = 'application/json';
    }
    opts.headers = Object.assign(headers, opts.headers || {});
    return fetch(url, opts);
  }
  window.apiFetch = apiFetch;

  // ── Cart (localStorage-backed) ──────────────────────────────────────────
  const Cart = {
    key: "pp-cart",
    read() { try { return JSON.parse(localStorage.getItem(this.key)) || []; } catch { return []; } },
    write(items) { localStorage.setItem(this.key, JSON.stringify(items)); this.sync(); },
    get items() { return this.read(); },
    get count() { return this.read().reduce((s, i) => s + i.quantity, 0); },
    get total() { return this.read().reduce((s, i) => s + (i.salePrice || i.sale_price || 0) * i.quantity, 0); },
    add(product) {
      const items = this.read();
      const ex = items.find((i) => i.id === product.id);
      if (ex) ex.quantity += 1; else items.push(Object.assign({}, product, { quantity: 1 }));
      this.write(items);
    },
    updateQty(id, qty) { const items = this.read().map((i) => i.id === id ? Object.assign(i, { quantity: qty }) : i); this.write(items); },
    remove(id) { this.write(this.read().filter((i) => i.id !== id)); },
    clear() { this.write([]); },
    sync() {
      const badge = document.getElementById("cartBadge");
      if (badge) { const c = this.count; badge.textContent = c; badge.style.display = c > 0 ? "" : "none"; }
      document.dispatchEvent(new CustomEvent("cart:change"));
    },
  };

  // ── Compare (localStorage-backed, max 3) ────────────────────────────────
  const MAX_COMPARE = 3;
  const Compare = {
    key: "pp-compare",
    read() { try { return JSON.parse(localStorage.getItem(this.key)) || []; } catch { return []; } },
    write(items) { localStorage.setItem(this.key, JSON.stringify(items)); this.render(); document.dispatchEvent(new CustomEvent("compare:change")); },
    get items() { return this.read(); },
    add(product) {
      const items = this.read();
      if (items.find((p) => p.id === product.id)) return;
      if (items.length >= MAX_COMPARE) { alert('You can compare up to 3 products.'); return; }
      items.push(product); this.write(items);
    },
    remove(id) { this.write(this.read().filter((p) => p.id !== id)); },
    clear() { this.write([]); },
    isIn(id) { return this.read().some((p) => p.id === id); },
    max: MAX_COMPARE,
    render() {
      const bar = document.getElementById("compareBar");
      const wrap = document.getElementById("compareItems");
      if (!bar || !wrap) return;
      const items = this.read();
      if (items.length === 0) { bar.style.display = "none"; return; }
      bar.style.display = "";
      wrap.innerHTML = '<span style="font-weight:700;font-size:.875rem;white-space:nowrap">⚖️ Compare (' + items.length + '/3):</span>' +
        items.map((p) => '<div style="display:flex;align-items:center;gap:.4rem;background:rgba(255,255,255,0.12);border-radius:8px;padding:.3rem .6rem;font-size:.8rem"><span>' +
          (p.name || "") + '</span><button data-cmp-remove="' + p.id + '" style="background:none;border:none;color:rgba(255,255,255,0.6);cursor:pointer;font-size:1rem;line-height:1;padding:0">×</button></div>').join("");
      wrap.querySelectorAll("[data-cmp-remove]").forEach((b) =>
        b.addEventListener("click", () => Compare.remove(Number(b.getAttribute("data-cmp-remove")))));
    },
  };

  // ── Customer auth ───────────────────────────────────────────────────────
  const Auth = {
    async login(email, password) {
      const res = await apiFetch("/api/customer/auth/login", { method: "POST", body: JSON.stringify({ email, password }) });
      const data = await res.json();
      if (!res.ok) throw new Error(data.message || "Login failed.");
      localStorage.setItem("cust_session", "1");
      return data.data;
    },
    async register(payload) {
      const res = await apiFetch("/api/customer/auth/register", { method: "POST", body: JSON.stringify(payload) });
      const data = await res.json();
      if (!res.ok) throw new Error(data.message || "Registration failed.");
      localStorage.setItem("cust_session", "1");
      return data.data;
    },
    async logout() {
      await apiFetch("/api/customer/auth/logout", { method: "POST" });
      localStorage.removeItem("cust_session");
      window.location.href = "/";
    },
  };

  window.TrinetraaCart = Cart;
  window.TrinetraaCompare = Compare;
  window.TrinetraaAuth = Auth;

  // ── DOM behavior ────────────────────────────────────────────────────────
  document.addEventListener("DOMContentLoaded", function () {
    Cart.sync();
    Compare.render();

    // dark mode
    const root = document.getElementById("ppRoot");
    // The site is dark-glass by design now; clear any stale dark-mode toggle state.
    if (root) root.classList.remove("pp-dark");
    localStorage.removeItem("pp-dark");

    // scroll: nav shadow + back to top
    const nav = document.getElementById("ppNav");
    const backToTop = document.getElementById("backToTop");
    function onScroll() {
      if (nav) nav.classList.toggle("scrolled", window.scrollY > 40);
      if (backToTop) backToTop.style.display = window.scrollY > 300 ? "" : "none";
    }
    window.addEventListener("scroll", onScroll, { passive: true });
    onScroll();
    if (backToTop) backToTop.addEventListener("click", () => window.scrollTo({ top: 0, behavior: "smooth" }));

    // scroll: hide nav on scroll-down, reveal on scroll-up
    if (nav) {
      let lastScrollTop = 0;
      const scrollThreshold = 50;
      window.addEventListener("scroll", () => {
        if (nav.classList.contains("mobile-open")) return;
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        if (Math.abs(scrollTop - lastScrollTop) <= scrollThreshold) return;
        nav.classList.toggle("pp-nav--hidden", scrollTop > lastScrollTop && scrollTop > 120);
        lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
      }, { passive: true });
    }

    // per-character hover animation on desktop nav links
    document.querySelectorAll(".pp-nav__links > li > a").forEach((link) => {
      const text = link.textContent;
      link.textContent = "";
      text.split("").forEach((char, i) => {
        const span = document.createElement("span");
        span.textContent = char === " " ? " " : char;
        span.classList.add("pp-nav__chara");
        span.style.transitionDelay = `${i * 25}ms`;
        link.appendChild(span);
      });
      link.addEventListener("mouseenter", () => {
        link.querySelectorAll(".pp-nav__chara").forEach((span, i) => {
          span.classList.add("pp-nav__chara--down");
          span.style.transitionDelay = `${i * 25}ms`;
        });
      });
      link.addEventListener("mouseleave", () => {
        link.querySelectorAll(".pp-nav__chara").forEach((span, i) => {
          span.classList.remove("pp-nav__chara--down");
          span.style.transitionDelay = `${i * 25}ms`;
        });
      });
    });

    // mobile menu
    const hamburger = document.getElementById("hamburger");
    const mobile = document.getElementById("ppMobile");
    if (hamburger && nav) hamburger.addEventListener("click", () => { nav.classList.toggle("mobile-open"); if (mobile) mobile.classList.toggle("open"); });
    const mobileToolsBtn = document.getElementById("mobileToolsBtn");
    const mobileTools = document.getElementById("mobileTools");
    if (mobileToolsBtn && mobileTools) mobileToolsBtn.addEventListener("click", () => { mobileTools.style.display = mobileTools.style.display === "none" ? "" : "none"; });

    // tools dropdown (hover handled by CSS :hover, also toggle .open for click)
    const ddWrap = document.getElementById("toolsDropdownWrap");
    if (ddWrap) {
      const dd = ddWrap.querySelector(".pp-nav__dropdown");
      ddWrap.addEventListener("mouseenter", () => dd && dd.classList.add("open"));
      ddWrap.addEventListener("mouseleave", () => dd && dd.classList.remove("open"));
    }

    // scroll reveal
    if ("IntersectionObserver" in window) {
      const obs = new IntersectionObserver((entries) => entries.forEach((e) => { if (e.isIntersecting) { e.target.classList.add("in-view"); obs.unobserve(e.target); } }), { threshold: 0.1 });
      document.querySelectorAll(".animate-on-scroll").forEach((el) => obs.observe(el));
    }

    // newsletter — use apiFetch for CSRF header
    const nlForm = document.getElementById("newsletterForm");
    if (nlForm) nlForm.addEventListener("submit", async function (e) {
      e.preventDefault();
      const email = nlForm.email.value;
      if (!email) return;
      try {
        const res = await apiFetch("/api/public/newsletter", { method: "POST", body: JSON.stringify({ email }) });
        if (res.ok) { document.getElementById("newsletterSuccess").style.display = "inline-block"; nlForm.style.display = "none"; }
        else { document.getElementById("newsletterError").style.display = "block"; }
      } catch { document.getElementById("newsletterError").style.display = "block"; }
    });

    // Mark lazy images as loaded (removes shimmer)
    document.querySelectorAll('img[loading="lazy"]').forEach(function (img) {
      if (img.complete) { img.classList.add('loaded'); }
      else { img.addEventListener('load', function () { img.classList.add('loaded'); }); }
    });

    // WhatsApp FAB — update href dynamically from site config
    const waFab = document.getElementById("waFab");
    if (waFab && window.__SITE && window.__SITE.whatsapp) {
      waFab.href = "https://wa.me/" + window.__SITE.whatsapp + "?text=Hello%20Trinetraa%20Optician%2C%20I%20have%20an%20enquiry.";
    }
  });

  // service worker
  if ("serviceWorker" in navigator) {
    window.addEventListener("load", function () { navigator.serviceWorker.register("/sw.js").catch(function () {}); });
  }
})();
