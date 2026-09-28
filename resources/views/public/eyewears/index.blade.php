@extends('layouts.public')
@section('title', 'Eyewear Collection — Glasses, Sunglasses & More | Trinetraa Optician')
@section('meta_description', 'Shop 1000+ prescription glasses, sunglasses, reading glasses and contact lenses at Trinetraa Optician Nashik. Filter by brand, category and price. Free eye test available.')
@php
    // category_slug/brand represent meaningfully distinct, linked-to content
    // (footer links, category cards, brand pages) and are worth their own
    // canonical URL. Pagination/price/sort are UI-only state and are
    // intentionally excluded so those variants canonicalize back to the base.
    $_ewCanonicalParams = array_filter([
        'category_slug' => request()->query('category_slug'),
        'brand' => request()->query('brand'),
    ]);
    $_ewCanonical = 'https://trinetraaoptician.com/eyewears' . ($_ewCanonicalParams ? ('?' . http_build_query($_ewCanonicalParams)) : '');
@endphp
@section('canonical', $_ewCanonical)
@section('content')

<style>
    .ew-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.25rem;
    }
    @media (max-width: 1100px) { .ew-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px)  { .ew-grid { grid-template-columns: repeat(2, 1fr); gap: 0.75rem; } }
    @media (max-width: 400px)  { .ew-grid { grid-template-columns: 1fr; } }

    .ew-layout {
        display: grid;
        grid-template-columns: 240px 1fr;
        gap: 2rem;
        align-items: start;
    }
    @media (max-width: 900px) {
        .ew-layout { grid-template-columns: 1fr; }
        .ew-sidebar-desktop { display: none; }
    }

    @keyframes skeleton-pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }
</style>

{{-- Page header --}}
<div style="padding-top:calc(64px + 2.5rem);padding-bottom:2.5rem;border-bottom:1px solid rgba(255,255,255,0.14)">
    <div class="pp-container">
        <p style="font-size:0.8rem;color:#94A3B8;margin-bottom:0.5rem">
            <a href="/" style="color:#2DD4BF;text-decoration:none">Home</a>
            <span style="margin:0 0.4rem">›</span>
            Eyewear Collection
        </p>
        <h1 style="font-family:var(--pp-font-heading);font-size:clamp(1.75rem, 3vw, 2.4rem);font-weight:800;color:#F8FAFC;letter-spacing:-0.03em;margin:0">
            Eyewear Collection
        </h1>
        <p id="ewHeaderCount" style="color:#8FA3BB;font-size:0.9rem;margin-top:0.4rem"></p>
    </div>
</div>

{{-- Mobile filter toggle --}}
<div style="display:none" class="ew-mobile-filter-toggle">
    <div class="pp-container" style="padding:0.75rem 1rem">
        <button id="ewMobileFilterBtn" style="padding:0.55rem 1rem;border:1.5px solid rgba(255,255,255,0.14);border-radius:8px;background:rgba(255,255,255,0.06);font-family:inherit;font-size:0.85rem;font-weight:600;cursor:pointer">
            ⚙ Filters <span id="ewMobileFilterActive"></span>
        </button>
    </div>
</div>

