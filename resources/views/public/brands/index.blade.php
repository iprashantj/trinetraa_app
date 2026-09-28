@extends('layouts.public')
@section('title', 'Eyewear Brands We Carry — Ray-Ban, Titan, Fastrack & More | Trinetraa Optician')
@section('meta_description', 'Explore branded eyewear at Trinetraa Optician Nashik — Ray-Ban, Titan, Fastrack, Lenskart and more. Authentic frames with warranty. Shop in-store or online.')
@section('canonical', 'https://trinetraaoptician.com/brands')
@section('content')
<style>
    @keyframes brand-shimmer { 0%,100%{opacity:0.6} 50%{opacity:1} }
    .brand-tile { position:relative; overflow:hidden; cursor:pointer; transition:transform 0.3s ease,box-shadow 0.3s ease; }
    .brand-tile:hover { transform:scale(1.02); box-shadow:0 20px 60px rgba(0,0,0,0.5) !important; z-index:2; }
    .brand-tile:hover .brand-tile__overlay { opacity:1 !important; }
    .brand-tile:hover .brand-tile__cta { opacity:1 !important; transform:translateY(0) !important; }
    .brand-tile__bg { position:absolute;inset:0;background-size:auto; transition:transform 0.4s ease; }
    .brand-tile:hover .brand-tile__bg { transform:scale(1.05); }
    .brand-tile__overlay { position:absolute;inset:0;background:rgba(0,0,0,0.3);opacity:0;transition:opacity 0.3s; }
    .brand-tile__content { position:relative;z-index:2;height:100%;display:flex;flex-direction:column;justify-content:flex-end;padding:1.75rem 1.5rem; }
    .brand-tile__cta { opacity:0;transform:translateY(8px);transition:all 0.3s ease 0.05s; }
    .brand-tag { display:inline-block;padding:3px 10px;border-radius:999px;font-size:0.65rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;margin-bottom:0.6rem; }
    @media(max-width:900px) {
      .brands-editorial-grid { grid-template-columns:1fr !important; grid-template-rows:auto !important; }
      .brands-editorial-grid .featured { grid-row:auto !important; min-height:320px !important; }
      .brands-right-grid { grid-template-columns:1fr 1fr !important; }
    }
    @media(max-width:600px) { .brands-right-grid { grid-template-columns:1fr !important; } }
</style>

<section style="background:#0a0a0a;color:#fff;padding:5rem 0 3rem;text-align:center">
    <div class="pp-container">
        <div style="font-size:.72rem;font-weight:700;letter-spacing:.18em;text-transform:uppercase;color:#f97316;margin-bottom:.75rem">Top Brands</div>
        <h1 style="font-size:clamp(2rem,5vw,3.5rem);font-weight:900;margin:0 0 1rem;letter-spacing:-0.03em">Brands We Carry</h1>
        <p style="max-width:520px;margin:0 auto 2rem;color:rgba(255,255,255,0.6);line-height:1.7">Authorised dealers for the world's most iconic eyewear. 100% authentic, full warranty guaranteed.</p>
        <div id="brandFilters" style="display:flex;gap:.5rem;justify-content:center;flex-wrap:wrap"></div>
    </div>
</section>

<div id="editorialWrap" style="background:#0a0a0a;padding:0.5rem">
    <div class="pp-container" style="max-width:100%;padding:0 .5rem">
        <div class="brands-editorial-grid" id="editorialGrid" style="display:grid;grid-template-columns:2fr 3fr;grid-template-rows:380px 220px;gap:.5rem"></div>
    </div>
</div>

<div id="remainingWrap" style="background:#0a0a0a;padding:.5rem .5rem 1rem">
    <div class="pp-container" style="max-width:100%;padding:0 .5rem">
        <div id="remainingGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:.5rem"></div>
    </div>
</div>

<section style="background:#111;border-top:1px solid #222;padding:2.5rem 0">
    <div class="pp-container">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1.5rem;text-align:center;color:#fff">
            <div><div style="font-size:1.75rem;margin-bottom:6px">✅</div><div style="font-weight:700;font-size:.85rem">100% Authentic</div></div>
            <div><div style="font-size:1.75rem;margin-bottom:6px">🛡️</div><div style="font-weight:700;font-size:.85rem">Full Warranty</div></div>
            <div><div style="font-size:1.75rem;margin-bottom:6px">🚚</div><div style="font-weight:700;font-size:.85rem">Free Shipping</div></div>
            <div><div style="font-size:1.75rem;margin-bottom:6px">↩️</div><div style="font-weight:700;font-size:.85rem">Easy Returns</div></div>
        </div>
    </div>
</section>

