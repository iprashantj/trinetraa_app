@extends('layouts.public')
@section('title', 'Customer Reviews')
@section('content')
<div class="pp-page-header">
    <div class="pp-container">
        <div class="pp-page-header__content">
            <h1 class="pp-page-header__title">Customer Reviews</h1>
            <p class="pp-page-header__breadcrumb"><a href="/">Home</a> / Reviews</p>
        </div>
    </div>
</div>

<section class="pp-section">
    <div class="pp-container">
        <div id="reviewsEmpty" style="display:none" class="pp-empty"><div class="pp-empty__icon">⭐</div><div class="pp-empty__title">No reviews yet. Be the first!</div></div>
        <div id="reviewsGrid" class="pp-reviews-grid" style="display:none"></div>
        <div id="reviewsPagination"></div>

        <div style="margin:4rem auto 0;max-width:560px">
            <h2 style="font-family:var(--pp-font-heading);font-weight:800;margin-bottom:1.5rem">Leave a Review</h2>
            <div id="reviewSuccess" style="display:none;background:#f0fdf4;color:#16a34a;padding:1rem;border-radius:12px;margin-bottom:1rem"></div>
            <div id="reviewError" style="display:none;background:#fef2f2;color:#dc2626;padding:1rem;border-radius:12px;margin-bottom:1rem"></div>
            <form id="reviewForm" style="display:flex;flex-direction:column;gap:1rem">
                <input id="rvName" class="pp-form-input" placeholder="Your name *" required style="padding:0.75rem 1rem;border:1.5px solid var(--pp-gray-200);border-radius:12px;font-size:0.95rem;font-family:inherit">
                <input id="rvEmail" type="email" class="pp-form-input" placeholder="Your email *" required style="padding:0.75rem 1rem;border:1.5px solid var(--pp-gray-200);border-radius:12px;font-size:0.95rem;font-family:inherit">
                <div>
                    <label style="font-weight:600;margin-bottom:0.5rem;display:block">Rating</label>
                    <div id="rvStars" style="display:flex;gap:0.5rem"></div>
                </div>
                <textarea id="rvText" placeholder="Write your review (max 200 words) *" required rows="4" style="padding:0.75rem 1rem;border:1.5px solid var(--pp-gray-200);border-radius:12px;font-size:0.95rem;font-family:inherit;resize:vertical"></textarea>
                <div id="rvCaptchaWrap" style="display:none">
                    <label id="rvCaptchaLabel" style="font-weight:600;margin-bottom:0.5rem;display:block"></label>
                    <input id="rvCaptchaAnswer" type="number" placeholder="Answer" required style="padding:0.75rem 1rem;border:1.5px solid var(--pp-gray-200);border-radius:12px;font-size:0.95rem;font-family:inherit;width:140px">
                </div>
                <button id="rvSubmit" type="submit" class="pp-btn-primary" style="justify-content:center">Submit Review</button>
            </form>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
@verbatim
(function () {
  var page = 1;
  var meta = { page: 1, per_page: 9, total: 0 };
  var captcha = null;
  var rating = 5;
  var submitting = false;

  function esc(s) {
    return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }

  function renderReviews(reviews) {
    var empty = document.getElementById("reviewsEmpty");
    var grid = document.getElementById("reviewsGrid");
    if (!reviews || reviews.length === 0) {
      empty.style.display = "block";
      grid.style.display = "none";
      return;
    }
    empty.style.display = "none";
    grid.style.display = "";
    grid.innerHTML = reviews.map(function (r) {
      var stars = [1, 2, 3, 4, 5].map(function (s) { return "<span>" + (s <= r.rating ? "★" : "☆") + "</span>"; }).join("");
      var initial = String(r.user_name || "").charAt(0).toUpperCase();
      var date = new Date(r.created_at).toLocaleDateString("en-IN", { month: "long", year: "numeric" });
      return '<div class="pp-review-card">' +
        '<div class="pp-review-card__stars">' + stars + '</div>' +
        '<p class="pp-review-card__text">"' + esc(r.review_text) + '"</p>' +
        '<div class="pp-review-card__author">' +
          '<div class="pp-review-card__avatar">' + esc(initial) + '</div>' +
          '<div><div class="pp-review-card__name">' + esc(r.user_name) + '</div>' +
          '<div class="pp-review-card__date">' + esc(date) + '</div></div>' +
        '</div></div>';
    }).join("");
  }

  function renderPagination() {
    var container = document.getElementById("reviewsPagination");
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
      btn.addEventListener("click", function () { page = parseInt(btn.getAttribute("data-page"), 10); loadReviews(); });
    });
    var prev = container.querySelector('[data-go="prev"]');
    var next = container.querySelector('[data-go="next"]');
    if (prev) prev.addEventListener("click", function () { if (page > 1) { page--; loadReviews(); } });
    if (next) next.addEventListener("click", function () { if (page < pages) { page++; loadReviews(); } });
  }

  function loadReviews() {
    fetch("/api/public/reviews?page=" + page + "&per_page=9")
      .then(function (r) { return r.json(); })
      .then(function (d) {
        meta = d.meta || {};
        renderReviews(d.data || []);
        renderPagination();
      });
  }

  function loadCaptcha() {
    return fetch("/api/public/reviews/captcha")
      .then(function (r) { return r.json(); })
      .then(function (d) {
        captcha = d.data;
        var wrap = document.getElementById("rvCaptchaWrap");
        if (captcha) {
          document.getElementById("rvCaptchaLabel").textContent = captcha.question;
          wrap.style.display = "block";
        } else {
          wrap.style.display = "none";
        }
      });
  }

  function renderStars() {
    var container = document.getElementById("rvStars");
    container.innerHTML = [1, 2, 3, 4, 5].map(function (s) {
      return '<button type="button" data-star="' + s + '" style="background:none;border:none;cursor:pointer;font-size:1.5rem;color:' +
        (s <= rating ? "#f97316" : "#d1d5db") + '">' + (s <= rating ? "★" : "☆") + '</button>';
    }).join("");
    container.querySelectorAll("[data-star]").forEach(function (btn) {
      btn.addEventListener("click", function () { rating = parseInt(btn.getAttribute("data-star"), 10); renderStars(); });
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    renderStars();
    loadReviews();
    loadCaptcha();

    var form = document.getElementById("reviewForm");
    var successEl = document.getElementById("reviewSuccess");
    var errorEl = document.getElementById("reviewError");
    var submitBtn = document.getElementById("rvSubmit");

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      if (!captcha || submitting) return;
      submitting = true;
      submitBtn.disabled = true;
      submitBtn.textContent = "Submitting…";
      errorEl.style.display = "none";
      successEl.style.display = "none";

      var payload = {
        user_name: document.getElementById("rvName").value,
        user_email: document.getElementById("rvEmail").value,
        rating: rating,
        review_text: document.getElementById("rvText").value,
        captcha_answer: document.getElementById("rvCaptchaAnswer").value,
        captcha_token: captcha.token,
      };

      fetch("/api/public/reviews", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      })
        .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
        .then(function (res) {
          if (!res.ok) {
            errorEl.textContent = res.d.message || "Submission failed.";
            errorEl.style.display = "block";
            loadCaptcha();
            return;
          }
          successEl.textContent = res.d.message;
          successEl.style.display = "block";
          form.style.display = "none";
        })
        .finally(function () {
          submitting = false;
          submitBtn.disabled = false;
          submitBtn.textContent = "Submit Review";
        });
    });
  });
})();
@endverbatim
</script>
@endpush