<section style="padding:2.5rem 0 5rem">
    <div class="pp-container" style="max-width:1280px">
        <div class="ew-layout">

            {{-- Sidebar --}}
            <div class="ew-sidebar-desktop" style="background:rgba(255,255,255,0.06);border-radius:16px;padding:1.5rem;border:1px solid rgba(255,255,255,0.14);position:sticky;top:80px">
                <div id="ewFilterPanel" style="display:flex;flex-direction:column;gap:1.75rem"></div>
            </div>

            {{-- Main content --}}
            <div>
                {{-- Toolbar --}}
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:0.75rem">
                    <div id="ewCountLabel" style="font-size:0.875rem;color:#8FA3BB;font-weight:500">Loading…</div>
                    {{-- Sort dropdown --}}
                    <div id="ewSortWrap" style="position:relative;min-width:190px;user-select:none">
                        <button id="ewSortBtn" style="width:100%;display:flex;align-items:center;justify-content:space-between;gap:0.75rem;padding:0.6rem 1rem;background:#0F766E;color:#fff;border:none;border-radius:10px;font-size:0.875rem;font-weight:600;font-family:inherit;cursor:pointer;transition:border-radius 0.15s">
                            <span id="ewSortLabel">Newest First</span>
                            <svg id="ewSortChevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="transition:transform 0.2s;flex-shrink:0">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </button>
                        <div id="ewSortMenu" style="display:none;position:absolute;top:100%;left:0;right:0;z-index:50;background:rgba(15,23,42,0.98);border:1px solid rgba(255,255,255,0.14);border-top:none;border-radius:0 0 10px 10px;box-shadow:0 8px 24px rgba(0,0,0,0.35);overflow:hidden"></div>
                    </div>
                </div>

                {{-- Grid / states --}}
                <div id="ewResults"></div>

                {{-- Pagination --}}
                <div id="ewPagination"></div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
