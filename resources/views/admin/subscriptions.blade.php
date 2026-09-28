@extends('layouts.admin')
@section('title', 'Contact Lens Subscriptions')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">🔄 Contact Lens Subscriptions</h2>
        <small class="text-muted"><span id="subTotal">0</span> total</small>
    </div>

    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="d-flex gap-2 flex-wrap" id="subFilters"></div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div id="subLoading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
            <div id="subTableWrap" class="table-responsive" style="display:none">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Customer</th>
                            <th>Lens</th>
                            <th>Interval</th>
                            <th>Next Due</th>
                            <th>Status</th>
                            <th>Orders</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="subBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
@verbatim
<script>
(function () {
    var STATUS_COLORS = { active: 'success', paused: 'warning', cancelled: 'danger' };
    var INTERVALS = { 30: 'Monthly', 60: 'Every 2 Months', 90: 'Every 3 Months' };
    var FILTERS = ['', 'active', 'paused', 'cancelled'];

    var state = { subs: [], total: 0 };
    var filter = '';
    var saving = false;

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function daysUntil(date) {
        return Math.ceil((new Date(date) - new Date()) / (1000 * 60 * 60 * 24));
    }

    function renderFilters() {
        var wrap = document.getElementById('subFilters');
        wrap.innerHTML = FILTERS.map(function (s) {
            var label = s === '' ? 'All' : s.charAt(0).toUpperCase() + s.slice(1);
            var cls = filter === s ? 'primary' : 'outline-secondary';
            return '<button class="btn btn-sm btn-' + cls + '" data-filter="' + s + '">' + label + '</button>';
        }).join('');
        wrap.querySelectorAll('[data-filter]').forEach(function (b) {
            b.addEventListener('click', function () {
                filter = b.getAttribute('data-filter');
                renderFilters();
                load(filter);
            });
        });
    }

    function render() {
        document.getElementById('subTotal').textContent = state.total;
        var body = document.getElementById('subBody');
        if (state.subs.length === 0) {
            body.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No subscriptions found</td></tr>';
            return;
        }
        body.innerHTML = state.subs.map(function (s) {
            var days = daysUntil(s.next_due_date);
            var cust = s.customer || {};
            var dueCls = days <= 3 ? 'text-danger fw-bold' : days <= 7 ? 'text-warning fw-bold' : 'text-muted';
            var dueTxt = days <= 0 ? 'Overdue!' : 'In ' + days + ' days';
            var lensExtra = '';
            if (s.brand) lensExtra += '<small class="text-muted">' + esc(s.brand) + '</small>';
            if (s.power) lensExtra += '<small class="d-block text-muted">Power: ' + esc(s.power) + '</small>';
            var interval = INTERVALS[s.interval_days] || (s.interval_days + ' days');

            var actions = '<div class="d-flex gap-1 flex-wrap">';
            if (s.status === 'active') {
                actions += '<button class="btn btn-sm btn-success" data-fulfill="' + s.id + '"' + (saving ? ' disabled' : '') + '>✓ Fulfill</button>';
                actions += '<button class="btn btn-sm btn-outline-warning" data-status="paused" data-id="' + s.id + '">Pause</button>';
            }
            if (s.status === 'paused') {
                actions += '<button class="btn btn-sm btn-outline-success" data-status="active" data-id="' + s.id + '">Resume</button>';
            }
            if (s.status !== 'cancelled') {
                actions += '<button class="btn btn-sm btn-outline-danger" data-status="cancelled" data-id="' + s.id + '">Cancel</button>';
            }
            actions += '</div>';

            return '<tr>' +
                '<td><div class="fw-semibold">' + esc(cust.name) + '</div><small class="text-muted">' + esc(cust.phone || cust.email) + '</small></td>' +
                '<td><div>' + esc(s.lens_name) + '</div>' + lensExtra + '</td>' +
                '<td>' + esc(interval) + '</td>' +
                '<td><div>' + new Date(s.next_due_date).toLocaleDateString('en-IN') + '</div><small class="' + dueCls + '">' + dueTxt + '</small></td>' +
                '<td><span class="badge bg-' + (STATUS_COLORS[s.status] || 'secondary') + '">' + esc(s.status) + '</span></td>' +
                '<td>' + (s.orders ? s.orders.length : 0) + '</td>' +
                '<td>' + actions + '</td>' +
                '</tr>';
        }).join('');

        body.querySelectorAll('[data-fulfill]').forEach(function (b) {
            b.addEventListener('click', function () { fulfill(Number(b.getAttribute('data-fulfill'))); });
        });
        body.querySelectorAll('[data-status]').forEach(function (b) {
            b.addEventListener('click', function () {
                changeStatus(Number(b.getAttribute('data-id')), b.getAttribute('data-status'));
            });
        });
    }

    function load(status) {
        if (status === undefined) status = filter;
        document.getElementById('subLoading').style.display = '';
        document.getElementById('subTableWrap').style.display = 'none';
        var qs = status ? '?status=' + status : '';
        fetch('/api/admin/subscriptions' + qs)
            .then(function (r) { return r.json(); })
            .then(function (json) {
                state = { subs: json.subs || [], total: json.total || 0 };
                document.getElementById('subLoading').style.display = 'none';
                document.getElementById('subTableWrap').style.display = '';
                render();
            });
    }

    function fulfill(id) {
        saving = true;
        render();
        fetch('/api/admin/subscriptions/' + id, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'fulfill' })
        }).then(function () {
            saving = false;
            load();
        });
    }

    function changeStatus(id, status) {
        fetch('/api/admin/subscriptions/' + id, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ status: status })
        }).then(function () { load(); });
    }

    renderFilters();
    load();
})();
</script>
@endverbatim
@endpush