<section style="background:#0a0a0a;padding:3rem 0;text-align:center;color:#fff">
    <h2 style="font-weight:700;margin-bottom:.5rem">Don't see your brand?</h2>
    <p style="color:rgba(255,255,255,0.5);margin-bottom:1.5rem">We're always expanding. Contact us to check availability or place a special order.</p>
    <a href="/contact" style="background:#f97316;color:#fff;text-decoration:none;padding:12px 28px;border-radius:8px;font-weight:700;font-size:.9rem">Enquire Now →</a>
</section>
@endsection
@push('scripts')
<script id="brandsData" type="application/json">@json($brands ?? [])</script>
@verbatim
<script>
(function () {
    var BRANDS = [
        { name:"Ray-Ban", slug:"ray-ban", tagline:"Iconic Since 1937", sub:"The world's most recognisable eyewear — Aviator, Wayfarer & beyond.", tag:"Premium", featured:true, photo:"https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=900&h=1100&fit=crop&crop=faces,top&auto=format&q=80", overlay:"linear-gradient(to top, rgba(20,0,0,0.92) 0%, rgba(20,0,0,0.45) 50%, rgba(0,0,0,0.15) 100%)", accent:"#CC0000", logoSvg:'<svg viewBox="0 0 200 50" width="160"><text x="100" y="36" text-anchor="middle" fill="white" font-size="28" font-weight="300" font-family="Georgia,serif" letter-spacing="6">Ray·Ban</text></svg>' },
        { name:"Oakley", slug:"oakley", tagline:"Performance Redefined", sub:"Engineered for champions. Built for every day.", tag:"Sports", photo:"https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?w=600&h=500&fit=crop&crop=faces&auto=format&q=80", overlay:"linear-gradient(to top, rgba(0,0,10,0.92) 0%, rgba(0,0,10,0.5) 55%, rgba(0,0,0,0.2) 100%)", accent:"#ef4444", logoSvg:'<svg viewBox="0 0 200 50" width="140"><text x="100" y="36" text-anchor="middle" fill="white" font-size="22" font-weight="900" font-family="Arial,sans-serif" letter-spacing="8">OAKLEY</text></svg>' },
        { name:"Carrera", slug:"carrera", tagline:"Italian Racing Heritage", sub:"Bold frames born on the racetrack.", tag:"Luxury", photo:"https://images.unsplash.com/photo-1509695507497-903c140c43b0?w=600&h=500&fit=crop&crop=faces&auto=format&q=80", overlay:"linear-gradient(to top, rgba(5,10,25,0.92) 0%, rgba(5,10,25,0.5) 55%, rgba(0,0,0,0.15) 100%)", accent:"#3b82f6", logoSvg:'<svg viewBox="0 0 200 50" width="140"><text x="100" y="36" text-anchor="middle" fill="white" font-size="20" font-weight="700" font-family="Arial,sans-serif" letter-spacing="6">CARRERA</text></svg>' },
        { name:"Vogue", slug:"vogue", tagline:"Runway to Reality", sub:"Fashion-forward frames for every look.", tag:"Fashion", photo:"https://images.unsplash.com/photo-1524253482453-3fed8d2fe12b?w=600&h=500&fit=crop&crop=faces,top&auto=format&q=80", overlay:"linear-gradient(to top, rgba(20,5,15,0.92) 0%, rgba(20,5,15,0.45) 55%, rgba(0,0,0,0.1) 100%)", accent:"#ec4899", logoSvg:'<svg viewBox="0 0 200 50" width="130"><text x="100" y="36" text-anchor="middle" fill="white" font-size="24" font-weight="300" font-family="Georgia,serif" letter-spacing="8">vogue</text></svg>' },
        { name:"Fastrack", slug:"fastrack", tagline:"Born Bold", sub:"Vibrant frames for India's youth.", tag:"Youth", photo:"https://images.unsplash.com/photo-1556306535-38febf6782e7?w=600&h=280&fit=crop&auto=format&q=80", overlay:"linear-gradient(to top, rgba(10,8,0,0.92) 0%, rgba(10,8,0,0.45) 55%, rgba(0,0,0,0.1) 100%)", accent:"#f59e0b", logoSvg:'<svg viewBox="0 0 200 50" width="150"><text x="100" y="36" text-anchor="middle" fill="white" font-size="18" font-weight="900" font-family="Arial,sans-serif" letter-spacing="5">FASTRACK</text></svg>' },
        { name:"Titan", slug:"titan", tagline:"Trust. Clarity. Style.", sub:"India's most trusted eyewear brand.", tag:"Indian", photo:"https://images.unsplash.com/photo-1577803645773-f96470509666?w=600&h=280&fit=crop&crop=faces&auto=format&q=80", overlay:"linear-gradient(to top, rgba(0,8,20,0.92) 0%, rgba(0,8,20,0.45) 55%, rgba(0,0,0,0.1) 100%)", accent:"#60a5fa", logoSvg:'<svg viewBox="0 0 200 50" width="120"><text x="100" y="36" text-anchor="middle" fill="white" font-size="22" font-weight="700" font-family="Arial,sans-serif" letter-spacing="8">TITAN</text></svg>' },
        { name:"VDGE", slug:"vdge", tagline:"Minimal. Modern. Sharp.", sub:"Contemporary eyewear for the design-conscious.", tag:"Contemporary", photo:"https://images.unsplash.com/photo-1516975080664-ed2fc6a32937?w=600&h=280&fit=crop&crop=faces&auto=format&q=80", overlay:"linear-gradient(to top, rgba(5,5,5,0.93) 0%, rgba(5,5,5,0.4) 55%, rgba(0,0,0,0.1) 100%)", accent:"#a3a3a3", logoSvg:'<svg viewBox="0 0 200 50" width="120"><text x="100" y="36" text-anchor="middle" fill="white" font-size="28" font-weight="900" font-family="Arial,sans-serif" letter-spacing="10">VDGE</text></svg>' },
        { name:"Eye Plus", slug:"eye-plus", tagline:"See More. Feel More.", sub:"Premium lenses. Accessible prices.", tag:"Value", photo:"https://images.unsplash.com/photo-1591076482161-42ce6da69f67?w=600&h=240&fit=crop&auto=format&q=80", overlay:"linear-gradient(to top, rgba(20,8,0,0.92) 0%, rgba(20,8,0,0.45) 55%, rgba(0,0,0,0.1) 100%)", accent:"#f97316", logoSvg:'<svg viewBox="0 0 200 50" width="140"><text x="100" y="36" text-anchor="middle" fill="white" font-size="18" font-weight="800" font-family="Arial,sans-serif" letter-spacing="4">EYE PLUS</text></svg>' },
        { name:"Police", slug:"police", tagline:"Rebellion in Every Frame", sub:"Edgy European design with attitude.", tag:"Lifestyle", photo:"https://images.unsplash.com/photo-1508296695146-257a814070b4?w=600&h=240&fit=crop&crop=faces&auto=format&q=80", overlay:"linear-gradient(to top, rgba(8,5,20,0.93) 0%, rgba(8,5,20,0.45) 55%, rgba(0,0,0,0.1) 100%)", accent:"#8b5cf6", logoSvg:'<svg viewBox="0 0 200 50" width="130"><text x="100" y="36" text-anchor="middle" fill="white" font-size="20" font-weight="700" font-family="Arial,sans-serif" letter-spacing="7">POLICE</text></svg>' },
        { name:"Essilor", slug:"essilor", tagline:"Better Vision for All", sub:"World leader in ophthalmic optics.", tag:"Lens", photo:"https://images.unsplash.com/photo-1499996860823-5214fcc65f8f?w=600&h=240&fit=crop&auto=format&q=80", overlay:"linear-gradient(to top, rgba(0,8,20,0.93) 0%, rgba(0,8,20,0.45) 55%, rgba(0,0,0,0.1) 100%)", accent:"#38bdf8", logoSvg:'<svg viewBox="0 0 200 50" width="140"><text x="100" y="36" text-anchor="middle" fill="white" font-size="20" font-weight="700" font-family="Arial,sans-serif" letter-spacing="5">ESSILOR</text></svg>' },
        { name:"Zeiss", slug:"zeiss", tagline:"Precision Without Compromise", sub:"The benchmark in optical lens technology.", tag:"Premium Lens", photo:"https://images.unsplash.com/photo-1582142306909-195724d33ffc?w=600&h=240&fit=crop&auto=format&q=80", overlay:"linear-gradient(to top, rgba(5,5,5,0.93) 0%, rgba(5,5,5,0.4) 55%, rgba(0,0,0,0.1) 100%)", accent:"#d1d5db", logoSvg:'<svg viewBox="0 0 200 50" width="120"><text x="100" y="36" text-anchor="middle" fill="white" font-size="24" font-weight="700" font-family="Arial,sans-serif" letter-spacing="7">ZEISS</text></svg>' },
        { name:"Hoya", slug:"hoya", tagline:"Japanese Optical Excellence", sub:"Advanced lens innovation since 1941.", tag:"Lens", photo:"https://images.unsplash.com/photo-1574258495973-f010dfbb5371?w=600&h=240&fit=crop&auto=format&q=80", overlay:"linear-gradient(to top, rgba(18,0,0,0.93) 0%, rgba(18,0,0,0.45) 55%, rgba(0,0,0,0.1) 100%)", accent:"#f87171", logoSvg:'<svg viewBox="0 0 200 50" width="110"><text x="100" y="36" text-anchor="middle" fill="white" font-size="26" font-weight="800" font-family="Arial,sans-serif" letter-spacing="6">HOYA</text></svg>' },
        { name:"Crizal", slug:"crizal", tagline:"Clarity You Can Count On", sub:"The world's #1 anti-reflective lens.", tag:"Lens", photo:"https://images.unsplash.com/photo-1487412947147-5cebf100ffc2?w=600&h=240&fit=crop&auto=format&q=80", overlay:"linear-gradient(to top, rgba(0,15,25,0.93) 0%, rgba(0,15,25,0.45) 55%, rgba(0,0,0,0.1) 100%)", accent:"#7dd3fc", logoSvg:'<svg viewBox="0 0 200 50" width="120"><text x="100" y="36" text-anchor="middle" fill="white" font-size="20" font-weight="700" font-family="Arial,sans-serif" letter-spacing="5">CRIZAL</text></svg>' },
    ];
    var TAGS = ["All"].concat(BRANDS.map(function (b) { return b.tag; }).filter(function (v, i, a) { return a.indexOf(v) === i; }));
    var EDITORIAL = BRANDS.slice(0, 7), REMAINING = BRANDS.slice(7);
    var filter = "All";

    function tile(brand, extraStyle, ctaFull) {
        var cta = ctaFull
            ? '<div class="brand-tile__cta" style="display:inline-flex;align-items:center;gap:8px;background:' + brand.accent + ';color:#fff;padding:10px 22px;border-radius:8px;font-size:.82rem;font-weight:700">Explore ' + brand.name + ' →</div>'
            : '<div class="brand-tile__cta" style="margin-top:.75rem;color:' + brand.accent + ';font-size:.75rem;font-weight:700">Explore →</div>';
        var sub = ctaFull ? '<p style="color:rgba(255,255,255,0.65);font-size:.82rem;margin:0 0 1.25rem;line-height:1.6;max-width:320px">' + brand.sub + '</p>' : '';
        return '<a href="/eyewears?brand=' + encodeURIComponent(brand.name) + '" style="text-decoration:none;display:block;' + (extraStyle || '') + '" class="brand-tile' + (ctaFull ? ' featured' : '') + '">' +
            '<div class="brand-tile__bg" style="background-image:' + brand.overlay + ", url('" + brand.photo + "');background-size:cover;background-position:center top\"></div>" +
            '<div class="brand-tile__overlay"></div>' +
            '<div class="brand-tile__content"' + (ctaFull ? ' style="justify-content:space-between;padding:2rem"' : '') + '>' +
            '<span class="brand-tag" style="background:' + brand.accent + '22;color:' + brand.accent + ';border:1px solid ' + brand.accent + '44;align-self:flex-start;margin-bottom:auto">' + brand.tag + '</span>' +
            '<div>' + brand.logoSvg + '<div style="color:rgba(255,255,255,0.45);font-size:.72rem;letter-spacing:.03em">' + brand.tagline + '</div>' + cta + '</div>' +
            '</div></a>';
    }

    function render() {
        var ed = filter === "All" ? EDITORIAL : EDITORIAL.filter(function (b) { return b.tag === filter; });
        var rem = filter === "All" ? REMAINING : REMAINING.filter(function (b) { return b.tag === filter; });

        document.getElementById('brandFilters').innerHTML = TAGS.map(function (t) {
            var on = filter === t;
            return '<button data-tag="' + t + '" style="background:' + (on ? '#f97316' : 'rgba(255,255,255,0.08)') + ';color:' + (on ? '#fff' : 'rgba(255,255,255,0.65)') + ';border:1px solid ' + (on ? '#f97316' : 'rgba(255,255,255,0.15)') + ';border-radius:999px;padding:6px 18px;font-size:.78rem;font-weight:600;cursor:pointer">' + (t === 'All' ? 'All Brands' : t) + '</button>';
        }).join('');
        document.getElementById('brandFilters').querySelectorAll('[data-tag]').forEach(function (b) {
            b.addEventListener('click', function () { filter = b.getAttribute('data-tag'); render(); });
        });

        var edWrap = document.getElementById('editorialWrap'), grid = document.getElementById('editorialGrid');
        if (ed.length) {
            edWrap.style.display = '';
            var html = '';
            if (ed[0]) html += tile(ed[0], 'grid-row:1/3', true);
            html += '<div class="brands-right-grid" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.5rem">' + ed.slice(1, 4).map(function (b) { return tile(b); }).join('') + '</div>';
            html += '<div class="brands-right-grid" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.5rem">' + ed.slice(4, 7).map(function (b) { return tile(b); }).join('') + '</div>';
            grid.innerHTML = html;
        } else { edWrap.style.display = 'none'; }

        var remWrap = document.getElementById('remainingWrap'), rgrid = document.getElementById('remainingGrid');
        if (rem.length) { remWrap.style.display = ''; rgrid.innerHTML = rem.map(function (b) { return tile(b, 'min-height:180px'); }).join(''); }
        else { remWrap.style.display = 'none'; }
    }
    render();
})();
</script>
@endverbatim
@endpush