@verbatim
(function () {
  var SORT_OPTIONS = [
    { value: "newest",     label: "Newest First" },
    { value: "price_asc",  label: "Price: Low to High" },
    { value: "price_desc", label: "Price: High to Low" },
    { value: "name_asc",   label: "Name: A–Z" },
  ];

  var state = {
    eyewears: [],
    categories: [],
    brands: [],
    meta: { page: 1, per_page: 12, total: 0 },
    loading: true,
    page: 1,
    filters: { category_slug: "", brand: "", min_price: "", max_price: "", sort: "newest" },
    sortOpen: false,
  };

  // Respect URL query string for category_slug / brand
  var qs = new URLSearchParams(window.location.search);
  if (qs.get("category_slug")) state.filters.category_slug = qs.get("category_slug");
  if (qs.get("brand")) state.filters.brand = qs.get("brand");

  function inr(n) { return Number(n).toLocaleString("en-IN"); }
  function esc(s) { return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) { return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]; }); }

  function fetchData() {
    state.loading = true;
    renderResults();
    renderToolbar();
    var params = new URLSearchParams({ page: state.page, per_page: 12 });
    if (state.filters.category_slug) params.set("category_slug", state.filters.category_slug);
    if (state.filters.brand)      params.set("brand", state.filters.brand);
    if (state.filters.min_price)  params.set("min_price", state.filters.min_price);
    if (state.filters.max_price)  params.set("max_price", state.filters.max_price);
    if (state.filters.sort)       params.set("sort", state.filters.sort);
    fetch("/api/public/eyewears?" + params)
      .then(function (r) { return r.json(); })
      .then(function (d) {
        state.eyewears = d.data || [];
        state.meta = d.meta || {};
        state.loading = false;
        renderToolbar();
        renderResults();
        renderPagination();
      });
  }

  function setF(k, v) { state.filters[k] = v; state.page = 1; renderFilterPanel(); fetchData(); }
  function clearFilters() { state.filters = { category_slug: "", brand: "", min_price: "", max_price: "", sort: "newest" }; state.page = 1; renderFilterPanel(); renderToolbar(); fetchData(); }
  function hasActiveFilters() { return state.filters.category_slug || state.filters.brand || state.filters.min_price || state.filters.max_price; }

  // ── Toolbar (count + sort) ──
  function renderToolbar() {
    var label = document.getElementById("ewCountLabel");
    if (label) {
      var t = state.meta.total == null ? 0 : state.meta.total;
      label.textContent = state.loading ? "Loading…" : (t + " item" + (t !== 1 ? "s" : ""));
    }
    var header = document.getElementById("ewHeaderCount");
    if (header) header.textContent = state.loading ? "" : ((state.meta.total || 0) + " styles across all categories");
    var mobileActive = document.getElementById("ewMobileFilterActive");
    if (mobileActive) mobileActive.textContent = hasActiveFilters() ? "(active)" : "";
    var sortLabel = document.getElementById("ewSortLabel");
    var current = SORT_OPTIONS.find(function (o) { return o.value === state.filters.sort; }) || SORT_OPTIONS[0];
    if (sortLabel) sortLabel.textContent = current.label;
    var chevron = document.getElementById("ewSortChevron");
    var btn = document.getElementById("ewSortBtn");
    var menu = document.getElementById("ewSortMenu");
    if (chevron) chevron.style.transform = state.sortOpen ? "rotate(180deg)" : "none";
    if (btn) btn.style.borderRadius = state.sortOpen ? "10px 10px 0 0" : "10px";
    if (menu) {
      menu.style.display = state.sortOpen ? "" : "none";
      menu.innerHTML = SORT_OPTIONS.map(function (opt) {
        var sel = opt.value === state.filters.sort;
        return '<button data-sort="' + opt.value + '" style="width:100%;display:block;text-align:left;padding:0.65rem 1rem;background:' + (sel ? "rgba(20,184,166,0.14)" : "transparent") + ';color:' + (sel ? "#2DD4BF" : "#F8FAFC") + ';border:none;border-bottom:1px solid rgba(255,255,255,0.14);font-size:0.875rem;font-weight:' + (sel ? 600 : 400) + ';font-family:inherit;cursor:pointer;transition:background 0.15s">' + esc(opt.label) + '</button>';
      }).join("");
      menu.querySelectorAll("[data-sort]").forEach(function (b) {
        b.addEventListener("click", function () { setF("sort", b.getAttribute("data-sort")); state.sortOpen = false; renderToolbar(); });
        b.addEventListener("mouseenter", function () { if (b.getAttribute("data-sort") !== state.filters.sort) b.style.background = "rgba(255,255,255,0.08)"; });
        b.addEventListener("mouseleave", function () { if (b.getAttribute("data-sort") !== state.filters.sort) b.style.background = "transparent"; });
      });
    }
  }

  // ── Filter panel ──
  function radioDot(active) {
    return '<div data-dot style="width:16px;height:16px;border-radius:50%;flex-shrink:0;border:2px solid ' + (active ? "#2DD4BF" : "rgba(255,255,255,0.14)") + ';background:' + (active ? "#14B8A6" : "transparent") + ';display:flex;align-items:center;justify-content:center;transition:all 0.15s">' + (active ? '<div style="width:5px;height:5px;border-radius:50%;background:#fff"></div>' : '') + '</div>';
  }

  function renderFilterPanel() {
    var panel = document.getElementById("ewFilterPanel");
    if (!panel) return;
    var html = "";

    // Category
    html += '<div><div style="font-size:0.72rem;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.75rem">Category</div>';
    var cats = [{ slug: "", name: "All Categories" }].concat(state.categories);
    cats.forEach(function (c) {
      var active = state.filters.category_slug === c.slug;
      html += '<label data-cat="' + esc(c.slug) + '" style="display:flex;align-items:center;gap:0.6rem;margin-bottom:0.5rem;cursor:pointer">' +
        radioDot(active) +
        '<span style="font-size:0.875rem;color:' + (active ? "#2DD4BF" : "#B8C6D8") + ';font-weight:' + (active ? 600 : 400) + ';cursor:pointer">' + esc(c.name) + '</span></label>';
    });
    html += '</div>';

    // Brand
    if (state.brands.length > 0) {
      html += '<div><div style="font-size:0.72rem;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.75rem">Brand</div>';
      var brandList = [{ name: "", display: "All Brands" }].concat(state.brands.map(function (b) {
        var nm = (typeof b === "string") ? b : b.name;
        return { name: nm, display: nm };
      }));
      brandList.forEach(function (b) {
        var active = state.filters.brand === b.name;
        html += '<label data-brand="' + esc(b.name) + '" style="display:flex;align-items:center;gap:0.6rem;margin-bottom:0.5rem;cursor:pointer">' +
          radioDot(active) +
          '<span style="font-size:0.875rem;color:' + (active ? "#2DD4BF" : "#B8C6D8") + ';font-weight:' + (active ? 600 : 400) + ';cursor:pointer">' + esc(b.display) + '</span></label>';
      });
      html += '</div>';
    }

    // Price range
    html += '<div><div style="font-size:0.72rem;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.75rem">Price Range (₹)</div>' +
      '<div style="display:flex;flex-direction:column;gap:0.5rem">' +
      '<input id="ewMinPrice" type="number" placeholder="Min price" value="' + esc(state.filters.min_price) + '" style="width:100%;padding:0.55rem 0.75rem;border:1.5px solid rgba(255,255,255,0.14);border-radius:8px;font-size:0.85rem;font-family:inherit;outline:none;box-sizing:border-box">' +
      '<input id="ewMaxPrice" type="number" placeholder="Max price" value="' + esc(state.filters.max_price) + '" style="width:100%;padding:0.55rem 0.75rem;border:1.5px solid rgba(255,255,255,0.14);border-radius:8px;font-size:0.85rem;font-family:inherit;outline:none;box-sizing:border-box">' +
      '</div></div>';

    // Clear
    if (hasActiveFilters()) {
      html += '<button id="ewClearFilters" style="padding:0.55rem 1rem;border:1.5px solid rgba(45,212,191,0.4);border-radius:8px;cursor:pointer;background:rgba(20,184,166,0.14);font-family:inherit;color:#2DD4BF;font-size:0.82rem;font-weight:600">✕ Clear Filters</button>';
    }

    panel.innerHTML = html;

    panel.querySelectorAll("[data-cat]").forEach(function (el) {
      el.addEventListener("click", function () { setF("category_slug", el.getAttribute("data-cat")); });
    });
    panel.querySelectorAll("[data-brand]").forEach(function (el) {
      el.addEventListener("click", function () { setF("brand", el.getAttribute("data-brand")); });
    });
    var minP = document.getElementById("ewMinPrice");
    var maxP = document.getElementById("ewMaxPrice");
    if (minP) {
      minP.addEventListener("change", function () { setF("min_price", minP.value); });
      minP.addEventListener("focus", function () { minP.style.borderColor = "#2DD4BF"; });
      minP.addEventListener("blur", function () { minP.style.borderColor = "rgba(255,255,255,0.14)"; });
    }
    if (maxP) {
      maxP.addEventListener("change", function () { setF("max_price", maxP.value); });
      maxP.addEventListener("focus", function () { maxP.style.borderColor = "#2DD4BF"; });
      maxP.addEventListener("blur", function () { maxP.style.borderColor = "rgba(255,255,255,0.14)"; });
    }
    var clear = document.getElementById("ewClearFilters");
    if (clear) clear.addEventListener("click", clearFilters);
  }

  // ── Product card ──
  function cardHtml(ew) {
    var price = Number(ew.price);
    var discount = Number(ew.discount_percentage != null ? ew.discount_percentage : 0);
    var salePrice = discount > 0 ? Math.round(price * (1 - discount / 100)) : price;
    var hasDiscount = discount > 0;
    var cartItems = window.TrinetraaCart.items;
    var cartItem = cartItems.find(function (i) { return i.id === ew.id; });
    var inCompare = window.TrinetraaCompare.isIn(ew.id);

    var imageHtml = ew.image
      ? '<img src="' + esc(ew.image) + '" alt="' + esc(ew.name) + '" style="width:100%;height:100%;object-fit:cover;transition:transform 0.35s ease">'
      : '<span style="font-size:3.5rem">🕶️</span>';

    var actionsTop;
    if (cartItem) {
      actionsTop = '<div style="display:flex;align-items:center;gap:0;border:1.5px solid #2DD4BF;border-radius:999px;overflow:hidden">' +
        '<button data-qty-dec="' + ew.id + '" style="flex:1;background:none;border:none;color:#2DD4BF;font-size:1.2rem;font-weight:700;cursor:pointer;padding:0.4rem">−</button>' +
        '<span style="font-weight:700;color:#F8FAFC;font-size:0.9rem;padding:0 0.5rem">' + cartItem.quantity + '</span>' +
        '<button data-qty-inc="' + ew.id + '" style="flex:1;background:none;border:none;color:#2DD4BF;font-size:1.2rem;font-weight:700;cursor:pointer;padding:0.4rem">+</button>' +
        '</div>';
    } else {
      actionsTop = '<button data-add="' + ew.id + '" style="width:100%;padding:0.6rem;background:#14B8A6;color:#fff;border:none;border-radius:999px;font-size:0.82rem;font-weight:700;font-family:inherit;cursor:pointer;transition:background 0.2s">Add to Cart</button>';
    }

    var compareBtn = '<button data-compare="' + ew.id + '" style="width:100%;padding:0.5rem;border:1.5px solid ' + (inCompare ? "#2DD4BF" : "rgba(255,255,255,0.14)") + ';border-radius:999px;background:' + (inCompare ? "rgba(20,184,166,0.14)" : "transparent") + ';color:' + (inCompare ? "#2DD4BF" : "#94A3B8") + ';font-size:0.78rem;font-weight:600;font-family:inherit;cursor:pointer;transition:all 0.2s">' + (inCompare ? "✓ In Compare" : "⚖ Compare") + '</button>';

    return '<div class="pp-product-card" data-card="' + ew.id + '" style="background:rgba(255,255,255,0.06);border-radius:16px;border:1px solid rgba(255,255,255,0.14);overflow:hidden;display:flex;flex-direction:column;transition:transform 0.25s ease, box-shadow 0.25s ease;box-shadow:0 2px 8px rgba(20,184,166,0.06)">' +
      '<a href="/eyewears/' + esc(ew.slug) + '" style="text-decoration:none;display:block;position:relative">' +
        '<div style="aspect-ratio:1/1;background:rgba(255,255,255,0.06);display:flex;align-items:center;justify-content:center;overflow:hidden">' + imageHtml + '</div>' +
        (hasDiscount ? '<span style="position:absolute;top:10px;left:10px;background:#EF4444;color:#fff;font-size:0.68rem;font-weight:700;padding:2px 8px;border-radius:999px">' + discount + '% OFF</span>' : '') +
      '</a>' +
      '<div style="padding:0.9rem 1rem 0.5rem;flex:1">' +
        (ew.brand ? '<div style="font-size:0.68rem;font-weight:700;color:#2DD4BF;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:0.3rem">' + esc(ew.brand) + '</div>' : '') +
        '<a href="/eyewears/' + esc(ew.slug) + '" style="text-decoration:none"><div style="font-size:0.9rem;font-weight:700;color:#F8FAFC;line-height:1.3;margin-bottom:0.5rem">' + esc(ew.name) + '</div></a>' +
        '<div style="display:flex;align-items:center;gap:0.5rem">' +
          '<span style="font-size:1rem;font-weight:800;color:#F8FAFC">₹' + inr(salePrice) + '</span>' +
          (hasDiscount ? '<span style="font-size:0.8rem;color:#94A3B8;text-decoration:line-through">₹' + inr(price) + '</span>' : '') +
        '</div>' +
      '</div>' +
      '<div style="padding:0.6rem 1rem 1rem;display:flex;flex-direction:column;gap:0.45rem">' + actionsTop + compareBtn + '</div>' +
    '</div>';
  }

  function renderResults() {
    var wrap = document.getElementById("ewResults");
    if (!wrap) return;
    if (state.loading) {
      var sk = "";
      for (var i = 0; i < 9; i++) sk += '<div style="background:rgba(255,255,255,0.06);border-radius:16px;aspect-ratio:3/4;animation:skeleton-pulse 1.4s ease infinite"></div>';
      wrap.innerHTML = '<div class="ew-grid">' + sk + '</div>';
      return;
    }
    if (state.eyewears.length === 0) {
      wrap.innerHTML = '<div style="text-align:center;padding:5rem 1rem;color:#94A3B8">' +
        '<div style="font-size:4rem;margin-bottom:1rem">🕶️</div>' +
        '<div style="font-weight:700;font-size:1.1rem;color:#B8C6D8;margin-bottom:0.5rem">No eyewear found</div>' +
        '<div style="font-size:0.875rem;margin-bottom:1.5rem">Try adjusting your filters</div>' +
        '<button id="ewEmptyClear" style="padding:0.65rem 1.5rem;background:#14B8A6;color:#fff;border:none;border-radius:999px;font-family:inherit;font-weight:600;cursor:pointer">Clear Filters</button>' +
        '</div>';
      var ec = document.getElementById("ewEmptyClear");
      if (ec) ec.addEventListener("click", clearFilters);
      return;
    }
    wrap.innerHTML = '<div class="ew-grid">' + state.eyewears.map(cardHtml).join("") + '</div>';
    bindCardEvents();
  }

  function bindCardEvents() {
    var wrap = document.getElementById("ewResults");
    if (!wrap) return;
    function find(id) { return state.eyewears.find(function (e) { return e.id === id; }); }

    wrap.querySelectorAll("[data-add]").forEach(function (b) {
      b.addEventListener("click", function (e) {
        e.preventDefault();
        var ew = find(Number(b.getAttribute("data-add")));
        if (!ew) return;
        var price = Number(ew.price);
        var discount = Number(ew.discount_percentage != null ? ew.discount_percentage : 0);
        var salePrice = discount > 0 ? Math.round(price * (1 - discount / 100)) : price;
        window.TrinetraaCart.add({ id: ew.id, name: ew.name, brand: ew.brand || "", price: salePrice, salePrice: salePrice, sale_price: salePrice, image: ew.image || null, slug: ew.slug });
        renderResults();
      });
    });
    wrap.querySelectorAll("[data-qty-dec]").forEach(function (b) {
      b.addEventListener("click", function () {
        var id = Number(b.getAttribute("data-qty-dec"));
        var ci = window.TrinetraaCart.items.find(function (i) { return i.id === id; });
        if (!ci) return;
        if (ci.quantity === 1) window.TrinetraaCart.remove(id); else window.TrinetraaCart.updateQty(id, ci.quantity - 1);
        renderResults();
      });
    });
    wrap.querySelectorAll("[data-qty-inc]").forEach(function (b) {
      b.addEventListener("click", function () {
        var id = Number(b.getAttribute("data-qty-inc"));
        var ci = window.TrinetraaCart.items.find(function (i) { return i.id === id; });
        if (!ci) return;
        window.TrinetraaCart.updateQty(id, ci.quantity + 1);
        renderResults();
      });
    });
    wrap.querySelectorAll("[data-compare]").forEach(function (b) {
      b.addEventListener("click", function () {
        var ew = find(Number(b.getAttribute("data-compare")));
        if (!ew) return;
        if (window.TrinetraaCompare.isIn(ew.id)) window.TrinetraaCompare.remove(ew.id);
        else window.TrinetraaCompare.add({ id: ew.id, name: ew.name, slug: ew.slug, image: ew.image, brand: ew.brand, price: Number(ew.price), discount_percentage: Number(ew.discount_percentage != null ? ew.discount_percentage : 0) });
        renderResults();
      });
    });

    // hover effects on cards & images
    wrap.querySelectorAll("[data-card]").forEach(function (card) {
      card.addEventListener("mouseenter", function () { card.style.transform = "translateY(-4px)"; card.style.boxShadow = "0 12px 32px rgba(20,184,166,0.14)"; });
      card.addEventListener("mouseleave", function () { card.style.transform = "none"; card.style.boxShadow = "0 2px 8px rgba(20,184,166,0.06)"; });
      var img = card.querySelector("img");
      if (img) {
        img.addEventListener("mouseenter", function () { img.style.transform = "scale(1.05)"; });
        img.addEventListener("mouseleave", function () { img.style.transform = "none"; });
      }
    });
  }

  function renderPagination() {
    var wrap = document.getElementById("ewPagination");
    if (!wrap) return;
    var totalPages = Math.ceil((state.meta.total || 0) / (state.meta.per_page || 12));
    if (totalPages <= 1) { wrap.innerHTML = ""; return; }
    var page = state.page;
    var html = '<div style="display:flex;justify-content:center;gap:0.4rem;margin-top:2.5rem;flex-wrap:wrap">';
    html += '<button data-page="' + (page - 1) + '"' + (page <= 1 ? " disabled" : "") + ' style="padding:0.5rem 0.9rem;border:1.5px solid rgba(255,255,255,0.14);border-radius:8px;background:' + (page <= 1 ? "rgba(255,255,255,0.03)" : "rgba(255,255,255,0.06)") + ';color:' + (page <= 1 ? "rgba(255,255,255,0.35)" : "#B8C6D8") + ';font-weight:600;cursor:' + (page <= 1 ? "not-allowed" : "pointer") + ';font-family:inherit">‹</button>';
    for (var p = 1; p <= totalPages; p++) {
      html += '<button data-page="' + p + '" style="padding:0.5rem 0.9rem;border:1.5px solid ' + (p === page ? "#2DD4BF" : "rgba(255,255,255,0.14)") + ';border-radius:8px;background:' + (p === page ? "#14B8A6" : "rgba(255,255,255,0.06)") + ';color:' + (p === page ? "#fff" : "#B8C6D8") + ';font-weight:600;cursor:pointer;font-family:inherit">' + p + '</button>';
    }
    html += '<button data-page="' + (page + 1) + '"' + (page >= totalPages ? " disabled" : "") + ' style="padding:0.5rem 0.9rem;border:1.5px solid rgba(255,255,255,0.14);border-radius:8px;background:' + (page >= totalPages ? "rgba(255,255,255,0.03)" : "rgba(255,255,255,0.06)") + ';color:' + (page >= totalPages ? "rgba(255,255,255,0.35)" : "#B8C6D8") + ';font-weight:600;cursor:' + (page >= totalPages ? "not-allowed" : "pointer") + ';font-family:inherit">›</button>';
    html += '</div>';
    wrap.innerHTML = html;
    wrap.querySelectorAll("[data-page]").forEach(function (b) {
      if (b.disabled) return;
      b.addEventListener("click", function () {
        var np = Number(b.getAttribute("data-page"));
        if (np < 1 || np > totalPages) return;
        state.page = np;
        fetchData();
        window.scrollTo({ top: 0, behavior: "smooth" });
      });
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    renderFilterPanel();
    renderToolbar();
    fetchData();

    // categories + brands for filters
    fetch("/api/public/categories").then(function (r) { return r.json(); }).then(function (d) { state.categories = d.data || []; renderFilterPanel(); });
    fetch("/api/public/brands").then(function (r) { return r.json(); }).then(function (d) { state.brands = d.data || []; renderFilterPanel(); });

    // sort dropdown toggle + outside click
    var sortBtn = document.getElementById("ewSortBtn");
    var sortWrap = document.getElementById("ewSortWrap");
    if (sortBtn) sortBtn.addEventListener("click", function () { state.sortOpen = !state.sortOpen; renderToolbar(); });
    document.addEventListener("mousedown", function (e) {
      if (sortWrap && !sortWrap.contains(e.target) && state.sortOpen) { state.sortOpen = false; renderToolbar(); }
    });

    // re-render cards when cart/compare changes elsewhere
    document.addEventListener("cart:change", renderResults);
    document.addEventListener("compare:change", renderResults);
  });
})();
@endverbatim
</script>
@endpush
