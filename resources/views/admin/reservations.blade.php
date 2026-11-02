@extends('layouts.admin')
@section('title', 'Store-pickup Reservations')
@section('content')
<div id="erpAlerts"></div>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h3 mb-0">Store-pickup Reservations <span class="badge bg-warning ms-2" id="rsPending" title="Waiting for staff to confirm">0</span></h1>
    <div class="d-flex gap-2">
        <select class="form-select form-select-sm" id="rsBranch" style="width:auto"></select>
        <input class="form-control form-control-sm" id="rsSearch" placeholder="Search code, name, phone" style="width:220px">
    </div>
</div>

<div class="mb-3 d-flex gap-2 flex-wrap" id="rsChips"></div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Code</th><th>Product</th><th>Store</th><th>Customer</th><th class="text-center">Qty</th><th>Holds until</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody id="rsRows"><tr><td colspan="8" class="text-center py-4 text-muted">Loading…</td></tr></tbody>
        </table>
    </div>
</div>
<p class="small text-muted mt-2">A reservation holds one unit for 48 hours and does not change stock. When the customer collects, bill it in <a href="/admin/billing">POS Billing</a> and mark it collected here. Lapsed holds release automatically.</p>
@endsection

@push('scripts')
@verbatim
<script>
(function () {
    var $ = function (id) { return document.getElementById(id); };
    var status = 'active';
    var STATUSES = [['active', 'Open'], ['pending', 'Pending'], ['confirmed', 'Confirmed'], ['collected', 'Collected'], ['cancelled', 'Cancelled'], ['expired', 'Expired'], ['', 'All']];
    var COLORS = { pending: 'warning', confirmed: 'primary', collected: 'success', cancelled: 'secondary', expired: 'secondary' };

    function chips() {
        $('rsChips').innerHTML = STATUSES.map(function (s) {
            return '<button type="button" class="btn btn-sm ' + (status === s[0] ? 'btn-primary' : 'btn-outline-secondary') + '" data-st="' + s[0] + '">' + s[1] + '</button>';
        }).join('');
    }
    $('rsChips').addEventListener('click', function (e) {
        var b = e.target.closest('[data-st]'); if (!b) return;
        status = b.dataset.st; chips(); load();
    });

    function actions(r) {
        var h = '';
        if (r.status === 'pending') h += '<button class="btn btn-sm btn-primary me-1" data-act="confirm" data-id="' + r.id + '">Confirm</button>';
        if (r.status === 'pending' || r.status === 'confirmed') {
            h += '<a class="btn btn-sm btn-outline-secondary me-1" href="/admin/billing">Bill at POS</a>';
            h += '<button class="btn btn-sm btn-success me-1" data-act="collect" data-id="' + r.id + '">Collected</button>';
            h += '<button class="btn btn-sm btn-outline-danger" data-act="cancel" data-id="' + r.id + '">Cancel</button>';
        }
        return h || '<span class="text-muted small">—</span>';
    }

    function load() {
        var q = [];
        if (status) q.push('status=' + status);
        if ($('rsBranch').value) q.push('branch_id=' + $('rsBranch').value);
        if ($('rsSearch').value.trim()) q.push('search=' + encodeURIComponent($('rsSearch').value.trim()));
        ERP.api('/api/admin/reservations' + (q.length ? '?' + q.join('&') : '')).then(function (j) {
            var rows = j.data || [];
            $('rsRows').innerHTML = rows.length ? rows.map(function (r) {
                var phone = String(r.phone || '').replace(/\D/g, '');
                return '<tr><td><code>' + ERP.esc(r.code) + '</code></td>'
                    + '<td><div class="fw-semibold">' + ERP.esc(r.item_name || '—') + '</div><div class="small text-muted">' + ERP.esc(r.model_number || '') + '</div></td>'
                    + '<td>' + ERP.esc(r.branch_name || '') + '</td>'
                    + '<td>' + ERP.esc(r.name) + '<div class="small text-muted"><a href="tel:' + ERP.esc(r.phone) + '">' + ERP.esc(r.phone) + '</a>' + (phone ? ' · <a target="_blank" rel="noopener" href="https://wa.me/' + (phone.length === 10 ? '91' + phone : phone) + '">WhatsApp</a>' : '') + '</div></td>'
                    + '<td class="text-center">' + r.quantity + '</td>'
                    + '<td>' + ERP.fmtDateTime(r.expires_at) + '</td>'
                    + '<td><span class="badge bg-' + (COLORS[r.status] || 'secondary') + '">' + ERP.esc(r.status) + '</span></td>'
                    + '<td class="text-end text-nowrap">' + actions(r) + '</td></tr>';
            }).join('') : '<tr><td colspan="8" class="text-center py-4 text-muted">No reservations here.</td></tr>';
        }).catch(ERP.error);
        ERP.api('/api/admin/reservations/pending-count' + ($('rsBranch').value ? '?branch_id=' + $('rsBranch').value : '')).then(function (j) { $('rsPending').textContent = j.data.pending; }).catch(function () {});
    }

    $('rsRows').addEventListener('click', function (e) {
        var b = e.target.closest('[data-act]'); if (!b) return;
        if (b.dataset.act === 'cancel' && !confirm('Cancel this reservation and release the hold?')) return;
        ERP.api('/api/admin/reservations/' + b.dataset.id + '/' + b.dataset.act, { method: 'POST' }).then(function () { load(); }).catch(ERP.error);
    });
    var t; $('rsSearch').addEventListener('input', function () { clearTimeout(t); t = setTimeout(load, 300); });
    $('rsBranch').addEventListener('change', load);

    chips();
    ERP.fillBranchSelect($('rsBranch'), { all: 'All branches', remember: false, value: ERP.currentBranch() }).then(load).catch(ERP.error);
})();
</script>
@endverbatim
@endpush
