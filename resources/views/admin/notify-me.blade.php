@extends('layouts.admin')
@section('title', 'Notify Me')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Notify Me Requests</h4>
    <span class="badge bg-secondary" id="totalBadge">0 total</span>
</div>

<div class="card">
    <div class="card-body p-0" id="nmBody">
        <div class="text-center py-5"><div class="spinner-border" role="status"></div></div>
    </div>
</div>

<div id="nmPager"></div>
@endsection
@push('scripts')
@verbatim
<script>
(function () {
    var page = 1;
    var meta = {};
    var bodyEl = document.getElementById('nmBody');
    var totalBadge = document.getElementById('totalBadge');
    var pagerEl = document.getElementById('nmPager');

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function fmtDate(d) { return d ? new Date(d).toLocaleDateString('en-IN') : ''; }

    function render(items) {
        if (!items.length) {
            bodyEl.innerHTML = '<div class="text-center py-5 text-muted">No notify-me requests yet.</div>';
            return;
        }
        var rows = items.map(function (r) {
            var product = r.eyewear
                ? '<a href="/eyewears/' + esc(r.eyewear.slug) + '" target="_blank" rel="noopener noreferrer" class="text-decoration-none">' + esc(r.eyewear.name) + '</a>'
                : 'ID: ' + esc(r.eyewear_id);
            var status = r.is_notified
                ? '<span class="badge bg-success">Notified</span>'
                : '<span class="badge bg-warning text-dark">Pending</span>';
            var action = !r.is_notified
                ? '<button class="btn btn-sm btn-outline-success" data-notify="' + esc(r.id) + '">Mark Notified</button>'
                : '';
            return '<tr>' +
                '<td>' + esc(r.name || '—') + '</td>' +
                '<td>' + esc(r.email) + '</td>' +
                '<td>' + product + '</td>' +
                '<td>' + fmtDate(r.created_at) + '</td>' +
                '<td>' + status + '</td>' +
                '<td>' + action + '</td>' +
                '</tr>';
        }).join('');

        bodyEl.innerHTML = '<div class="table-responsive"><table class="table table-hover mb-0">' +
            '<thead class="table-light"><tr><th>Name</th><th>Email</th><th>Product</th><th>Requested</th><th>Status</th><th>Action</th></tr></thead>' +
            '<tbody>' + rows + '</tbody></table></div>';

        bodyEl.querySelectorAll('[data-notify]').forEach(function (b) {
            b.addEventListener('click', function () { markNotified(b.getAttribute('data-notify')); });
        });
    }

    function renderPager() {
        if (meta.total && meta.per_page && meta.total > meta.per_page) {
            pagerEl.innerHTML = '<div class="d-flex justify-content-center gap-2 mt-3">' +
                '<button class="btn btn-outline-secondary btn-sm" id="nmPrev"' + (page <= 1 ? ' disabled' : '') + '>‹ Prev</button>' +
                '<span class="align-self-center text-muted small">Page ' + page + '</span>' +
                '<button class="btn btn-outline-secondary btn-sm" id="nmNext"' + (page * meta.per_page >= meta.total ? ' disabled' : '') + '>Next ›</button>' +
                '</div>';
            var prev = document.getElementById('nmPrev');
            var next = document.getElementById('nmNext');
            if (prev) prev.addEventListener('click', function () { if (page > 1) { page--; load(); } });
            if (next) next.addEventListener('click', function () { page++; load(); });
        } else {
            pagerEl.innerHTML = '';
        }
    }

    function load() {
        bodyEl.innerHTML = '<div class="text-center py-5"><div class="spinner-border" role="status"></div></div>';
        fetch('/api/admin/notify-me?page=' + page + '&per_page=50').then(function (r) { return r.json(); }).then(function (d) {
            meta = d.meta || {};
            totalBadge.textContent = (meta.total || 0) + ' total';
            render(d.data || []);
            renderPager();
        });
    }

    async function markNotified(id) {
        await fetch('/api/admin/notify-me', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: Number(id), isNotified: true })
        });
        load();
    }

    load();
})();
</script>
@endverbatim
@endpush
