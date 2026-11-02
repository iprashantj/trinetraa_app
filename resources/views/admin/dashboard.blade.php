@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
@php
    $ico = [
        'cart'    => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>',
        'file'    => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
        'eye'     => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
        'users'   => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'box'     => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>',
        'star'    => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
        'mail'    => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
        'cal'     => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'image'   => '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>',
        'plus'    => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
    ];
    $i = fn ($n) => '<svg viewBox="0 0 24 24" aria-hidden="true">' . $ico[$n] . '</svg>';

    $kpis = [
        ['key' => 'orders', 'label' => 'Orders', 'icon' => 'cart', 'to' => '/admin/orders', 'tone' => '', 'foot' => 'All-time orders'],
        ['key' => 'frame_bills', 'label' => 'Frame Bills', 'icon' => 'file', 'to' => '/admin/frame-bills', 'tone' => 'green', 'foot' => 'Total invoices'],
        ['key' => 'eye_checkup_bills', 'label' => 'Eye Checkup Bills', 'icon' => 'eye', 'to' => '/admin/eye-checkup-bills', 'tone' => 'orange', 'foot' => 'Total records'],
        ['key' => 'customers', 'label' => 'Customers', 'icon' => 'users', 'to' => '/admin/customers', 'tone' => 'red', 'foot' => 'Total registered'],
    ];
    $overview = [
        ['key' => 'eyewears', 'label' => 'Eyewears', 'sub' => 'Active listings', 'icon' => 'eye', 'to' => '/admin/eyewears'],
        ['key' => 'stock_items', 'label' => 'Stock Items', 'sub' => 'Items tracked', 'icon' => 'box', 'to' => '/admin/stock-items'],
        ['key' => 'pending_reviews', 'label' => 'Pending Reviews', 'sub' => 'Awaiting approval', 'icon' => 'star', 'to' => '/admin/reviews', 'warn' => true],
        ['key' => 'enquiries', 'label' => 'Enquiries', 'sub' => 'Contact messages', 'icon' => 'mail', 'to' => '/admin/enquiries'],
        ['key' => 'appointments', 'label' => 'Pending Appointments', 'sub' => 'Awaiting confirmation', 'icon' => 'cal', 'to' => '/admin/appointments', 'warn' => true],
    ];
    $quickLinks = [
        ['icon' => 'plus', 'title' => 'Add New Eyewear', 'sub' => 'List a new product', 'to' => '/admin/eyewears'],
        ['icon' => 'users', 'title' => 'Manage Customers', 'sub' => 'View customer records', 'to' => '/admin/customers'],
        ['icon' => 'cal', 'title' => 'Appointments', 'sub' => 'Check upcoming bookings', 'to' => '/admin/appointments'],
        ['icon' => 'image', 'title' => 'Gallery', 'sub' => 'Upload store photos', 'to' => '/admin/gallery'],
    ];
@endphp

<div id="dashError" class="alert alert-danger" style="display:none"></div>

