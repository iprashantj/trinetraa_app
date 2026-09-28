@extends('layouts.public')
@php
    $ew = \App\Models\Eyewear::with('category')->where('slug', $slug)->where('is_active', true)->firstOrFail();
    $_su = rtrim($site['siteUrl'] ?? 'https://trinetraaoptician.com', '/');
    $_ewTitle = $ew ? $ew->name . ' — ' . ($ew->brand ? $ew->brand . ' | ' : '') . 'Trinetraa Optician Nashik' : 'Premium Eyewear — Trinetraa Optician';
    $_ewDesc  = $ew && $ew->description ? \Illuminate\Support\Str::limit(strip_tags($ew->description), 155) : 'Shop premium eyewear at Trinetraa Optician Nashik. Quality frames with expert fitting.';
    $_ewImg   = $ew && $ew->image ? $ew->image : $_su . '/icons/icon-512.png';
    $_ewUrl   = $_su . '/eyewears/' . $slug;
@endphp
@section('title', $_ewTitle)
@section('meta_description', $_ewDesc)
@section('og_image', $_ewImg)
@section('og_type', 'product')
@section('canonical', $_ewUrl)
@if($ew)
@push('structured_data')
@php
    $at = '@';
    $ewPrice = number_format((float)$ew->price * (1 - ((float)($ew->discount_percentage ?? 0) / 100)), 2, '.', '');
    $ewJsonLd = json_encode(array_filter([
        $at.'context' => 'https://schema.org',
        $at.'type'    => 'Product',
        'name'        => $ew->name,
        'description' => $ew->description,
        'image'       => $ew->image ?: $_su . '/icons/icon-512.png',
        'sku'         => $ew->slug,
        'brand'       => $ew->brand ? [$at.'type' => 'Brand', 'name' => $ew->brand] : null,
        'offers'      => [
            $at.'type'        => 'Offer',
            'url'             => $_ewUrl,
            'priceCurrency'   => 'INR',
            'price'           => $ewPrice,
            'availability'    => ($ew->availability === 'out_of_stock') ? 'https://schema.org/OutOfStock' : 'https://schema.org/InStock',
            'seller'          => [$at.'type' => 'Organization', 'name' => 'Trinetraa Optician'],
        ],
    ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $breadJsonLd = json_encode([
        $at.'context'        => 'https://schema.org',
        $at.'type'           => 'BreadcrumbList',
        'itemListElement'    => array_values(array_filter([
            [$at.'type' => 'ListItem', 'position' => 1, 'name' => 'Home',     'item' => $_su . '/'],
            [$at.'type' => 'ListItem', 'position' => 2, 'name' => 'Eyewears', 'item' => $_su . '/eyewears'],
            $ew->category ? [$at.'type' => 'ListItem', 'position' => 3, 'name' => $ew->category->name, 'item' => $_su . '/eyewears?category=' . $ew->category->slug] : null,
            [$at.'type' => 'ListItem', 'position' => $ew->category ? 4 : 3, 'name' => $ew->name, 'item' => $_ewUrl],
        ])),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp
<script type="application/ld+json">{!! $ewJsonLd !!}</script>
<script type="application/ld+json">{!! $breadJsonLd !!}</script>
@endpush
@endif
@section('content')

<div id="ewDetailRoot">
    {{-- Loading state (default) --}}
    <div class="pp-section" id="ewLoading">
        <div class="pp-container">
            <div class="pp-loading"><div class="pp-spinner"></div></div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
  window.__EW_SLUG = @json($slug);
</script>
<script>
@verbatim
(function () {
  var slug = window.__EW_SLUG;
  var root = document.getElementById("ewDetailRoot");

  var AVAILABILITY_LABELS = {
    in_store:     { label: "In Store Only",        color: "#7DD3FC", bg: "rgba(125,211,252,0.15)" },
    amazon:       { label: "Available on Amazon",   color: "#ff9900", bg: "rgba(255,153,0,0.15)" },
    flipkart:     { label: "Available on Flipkart", color: "#2874f0", bg: "rgba(40,116,240,0.15)" },
    both:         { label: "Available Online",      color: "#34d399", bg: "rgba(52,211,153,0.15)" },
    out_of_stock: { label: "Out of Stock",          color: "#f87171", bg: "rgba(248,113,113,0.15)" },
  };

  function esc(s) { return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) { return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]; }); }
  function escAttr(s) { return esc(s); }

  var product = null;

  fetch("/api/public/eyewears/" + slug)
    .then(function (r) { if (!r.ok) { renderNotFound(); return null; } return r.json(); })
    .then(function (d) { if (d && d.data) { product = d.data; renderProduct(); renderReviews(); } });

  function renderNotFound() {
    root.innerHTML = '<div class="pp-section"><div class="pp-container" style="text-align:center;padding:4rem 0">' +
      '<div style="font-size:4rem">🔍</div>' +
      '<h2 style="font-family:var(--pp-font-heading);font-weight:700;margin-bottom:1rem">Product Not Found</h2>' +
      '<a href="/eyewears" class="pp-btn-primary">Browse All Eyewears</a>' +
      '</div></div>';
  }

  function renderProduct() {
    var discount = Number(product.discount_percentage || 0);
    var salePrice = discount > 0 ? Number(product.price) * (1 - discount / 100) : null;
    var avail = AVAILABILITY_LABELS[product.availability] || AVAILABILITY_LABELS.in_store;
    var isOutOfStock = product.availability === "out_of_stock";
    var priceText = salePrice !== null ? salePrice.toFixed(0) : Number(product.price).toFixed(0);
    var waNum = (window.__SITE && window.__SITE.whatsapp) ? window.__SITE.whatsapp : "918888899737";
    var whatsappMsg = encodeURIComponent("Hello Trinetraa Optician! I'm interested in: " + product.name + (product.brand ? " (" + product.brand + ")" : "") + ". Price: ₹" + priceText + ". Please let me know the availability.");

    var imageHtml = product.image
      ? '<img src="' + escAttr(product.image) + '" alt="' + escAttr(product.name) + '" style="width:100%;height:100%;object-fit:cover">'
      : '<div style="font-size:6rem">🕶️</div>';

    // ── Actions block ──
    var actionsHtml = "";
    if (isOutOfStock) {
      actionsHtml = '<div id="ewNotifyMe"></div>';
    } else {
      actionsHtml += '<div id="ewCartControl" style="margin-bottom:1rem"></div>';
      actionsHtml += '<div style="display:flex;flex-direction:column;gap:0.75rem;margin-bottom:1.25rem">';
      if (product.amazon_enabled && product.amazon_url) {
        actionsHtml += '<a href="' + escAttr(product.amazon_url) + '" target="_blank" rel="noopener noreferrer" style="display:flex;align-items:center;justify-content:center;gap:0.6rem;background:#ff9900;color:#fff;border-radius:0.5rem;padding:0.75rem 1rem;font-weight:700;font-size:0.95rem;text-decoration:none">📦 Buy on Amazon</a>';
      }
      if (product.flipkart_enabled && product.flipkart_url) {
        actionsHtml += '<a href="' + escAttr(product.flipkart_url) + '" target="_blank" rel="noopener noreferrer" style="display:flex;align-items:center;justify-content:center;gap:0.6rem;background:#2874f0;color:#fff;border-radius:0.5rem;padding:0.75rem 1rem;font-weight:700;font-size:0.95rem;text-decoration:none">🛍️ Buy on Flipkart</a>';
      }
      actionsHtml += '<a href="https://wa.me/' + waNum + '?text=' + whatsappMsg + '" target="_blank" rel="noopener noreferrer" style="display:flex;align-items:center;justify-content:center;gap:0.6rem;background:#25D366;color:#fff;border-radius:0.5rem;padding:0.75rem 1rem;font-weight:700;font-size:0.95rem;text-decoration:none">' +
        '<svg viewBox="0 0 32 32" fill="currentColor" width="18" height="18"><path d="M16 3C9.37 3 4 8.37 4 15c0 2.39.67 4.62 1.83 6.52L4 29l7.7-1.8A12.93 12.93 0 0016 28c6.63 0 12-5.37 12-12S22.63 3 16 3zm6.27 17.1c-.27.76-1.57 1.46-2.16 1.55-.56.09-1.27.13-2.05-.13a18.9 18.9 0 01-1.86-.7c-3.25-1.4-5.37-4.66-5.53-4.87-.16-.22-1.28-1.7-1.28-3.24s.81-2.3 1.1-2.61c.29-.32.63-.4.84-.4l.61.01c.19 0 .46-.07.72.55l.98 2.39c.09.23.05.5-.09.71l-.39.56-.38.43c-.13.15-.27.3-.12.59.15.28.68 1.13 1.46 1.83.99.89 1.83 1.16 2.1 1.29.27.13.43.11.59-.07l.84-.99c.15-.2.31-.15.52-.08l2.5.98c.24.09.39.14.45.22.06.09.06.51-.2 1.27z"/></svg>' +
        'Enquire on WhatsApp</a>';
      actionsHtml += '<button id="ewEnquiryBtn" style="display:flex;align-items:center;justify-content:center;gap:0.6rem;background:var(--pp-gray-50);color:var(--pp-navy);border:1.5px solid var(--pp-gray-200);border-radius:0.5rem;padding:0.75rem 1rem;font-weight:700;font-size:0.95rem;cursor:pointer;font-family:inherit">✉️ Send Product Enquiry</button>';
      actionsHtml += '</div>';
    }

    var html =
      '<div class="pp-page-header"><div class="pp-container"><div class="pp-page-header__content">' +
        '<h1 class="pp-page-header__title">' + esc(product.name) + '</h1>' +
        '<p class="pp-page-header__breadcrumb"><a href="/">Home</a> / <a href="/eyewears">Eyewears</a> / ' + esc(product.name) + '</p>' +
      '</div></div></div>' +
      '<section class="pp-section"><div class="pp-container">' +
        '<div style="display:grid;grid-template-columns:1fr 1fr;gap:3rem;align-items:start">' +
          '<div style="border-radius:var(--pp-radius-lg);overflow:hidden;background:var(--pp-gray-100);aspect-ratio:1;display:flex;align-items:center;justify-content:center">' + imageHtml + '</div>' +
          '<div>' +
            (product.category ? '<div style="font-size:0.8rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--pp-gold);margin-bottom:0.5rem">' + esc(product.category.name) + '</div>' : '') +
            '<h2 style="font-family:var(--pp-font-heading);font-size:2rem;font-weight:800;color:var(--pp-gray-900);margin-bottom:0.5rem">' + esc(product.name) + '</h2>' +
            (product.brand ? '<div style="color:var(--pp-gray-500);margin-bottom:1rem">Brand: <strong>' + esc(product.brand) + '</strong></div>' : '') +
            '<div style="display:flex;align-items:baseline;gap:0.75rem;margin-bottom:1.5rem">' +
              '<span style="font-size:2rem;font-weight:800;color:var(--pp-gray-900)">₹' + priceText + '</span>' +
              (salePrice !== null ? '<span style="font-size:1.2rem;text-decoration:line-through;color:var(--pp-gray-400)">₹' + Number(product.price).toFixed(0) + '</span>' : '') +
              (discount > 0 ? '<span style="background:rgba(245,184,65,0.15);color:#F5B841;padding:0.2rem 0.6rem;border-radius:999px;font-size:0.8rem;font-weight:700">' + discount + '% OFF</span>' : '') +
            '</div>' +
            (product.description ? '<p style="color:var(--pp-gray-600);line-height:1.7;margin-bottom:1.5rem">' + esc(product.description) + '</p>' : '') +
            '<div style="display:inline-flex;align-items:center;gap:0.5rem;background:' + avail.bg + ';color:' + avail.color + ';border-radius:2rem;padding:0.35rem 0.9rem;font-size:0.82rem;font-weight:700;margin-bottom:1.5rem"><span>●</span>' + esc(avail.label) + '</div>' +
            actionsHtml +
            '<div id="ewPincode"></div>' +
            '<div id="ewCompareRow" style="display:flex;gap:0.75rem;margin-bottom:0.75rem"></div>' +
            '<a href="/appointment" class="pp-btn-outline" style="display:flex;width:100%;text-align:center;justify-content:center;margin-bottom:0.75rem">📅 Book Store Visit</a>' +
            '<a href="/eyewears" class="pp-btn-outline" style="display:inline-block;width:100%;text-align:center">← Back to Catalog</a>' +
          '</div>' +
        '</div>' +
      '</div></section>';

    root.innerHTML = html;

    if (isOutOfStock) renderNotifyMe();
    else renderCartControl();
    renderPincode();
    renderCompareRow();

    var enqBtn = document.getElementById("ewEnquiryBtn");
    if (enqBtn) enqBtn.addEventListener("click", openEnquiryModal);
  }

  // ── Cart control ──
  function renderCartControl() {
    var wrap = document.getElementById("ewCartControl");
    if (!wrap) return;
    var discount = Number(product.discount_percentage || 0);
    var salePrice = discount > 0 ? Number(product.price) * (1 - discount / 100) : null;
    var cartItem = window.TrinetraaCart.items.find(function (i) { return i.id === product.id; });
    if (cartItem) {
      wrap.innerHTML = '<div class="pp-qty-control">' +
        '<button class="pp-qty-control__btn" data-dec>−</button>' +
        '<span class="pp-qty-control__count">' + cartItem.quantity + '</span>' +
        '<button class="pp-qty-control__btn" data-inc>+</button>' +
        '</div>';
      wrap.querySelector("[data-dec]").addEventListener("click", function () {
        if (cartItem.quantity === 1) window.TrinetraaCart.remove(product.id); else window.TrinetraaCart.updateQty(product.id, cartItem.quantity - 1);
        renderCartControl();
      });
      wrap.querySelector("[data-inc]").addEventListener("click", function () {
        window.TrinetraaCart.updateQty(product.id, cartItem.quantity + 1);
        renderCartControl();
      });
    } else {
      wrap.innerHTML = '<button class="pp-btn-primary" data-add style="margin-bottom:1rem;width:100%;justify-content:center">Add to Cart</button>';
      wrap.querySelector("[data-add]").addEventListener("click", function () {
        var sp = salePrice !== null ? salePrice : Number(product.price);
        window.TrinetraaCart.add({ id: product.id, name: product.name, price: Number(product.price), salePrice: sp, sale_price: sp, image: product.image || null, brand: product.brand || "", slug: product.slug });
        renderCartControl();
      });
    }
  }

  // ── Compare row ──
  function renderCompareRow() {
    var wrap = document.getElementById("ewCompareRow");
    if (!wrap) return;
    var inCompare = window.TrinetraaCompare.isIn(product.id);
    var html = '<button data-compare style="flex:1;display:flex;align-items:center;justify-content:center;gap:0.5rem;background:' + (inCompare ? "#0F766E" : "transparent") + ';color:' + (inCompare ? "#fff" : "var(--pp-navy)") + ';border:1.5px solid var(--pp-navy);border-radius:0.5rem;padding:0.65rem 1rem;font-weight:700;font-size:0.875rem;cursor:pointer;font-family:inherit;transition:all 0.2s">⚖️ ' + (inCompare ? "✓ In Compare" : "Compare") + '</button>';
    if (inCompare) {
      html += '<a href="/compare" style="flex:1;display:flex;align-items:center;justify-content:center;background:var(--pp-gold);color:var(--pp-navy);border-radius:0.5rem;padding:0.65rem 1rem;font-weight:700;font-size:0.875rem;text-decoration:none">View Comparison →</a>';
    }
    wrap.innerHTML = html;
    wrap.querySelector("[data-compare]").addEventListener("click", function () {
      if (window.TrinetraaCompare.isIn(product.id)) window.TrinetraaCompare.remove(product.id);
      else window.TrinetraaCompare.add({ id: product.id, name: product.name, slug: product.slug, image: product.image, brand: product.brand, price: product.price, discount_percentage: Number(product.discount_percentage || 0), availability: product.availability, description: product.description, category: product.category });
      renderCompareRow();
    });
  }

  // ── Notify me form (out of stock) ──
  function renderNotifyMe() {
    var wrap = document.getElementById("ewNotifyMe");
    if (!wrap) return;
    wrap.innerHTML = '<div style="background:rgba(248,113,113,0.15);border:1.5px solid rgba(248,113,113,0.4);border-radius:16px;padding:1.25rem;margin-bottom:1rem">' +
      '<div style="font-weight:700;color:#f87171;margin-bottom:0.25rem">Out of Stock</div>' +
      '<p style="color:var(--pp-gray-600);font-size:0.875rem;margin-bottom:1rem">Get notified when this product is back in stock.</p>' +
      '<div id="ewNotifyErr" style="color:#f87171;font-size:0.875rem;margin-bottom:0.75rem;display:none"></div>' +
      '<form id="ewNotifyForm" style="display:flex;flex-direction:column;gap:0.65rem">' +
        '<input id="ewNotifyName" placeholder="Your name" style="padding:0.65rem 0.9rem;border:1.5px solid var(--pp-gray-200);border-radius:10px;font-size:0.875rem;font-family:inherit">' +
        '<input id="ewNotifyEmail" type="email" placeholder="Your email *" required style="padding:0.65rem 0.9rem;border:1.5px solid var(--pp-gray-200);border-radius:10px;font-size:0.875rem;font-family:inherit">' +
        '<button id="ewNotifySubmit" type="submit" style="background:#e53e3e;color:#fff;border:none;border-radius:10px;padding:0.7rem;font-weight:700;cursor:pointer;font-family:inherit;font-size:0.875rem">🔔 Notify Me When Available</button>' +
      '</form></div>';
    var form = document.getElementById("ewNotifyForm");
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var submit = document.getElementById("ewNotifySubmit");
      var errBox = document.getElementById("ewNotifyErr");
      submit.disabled = true; submit.textContent = "Registering…"; errBox.style.display = "none";
      fetch("/api/public/notify-me", {
        method: "POST", headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ name: document.getElementById("ewNotifyName").value, email: document.getElementById("ewNotifyEmail").value, eyewearId: product.id }),
      }).then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
        .then(function (res) {
          if (!res.ok) { errBox.textContent = res.d.message || "Failed to register."; errBox.style.display = "block"; return; }
          wrap.innerHTML = '<div style="background:rgba(52,211,153,0.15);color:#34d399;padding:1rem;border-radius:12px;text-align:center;font-weight:600">🔔 You\'re on the list! We\'ll notify you when this is back in stock.</div>';
        })
        .finally(function () { if (document.getElementById("ewNotifySubmit")) { submit.disabled = false; submit.textContent = "🔔 Notify Me When Available"; } });
    });
  }

  // ── Pincode checker ──
  function renderPincode() {
    var wrap = document.getElementById("ewPincode");
    if (!wrap) return;
    wrap.innerHTML = '<div style="background:var(--pp-gray-50);border-radius:16px;padding:1.25rem;margin-bottom:1rem">' +
      '<div style="font-weight:700;margin-bottom:0.5rem;font-size:0.9rem">📦 Check Delivery Availability</div>' +
      '<div style="display:flex;gap:0.5rem">' +
        '<input id="ewPincodeInput" type="text" placeholder="Enter 6-digit pincode" maxlength="6" style="flex:1;padding:0.6rem 0.9rem;border:1.5px solid var(--pp-gray-200);border-radius:10px;font-size:0.875rem;font-family:inherit">' +
        '<button id="ewPincodeBtn" disabled style="background:var(--pp-navy);color:#fff;border:none;border-radius:10px;padding:0.6rem 1rem;font-weight:700;cursor:pointer;font-family:inherit;font-size:0.875rem;white-space:nowrap">Check</button>' +
      '</div>' +
      '<div id="ewPincodeResult"></div>' +
    '</div>';
    var input = document.getElementById("ewPincodeInput");
    var btn = document.getElementById("ewPincodeBtn");
    var resultBox = document.getElementById("ewPincodeResult");

    input.addEventListener("input", function () {
      input.value = input.value.replace(/\D/g, "");
      resultBox.innerHTML = "";
      btn.disabled = input.value.length !== 6;
    });
    input.addEventListener("keydown", function (e) { if (e.key === "Enter") check(); });
    btn.addEventListener("click", check);

    function showResult(serviceable, message) {
      resultBox.innerHTML = '<div style="margin-top:0.6rem;font-size:0.85rem;font-weight:600;color:' + (serviceable ? "#34d399" : "#f87171") + '">' + (serviceable ? "✓" : "✗") + ' ' + esc(message) + '</div>';
    }
    function check() {
      var pin = input.value;
      if (!/^\d{6}$/.test(pin)) { showResult(false, "Enter a valid 6-digit pincode."); return; }
      btn.disabled = true; btn.textContent = "…"; resultBox.innerHTML = "";
      fetch("/api/public/check-pincode?pincode=" + pin)
        .then(function (r) { return r.json(); })
        .then(function (d) { showResult(!!d.serviceable, d.message || ""); })
        .finally(function () { btn.textContent = "Check"; btn.disabled = input.value.length !== 6; });
    }
  }

  // ── Enquiry modal ──
  function openEnquiryModal() {
    var overlay = document.createElement("div");
    overlay.style.cssText = "position:fixed;inset:0;z-index:1000;display:flex;align-items:center;justify-content:center;padding:1rem";
    overlay.innerHTML =
      '<div data-bg style="position:absolute;inset:0;background:rgba(13,27,42,0.6);backdrop-filter:blur(4px)"></div>' +
      '<div style="position:relative;background:rgba(15,23,42,0.98);border-radius:20px;padding:2rem;width:100%;max-width:480px;box-shadow:0 20px 60px rgba(0,0,0,0.5);z-index:1">' +
        '<button data-close style="position:absolute;top:1rem;right:1rem;background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--pp-gray-400);line-height:1">×</button>' +
        '<h3 style="font-family:var(--pp-font-heading);font-weight:700;margin-bottom:0.25rem">Product Enquiry</h3>' +
        '<p style="color:var(--pp-gray-500);font-size:0.875rem;margin-bottom:1.5rem">' + esc(product.name) + (product.brand ? " — " + esc(product.brand) : "") + '</p>' +
        '<div data-body></div>' +
      '</div>';
    document.body.appendChild(overlay);

    function close() { document.body.removeChild(overlay); }
    overlay.querySelector("[data-bg]").addEventListener("click", close);
    overlay.querySelector("[data-close]").addEventListener("click", close);

    var body = overlay.querySelector("[data-body]");
    renderForm();

    function renderForm() {
      body.innerHTML =
        '<form id="ewEnqForm" style="display:flex;flex-direction:column;gap:0.85rem">' +
          '<div id="ewEnqErr" style="background:rgba(248,113,113,0.15);color:#f87171;padding:0.75rem 1rem;border-radius:10px;font-size:0.875rem;display:none"></div>' +
          '<input id="ewEnqName" placeholder="Your name *" required style="padding:0.7rem 0.9rem;border:1.5px solid var(--pp-gray-200);border-radius:10px;font-size:0.9rem;font-family:inherit">' +
          '<input id="ewEnqEmail" type="email" placeholder="Your email *" required style="padding:0.7rem 0.9rem;border:1.5px solid var(--pp-gray-200);border-radius:10px;font-size:0.9rem;font-family:inherit">' +
          '<input id="ewEnqPhone" type="tel" placeholder="Phone number (optional)" style="padding:0.7rem 0.9rem;border:1.5px solid var(--pp-gray-200);border-radius:10px;font-size:0.9rem;font-family:inherit">' +
          '<textarea id="ewEnqMsg" placeholder="Your message (optional)" rows="3" style="padding:0.7rem 0.9rem;border:1.5px solid var(--pp-gray-200);border-radius:10px;font-size:0.9rem;font-family:inherit;resize:vertical"></textarea>' +
          '<button id="ewEnqSubmit" type="submit" class="pp-btn-primary" style="justify-content:center;margin-top:0.25rem">Send Enquiry</button>' +
        '</form>';
      document.getElementById("ewEnqForm").addEventListener("submit", function (e) {
        e.preventDefault();
        var submit = document.getElementById("ewEnqSubmit");
        var errBox = document.getElementById("ewEnqErr");
        submit.disabled = true; submit.textContent = "Sending…"; errBox.style.display = "none";
        fetch("/api/public/enquiry", {
          method: "POST", headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            name: document.getElementById("ewEnqName").value,
            email: document.getElementById("ewEnqEmail").value,
            phone: document.getElementById("ewEnqPhone").value,
            message: document.getElementById("ewEnqMsg").value,
            eyewearId: product.id, eyewearName: product.name,
          }),
        }).then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
          .then(function (res) {
            if (!res.ok) { errBox.textContent = res.d.message || "Failed to send."; errBox.style.display = "block"; return; }
            body.innerHTML = '<div style="text-align:center;padding:1.5rem 0">' +
              '<div style="font-size:3rem;margin-bottom:0.75rem">✅</div>' +
              '<div style="font-weight:700;font-size:1.1rem;margin-bottom:0.5rem">Enquiry Sent!</div>' +
              '<p style="color:var(--pp-gray-500);margin-bottom:1.5rem">We will get back to you shortly.</p>' +
              '<button data-done class="pp-btn-primary" style="justify-content:center">Close</button></div>';
            body.querySelector("[data-done]").addEventListener("click", close);
          })
          .finally(function () { if (document.getElementById("ewEnqSubmit")) { submit.disabled = false; submit.textContent = "Send Enquiry"; } });
      });
    }
  }

  // ── Reviews section ────────────────────────────────────────────────────
  function renderReviews() {
    var section = document.createElement('section');
    section.className = 'pp-section pp-section--alt';
    section.id = 'ewReviews';
    section.innerHTML = '<div class="pp-container" style="max-width:760px">' +
      '<div class="pp-section__header"><div class="pp-section__eyebrow">Customer Feedback</div>' +
      '<h2 class="pp-section__title">Reviews &amp; Ratings</h2></div>' +
      '<div id="ewReviewList"><div style="text-align:center;padding:2rem 0"><div class="pp-spinner"></div></div></div>' +
    '</div>';
    root.appendChild(section);

    var stars = '★★★★★';
    fetch('/api/public/reviews?eyewear_id=' + product.id + '&per_page=6')
      .then(function (r) { return r.json(); })
      .then(function (d) {
        var list = document.getElementById('ewReviewList');
        if (!list) return;
        var reviews = (d.data || []);
        if (!reviews.length) {
          list.innerHTML = '<div class="pp-empty" style="padding:2rem 0"><div class="pp-empty__icon">⭐</div><div class="pp-empty__title">No reviews yet</div><p>Be the first to review this product.</p></div>';
          return;
        }
        var avg = reviews.reduce(function (s, r) { return s + (r.rating || 0); }, 0) / reviews.length;
        list.innerHTML =
          '<div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;padding:1rem;background:var(--pp-gray-50);border-radius:12px">' +
            '<span style="font-size:2.5rem;font-weight:800;color:var(--pp-gold)">' + avg.toFixed(1) + '</span>' +
            '<div><div style="color:var(--pp-gold);font-size:1.2rem;letter-spacing:2px">' + stars.slice(0, Math.round(avg)) + '</div>' +
            '<div style="font-size:.85rem;color:var(--pp-gray-500)">' + reviews.length + ' review' + (reviews.length !== 1 ? 's' : '') + '</div></div>' +
          '</div>' +
          '<div style="display:flex;flex-direction:column;gap:1rem">' +
            reviews.map(function (r) {
              return '<div style="background:rgba(255,255,255,0.06);border:1px solid var(--pp-gray-100);border-radius:12px;padding:1rem">' +
                '<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.5rem">' +
                  '<div><div style="font-weight:700;font-size:.95rem">' + esc(r.user_name || 'Anonymous') + '</div>' +
                  '<div style="color:var(--pp-gold);font-size:.9rem">' + stars.slice(0, r.rating || 0) + '</div></div>' +
                  '<div style="font-size:.78rem;color:var(--pp-gray-400)">' + (r.created_at ? new Date(r.created_at).toLocaleDateString('en-IN',{day:'numeric',month:'short',year:'numeric'}) : '') + '</div>' +
                '</div>' +
                (r.review_text ? '<p style="color:var(--pp-gray-600);font-size:.9rem;line-height:1.6;margin:0">' + esc(r.review_text) + '</p>' : '') +
              '</div>';
            }).join('') +
          '</div>';
      });
  }

  // refresh cart/compare display on external changes
  document.addEventListener("cart:change", function () { if (product) renderCartControl(); });
  document.addEventListener("compare:change", function () { if (product) renderCompareRow(); });
})();
@endverbatim
</script>
@endpush
