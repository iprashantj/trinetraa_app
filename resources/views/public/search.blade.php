@extends('layouts.public')
@section('title', 'Search — Trinetraa Optician')
@section('no_index', '1')
@section('content')
<section style="background:linear-gradient(135deg,rgba(15,118,110,0.28) 0%,rgba(11,18,32,0.1) 100%),rgba(255,255,255,0.03);border-bottom:1px solid rgba(255,255,255,0.12);color:#fff;padding:calc(64px + 4rem) 0 3rem;text-align:center">
    <div class="pp-container"><h1 style="font-size:2rem;font-weight:800">Search</h1></div>
</section>

<div class="pp-container" style="padding-top:2rem;padding-bottom:4rem;max-width:900px">
    <form id="searchForm" style="display:flex;gap:0.75rem;margin-bottom:2.5rem">
        <input id="searchInput"
            style="font-size:1.1rem;padding:0.75rem 1rem;border-radius:0.5rem;border:2px solid rgba(255,255,255,0.2);flex:1;outline:none"
            placeholder="Search eyewears, brands, articles…" autofocus>
        <button type="submit" class="pp-btn-primary" style="padding:0.75rem 1.5rem;white-space:nowrap">Search</button>
    </form>

    <div id="searchLoading" class="pp-loading" style="display:none"><div class="pp-spinner"></div></div>

    <div id="searchResults"></div>
</div>
@endsection