<div id="dashWrap">
    <div class="erp-kpis">
        <div class="erp-card erp-kpi">
            <div class="text-muted small">Sales today</div>
            <div class="erp-kpi__value" id="ownSales">₹0</div>
            <div class="small mt-1" id="ownSalesDelta">&nbsp;</div>
        </div>
        <a href="/admin/stock-items" class="erp-card erp-kpi">
            <div class="text-muted small">Items in stock</div>
            <div class="erp-kpi__value" id="ownStock">0</div>
            <div class="small text-muted mt-1" id="ownBranches">&nbsp;</div>
        </a>
        <a href="/admin/stock-alerts" class="erp-card erp-kpi">
            <div class="text-muted small">Low-stock alerts</div>
            <div class="erp-kpi__value" id="ownLow">0</div>
            <div class="small mt-1" id="ownLowNote">&nbsp;</div>
        </a>
        <a href="/admin/transfers" class="erp-card erp-kpi">
            <div class="text-muted small">Transfers in transit</div>
            <div class="erp-kpi__value" id="ownTransit">0</div>
            <div class="small text-muted mt-1" id="ownTransitNote">&nbsp;</div>
        </a>
    </div>

    <div class="erp-kpis">
        @foreach($kpis as $k)
            <a href="{{ $k['to'] }}" class="erp-card erp-kpi erp-kpi--{{ $k['tone'] ?: 'blue' }}">
                <div class="erp-kpi__top">
                    <div class="erp-kpi__icon">{!! $i($k['icon']) !!}</div>
                    <div>
                        <div class="erp-kpi__value" data-stat="{{ $k['key'] }}">—</div>
                        <div class="erp-kpi__label">{{ $k['label'] }}</div>
                    </div>
                </div>
                <div class="erp-kpi__foot"><span>{{ $k['foot'] }}</span><strong data-share="{{ $k['key'] }}"></strong></div>
                <div class="erp-kpi__bar"><span data-bar="{{ $k['key'] }}"></span></div>
            </a>
        @endforeach
    </div>

    <div class="erp-row">
        <div class="erp-card">
            <div class="erp-card__head">
                <h3 class="erp-card__title">Billing Record</h3>
                <div class="erp-chart__legend">
                    <span><i style="background:#dfe2ec"></i>Frame bills</span>
                    <span><i style="background:var(--erp-primary)"></i>Eye checkup bills</span>
                </div>
            </div>
            <div class="erp-card__body">
                <div class="erp-chart" id="billChart"></div>
            </div>
        </div>

        <div class="erp-card erp-sales">
            <div class="erp-sales__hero">
                <span class="erp-sales__badge">12 mo</span>
                <h2 id="revTotal">₹0</h2>
                <p>Total Billed (last 12 months)</p>
                <svg class="erp-sales__wave" viewBox="0 0 400 90" preserveAspectRatio="none" aria-hidden="true">
                    <path d="M0 60 C 40 20, 80 20, 120 55 S 200 90, 240 45 S 320 5, 360 40 S 390 55, 400 50 L400 90 L0 90 Z" fill="rgba(255,255,255,.22)"/>
                </svg>
            </div>
            <div class="erp-sales__list" id="recentBills">
                <div class="erp-item__sub py-3">No bills yet.</div>
            </div>
        </div>
    </div>

    <div class="erp-row erp-row--even">
        <div class="erp-card">
            <div class="erp-card__head"><h3 class="erp-card__title">Low-stock alerts</h3><a class="erp-card__link" href="/admin/stock-alerts">View all</a></div>
            <div class="erp-card__body" id="lowList" style="padding-top:6px;padding-bottom:6px"><div class="erp-item__sub py-3">No alerts. Set alert levels per brand or category under Inventory → Low-stock Alerts.</div></div>
        </div>
        <div class="erp-card">
            <div class="erp-card__head"><h3 class="erp-card__title">Sales by branch (today)</h3></div>
            <div class="erp-card__body" id="branchBars"><div class="erp-item__sub">No branches yet.</div></div>
        </div>
    </div>

    <div class="erp-card mb-4" style="margin-bottom:20px">
        <div class="erp-card__head"><h3 class="erp-card__title">Recent activity</h3><a class="erp-card__link" href="/admin/activity">Activity log</a></div>
        <div class="erp-card__body" id="activityList" style="padding-top:6px;padding-bottom:6px"><div class="erp-item__sub py-3">No activity yet.</div></div>
    </div>

    <div class="erp-row erp-row--even">
        <div class="erp-card">
            <div class="erp-card__head"><h3 class="erp-card__title">Store Overview</h3></div>
            <div class="erp-card__body" style="padding-top:6px;padding-bottom:6px">
                @foreach($overview as $o)
                    <a href="{{ $o['to'] }}" class="erp-item">
                        <div class="erp-item__icon">{!! $i($o['icon']) !!}</div>
                        <div class="erp-item__body">
                            <div class="erp-item__title">{{ $o['label'] }}</div>
                            <div class="erp-item__sub">{{ $o['sub'] }}</div>
                        </div>
                        <div class="erp-item__val" data-stat="{{ $o['key'] }}"@if(!empty($o['warn'])) data-warn="1"@endif>—</div>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="erp-card">
            <div class="erp-card__head"><h3 class="erp-card__title"><span id="greetingText">Hello</span>, Admin — What's on your mind?</h3></div>
            <div class="erp-card__body" style="padding-top:6px;padding-bottom:6px">
                @foreach($quickLinks as $q)
                    <a href="{{ $q['to'] }}" class="erp-item">
                        <div class="erp-item__icon">{!! $i($q['icon']) !!}</div>
                        <div class="erp-item__body">
                            <div class="erp-item__title">{{ $q['title'] }}</div>
                            <div class="erp-item__sub">{{ $q['sub'] }}</div>
                        </div>
                        <span class="erp-item__sub">→</span>
                    </a>
                @endforeach
            </div>
        </div>
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
        fetch('/api/admin/dashboard' + (ERP.currentBranch() ? '?branch_id=' + encodeURIComponent(ERP.currentBranch()) : ''))
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

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function inr(n) { return '₹' + Math.round(n).toLocaleString('en-IN'); }

    function renderStats(stats) {
        document.querySelectorAll('[data-stat]').forEach(function (el) {
            var key = el.getAttribute('data-stat');
            el.textContent = (stats[key] !== undefined && stats[key] !== null) ? stats[key] : '—';
            if (el.hasAttribute('data-warn')) el.style.color = stats[key] > 0 ? '#ea4d4d' : '#17c666';
        });

        // KPI bars: each card relative to the largest of the four.
        var keys = ['orders', 'frame_bills', 'eye_checkup_bills', 'customers'];
        var max = Math.max.apply(null, keys.map(function (k) { return +stats[k] || 0; })) || 1;
        keys.forEach(function (k) {
            var pct = Math.round(((+stats[k] || 0) / max) * 100);
            var bar = document.querySelector('[data-bar="' + k + '"]');
            if (bar) bar.style.width = pct + '%';
            var share = document.querySelector('[data-share="' + k + '"]');
            if (share) share.textContent = (+stats[k] || 0).toLocaleString('en-IN');
        });

        renderOwner(stats);

        var monthly = stats.monthly || [];
        document.getElementById('revTotal').textContent = inr(stats.revenue_total || 0);
        drawChart(monthly);

        var bills = stats.recent_bills || [];
        if (bills.length) {
            document.getElementById('recentBills').innerHTML = bills.map(function (b) {
                return '<a href="/admin/frame-bills" class="erp-item"><div class="erp-item__icon">'
                    + '<svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>'
                    + '<div class="erp-item__body"><div class="erp-item__title">' + esc(b.customer_name || 'Walk-in') + '</div>'
                    + '<div class="erp-item__sub">' + esc(b.bill_number) + '</div></div>'
                    + '<div class="erp-item__val">' + inr(b.total) + '<small>' + esc(b.bill_date) + '</small></div></a>';
            }).join('');
        }
    }

    function renderOwner(s) {
        var today = +s.sales_today || 0, yest = +s.sales_yesterday || 0;
        document.getElementById('ownSales').textContent = inr(today);
        var delta = document.getElementById('ownSalesDelta');
        if (yest > 0) {
            var pct = Math.round((today - yest) / yest * 100);
            delta.innerHTML = '<span style="color:' + (pct >= 0 ? '#17c666' : '#ea4d4d') + ';font-weight:700">' + (pct >= 0 ? '▲ ' : '▼ ') + Math.abs(pct) + '%</span> <span class="text-muted">vs yesterday</span>';
        } else {
            delta.innerHTML = '<span class="text-muted">' + (today > 0 ? 'No sales yesterday' : 'No sales yet today') + '</span>';
        }
        document.getElementById('ownStock').textContent = (+s.items_in_stock || 0).toLocaleString('en-IN');
        document.getElementById('ownBranches').textContent = (s.branches_count || 0) + ' branch' + (s.branches_count === 1 ? '' : 'es');
        var low = +s.low_stock_count || 0;
        document.getElementById('ownLow').textContent = low;
        document.getElementById('ownLow').style.color = low > 0 ? '#ea4d4d' : '';
        document.getElementById('ownLowNote').innerHTML = low > 0 ? '<span style="color:#ea4d4d;font-weight:600">Action needed</span>' : '<span class="text-muted">All stocked</span>';
        var tr = +s.transfers_in_transit || 0;
        document.getElementById('ownTransit').textContent = tr;
        document.getElementById('ownTransitNote').textContent = tr ? tr + ' awaiting receipt' : 'Nothing on the way';

        var list = s.low_stock || [];
        if (list.length) {
            document.getElementById('lowList').innerHTML = list.map(function (a) {
                return '<a href="/admin/stock-items?search=' + encodeURIComponent(a.name) + '" class="erp-item"><div class="erp-item__body"><div class="erp-item__title">' + esc((a.brand ? a.brand + ' · ' : '') + a.name) + '</div>' +
                    '<div class="erp-item__sub">Alert level ' + a.threshold + '</div></div><span class="erp-pill erp-pill--red">' + a.quantity + ' left</span></a>';
            }).join('');
        }

        var br = s.by_branch || [];
        if (br.length) {
            var max = Math.max.apply(null, br.map(function (b) { return +b.sales_today || 0; }).concat([1]));
            document.getElementById('branchBars').innerHTML = br.map(function (b) {
                var w = Math.round((+b.sales_today || 0) / max * 100);
                return '<div style="margin-bottom:14px"><div class="d-flex justify-content-between small mb-1"><b>' + esc(b.name) + '</b><span>' + inr(b.sales_today) + ' <span class="text-muted">· ' + (+b.stock || 0) + ' in stock</span></span></div>' +
                    '<div style="height:12px;background:#eef0f6;border-radius:6px;overflow:hidden"><div style="width:' + w + '%;min-width:3px;height:100%;background:#3454d1;border-radius:6px"></div></div></div>';
            }).join('');
        }

        var act = s.recent_activity || [];
        if (act.length) {
            document.getElementById('activityList').innerHTML = act.map(function (a) {
                return '<div class="erp-item"><div class="erp-item__body"><div class="erp-item__title">' + esc(a.action) + '</div><div class="erp-item__sub">' + esc(a.user || 'system') + '</div></div><div class="erp-item__sub">' + esc(a.at || '') + '</div></div>';
            }).join('');
        }
    }

    // Bar (frame / eye-checkup) + trend line chart, drawn as inline SVG.
    function drawChart(data) {
        var host = document.getElementById('billChart');
        if (!data.length) { host.innerHTML = '<div class="erp-item__sub py-4">No billing data yet.</div>'; return; }
        var W = 760, H = 320, L = 46, R = 12, T = 12, B = 28;
        var totals = data.map(function (m) { return m.frame + m.eye; });
        var maxV = Math.max.apply(null, totals.concat([1]));
        var step = Math.pow(10, Math.floor(Math.log10(maxV)));
        var top = Math.ceil(maxV / step) * step;
        var pw = W - L - R, ph = H - T - B, n = data.length, slot = pw / n, bw = Math.min(14, slot / 3);
        function y(v) { return T + ph - (v / top) * ph; }
        var out = '';
        for (var g = 0; g <= 5; g++) {
            var gv = top * g / 5, gy = y(gv);
            out += '<line class="grid" x1="' + L + '" x2="' + (W - R) + '" y1="' + gy + '" y2="' + gy + '"/>'
                + '<text x="' + (L - 8) + '" y="' + (gy + 3) + '" text-anchor="end">' + (gv >= 1000 ? Math.round(gv / 1000) + 'K' : Math.round(gv)) + '</text>';
        }
        var pts = [];
        data.forEach(function (m, i) {
            var cx = L + slot * i + slot / 2;
            out += '<rect class="bar-a" x="' + (cx - bw - 1) + '" y="' + y(m.frame) + '" width="' + bw + '" height="' + (T + ph - y(m.frame)) + '" rx="2"><title>' + esc(m.label) + ' frame: ' + inr(m.frame) + '</title></rect>'
                + '<rect class="bar-b" x="' + (cx + 1) + '" y="' + y(m.eye) + '" width="' + bw + '" height="' + (T + ph - y(m.eye)) + '" rx="2"><title>' + esc(m.label) + ' eye checkup: ' + inr(m.eye) + '</title></rect>'
                + '<text x="' + cx + '" y="' + (H - 8) + '" text-anchor="middle">' + esc(m.label) + '</text>';
            pts.push([cx, y(totals[i])]);
        });
        // Smooth line through monthly totals (Catmull-Rom -> cubic Bezier).
        var d = 'M' + pts[0][0] + ' ' + pts[0][1];
        for (var i = 0; i < pts.length - 1; i++) {
            var p0 = pts[i - 1] || pts[i], p1 = pts[i], p2 = pts[i + 1], p3 = pts[i + 2] || p2;
            d += ' C' + (p1[0] + (p2[0] - p0[0]) / 6) + ' ' + (p1[1] + (p2[1] - p0[1]) / 6)
                + ',' + (p2[0] - (p3[0] - p1[0]) / 6) + ' ' + (p2[1] - (p3[1] - p1[1]) / 6)
                + ',' + p2[0] + ' ' + p2[1];
        }
        out += '<path class="line" d="' + d + '"/>';
        host.innerHTML = '<svg viewBox="0 0 ' + W + ' ' + H + '" role="img" aria-label="Monthly billing">' + out + '</svg>';
    }
})();
</script>
@endverbatim
@endpush
