@extends('layouts.admin')
@section('title', 'Newsletter')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Newsletter Subscribers</h1>
    <span class="badge bg-primary fs-6" id="totalBadge">0 total</span>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>ID</th><th>Email</th><th>Name</th><th>Status</th><th>Subscribed</th><th>Actions</th></tr></thead>
                <tbody id="nlRows">
                    <tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm"></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@push('scripts')
@verbatim
<script>
(function () {
    var tbody = document.getElementById('nlRows');
    var totalBadge = document.getElementById('totalBadge');

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function fmtDate(d) { return d ? new Date(d).toLocaleDateString('en-IN') : ''; }

    function render(rows) {
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No subscribers yet.</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map(function (row) {
            return '<tr>' +
                '<td>' + esc(row.id) + '</td>' +
                '<td>' + esc(row.email) + '</td>' +
                '<td>' + esc(row.name || '—') + '</td>' +
                '<td><span class="badge ' + (row.is_active ? 'bg-success' : 'bg-secondary') + '">' + (row.is_active ? 'Active' : 'Unsubscribed') + '</span></td>' +
                '<td>' + fmtDate(row.created_at) + '</td>' +
                '<td><button class="btn btn-sm btn-outline-danger" data-remove="' + esc(row.id) + '">Remove</button></td>' +
                '</tr>';
        }).join('');

        tbody.querySelectorAll('[data-remove]').forEach(function (b) {
            b.addEventListener('click', function () { remove(b.getAttribute('data-remove')); });
        });
    }

    function load() {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="spinner-border spinner-border-sm"></div></td></tr>';
        fetch('/api/admin/newsletter').then(function (r) { return r.json(); }).then(function (d) {
            var total = (d.meta && d.meta.total != null) ? d.meta.total : 0;
            totalBadge.textContent = total + ' total';
            render(d.data || []);
        });
    }

    async function remove(id) {
        if (!window.confirm('Remove subscriber?')) return;
        await fetch('/api/admin/newsletter?id=' + id, { method: 'DELETE' });
        load();
    }

    load();
})();
</script>
@endverbatim
@endpush
