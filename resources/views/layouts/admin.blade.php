<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') | Admin — Trinetraa</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/svg+xml" href="/icons/favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon.png">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    @php $_assetV = fn ($p) => $p . '?v=' . @filemtime(public_path(ltrim($p, '/'))); @endphp
    <link rel="stylesheet" href="{{ $_assetV('/css/globals.css') }}">
    <link rel="stylesheet" href="{{ $_assetV('/css/admin.css') }}">
    @stack('head')
</head>
<body>
@php
    $path = '/' . ltrim(request()->path(), '/');

    // Feather-style line icons (inner SVG markup).
    $ic = [
        'grid'     => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>',
        'box'      => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>',
        'eye'      => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
        'list'     => '<line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>',
        'tag'      => '<path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>',
        'cart'     => '<circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>',
        'file'     => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
        'gift'     => '<polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>',
        'award'    => '<circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/>',
        'refresh'  => '<polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>',
        'users'    => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'cal'      => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'clock'    => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'mail'     => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
        'star'     => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
        'globe'    => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
        'edit'     => '<path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>',
        'image'    => '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>',
        'bell'     => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
        'pin'      => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
        'upload'   => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>',
        'shield'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'activity' => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
        'external' => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>',
        'logout'   => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        'menu'     => '<line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>',
        'search'   => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
        'plus'     => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
        'chev'     => '<polyline points="9 18 15 12 9 6"/>',
        'maximize' => '<path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/>',
        'receipt'  => '<path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1z"/><line x1="8" y1="8" x2="16" y2="8"/><line x1="8" y1="12" x2="16" y2="12"/>',
    ];
    $svg = fn ($name) => '<svg viewBox="0 0 24 24" aria-hidden="true">' . ($ic[$name] ?? '') . '</svg>';

    // Navigation, grouped by the SOW modules. Every existing admin route is kept.
    $groups = [
        ['Inventory', 'box', [
            ['/admin/stock-items', 'box', 'Stock Items', 'inventory'], ['/admin/stock-inward', 'upload', 'Stock Inward', 'inventory'],
            ['/admin/transfers', 'refresh', 'Stock Transfers', 'transfers'], ['/admin/stock-alerts', 'bell', 'Low-stock Alerts', 'inventory'],
            ['/admin/stock-ledger', 'list', 'Stock Ledger', 'inventory'], ['/admin/suppliers', 'users', 'Suppliers', 'inventory'],
            ['/admin/purchase-orders', 'file', 'Purchase Orders', 'inventory'], ['/admin/stock-takes', 'list', 'Stock Take', 'inventory'],
            ['/admin/eyewears', 'eye', 'Eyewears', null], ['/admin/categories', 'list', 'Categories', null], ['/admin/brands', 'tag', 'Brands', null],
        ]],
        ['Billing & Sales', 'receipt', [
            ['/admin/billing', 'cart', 'POS Billing', 'billing'], ['/admin/frame-bills', 'file', 'Frame Bills', 'billing'],
            ['/admin/eye-checkup-bills', 'eye', 'Eye Checkup Bills', 'billing'], ['/admin/returns', 'refresh', 'Returns & Exchanges', 'billing'],
            ['/admin/orders', 'cart', 'Orders', 'billing'], ['/admin/work-orders', 'clock', 'Work Orders', 'billing'],
            ['/admin/reservations', 'pin', 'Reservations', 'billing'], ['/admin/coupons', 'gift', 'Coupons', 'settings'], ['/admin/offers', 'gift', 'Offers', null],
            ['/admin/loyalty', 'award', 'Loyalty', null], ['/admin/subscriptions', 'refresh', 'Subscriptions', null],
        ]],
        ['Customers', 'users', [
            ['/admin/customers', 'users', 'Customers', 'customers'], ['/admin/crm', 'users', 'CRM', null],
            ['/admin/appointments', 'cal', 'Appointments', null], ['/admin/reminders', 'clock', 'Reminders', null],
            ['/admin/enquiries', 'mail', 'Enquiries', null], ['/admin/reviews', 'star', 'Reviews', null],
        ]],
        ['Website', 'globe', [
            ['/admin/services', 'eye', 'Services', null], ['/admin/blog', 'edit', 'Blog', null],
            ['/admin/gallery', 'image', 'Gallery', null], ['/admin/newsletter', 'mail', 'Newsletter', null],
            ['/admin/notify-me', 'bell', 'Notify Me', null], ['/admin/push-notify', 'bell', 'Push Notify', null],
            ['/admin/pincodes', 'pin', 'Pincodes', null],
        ]],
        ['Reports', 'upload', [
            ['/admin/reports', 'activity', 'Branch Reports', 'reports'], ['/admin/profit-report', 'activity', 'Profit & Margin', 'reports'], ['/admin/gst-report', 'receipt', 'GST Report', 'reports'], ['/admin/erp-export', 'upload', 'ERP Export', null],
        ]],
    ];
    $__adm = request()->attributes->get('admin') ?? [];
    $allowed = fn ($perm) => $perm === null || \App\Support\Access::can($__adm, $perm);
    $groups = array_values(array_filter(array_map(function ($g) use ($allowed) {
        $g[2] = array_values(array_filter($g[2], fn ($i) => $allowed($i[3] ?? null)));
        return $g;
    }, $groups), fn ($g) => count($g[2]) > 0));
    $isActive = fn ($to) => $path === $to || str_starts_with($path, $to . '/');
    $navItem = function ($to, $icon, $label) use ($isActive, $svg) {
        return '<a href="' . $to . '" class="admin-nav-item' . ($isActive($to) ? ' active' : '') . '"><span class="admin-nav-item__icon">' . $svg($icon) . '</span><span>' . $label . '</span></a>';
    };

    $adminName = $authAdmin['name'] ?? ($authAdmin['email'] ?? 'A');
    // Owner-only sections. Pre-role tokens carry no role claim — resolve from DB.
    $adminRole = $authAdmin['role'] ?? (isset($authAdmin['id']) ? \App\Models\User::where('id', $authAdmin['id'])->value('role') : null);
    $isOwner = $adminRole === 'owner';
    $visitsToday = 0;
    $visitsTotal = 0;
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('site_visits')) {
            $visitsToday = (int) \App\Models\SiteVisit::whereDate('visit_date', now()->toDateString())->value('count');
            $visitsTotal = (int) \App\Models\SiteVisit::sum('count');
        }
    } catch (\Throwable $e) {
        // Table not migrated yet — footer just shows zeros.
    }
    $pageTitle = trim($__env->yieldContent('title', 'Admin'));
