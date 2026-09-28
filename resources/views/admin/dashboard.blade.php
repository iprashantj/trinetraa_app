@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
<div id="dashError" class="alert alert-danger m-3" style="display:none"></div>

<div id="dashWrap">
    <div class="admin-page-header">
        <h1 class="admin-page-title">Dashboard</h1>
    </div>

    <div class="admin-bento">
        <a href="/admin/eyewears" class="admin-bento-card admin-bento-card--featured">
            <div class="admin-bento-card__thumb admin-bento-card__thumb--g1 admin-bento-card__thumb--overlay">
                <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:5rem;opacity:0.25">🕶️</div>
                <div class="admin-bento-card__arrow">↗</div>
            </div>
            <div class="admin-bento-card__body">
                <div class="admin-bento-card__label">Eyewears</div>
                <div class="admin-bento-card__count" data-stat="eyewears">—</div>
                <div class="admin-bento-card__sub">Active listings</div>
            </div>
        </a>

        <div class="admin-bento-card admin-bento-card--tall admin-bento-card--greeting" style="cursor:default">
            <div class="admin-greeting-orb"></div>
            <div class="admin-greeting-title"><span id="greetingText">Hello</span>, <span>Admin</span> — What's on your mind?</div>
            <div class="admin-stats-list" id="greetingStats" style="display:none">
                <div class="admin-stat-row">
                    <span class="admin-stat-row__label">⭐ Pending Reviews</span>
                    <span class="admin-stat-row__value" id="gsPendingReviews"></span>
                </div>
                <div class="admin-stat-row">
                    <span class="admin-stat-row__label">📦 Total Orders</span>
                    <span class="admin-stat-row__value" id="gsOrders"></span>
                </div>
                <div class="admin-stat-row">
                    <span class="admin-stat-row__label">👥 Customers</span>
                    <span class="admin-stat-row__value" id="gsCustomers"></span>
                </div>
            </div>
            <div style="margin-top:16px;position:relative;z-index:1;width:100%">
                <div style="font-weight:700;font-size:0.75rem;color:#9ca3af;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:8px">Quick Links</div>
                @php
                    $quickLinks = [
                        ['icon' => '🕶️', 'title' => 'Add New Eyewear', 'sub' => 'List a new product', 'to' => '/admin/eyewears'],
                        ['icon' => '👥', 'title' => 'Manage Customers', 'sub' => 'View customer records', 'to' => '/admin/customers'],
                        ['icon' => '📅', 'title' => 'Appointments', 'sub' => 'Check upcoming bookings', 'to' => '/admin/appointments'],
                        ['icon' => '🖼', 'title' => 'Gallery', 'sub' => 'Upload store photos', 'to' => '/admin/gallery'],
                    ];
                @endphp
                @foreach($quickLinks as $q)
                    <a href="{{ $q['to'] }}" class="admin-quick-card">
                        <div class="admin-quick-card__icon">{{ $q['icon'] }}</div>
                        <div class="admin-quick-card__body">
                            <div class="admin-quick-card__title">{{ $q['title'] }}</div>
                            <div class="admin-quick-card__sub">{{ $q['sub'] }}</div>
                        </div>
                        <span class="admin-quick-card__arrow">→</span>
                    </a>
                @endforeach
            </div>
        </div>

        @php
            $rest = [
                ['key' => 'customers', 'label' => 'Customers', 'to' => '/admin/customers', 'gradient' => 'g3', 'sub' => 'Total registered'],
                ['key' => 'orders', 'label' => 'Orders', 'to' => '/admin/orders', 'gradient' => 'g8', 'sub' => 'All time orders'],
                ['key' => 'pending_reviews', 'label' => 'Pending Reviews', 'to' => '/admin/reviews', 'gradient' => 'g5', 'sub' => 'Awaiting approval', 'badge' => 'red', 'warningKey' => true],
                ['key' => 'stock_items', 'label' => 'Stock Items', 'to' => '/admin/stock-items', 'gradient' => 'g6', 'sub' => 'Items tracked'],
                ['key' => 'frame_bills', 'label' => 'Frame Bills', 'to' => '/admin/frame-bills', 'gradient' => 'g7', 'sub' => 'Total invoices'],
                ['key' => 'eye_checkup_bills', 'label' => 'Eye Checkup Bills', 'to' => '/admin/eye-checkup-bills', 'gradient' => 'g4', 'sub' => 'Total records'],
                ['key' => 'enquiries', 'label' => 'Enquiries', 'to' => '/admin/enquiries', 'gradient' => 'g8', 'sub' => 'Contact messages'],
            ];
        @endphp
        @foreach($rest as $card)
            <a href="{{ $card['to'] }}" class="admin-bento-card admin-bento-card--stat">
                <div class="admin-bento-card__thumb admin-bento-card__thumb--{{ $card['gradient'] }} admin-bento-card__thumb--overlay" style="height:90px">
                    <div class="admin-bento-card__arrow">↗</div>
                </div>
                <div class="admin-bento-card__body">
                    <div class="admin-bento-card__label">{{ $card['label'] }}</div>
                    <div class="admin-bento-card__count" data-stat="{{ $card['key'] }}" style="font-size:1.6rem">—</div>
                    <div class="admin-bento-card__sub">{{ $card['sub'] }}</div>
                    @if(!empty($card['warningKey']))
                        <span class="admin-bento-card__badge admin-bento-card__badge--{{ $card['badge'] ?? 'orange' }}" data-warning="{{ $card['key'] }}" style="display:none"></span>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
@verbatim
<script>
(function () {
    function getGreeting() {
        var h = new Date().getHours();
        if (h < 12) return 'Good Morning';
        if (h < 18) return 'Good Afternoon';
        return 'Good Evening';
    }
    document.getElementById('greetingText').textContent = getGreeting();

    document.addEventListener('DOMContentLoaded', function () {
        fetch('/api/admin/dashboard')
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.data) { showError('Failed to load.'); return; }
                renderStats(d.data);
            })
            .catch(function () { showError('Failed to load dashboard.'); });
    });

    function showError(msg) {
        var wrap = document.getElementById('dashWrap');
        if (wrap) wrap.style.display = 'none';
        var err = document.getElementById('dashError');
        err.textContent = msg;
        err.style.display = 'block';
    }

    function renderStats(stats) {
        document.querySelectorAll('[data-stat]').forEach(function (el) {
            var key = el.getAttribute('data-stat');
            el.textContent = (stats[key] !== undefined && stats[key] !== null) ? stats[key] : '—';
        });

        var gs = document.getElementById('greetingStats');
        gs.style.display = '';
        var pr = document.getElementById('gsPendingReviews');
        pr.textContent = stats.pending_reviews;
        pr.style.color = stats.pending_reviews > 0 ? '#dc2626' : '#16a34a';
        document.getElementById('gsOrders').textContent = stats.orders;
        document.getElementById('gsCustomers').textContent = stats.customers;

        document.querySelectorAll('[data-warning]').forEach(function (el) {
            var key = el.getAttribute('data-warning');
            if (stats[key] > 0) {
                el.textContent = stats[key] + ' pending';
                el.style.display = '';
            }
        });
    }
})();
</script>
@endverbatim
@endpush
