@extends('layouts.public')
@section('title', 'Shop on Amazon')
@section('content')
<section style="background:linear-gradient(135deg,#ff9900 0%,#ff6600 100%);color:#fff;padding:5rem 0 3rem;text-align:center">
    <div class="pp-container">
        <div style="font-size:3.5rem;margin-bottom:0.75rem">📦</div>
        <h1 style="font-size:clamp(2rem,5vw,3rem);font-weight:800;margin-bottom:1rem">Shop on Amazon</h1>
        <p style="max-width:560px;margin:0 auto 2rem;opacity:0.92">Find authentic Trinetraa Optician products on Amazon with fast delivery across India, easy returns, and Amazon's buyer protection.</p>
        <a id="heroStoreLink" href="https://www.amazon.in" target="_blank" rel="noopener noreferrer"
            style="display:inline-flex;align-items:center;gap:0.6rem;background:#fff;color:#ff6600;border-radius:0.5rem;padding:0.85rem 2rem;font-weight:800;font-size:1.1rem;text-decoration:none">
            Visit Our Amazon Store →
        </a>
    </div>
</section>

<section class="pp-section pp-section--alt animate-on-scroll">
    <div class="pp-container">
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.5rem">
            @php
                $features = [
                    ['🚀', 'Fast Delivery', 'Prime delivery across India in 1–2 days'],
                    ['↩️', 'Easy Returns', '30-day hassle-free return policy'],
                    ['🔒', 'Secure Payment', "Amazon's trusted payment gateway"],
                    ['⭐', 'Verified Reviews', 'Genuine customer ratings and reviews'],
                ];
            @endphp
            @foreach($features as $f)
                <div style="background:rgba(255,255,255,0.06);border-radius:1rem;padding:1.5rem;text-align:center;box-shadow:0 2px 12px rgba(0,0,0,0.35)">
                    <div style="font-size:2rem;margin-bottom:0.75rem">{{ $f[0] }}</div>
                    <div style="font-weight:700;margin-bottom:0.35rem">{{ $f[1] }}</div>
                    <div style="color:#9FB1C7;font-size:0.875rem">{{ $f[2] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="pp-section animate-on-scroll">
    <div class="pp-container">
        <div class="pp-section__header">
            <div class="pp-section__eyebrow">Available on Amazon</div>
            <h2 class="pp-section__title">Products on Amazon</h2>
        </div>
        <div id="amazonLoading" class="pp-loading"><div class="pp-spinner"></div></div>
        <div id="amazonGrid" style="display:none;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.5rem"></div>
        <div id="amazonEmpty" style="display:none">
            <div class="pp-empty">
                <div class="pp-empty__icon">📦</div>
                <div class="pp-empty__title">Amazon products coming soon</div>
                <p style="margin-top:0.5rem"><a id="emptyStoreLink" href="https://www.amazon.in" target="_blank" rel="noopener noreferrer" style="color:#ff9900">Browse our Amazon store directly</a></p>
            </div>
        </div>
    </div>
</section>

<section class="pp-section pp-section--alt animate-on-scroll">
    <div class="pp-container" style="text-align:center">
        <h2 style="font-weight:700;margin-bottom:0.5rem">Ready to shop on Amazon?</h2>
        <p style="color:#9FB1C7;margin-bottom:1.5rem">Visit our official Amazon store for the full collection with Amazon Prime benefits.</p>
        <a id="ctaStoreLink" href="https://www.amazon.in" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:0.5rem;background:#ff9900;color:#fff;border-radius:0.5rem;padding:0.85rem 2rem;font-weight:700;font-size:1rem;text-decoration:none">Visit Amazon Store →</a>
    </div>
</section>
@endsection

@push('scripts')
<script>
@verbatim
(function () {
  function esc(s) {
    return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }

  function render(products) {
    var grid = document.getElementById("amazonGrid");
    grid.innerHTML = products.map(function (item) {
      var price = Number(item.price);
      var discount = Number(item.discount_percentage) || 0;
      var salePrice = discount > 0 ? price * (1 - discount / 100) : null;
      var media = item.image
        ? '<img src="' + esc(item.image) + '" alt="' + esc(item.name) + '" style="width:100%;height:180px;object-fit:cover">'
        : '<div style="height:150px;background:rgba(255,255,255,0.06);display:flex;align-items:center;justify-content:center;font-size:3rem">🕶️</div>';
      var badge = discount > 0
        ? '<span style="position:absolute;top:8px;right:8px;background:#ff6600;color:#fff;border-radius:0.25rem;padding:0.2rem 0.5rem;font-size:0.75rem;font-weight:700">' + discount + '% OFF</span>'
        : '';
      var priceText = '₹' + (salePrice !== null ? salePrice.toFixed(0) : price.toFixed(0));
      return '<div style="background:rgba(255,255,255,0.06);border-radius:1rem;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.35)">' +
        '<div style="position:relative">' + media + badge + '</div>' +
        '<div style="padding:1rem">' +
        '<div style="font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">' + esc(item.name) + '</div>' +
        '<div style="color:#ff6600;font-weight:800;font-size:1.1rem;margin-bottom:0.75rem">' + priceText + '</div>' +
        '<div style="display:flex;gap:0.5rem">' +
        '<a href="/eyewears/' + esc(item.slug) + '" style="flex:1;background:rgba(255,255,255,0.06);color:#F8FAFC;border-radius:0.35rem;padding:0.5rem;text-align:center;text-decoration:none;font-size:0.8rem;font-weight:600">Details</a>' +
        '<a href="' + esc(item.amazon_url) + '" target="_blank" rel="noopener noreferrer" style="flex:1;background:#ff9900;color:#fff;border-radius:0.35rem;padding:0.5rem;text-align:center;text-decoration:none;font-size:0.8rem;font-weight:700">Buy Now</a>' +
        '</div></div></div>';
    }).join("");
  }

  function finish(products) {
    document.getElementById("amazonLoading").style.display = "none";
    if (products.length > 0) {
      var grid = document.getElementById("amazonGrid");
      grid.style.display = "grid";
      render(products);
    } else {
      document.getElementById("amazonEmpty").style.display = "block";
    }
  }

  document.addEventListener("DOMContentLoaded", function () {
    Promise.all([
      fetch("/api/public/settings").then(function (r) { return r.json(); }),
      fetch("/api/public/eyewears?per_page=12").then(function (r) { return r.json(); })
    ]).then(function (res) {
      var s = res[0], p = res[1];
      var settings = (s && s.data) || {};
      var storeUrl = settings.amazon_store_url || "https://www.amazon.in";
      ["heroStoreLink", "emptyStoreLink", "ctaStoreLink"].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.href = storeUrl;
      });
      var products = ((p && p.data) || []).filter(function (e) { return e.amazon_enabled && e.amazon_url; });
      finish(products);
    }).catch(function () {
      finish([]);
    });
  });
})();
@endverbatim
</script>
@endpush
