@extends('layouts.public')
@section('title', 'Gallery — Store & Eyewear Photos | Trinetraa Optician')
@section('meta_description', 'Browse our gallery of eyewear collections, store photos and customer moments at Trinetraa Optician Nashik.')
@section('canonical', 'https://trinetraaoptician.com/gallery')
@section('content')
<div class="pp-page-header">
    <div class="pp-container">
        <div class="pp-page-header__content">
            <h1 class="pp-page-header__title">Gallery</h1>
            <p class="pp-page-header__breadcrumb"><a href="/">Home</a> / Gallery</p>
        </div>
    </div>
</div>

<section class="pp-section">
    <div class="pp-container">
        <div id="galleryLoading" class="pp-loading"><div class="pp-spinner"></div></div>
        <div id="galleryEmpty" style="display:none" class="pp-empty"><div class="pp-empty__icon">🖼️</div><div class="pp-empty__title">Gallery coming soon</div></div>
        <div id="galleryGrid" style="display:none;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1.25rem"></div>
        <div id="galleryPagination"></div>
    </div>
</section>
@endsection

@push('scripts')
<script>
@verbatim
(function () {
  var page = 1;
  var meta = { page: 1, per_page: 12, total: 0 };

  function esc(s) {
    return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }

  function renderItems(items) {
    var loading = document.getElementById("galleryLoading");
    var empty = document.getElementById("galleryEmpty");
    var grid = document.getElementById("galleryGrid");
    loading.style.display = "none";

    if (!items || items.length === 0) {
      empty.style.display = "block";
      grid.style.display = "none";
      return;
    }
    empty.style.display = "none";
    grid.style.display = "grid";
    grid.innerHTML = items.map(function (item) {
      var media;
      if (item.type === "video" && item.embed_url) {
        media = '<iframe src="' + esc(item.embed_url) + '" title="' + esc(item.title) + '" style="width:100%;aspect-ratio:16/9;border:none;border-radius:var(--pp-radius-md)" allowfullscreen></iframe>';
      } else if (item.image) {
        media = '<img src="' + esc(item.image) + '" alt="' + esc(item.title) + '" loading="lazy" style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:var(--pp-radius-md)">';
      } else {
        media = '<div class="pp-gallery-placeholder">🖼️</div>';
      }
      return '<div class="pp-gallery-item animate-on-scroll" style="position:relative">' +
        media +
        '<div class="pp-gallery-item__overlay"><div class="pp-gallery-item__title">' + esc(item.title) + '</div></div>' +
        '</div>';
    }).join("");
  }

  function renderPagination() {
    var container = document.getElementById("galleryPagination");
    if (!(meta.total > meta.per_page)) { container.innerHTML = ""; return; }
    var pages = Math.ceil(meta.total / meta.per_page);
    var html = '<div class="pp-pagination" style="margin-top:2rem">';
    html += '<button class="pp-pagination__btn" data-go="prev"' + (page <= 1 ? " disabled" : "") + '>‹</button>';
    for (var p = 1; p <= pages; p++) {
      html += '<button class="pp-pagination__btn' + (p === page ? " active" : "") + '" data-page="' + p + '">' + p + '</button>';
    }
    html += '<button class="pp-pagination__btn" data-go="next"' + (page >= pages ? " disabled" : "") + '>›</button>';
    html += '</div>';
    container.innerHTML = html;

    container.querySelectorAll("[data-page]").forEach(function (btn) {
      btn.addEventListener("click", function () { page = parseInt(btn.getAttribute("data-page"), 10); load(); });
    });
    var prev = container.querySelector('[data-go="prev"]');
    var next = container.querySelector('[data-go="next"]');
    if (prev) prev.addEventListener("click", function () { if (page > 1) { page--; load(); } });
    if (next) next.addEventListener("click", function () { if (page < pages) { page++; load(); } });
  }

  function load() {
    document.getElementById("galleryLoading").style.display = "block";
    document.getElementById("galleryGrid").style.display = "none";
    document.getElementById("galleryEmpty").style.display = "none";
    fetch("/api/public/gallery?page=" + page + "&per_page=12")
      .then(function (r) { return r.json(); })
      .then(function (d) {
        meta = d.meta || {};
        renderItems(d.data || []);
        renderPagination();
      })
      .catch(function () { renderItems([]); });
  }

  document.addEventListener("DOMContentLoaded", load);
})();
@endverbatim
</script>
@endpush
