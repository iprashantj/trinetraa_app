@extends('layouts.admin')
@section('title', 'Activity Log')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Activity Log</h1>
        <p class="text-muted small mb-0">Every change made through the admin panel — who did what, and when.</p>
    </div>
    <input class="form-control" id="actFilter" placeholder="Filter by email…" style="max-width:260px">
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th style="padding-left:1.25rem">When</th>
                    <th>Who</th>
                    <th>Action</th>
                    <th>Endpoint</th>
                    <th>Result</th>
                    <th style="padding-right:1.25rem">IP</th>
                </tr>
            </thead>
            <tbody id="actRows">
                <tr><td colspan="6" class="text-center text-muted py-4">Loading…</td></tr>
            </tbody>
        </table>
    </div>
</div>
<div class="d-flex justify-content-between align-items-center mt-3">
    <span class="text-muted small" id="actTotal"></span>
    <div>
        <button class="btn btn-sm btn-outline-secondary" id="actPrev">← Prev</button>
        <button class="btn btn-sm btn-outline-secondary" id="actNext">Next →</button>
    </div>
</div>
@endsection

@push('scripts')
@verbatim
<script>
(function () {
    var rows = document.getElementById('actRows');
    var state = { page: 1, total: 0, perPage: 30, filter: '' };

    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }

    function load() {
        var params = new URLSearchParams({ page: state.page, per_page: state.perPage });
        if (state.filter) params.set('user', state.filter);
        window.apiFetch('/api/admin/activity?' + params).then(function (r) {
            if (r.status === 403) { rows.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Owner access required.</td></tr>'; return null; }
            return r.json();
        }).then(function (d) {
            if (!d) return;
            var items = d.data || [];
            state.total = (d.meta && d.meta.total) || 0;
            document.getElementById('actTotal').textContent = state.total + ' entries · page ' + state.page;
            if (!items.length) {
                rows.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No activity recorded yet.</td></tr>';
                return;
            }
            rows.innerHTML = items.map(function (a) {
                var when = a.created_at ? new Date(a.created_at).toLocaleString('en-IN', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) : '—';
                var ok = a.status >= 200 && a.status < 300;
                return '<tr>' +
                    '<td style="padding-left:1.25rem" class="text-muted small">' + when + '</td>' +
                    '<td class="fw-semibold">' + esc(a.user_email || '—') + '</td>' +
                    '<td>' + esc(a.action || a.method) + '</td>' +
                    '<td class="text-muted small">' + esc(a.method + ' ' + a.path) + '</td>' +
                    '<td><span class="badge ' + (ok ? 'bg-success' : 'bg-danger') + '">' + a.status + '</span></td>' +
                    '<td style="padding-right:1.25rem" class="text-muted small">' + esc(a.ip || '—') + '</td>' +
                '</tr>';
            }).join('');
        });
    }

    var debounce;
    document.getElementById('actFilter').addEventListener('input', function (e) {
        clearTimeout(debounce);
        debounce = setTimeout(function () { state.filter = e.target.value.trim(); state.page = 1; load(); }, 350);
    });
    document.getElementById('actPrev').addEventListener('click', function () { if (state.page > 1) { state.page--; load(); } });
    document.getElementById('actNext').addEventListener('click', function () {
        if (state.page * state.perPage < state.total) { state.page++; load(); }
    });

    load();
})();
</script>
@endverbatim
@endpush