@endphp
<div class="admin-layout">
    <aside class="admin-sidebar" id="adminSidebar">
        <a href="/admin/dashboard" class="admin-logo"><img src="/icons/favicon.svg" width="34" height="34" alt="" style="flex-shrink:0"><span class="admin-logo__text">Trinetraa</span></a>
        <nav class="admin-nav">
            <span class="admin-nav-label">Navigation</span>
            {!! $navItem('/admin/dashboard', 'grid', 'Dashboard') !!}
            @foreach($groups as $g)
                @php $open = collect($g[2])->contains(fn ($i) => $isActive($i[0])); @endphp
                <div class="admin-nav-group{{ $open ? ' open' : '' }}">
                    <button type="button" class="admin-nav-group__toggle" onclick="this.parentElement.classList.toggle('open')">
                        <span class="admin-nav-item__icon">{!! $svg($g[1]) !!}</span>
                        <span class="admin-nav-group__text">{{ $g[0] }}</span>
                        <svg class="admin-nav-group__chev" viewBox="0 0 24 24" aria-hidden="true">{!! $ic['chev'] !!}</svg>
                    </button>
                    <div class="admin-nav-group__items">
                        @foreach($g[2] as $i) {!! $navItem($i[0], $i[1], $i[2]) !!} @endforeach
                    </div>
                </div>
            @endforeach
            {!! $navItem('/admin/settings', 'settings', 'Settings') !!}
            @if($allowed('settings'))
                {!! $navItem('/admin/branches', 'pin', 'Branches') !!}
            @endif
            {!! $navItem('/admin/security', 'shield', 'Security & MFA') !!}
            @if($isOwner)
                <span class="admin-nav-label">Owner</span>
                {!! $navItem('/admin/team', 'shield', 'Team') !!}
                {!! $navItem('/admin/activity', 'activity', 'Activity Log') !!}
            @endif
            <div class="admin-nav-divider"></div>
            <a href="/" class="admin-nav-item" target="_blank"><span class="admin-nav-item__icon">{!! $svg('external') !!}</span><span>View Site</span></a>
            <button class="admin-nav-item" onclick="window.AdminAuth.logout()"><span class="admin-nav-item__icon">{!! $svg('logout') !!}</span><span>Logout</span></button>
        </nav>
        <div class="admin-sidebar-promo">
            <strong>Trinetraa Optician</strong>
            Pune &middot; Mumbai &middot; Nashik
            <a href="/admin/erp-export" class="admin-btn-block">Export data</a>
        </div>
    </aside>
    <div class="admin-scrim" onclick="document.body.classList.remove('sidebar-open')"></div>
    <div class="admin-content">
        <div class="admin-topbar">
            <button type="button" class="admin-topbar__icon-btn" id="sidebarToggle" title="Toggle menu" aria-label="Toggle menu">{!! $svg('menu') !!}</button>
            <a href="/admin/eyewears" class="admin-topbar__icon-btn admin-topbar__icon-btn--primary" title="Add eyewear">{!! $svg('plus') !!}</a>
            <div class="admin-topbar__search">
                <svg class="admin-topbar__search-icon" viewBox="0 0 24 24" aria-hidden="true">{!! $ic['search'] !!}</svg>
                <input class="admin-topbar__search-input" placeholder="Search...">
            </div>
            <div class="admin-topbar__spacer"></div>
            <select id="erpBranchSel" class="admin-topbar__pill" style="display:none" title="Branch" aria-label="Branch"></select>
            <a href="/" class="admin-topbar__icon-btn" title="View Site" target="_blank">{!! $svg('external') !!}</a>
            <button type="button" class="admin-topbar__icon-btn" id="fullscreenToggle" title="Fullscreen" aria-label="Fullscreen">{!! $svg('maximize') !!}</button>
            <a href="/admin/reminders" class="admin-topbar__icon-btn" title="Reminders">{!! $svg('bell') !!}</a>
            <div class="admin-topbar__avatar" title="{{ $adminName }}">{{ strtoupper(substr($adminName, 0, 1)) }}</div>
        </div>
        <div class="admin-pagebar">
            <h2 class="admin-pagebar__title">{!! $pageTitle !!}</h2>
            <div class="admin-crumbs">
                <a href="/admin/dashboard">Home</a>
                <svg viewBox="0 0 24 24" aria-hidden="true">{!! $ic['chev'] !!}</svg>
                <span>{!! $pageTitle !!}</span>
            </div>
        </div>
        <main class="admin-main">@yield('content')</main>
        <footer class="admin-footer">
            <span>&copy; {{ date('Y') }} Trinetraa Optician — Admin Panel</span>
            <span title="Site visitors (admin-only)">Visitors — Today: <strong>{{ number_format($visitsToday) }}</strong> &nbsp;|&nbsp; All-time: <strong>{{ number_format($visitsTotal) }}</strong></span>
        </footer>
    </div>
