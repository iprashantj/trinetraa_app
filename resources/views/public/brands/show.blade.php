@extends('layouts.public')
@php
    $brand = \App\Models\Brand::where('slug', $slug)->where('is_active', true)->firstOrFail();
    $_su   = rtrim($site['siteUrl'] ?? 'https://trinetraaoptician.com', '/');
    $_brTitle = $brand ? $brand->name . ' Eyewear Collection | Trinetraa Optician Nashik' : 'Brand Collection — Trinetraa Optician';
    $_brDesc  = $brand && $brand->description ? \Illuminate\Support\Str::limit(strip_tags($brand->description), 155) : 'Shop ' . ($brand ? $brand->name . ' ' : '') . 'eyewear at Trinetraa Optician Nashik. Authentic frames with warranty.';
    $_brImg   = $brand && $brand->logo ? $brand->logo : $_su . '/icons/icon-512.png';
    $_brUrl   = $_su . '/brands/' . $slug;
@endphp
@section('title', $_brTitle)
@section('meta_description', $_brDesc)
@section('og_image', $_brImg)
@section('canonical', $_brUrl)
@if($brand)
@push('structured_data')
@php
    $at = '@';
    $brandJsonLd = json_encode(array_filter([
        $at.'context'   => 'https://schema.org',
        $at.'type'      => 'Brand',
        'name'          => $brand->name,
        'description'   => $brand->description,
        'logo'          => $brand->logo ?: null,
        'url'           => $brand->website ?: $_brUrl,
    ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $brBreadJsonLd = json_encode([
        $at.'context'     => 'https://schema.org',
        $at.'type'        => 'BreadcrumbList',
        'itemListElement' => [
            [$at.'type' => 'ListItem', 'position' => 1, 'name' => 'Home',   'item' => $_su . '/'],
            [$at.'type' => 'ListItem', 'position' => 2, 'name' => 'Brands', 'item' => $_su . '/brands'],
            [$at.'type' => 'ListItem', 'position' => 3, 'name' => $brand->name, 'item' => $_brUrl],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp
<script type="application/ld+json">{!! $brandJsonLd !!}</script>
<script type="application/ld+json">{!! $brBreadJsonLd !!}</script>
@endpush
@endif
@section('content')
<div id="brandRoot">
    <div class="pp-loading" style="min-height:60vh"><div class="pp-spinner"></div></div>
</div>
@endsection
@push('scripts')
<script>window.__brandSlug = @json($slug);</script>
@verbatim
<script>
(function () {
    var root = document.getElementById('brandRoot');
    function esc(s){ return String(s==null?'':s); }

    function productCard(item) {
        var dp = item.discount_percentage || 0;
        var price = Number(item.price);
        var sale = dp > 0 ? price * (1 - dp / 100) : null;
        var img = item.image ? '<img src="' + item.image + '" alt="' + esc(item.name) + '" loading="lazy">' : '<div class="pp-product-card__placeholder">🕶️</div>';
        var badge = dp > 0 ? '<span class="pp-badge-sale">' + dp + '% OFF</span>' : '';
        var orig = sale !== null ? '<span class="pp-product-card__original">₹' + price.toFixed(0) + '</span>' : '';
        var prod = { id:item.id, name:item.name, price:price, sale_price: sale!==null?sale:price, image:item.image||null, brand:item.brand||'', slug:item.slug };
        return '<div class="pp-product-card"><div class="pp-product-card__image-wrap">' + img + badge + '</div>' +
            '<div class="pp-product-card__body"><div class="pp-product-card__category">' + esc(item.category && item.category.name) + '</div>' +
            '<div class="pp-product-card__name">' + esc(item.name) + '</div>' +
            '<div class="pp-product-card__pricing"><span class="pp-product-card__price">₹' + (sale !== null ? sale.toFixed(0) : price.toFixed(0)) + '</span>' + orig + '</div></div>' +
            '<div class="pp-product-card__footer" style="display:flex;gap:.5rem">' +
            '<a href="/eyewears/' + item.slug + '" class="pp-btn-navy pp-btn-sm" style="flex:1;justify-content:center">View</a>' +
            '<button class="pp-btn-primary pp-btn-sm" data-add=\'' + JSON.stringify(prod).replace(/'/g, "&#39;") + '\'>+ Cart</button></div></div>';
    }

    fetch('/api/public/brands/' + window.__brandSlug).then(function (r) {
        if (!r.ok) return null; return r.json();
    }).then(function (d) {
        if (!d || !d.data) {
            root.innerHTML = '<div style="text-align:center;padding:6rem 2rem"><div style="font-size:4rem;margin-bottom:1rem">🔍</div><h2>Brand not found</h2><a href="/brands" class="pp-btn-outline" style="margin-top:1rem;display:inline-block">← All Brands</a></div>';
            return;
        }
        var brand = d.data.brand, eyewears = d.data.eyewears || [];
        var logo = brand.logo ? '<img src="' + brand.logo + '" alt="' + esc(brand.name) + '" style="height:80px;object-fit:contain;background:#fff;padding:.75rem;border-radius:.75rem">' : '';
        var desc = brand.description ? '<p style="opacity:.85;max-width:600px">' + esc(brand.description) + '</p>' : '';
        var story = brand.story ? '<section class="pp-section animate-on-scroll"><div class="pp-container" style="max-width:800px"><div class="pp-section__eyebrow">Brand Story</div><div style="color:#9FB1C7;line-height:1.9;font-size:1.05rem;white-space:pre-wrap">' + esc(brand.story) + '</div></div></section>' : '';
        var grid;
        if (eyewears.length) {
            grid = '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.5rem">' + eyewears.map(productCard).join('') + '</div>' +
                '<div style="text-align:center;margin-top:2rem"><a href="/eyewears?brand=' + encodeURIComponent(brand.name) + '" class="pp-btn-outline">See All ' + esc(brand.name) + ' Products →</a></div>';
        } else {
            grid = '<div class="pp-empty"><div class="pp-empty__icon">🕶️</div><div class="pp-empty__title">Products coming soon</div><p>Check back or <a href="/contact">contact us</a> to enquire.</p></div>';
        }

        root.innerHTML =
            '<section style="background:linear-gradient(135deg,#0d1b2a 0%,#1a3a5c 100%);color:#fff;padding:5rem 0 3rem"><div class="pp-container" style="display:flex;align-items:center;gap:2rem;flex-wrap:wrap">' + logo +
            '<div><div style="color:#d4af37;font-weight:600;margin-bottom:.5rem">Authorised Dealer</div><h1 style="font-size:clamp(2rem,5vw,3rem);font-weight:800;margin-bottom:.5rem">' + esc(brand.name) + '</h1>' + desc + '</div></div></section>' +
            story +
            '<section class="pp-section pp-section--alt animate-on-scroll"><div class="pp-container"><div class="pp-section__header"><div class="pp-section__eyebrow">' + esc(brand.name) + ' Collection</div><h2 class="pp-section__title">Available Products</h2></div>' + grid + '</div></section>' +
            '<section class="pp-section animate-on-scroll"><div class="pp-container"><div class="pp-cta-banner"><h2 class="pp-cta-banner__title">Looking for a specific ' + esc(brand.name) + ' model?</h2><p class="pp-cta-banner__subtitle">Contact us with the model number and we\'ll check stock or help you order it.</p><a href="/contact" class="pp-btn-primary">Enquire Now →</a></div></div></section>';

        root.querySelectorAll('[data-add]').forEach(function (b) {
            b.addEventListener('click', function (e) { e.preventDefault(); window.TrinetraaCart.add(JSON.parse(b.getAttribute('data-add'))); });
        });
    });
})();
</script>
@endverbatim
@endpush
