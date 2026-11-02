@extends('layouts.admin')
@section('title', 'Stock Ledger')
@section('content')
<div id="erpAlerts"></div>
<style>
    .lg-qty--pos { color: #0e9d4e; font-weight: 700; }
    .lg-qty--neg { color: var(--erp-danger); font-weight: 700; }
    .lg-type { text-transform: capitalize; }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Stock Ledger &amp; Corrections</h1>
    <a href="/admin/stock-items" class="btn btn-outline-secondary btn-sm">← Inventory</a>
</div>

<div class="card mb-4">
    <div class="card-header">Correct stock</div>
    <div class="card-body">
        <p class="text-muted small mb-3">A mistake is fixed with a <strong>reasoned correction</strong>, never by silently editing a quantity. Every correction is written to the ledger below with who, when and why.</p>
        <form id="lgForm" class="row g-3">
            <div class="col-md-4"><label class="form-label fw-semibold">Product *</label><select class="form-select" id="lgItem" required><option value="">Select…</option></select></div>
            <div class="col-md-2"><label class="form-label fw-semibold">Branch *</label><select class="form-select" id="lgBranch" required></select></div>
            <div class="col-md-2"><label class="form-label fw-semibold">Change (+/−) *</label><input type="number" class="form-control" id="lgDelta" placeholder="-2 or 5" required></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Reason *</label><input class="form-control" id="lgReason" minlength="3" maxlength="255" placeholder="e.g. Broken in display, miscount" required></div>
            <div class="col-12 d-flex align-items-center gap-3">
                <button type="submit" class="btn btn-primary" id="lgSave">Record correction</button>
                <span class="small text-muted" id="lgHint"></span>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body border-bottom">
        <div class="row g-2 align-items-end">
            <div class="col-md-4"><select class="form-select" id="lgFilterItem"><option value="">All products</option></select></div>
            <div class="col-md-3">
                <select class="form-select" id="lgFilterType">
                    <option value="">All movement types</option><option value="inward">Inward</option><option value="transfer_out">Transfer out</option>
                    <option value="transfer_in">Transfer in</option><option value="sale">Sale</option><option value="return">Return</option>
                    <option value="adjustment">Adjustment</option><option value="void">Void</option>
                </select>
            </div>
            <div class="col-md-3"><select class="form-select" id="lgFilterBranch"></select></div>
            <div class="col-md-2 text-md-end"><span class="small text-muted" id="lgCount"></span></div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>When</th><th>Product</th><th>Branch</th><th>Type</th><th class="text-end">Qty</th><th>Reason / reference</th><th>By</th></tr></thead>
            <tbody id="lgRows"><tr><td colspan="7" class="text-center py-4 text-muted">Loading…</td></tr></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
@verbatim
<script>
(function () {
    var $ = function (id) { return document.getElementById(id); };

    function loadLedger() {
        var q = [];
        if ($('lgFilterItem').value) q.push('stock_item_id=' + $('lgFilterItem').value);
        if ($('lgFilterType').value) q.push('type=' + $('lgFilterType').value);
        if ($('lgFilterBranch').value) q.push('branch_id=' + $('lgFilterBranch').value);
        ERP.api('/api/admin/stock-movements' + (q.length ? '?' + q.join('&') : '')).then(function (j) {
            var rows = j.data || [];
            $('lgCount').textContent = rows.length + ' movement' + (rows.length === 1 ? '' : 's') + (rows.length >= 300 ? ' (latest 300)' : '');
            $('lgRows').innerHTML = rows.length ? rows.map(function (m) {
                var cls = m.quantity > 0 ? 'lg-qty--pos' : 'lg-qty--neg';
                var ref = m.reason || (m.reference_type ? m.reference_type.replace(/_/g, ' ') + (m.reference_id ? ' #' + m.reference_id : '') : '');
                return '<tr><td class="text-nowrap">' + ERP.esc(ERP.fmtDateTime(m.created_at)) + '</td><td class="fw-semibold">' + ERP.esc(m.item_name) + '</td><td>' + ERP.esc(m.branch_name)
                    + '</td><td class="lg-type">' + ERP.esc(String(m.type).replace(/_/g, ' ')) + '</td><td class="text-end ' + cls + '">' + (m.quantity > 0 ? '+' : '') + m.quantity
                    + '</td><td>' + ERP.esc(ref) + '</td><td>' + ERP.esc(m.user_name || '—') + '</td></tr>';
            }).join('') : '<tr><td colspan="7" class="text-center py-4 text-muted">No stock movements match.</td></tr>';
        }).catch(ERP.error);
    }

    ['lgFilterItem', 'lgFilterType', 'lgFilterBranch'].forEach(function (id) { $(id).addEventListener('change', loadLedger); });

    $('lgForm').addEventListener('submit', function (e) {
        e.preventDefault();
        var delta = parseInt($('lgDelta').value, 10);
        if (!delta) { ERP.notify('Enter a non-zero change, e.g. -2 or 5.', 'warning'); return; }
        var reason = $('lgReason').value.trim();
        if (reason.length < 3) { ERP.notify('A reason is required for every correction.', 'warning'); return; }
        $('lgSave').disabled = true;
        ERP.api('/api/admin/stock-adjustments', { method: 'POST', body: { stock_item_id: +$('lgItem').value, branch_id: +$('lgBranch').value || null, delta: delta, reason: reason } })
            .then(function (j) {
                ERP.notify('Correction recorded. New quantity at this branch: ' + j.data.quantity + '.');
                $('lgDelta').value = ''; $('lgReason').value = '';
                loadLedger();
            }).catch(ERP.error).then(function () { $('lgSave').disabled = false; });
    });

    Promise.all([
        ERP.api('/api/admin/stock-items/lookup'),
        ERP.fillBranchSelect($('lgBranch'), { all: false }),
        ERP.fillBranchSelect($('lgFilterBranch'), { all: 'All branches', remember: false, value: ERP.currentBranch() })
    ]).then(function (r) {
        var opts = (r[0].data || []).map(function (i) {
            return '<option value="' + i.id + '">' + ERP.esc((i.brand ? i.brand + ' — ' : '') + i.name + (i.model_number ? ' (' + i.model_number + ')' : '')) + '</option>';
        }).join('');
        $('lgItem').innerHTML = '<option value="">Select…</option>' + opts;
        $('lgFilterItem').innerHTML = '<option value="">All products</option>' + opts;
        loadLedger();
    }).catch(ERP.error);
})();
</script>
@endverbatim
@endpush
