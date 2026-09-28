@extends('layouts.public')
@section('title', 'Compare Eyewear — Trinetraa Optician')
@section('no_index', '1')
@section('content')

<div id="compareRoot">
    {{-- Rendered by JS from window.TrinetraaCompare (localStorage) --}}
</div>

@endsection

@push('scripts')
@verbatim
<script>
(function () {
  var SPECS = [
    { key: "brand", label: "Brand" },
    { key: "category", label: "Category", format: function (v) { return (v && v.name) || "—"; } },
    { key: "price", label: "Price", format: function (v) { return "₹" + Number(v).toFixed(0); } },
    { key: "discount_percentage", label: "Discount", format: function (v) { return v > 0 ? (v + "% OFF") : "No discount"; } },
    { key: "availability", label: "Availability", format: function (v) {
        return ({ in_store: "In Store", amazon: "Amazon", flipkart: "Flipkart", both: "Online & Store", out_of_stock: "Out of Stock" }[v]) || v;
      } },
    { key: "description", label: "Description" },
  ];

  function esc(s) {
    return String(s == null ? "" : s)
      .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;").replace(/'/g, "&#39;");
  }

  function render() {
    var root = document.getElementById("compareRoot");
    if (!root) return;
    var items = window.TrinetraaCompare.items;

    if (!items || items.length === 0) {
      root.innerHTML =
        '<div class="pp-section">' +
          '<div class="pp-container" style="text-align:center;padding:5rem 0">' +
            '<div style="font-size:4rem;margin-bottom:1rem">⚖️</div>' +
            '<h2 style="font-family:var(--pp-font-heading);font-weight:700;margin-bottom:1rem">No Products to Compare</h2>' +
            '<p style="color:var(--pp-gray-500);margin-bottom:2rem">Add up to 3 products using the Compare button on product cards.</p>' +
            '<a href="/eyewears" class="pp-btn-primary">Browse Eyewears</a>' +
          '</div>' +
        '</div>';
      return;
    }

    var html = '';

    html +=
      '<div class="pp-page-header">' +
        '<div class="pp-container">' +
          '<div class="pp-page-header__content">' +
            '<h1 class="pp-page-header__title">Compare Products</h1>' +
            '<p class="pp-page-header__breadcrumb"><a href="/">Home</a> / Compare</p>' +
          '</div>' +
        '</div>' +
      '</div>';

    html += '<section class="pp-section"><div class="pp-container">';

    html +=
      '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">' +
        '<div style="color:var(--pp-gray-500);font-size:0.9rem">Comparing ' + items.length + ' product' + (items.length !== 1 ? "s" : "") + '</div>' +
        '<button data-cmp-clear style="background:none;border:1.5px solid var(--pp-gray-200);border-radius:8px;padding:0.4rem 1rem;cursor:pointer;font-size:0.85rem;color:var(--pp-gray-500);font-family:inherit">Clear All</button>' +
      '</div>';

    html += '<div style="overflow-x:auto">';
    html += '<table style="width:100%;border-collapse:collapse;min-width:400px">';

    // head
    html += '<thead><tr>';
    html += '<th style="padding:1rem;text-align:left;color:var(--pp-gray-500);font-size:0.85rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;border-bottom:2px solid var(--pp-gray-100);width:140px">Feature</th>';
    items.forEach(function (p) {
      html += '<th style="padding:1rem;border-bottom:2px solid var(--pp-gray-100);min-width:200px">';
      html += '<div style="text-align:center">';
      html += '<div style="width:120px;height:120px;margin:0 auto 0.75rem;border-radius:12px;overflow:hidden;background:var(--pp-gray-100);display:flex;align-items:center;justify-content:center">';
      if (p.image) {
        html += '<img src="' + esc(p.image) + '" alt="' + esc(p.name) + '" style="width:100%;height:100%;object-fit:cover">';
      } else {
        html += '<span style="font-size:2.5rem">🕶️</span>';
      }
      html += '</div>';
      html += '<div style="font-weight:700;font-size:0.95rem;margin-bottom:0.25rem">' + esc(p.name) + '</div>';
      if (p.brand) {
        html += '<div style="color:var(--pp-gray-500);font-size:0.8rem;margin-bottom:0.5rem">' + esc(p.brand) + '</div>';
      }
      html += '<button data-cmp-remove="' + esc(p.id) + '" style="background:none;border:none;cursor:pointer;color:var(--pp-gray-400);font-size:1rem" title="Remove">✕</button>';
      html += '</div></th>';
    });
    html += '</tr></thead>';

    // body
    html += '<tbody>';
    SPECS.forEach(function (spec) {
      html += '<tr style="border-bottom:1px solid var(--pp-gray-100)">';
      html += '<td style="padding:0.85rem 1rem;font-size:0.85rem;font-weight:600;color:var(--pp-gray-600);background:var(--pp-gray-50)">' + esc(spec.label) + '</td>';
      items.forEach(function (p) {
        var raw = p[spec.key];
        var val = spec.format ? spec.format(raw) : (raw == null ? "—" : raw);
        html += '<td style="padding:0.85rem 1rem;text-align:center;font-size:0.9rem;color:var(--pp-gray-800);vertical-align:top">' + esc(val || "—") + '</td>';
      });
      html += '</tr>';
    });

    // action row
    html += '<tr>';
    html += '<td style="padding:1rem;background:var(--pp-gray-50)"></td>';
    items.forEach(function (p) {
      html += '<td style="padding:1rem;text-align:center">';
      html += '<div style="display:flex;flex-direction:column;gap:0.5rem">';
      html += '<button data-cmp-cart="' + esc(p.id) + '" class="pp-btn-primary" style="justify-content:center;font-size:0.85rem">Add to Cart</button>';
      html += '<a href="/eyewears/' + esc(p.slug) + '" class="pp-btn-outline" style="display:block;text-align:center;font-size:0.85rem">View Details</a>';
      html += '</div></td>';
    });
    html += '</tr>';

    html += '</tbody></table></div>';

    if (items.length < 3) {
      html +=
        '<div style="margin-top:2rem;text-align:center">' +
          '<p style="color:var(--pp-gray-500);margin-bottom:1rem">You can compare up to 3 products. Add more from the catalog.</p>' +
          '<a href="/eyewears" class="pp-btn-outline">+ Add More Products</a>' +
        '</div>';
    }

    html += '</div></section>';

    root.innerHTML = html;

    // wire events
    var clearBtn = root.querySelector("[data-cmp-clear]");
    if (clearBtn) clearBtn.addEventListener("click", function () { window.TrinetraaCompare.clear(); });

    root.querySelectorAll("[data-cmp-remove]").forEach(function (b) {
      b.addEventListener("click", function () {
        var id = b.getAttribute("data-cmp-remove");
        window.TrinetraaCompare.remove(isNaN(Number(id)) ? id : Number(id));
      });
    });

    root.querySelectorAll("[data-cmp-cart]").forEach(function (b) {
      b.addEventListener("click", function () {
        var id = b.getAttribute("data-cmp-cart");
        var pid = isNaN(Number(id)) ? id : Number(id);
        var p = window.TrinetraaCompare.items.find(function (x) { return x.id === pid; });
        if (!p) return;
        var price = Number(p.price);
        var salePrice = (p.discount_percentage > 0) ? price * (1 - p.discount_percentage / 100) : price;
        window.TrinetraaCart.add({ id: p.id, name: p.name, price: price, salePrice: salePrice, sale_price: salePrice, image: p.image, brand: p.brand });
      });
    });
  }

  document.addEventListener("DOMContentLoaded", render);
  document.addEventListener("compare:change", render);
})();
</script>
@endverbatim
@endpush