</div>
<script>
(function () {
    var b = document.body;
    try { if (localStorage.getItem('adminSidebar') === 'collapsed') b.classList.add('sidebar-collapsed'); } catch (e) {}
    document.getElementById('sidebarToggle').addEventListener('click', function () {
        if (window.matchMedia('(max-width: 992px)').matches) { b.classList.toggle('sidebar-open'); return; }
        b.classList.toggle('sidebar-collapsed');
        try { localStorage.setItem('adminSidebar', b.classList.contains('sidebar-collapsed') ? 'collapsed' : 'open'); } catch (e) {}
    });
    document.getElementById('fullscreenToggle').addEventListener('click', function () {
        if (document.fullscreenElement) document.exitFullscreen(); else document.documentElement.requestFullscreen();
    });
})();
</script>
<script>
(function () {
    var meta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = meta ? meta.getAttribute('content') : '';

    window.AdminAuth = {
        async logout() {
            await fetch('/api/auth/logout', { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken } });
            window.location.href = '/admin/login';
        }
    };

    window.apiFetch = function (url, opts) {
        opts = opts || {};
        opts.headers = Object.assign({ 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }, opts.headers || {});
        return fetch(url, opts);
    };

    // Upload a File object to /api/admin/upload-image and return the public URL.
    window.uploadImage = async function (file, folder) {
        var fd = new FormData();
        fd.append('image', file);
        if (folder) fd.append('folder', folder);
        var res = await fetch('/api/admin/upload-image', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        });
        var data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Upload failed');
        return data.url;
    };

    // Attach upload + preview behaviour to an image field group.
    // opts: { urlInputId, fileInputId, previewId, statusId, folder, placeholder }
    window.initImageWidget = function (opts) {
        var urlInput  = document.getElementById(opts.urlInputId);
        var fileInput = document.getElementById(opts.fileInputId);
        var preview   = document.getElementById(opts.previewId);
        var status    = document.getElementById(opts.statusId);
        var folder    = opts.folder || 'misc';
        var ph        = opts.placeholder || '🖼️';

        function updatePreview(url) {
            if (!preview) return;
            if (url && url.trim()) {
                preview.innerHTML = '<img src="' + url.trim() + '" style="width:100%;height:100%;object-fit:cover" '
                    + 'onerror="this.parentElement.innerHTML=\'' + ph + '\'">';
            } else {
                preview.innerHTML = ph;
            }
        }

        if (urlInput) {
            updatePreview(urlInput.value);
            urlInput.addEventListener('input',  function () { updatePreview(urlInput.value); });
            urlInput.addEventListener('change', function () { updatePreview(urlInput.value); });
        }

        if (fileInput) {
            fileInput.addEventListener('change', async function () {
                var file = fileInput.files[0];
                if (!file) return;
                if (status) { status.textContent = 'Uploading…'; status.className = 'small text-muted'; }
                try {
                    var url = await window.uploadImage(file, folder);
                    if (urlInput) urlInput.value = url;
                    updatePreview(url);
                    if (status) { status.textContent = 'Uploaded ✓'; status.className = 'small text-success'; }
                    setTimeout(function () { if (status) { status.textContent = ''; } }, 4000);
                } catch (e) {
                    if (status) { status.textContent = 'Error: ' + e.message; status.className = 'small text-danger'; }
                }
                fileInput.value = '';
            });
        }

        return { updatePreview: updatePreview };
    };
})();
</script>
<script src="{{ $_assetV('/js/vendor/qrcode.js') }}"></script>
<script src="{{ $_assetV('/js/erp.js') }}"></script>
<script>
(function () {
    // Branch picker in the header: shown only when the user can see more than one branch.
    var sel = document.getElementById('erpBranchSel');
    if (sel && window.ERP) {
        ERP.fillBranchSelect(sel, { all: 'All branches' }).then(function (list) {
            if (list.length > 1) {
                sel.style.display = '';
                sel.addEventListener('change', function () { window.location.reload(); });
            }
        }).catch(function () {});
    }
    // Auto sign-out after 30 minutes without activity (security: unattended counter screens).
    var IDLE_MS = 30 * 60 * 1000, timer;
    function arm() { clearTimeout(timer); timer = setTimeout(function () { window.AdminAuth.logout(); }, IDLE_MS); }
    ['click', 'keydown', 'mousemove', 'touchstart', 'scroll'].forEach(function (ev) { window.addEventListener(ev, arm, { passive: true }); });
    arm();
})();
</script>
@stack('scripts')
</body>
</html>
