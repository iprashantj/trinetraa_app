@extends('layouts.public')
@section('title', 'Offers & Promotions — Deals on Eyewear | Trinetraa Optician')
@section('meta_description', 'Browse current eyewear offers, discounts and promotional deals at Trinetraa Optician Nashik. Save on branded glasses, sunglasses and eye care services.')
@section('canonical', 'https://trinetraaoptician.com/offers')
@section('content')
<section style="background:linear-gradient(135deg,#b8860b 0%,#1a3a5c 100%);color:#fff;padding:5rem 0 3rem;text-align:center">
    <div class="pp-container">
        <div style="font-size:3rem;margin-bottom:0.75rem">🎁</div>
        <h1 style="font-size:clamp(2rem,5vw,3rem);font-weight:800;margin-bottom:1rem">Offers &amp; Promotions</h1>
        <p style="max-width:560px;margin:0 auto;opacity:0.9">Great deals on premium eyewear, eye tests, and more. New offers added regularly — don't miss out!</p>
    </div>
</section>

<section class="pp-section animate-on-scroll">
    <div class="pp-container">
        <div id="offersLoading" class="pp-loading"><div class="pp-spinner"></div></div>
        <div id="offersGrid" class="animate-stagger" style="display:none;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.5rem"></div>
    </div>
</section>

<section class="pp-section pp-section--alt animate-on-scroll">
    <div class="pp-container" style="text-align:center">
        <h2 style="font-weight:700;margin-bottom:0.5rem">Stay updated on the latest offers</h2>
        <p style="color:#9FB1C7;margin-bottom:1.5rem">Subscribe to our newsletter or follow us on social media to never miss a deal.</p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
            <a href="/contact" class="pp-btn-outline pp-btn-outline--gold">Contact Us</a>
            <a href="/appointment" class="pp-btn-primary">Book Eye Test →</a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
@verbatim
(function () {
  var DEFAULT_OFFERS = [
    { title: "Buy 1 Get 1 Free", badge: "BOGO", description: "Purchase any frame and get a second frame of equal or lesser value absolutely free. Valid on select brands.", image: null, valid_to: null },
    { title: "Student Discount — 15% Off", badge: "STUDENT", description: "Show your valid student ID and get 15% off on all eyeglasses, frames, and lenses. Not applicable on contact lenses.", image: null, valid_to: null },
    { title: "Senior Citizen Special — 20% Off", badge: "SENIOR", description: "Customers aged 60 and above get a flat 20% discount on all purchases including eye test, frames and lenses.", image: null, valid_to: null },
    { title: "Festival Season Offer", badge: "FESTIVAL", description: "Celebrate with up to 30% off on premium frames and sunglasses from top brands. Limited period offer.", image: null, valid_to: null },
    { title: "Combo Package — Frame + Lens", badge: "COMBO", description: "Get an anti-reflective lens coated frame combo starting at just ₹999. Includes free eye test.", image: null, valid_to: null },
    { title: "Free Eye Test — Always", badge: "FREE", description: "Complimentary computerised eye testing for all customers — no purchase required. Walk in or book online.", image: null, valid_to: null },
  ];

  var BADGE_COLORS = { "BOGO": "#1a3a5c", "STUDENT": "#2d7a2d", "SENIOR": "#7a2d2d", "FESTIVAL": "#b8860b", "COMBO": "#4a1a7a", "FREE": "#1a7a5c" };

  function esc(s) {
    return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }

  function render(offers) {
    var loading = document.getElementById("offersLoading");
    var grid = document.getElementById("offersGrid");
    loading.style.display = "none";
    grid.style.display = "grid";
    grid.innerHTML = offers.map(function (offer, i) {
      var bgColor = BADGE_COLORS[offer.badge] || "#1a3a5c";
      var media = offer.image
        ? '<img src="' + esc(offer.image) + '" alt="' + esc(offer.title) + '" style="width:100%;height:180px;object-fit:cover">'
        : '<div style="height:120px;background:' + bgColor + ';display:flex;align-items:center;justify-content:center;font-size:3rem">🎁</div>';
      var badge = offer.badge
        ? '<span style="background:' + bgColor + ';color:#fff;border-radius:0.25rem;padding:0.2rem 0.6rem;font-size:0.75rem;font-weight:700;letter-spacing:0.05em;margin-bottom:0.75rem;display:inline-block">' + esc(offer.badge) + '</span>'
        : '';
      var description = offer.description
        ? '<p style="color:#9FB1C7;font-size:0.875rem;line-height:1.7;margin-bottom:1rem">' + esc(offer.description) + '</p>'
        : '';
      var validTo = offer.valid_to
        ? '<div style="color:#e74c3c;font-size:0.8rem;font-weight:600">Valid until ' +
          esc(new Date(offer.valid_to).toLocaleDateString("en-IN", { day: "numeric", month: "long", year: "numeric" })) + '</div>'
        : '';
      return '<div class="animate-on-scroll" style="background:rgba(255,255,255,0.06);border-radius:1rem;overflow:hidden;box-shadow:0 2px 16px rgba(0,0,0,0.08);transition-delay:' + (i * 0.07) + 's">' +
        media +
        '<div style="padding:1.5rem">' +
        badge +
        '<h3 style="font-weight:700;font-size:1.1rem;margin-bottom:0.5rem">' + esc(offer.title) + '</h3>' +
        description +
        validTo +
        '</div></div>';
    }).join("");
  }

  document.addEventListener("DOMContentLoaded", function () {
    fetch("/api/public/offers")
      .then(function (r) { return r.json(); })
      .then(function (d) { render(d.data && d.data.length > 0 ? d.data : DEFAULT_OFFERS); })
      .catch(function () { render(DEFAULT_OFFERS); });
  });
})();
@endverbatim
</script>
@endpush
