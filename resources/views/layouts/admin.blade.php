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
    $general = [
        ['/admin/dashboard','⊞','Dashboard'], ['/admin/eyewears','🕶️','Eyewears'], ['/admin/categories','☰','Categories'],
        ['/admin/brands','🏷️','Brands'], ['/admin/services','👁️','Services'], ['/admin/offers','🎁','Offers'],
        ['/admin/blog','📝','Blog'], ['/admin/reviews','★','Reviews'], ['/admin/enquiries','✉','Enquiries'],
        ['/admin/appointments','📅','Appointments'], ['/admin/gallery','🖼','Gallery'], ['/admin/newsletter','📧','Newsletter'],
        ['/admin/notify-me','🔔','Notify Me'], ['/admin/pincodes','📌','Pincodes'], ['/admin/settings','⚙️','Settings'],
    ];
    $others = [
        ['/admin/stock-items','📦','Stock Items'], ['/admin/customers','👥','Customers'], ['/admin/orders','🛒','Orders'],
        ['/admin/frame-bills','🧾','Frame Bills'], ['/admin/eye-checkup-bills','👁','Eye Checkup Bills'],
    ];
    $advanced = [
        ['/admin/loyalty','🏆','Loyalty'], ['/admin/subscriptions','🔄','Subscriptions'], ['/admin/crm','👥','CRM'],
        ['/admin/reminders','⏰','Reminders'], ['/admin/push-notify','🔔','Push Notify'], ['/admin/erp-export','📤','ERP Export'],
    ];
    $navItem = function($to, $icon, $label) use ($path) {
        $active = $path === $to || str_starts_with($path, $to.'/');
        return '<a href="'.$to.'" class="admin-nav-item'.($active ? ' active' : '').'"><span class="admin-nav-item__icon">'.$icon.'</span><span>'.$label.'</span></a>';
    };
    $adminName = $authAdmin['name'] ?? ($authAdmin['email'] ?? 'A');
    // Owner-only sections. Pre-role tokens carry no role claim — resolve from DB.
    $adminRole = $authAdmin['role'] ?? (isset($authAdmin['id']) ? \App\Models\User::where('id', $authAdmin['id'])->value('role') : null);
    $isOwner = $adminRole === 'owner';
@endphp
<div class="admin-layout">
    <aside class="admin-sidebar">
        <a href="/admin/dashboard" class="admin-logo"><img src="/icons/favicon.svg" width="36" height="36" alt="" style="flex-shrink:0"><span class="admin-logo__text">Trinetraa</span></a>
        <span class="admin-nav-label">GENERAL</span>
        @foreach($general as $i) {!! $navItem($i[0],$i[1],$i[2]) !!} @endforeach
        <div class="admin-nav-divider"></div>
        <span class="admin-nav-label">OTHERS</span>
        @foreach($others as $i) {!! $navItem($i[0],$i[1],$i[2]) !!} @endforeach
        <div class="admin-nav-divider"></div>
        <span class="admin-nav-label">ADVANCED</span>
        @foreach($advanced as $i) {!! $navItem($i[0],$i[1],$i[2]) !!} @endforeach
        @if($isOwner)
        <div class="admin-nav-divider"></div>
        <span class="admin-nav-label">OWNER</span>
        {!! $navItem('/admin/team','🛡️','Team') !!}
        {!! $navItem('/admin/activity','📋','Activity Log') !!}
        @endif
        <div class="admin-nav-divider"></div>
        <a href="/" class="admin-nav-item" target="_blank"><span class="admin-nav-item__icon">↗</span><span>View Site</span></a>
        <button class="admin-nav-item" onclick="window.AdminAuth.logout()"><span class="admin-nav-item__icon">⏏</span><span>Logout</span></button>
    </aside>
    <div class="admin-content">
        <div class="admin-topbar">
            <div class="admin-topbar__search">
                <span class="admin-topbar__search-icon">🔍</span>
                <input class="admin-topbar__search-input" placeholder="Search...">
            </div>
            <a href="/" class="admin-topbar__icon-btn" title="View Site" target="_blank">↗</a>
            <div class="admin-topbar__avatar" title="{{ $adminName }}">{{ strtoupper(substr($adminName, 0, 1)) }}</div>
        </div>
        <main class="admin-main">@yield('content')</main>
    </div>
</div>
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
@stack('scripts')
</body>
</html>