@push('scripts')
<script>
@verbatim
(function () {
  var input = document.getElementById("searchInput");
  var form = document.getElementById("searchForm");
  var loadingEl = document.getElementById("searchLoading");
  var resultsEl = document.getElementById("searchResults");
  var currentQuery = "";

  function esc(s) {
    return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }

  function num(v) { return Number(v) || 0; }

  function fmtBlogDate(d) {
    if (!d) return "";
    try {
      return new Date(d).toLocaleDateString("en-IN", { month: "long", year: "numeric" });
    } catch (e) { return ""; }
  }

  function renderEyewears(items) {
    var cards = items.map(function (item) {
      var price = num(item.price);
      var salePrice = item.discount_percentage > 0 ? price * (1 - item.discount_percentage / 100) : null;
      var displayPrice = salePrice !== null ? salePrice.toFixed(0) : price.toFixed(0);
      var media = item.image
        ? '<img src="' + esc(item.image) + '" alt="' + esc(item.name) + '" style="width:100%;height:120px;object-fit:cover">'
        : '<div style="height:90px;background:#eef4fd;display:flex;align-items:center;justify-content:center;font-size:2rem">🕶️</div>';
      return '<a href="/eyewears/' + esc(item.slug) + '" style="background:rgba(255,255,255,0.06);border-radius:0.75rem;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.35);text-decoration:none;color:inherit;display:block">' +
        media +
        '<div style="padding:0.75rem">' +
          '<div style="font-weight:600;font-size:0.875rem;margin-bottom:0.25rem">' + esc(item.name) + '</div>' +
          '<div style="color:#F8FAFC;font-weight:700;font-size:0.9rem">₹' + displayPrice + '</div>' +
        '</div></a>';
    }).join("");
    return '<section style="margin-bottom:2.5rem">' +
      '<h2 style="font-weight:700;font-size:1.25rem;margin-bottom:1rem;border-bottom:2px solid #eee;padding-bottom:0.5rem">Eyewears (' + items.length + ')</h2>' +
      '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:1rem">' + cards + '</div>' +
      '</section>';
  }

  function renderBrands(brands) {
    var cards = brands.map(function (b) {
      var logo = b.logo ? '<img src="' + esc(b.logo) + '" alt="' + esc(b.name) + '" style="height:32px;object-fit:contain;margin-bottom:0.5rem;display:block">' : '';
      return '<a href="/brands/' + esc(b.slug) + '" style="background:rgba(255,255,255,0.06);border-radius:0.75rem;padding:1rem 1.5rem;box-shadow:0 2px 12px rgba(0,0,0,0.35);text-decoration:none;color:inherit;font-weight:600">' +
        logo + esc(b.name) + '</a>';
    }).join("");
    return '<section style="margin-bottom:2.5rem">' +
      '<h2 style="font-weight:700;font-size:1.25rem;margin-bottom:1rem;border-bottom:2px solid #eee;padding-bottom:0.5rem">Brands (' + brands.length + ')</h2>' +
      '<div style="display:flex;gap:1rem;flex-wrap:wrap">' + cards + '</div>' +
      '</section>';
  }

  function renderBlog(posts) {
    var cards = posts.map(function (p) {
      var media = p.image
        ? '<img src="' + esc(p.image) + '" alt="' + esc(p.title) + '" style="width:80px;height:60px;object-fit:cover;border-radius:0.5rem;flex-shrink:0">'
        : '<div style="width:80px;height:60px;background:#eef4fd;border-radius:0.5rem;display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0">📖</div>';
      var date = p.published_at ? '<div style="color:rgba(255,255,255,0.5);font-size:0.8rem">' + esc(fmtBlogDate(p.published_at)) + '</div>' : '';
      return '<a href="/blog/' + esc(p.slug) + '" style="background:rgba(255,255,255,0.06);border-radius:0.75rem;padding:1.25rem;box-shadow:0 2px 12px rgba(0,0,0,0.35);text-decoration:none;color:inherit;display:flex;gap:1rem;align-items:center">' +
        media +
        '<div><div style="font-weight:600;margin-bottom:0.25rem">' + esc(p.title) + '</div>' + date + '</div>' +
        '</a>';
    }).join("");
    return '<section>' +
      '<h2 style="font-weight:700;font-size:1.25rem;margin-bottom:1rem;border-bottom:2px solid #eee;padding-bottom:0.5rem">Articles (' + posts.length + ')</h2>' +
      '<div style="display:flex;flex-direction:column;gap:1rem">' + cards + '</div>' +
      '</section>';
  }

  function render(results) {
    if (!results) { resultsEl.innerHTML = ""; return; }
    var eyewears = results.eyewears || [];
    var brands = results.brands || [];
    var blog = results.blog || [];
    var hasResults = eyewears.length > 0 || brands.length > 0 || blog.length > 0;

    var html = "";
    if (currentQuery && !hasResults) {
      html = '<div class="pp-empty"><div class="pp-empty__icon">🔍</div>' +
        '<div class="pp-empty__title">No results for "' + esc(currentQuery) + '"</div>' +
        '<p>Try a different keyword or browse our <a href="/eyewears">full collection</a>.</p></div>';
    } else {
      if (eyewears.length > 0) html += renderEyewears(eyewears);
      if (brands.length > 0) html += renderBrands(brands);
      if (blog.length > 0) html += renderBlog(blog);
    }
    resultsEl.innerHTML = html;
  }

  function search(q) {
    if (!q || q.length < 2) { resultsEl.innerHTML = ""; return; }
    loadingEl.style.display = "";
    resultsEl.innerHTML = "";
    fetch("/api/public/search?q=" + encodeURIComponent(q))
      .then(function (r) { return r.json(); })
      .then(function (d) { render(d.data); })
      .finally(function () { loadingEl.style.display = "none"; });
  }

  function getInitialQ() {
    try { return new URL(window.location.href).searchParams.get("q") || ""; }
    catch (e) { return ""; }
  }

  document.addEventListener("DOMContentLoaded", function () {
    var initialQ = getInitialQ();
    input.value = initialQ;
    currentQuery = initialQ;
    search(initialQ);

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var val = input.value;
      currentQuery = val;
      search(val);
      try {
        var url = new URL(window.location.href);
        url.searchParams.set("q", val);
        window.history.pushState({}, "", url);
      } catch (e) {}
    });
  });
})();
@endverbatim
</script>
@endpush
