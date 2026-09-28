@extends('layouts.public')
@section('title', 'My Account | Trinetraa Optician')
@section('no_index', '1')
@section('content')
<div class="pp-page-header">
    <div class="pp-container">
        <div class="pp-page-header__content">
            <h1 class="pp-page-header__title">My Account</h1>
            <p class="pp-page-header__breadcrumb"><a href="/">Home</a> / Account</p>
        </div>
    </div>
</div>

<section class="pp-section">
    <div class="pp-container">
        <div style="display:grid;grid-template-columns:260px 1fr;gap:2rem;align-items:start">
            <div style="background:var(--pp-gray-50);border-radius:20px;padding:1.5rem">
                <div id="acctAvatar" style="width:64px;height:64px;border-radius:50%;background:var(--pp-gold);display:flex;align-items:center;justify-content:center;font-size:1.75rem;font-weight:800;color:#fff;margin:0 auto 1rem">{{ strtoupper(substr($authCustomer['name'] ?? 'U', 0, 1)) }}</div>
                <div style="text-align:center;font-weight:700;font-size:1.05rem;color:var(--pp-gray-900)">{{ $authCustomer['name'] ?? '' }}</div>
                <div style="text-align:center;font-size:.85rem;color:var(--pp-gray-500);margin-bottom:1.5rem">{{ $authCustomer['email'] ?? '' }}</div>
                <div style="display:flex;flex-direction:column;gap:.5rem">
                    <button id="tabWishlist" class="acct-tab" data-tab="wishlist" style="padding:.6rem 1rem;border-radius:10px;border:none;cursor:pointer;text-align:left;font-family:inherit;font-weight:700;background:var(--pp-gold);color:#fff">❤️ Wishlist (<span id="wishCount">0</span>)</button>
                    <button id="tabRecent" class="acct-tab" data-tab="recent" style="padding:.6rem 1rem;border-radius:10px;border:none;cursor:pointer;text-align:left;font-family:inherit;font-weight:500;background:transparent;color:var(--pp-gray-700)">🕑 Recently Viewed (<span id="recentCount">0</span>)</button>
                    <a href="/loyalty" style="padding:.6rem 1rem;border-radius:10px;text-decoration:none;display:block;font-family:inherit;font-weight:500;color:var(--pp-gray-700);background:transparent">🏆 Loyalty Points</a>
                    <a href="/subscriptions" style="padding:.6rem 1rem;border-radius:10px;text-decoration:none;display:block;font-family:inherit;font-weight:500;color:var(--pp-gray-700);background:transparent">🔄 Lens Subscriptions</a>
                </div>
                <button onclick="window.TrinetraaAuth.logout()" style="margin-top:1.5rem;width:100%;padding:.6rem 1rem;border-radius:10px;border:1.5px solid var(--pp-gray-200);cursor:pointer;background:none;font-family:inherit;color:var(--pp-gray-500);font-size:.9rem">Sign Out</button>
            </div>

            <div>
                <h2 id="acctTitle" style="font-family:var(--pp-font-heading);font-weight:800;margin-bottom:1.5rem">My Wishlist</h2>
                <div id="acctItems"></div>
            </div>
        </div>
    </div>
</section>
@endsection
@push('scripts')
@verbatim
<script>
(function () {
    var wishlist = [], recent = [], tab = 'wishlist';
    var itemsEl = document.getElementById('acctItems');

    function render() {
        var items = tab === 'wishlist' ? wishlist : recent;
        document.getElementById('acctTitle').textContent = tab === 'wishlist' ? 'My Wishlist' : 'Recently Viewed';
        document.getElementById('tabWishlist').style.fontWeight = tab === 'wishlist' ? 700 : 500;
        document.getElementById('tabWishlist').style.background = tab === 'wishlist' ? 'var(--pp-gold)' : 'transparent';
        document.getElementById('tabWishlist').style.color = tab === 'wishlist' ? '#fff' : 'var(--pp-gray-700)';
        document.getElementById('tabRecent').style.fontWeight = tab === 'recent' ? 700 : 500;
        document.getElementById('tabRecent').style.background = tab === 'recent' ? 'var(--pp-gold)' : 'transparent';
        document.getElementById('tabRecent').style.color = tab === 'recent' ? '#fff' : 'var(--pp-gray-700)';

        if (!items.length) {
            itemsEl.innerHTML = '<div class="pp-empty"><div class="pp-empty__icon">' + (tab === 'wishlist' ? '❤️' : '👁️') + '</div><div class="pp-empty__title">' + (tab === 'wishlist' ? 'Your wishlist is empty' : 'Nothing viewed yet') + '</div><a href="/eyewears" class="pp-btn-primary" style="margin-top:1rem">Browse Eyewears</a></div>';
            return;
        }
        itemsEl.innerHTML = '<div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(200px, 1fr));gap:1rem">' + items.map(function (item) {
            var ew = item.eyewear || {};
            var img = ew.image ? '<img src="' + ew.image + '" alt="' + ew.name + '" style="width:100%;height:100%;object-fit:cover">' : '<span style="font-size:2.5rem">🕶️</span>';
            var removeBtn = tab === 'wishlist' ? '<button data-remove="' + ew.id + '" style="padding:.4rem .6rem;background:none;border:1.5px solid var(--pp-gray-200);border-radius:8px;cursor:pointer;font-size:.8rem;color:var(--pp-gray-500)">✕</button>' : '';
            return '<div style="background:var(--pp-gray-50);border-radius:16px;overflow:hidden"><div style="width:100%;aspect-ratio:4/3;background:var(--pp-gray-200);display:flex;align-items:center;justify-content:center">' + img + '</div><div style="padding:.75rem"><div style="font-weight:700;font-size:.9rem;color:var(--pp-gray-900);margin-bottom:.25rem">' + ew.name + '</div><div style="font-weight:700;color:var(--pp-gold);font-size:.9rem">₹' + ew.price + '</div><div style="display:flex;gap:.5rem;margin-top:.5rem"><a href="/eyewears/' + ew.slug + '" style="flex:1;text-align:center;padding:.4rem;background:var(--pp-gold);color:#fff;border-radius:8px;font-size:.8rem;font-weight:600;text-decoration:none">View</a>' + removeBtn + '</div></div></div>';
        }).join('') + '</div>';

        itemsEl.querySelectorAll('[data-remove]').forEach(function (b) {
            b.addEventListener('click', async function () {
                var id = Number(b.getAttribute('data-remove'));
                await fetch('/api/customer/wishlist/' + id, { method: 'DELETE' });
                wishlist = wishlist.filter(function (w) { return w.eyewear.id !== id; });
                document.getElementById('wishCount').textContent = wishlist.length;
                render();
            });
        });
    }

    document.getElementById('tabWishlist').addEventListener('click', function () { tab = 'wishlist'; render(); });
    document.getElementById('tabRecent').addEventListener('click', function () { tab = 'recent'; render(); });

    fetch('/api/customer/wishlist').then(function (r) { return r.json(); }).then(function (d) { wishlist = d.data || []; document.getElementById('wishCount').textContent = wishlist.length; render(); });
    fetch('/api/customer/recently-viewed').then(function (r) { return r.json(); }).then(function (d) { recent = d.data || []; document.getElementById('recentCount').textContent = recent.length; });
})();
</script>
@endverbatim
@endpush
