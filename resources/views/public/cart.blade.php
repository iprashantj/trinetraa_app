@extends('layouts.public')
@section('title', 'Your Cart | Trinetraa Optician')
@section('content')
<div class="pp-page-header">
    <div class="pp-container">
        <div class="pp-page-header__content">
            <h1 class="pp-page-header__title">Your Cart</h1>
            <p class="pp-page-header__breadcrumb"><a href="/">Home</a> / Cart</p>
        </div>
    </div>
</div>

<section class="pp-section">
    <div class="pp-container" id="cartContainer" style="max-width:800px"></div>
</section>
@endsection
@push('scripts')
@verbatim
<script>
(function () {
    var container = document.getElementById('cartContainer');

    function render() {
        var Cart = window.TrinetraaCart;
        if (!Cart) return;
        var items = Cart.items;
        if (!items.length) {
            container.style.maxWidth = '';
            container.innerHTML = '<div class="pp-empty"><div class="pp-empty__icon">🛒</div><div class="pp-empty__title">Your cart is empty</div><p style="color:var(--pp-gray-600);margin-bottom:1.5rem">Add some eyewears to get started.</p><a href="/eyewears" class="pp-btn-primary">Browse Eyewears</a></div>';
            return;
        }
        container.style.maxWidth = '800px';
        var rows = items.map(function (item) {
            var sale = Number(item.sale_price != null ? item.sale_price : item.salePrice || item.price || 0);
            var img = item.image ? '<img src="' + item.image + '" alt="' + item.name + '" style="width:100%;height:100%;object-fit:cover">' : '<span style="font-size:2rem">🕶️</span>';
            var brand = item.brand ? '<div style="font-size:.8rem;color:var(--pp-gray-500)">' + item.brand + '</div>' : '';
            return '<div style="display:flex;align-items:center;gap:1.5rem;padding:1.25rem;background:var(--pp-gray-50);border-radius:16px">' +
                '<div style="width:80px;height:80px;border-radius:12px;overflow:hidden;flex-shrink:0;background:var(--pp-gray-200);display:flex;align-items:center;justify-content:center">' + img + '</div>' +
                '<div style="flex:1"><div style="font-weight:700;color:var(--pp-gray-900)">' + item.name + '</div>' + brand + '<div style="font-weight:700;color:var(--pp-gold);margin-top:.25rem">₹' + sale.toFixed(0) + ' each</div></div>' +
                '<div class="pp-qty-control"><button class="pp-qty-control__btn" data-dec="' + item.id + '">−</button><span class="pp-qty-control__count">' + item.quantity + '</span><button class="pp-qty-control__btn" data-inc="' + item.id + '">+</button></div>' +
                '<div style="font-weight:800;min-width:80px;text-align:right">₹' + (sale * item.quantity).toFixed(0) + '</div>' +
                '<button data-rm="' + item.id + '" style="background:none;border:none;cursor:pointer;color:var(--pp-gray-400);font-size:1.2rem">×</button></div>';
        }).join('');

        container.innerHTML = '<div style="display:flex;flex-direction:column;gap:1rem">' + rows + '</div>' +
            '<div style="margin-top:2rem;padding:1.5rem;background:var(--pp-gray-50);border-radius:16px">' +
            '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem"><span style="font-weight:700;font-size:1.1rem">Total</span><span style="font-weight:800;font-size:1.4rem;color:var(--pp-gray-900)">₹' + Cart.total.toFixed(0) + '</span></div>' +
            '<div style="display:flex;gap:1rem"><a href="/eyewears" class="pp-btn-outline" style="flex:1;text-align:center">Continue Shopping</a><a href="/contact" class="pp-btn-primary" style="flex:1;justify-content:center">Contact to Order</a></div>' +
            '<button id="clearCart" style="margin-top:.75rem;background:none;border:none;color:var(--pp-gray-400);cursor:pointer;width:100%;text-align:center;font-size:.85rem">Clear Cart</button></div>';

        container.querySelectorAll('[data-inc]').forEach(function (b) { b.addEventListener('click', function () { var id = Number(b.getAttribute('data-inc')); var it = Cart.items.find(function (i) { return i.id === id; }); Cart.updateQty(id, it.quantity + 1); }); });
        container.querySelectorAll('[data-dec]').forEach(function (b) { b.addEventListener('click', function () { var id = Number(b.getAttribute('data-dec')); var it = Cart.items.find(function (i) { return i.id === id; }); if (it.quantity === 1) Cart.remove(id); else Cart.updateQty(id, it.quantity - 1); }); });
        container.querySelectorAll('[data-rm]').forEach(function (b) { b.addEventListener('click', function () { Cart.remove(Number(b.getAttribute('data-rm'))); }); });
        var cc = document.getElementById('clearCart'); if (cc) cc.addEventListener('click', function () { Cart.clear(); });
    }
    document.addEventListener('cart:change', render);
    document.addEventListener('DOMContentLoaded', render);
})();
</script>
@endverbatim
@endpush
