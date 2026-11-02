@extends('layouts.admin')
@section('title', 'Low-stock Alerts')
@section('content')
<div id="erpAlerts"></div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Low-stock Alerts</h1>
    <a href="/admin/stock-inward" class="btn btn-outline-primary btn-sm">+ Stock inward</a>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Needs attention <span class="badge bg-warning ms-1" id="alCount">0</span></span>
                <select class="form-select form-select-sm" id="alBranch" style="width:auto"></select>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Product</th><th>Brand</th><th>Category</th><th class="text-end">Qty</th><th class="text-end">Alert at</th></tr></thead>
                    <tbody id="alRows"><tr><td colspan="5" class="text-center py-4 text-muted">Loading…</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header">Alert levels</div>
            <div class="card-body">
                <p class="small text-muted">Set the quantity at which an item raises an alert, per brand or per category. A brand rule overrides a category rule. Example: alert when Ray-Ban falls to 15 or below.</p>
                <form id="alForm" class="row g-2 align-items-end">
                    <div class="col-12">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-primary" id="alScopeBrand">By brand</button>
                            <button type="button" class="btn btn-outline-secondary" id="alScopeCat">By category</button>
                        </div>
                    </div>
                    <div class="col-7" id="alBrandWrap"><label class="form-label small fw-semibold">Brand</label><input class="form-control" id="alBrand" list="alBrandList" required placeholder="e.g. Ray-Ban"><datalist id="alBrandList"></datalist></div>
                    <div class="col-7 d-none" id="alCatWrap"><label class="form-label small fw-semibold">Category</label><select class="form-select" id="alCat"></select></div>
                    <div class="col-3"><label class="form-label small fw-semibold">Alert at ≤</label><input type="number" min="0" class="form-control" id="alThreshold" value="15" required></div>
                    <div class="col-2"><button type="submit" class="btn btn-primary w-100">Set</button></div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Applies to</th><th class="text-end">Alert at ≤</th><th></th></tr></thead>
                    <tbody id="ruleRows"><tr><td colspan="3" class="text-center py-3 text-muted">Loading…</td></tr></tbody>
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
    var $ = function (id) { return document.getElementById(id); };
    var scope = 'brand';

    function setScope(s) {
        scope = s;
        $('alBrandWrap').classList.toggle('d-none', s !== 'brand');
        $('alCatWrap').classList.toggle('d-none', s !== 'category');
        $('alBrand').required = s === 'brand';
        $('alScopeBrand').className = 'btn ' + (s === 'brand' ? 'btn-primary' : 'btn-outline-secondary');
        $('alScopeCat').className = 'btn ' + (s === 'category' ? 'btn-primary' : 'btn-outline-secondary');
    }
    $('alScopeBrand').onclick = function () { setScope('brand'); };
    $('alScopeCat').onclick = function () { setScope('category'); };

    function loadAlerts() {
        var b = $('alBranch').value;
        ERP.api('/api/admin/stock-alerts' + (b ? '?branch_id=' + b : '')).then(function (j) {
            var rows = j.data || [];
            $('alCount').textContent = rows.length;
            $('alRows').innerHTML = rows.length ? rows.map(function (r) {
                return '<tr><td><div class="fw-semibold">' + ERP.esc(r.name) + '</div><div class="small text-muted">' + ERP.esc(r.model_number || '') + '</div></td><td>' + ERP.esc(r.brand || '—') + '</td><td>' + ERP.esc(r.category || '—')
                    + '</td><td class="text-end">' + (r.quantity <= 0 ? '<span class="badge bg-danger">0</span>' : '<span class="badge bg-warning">' + r.quantity + '</span>') + '</td><td class="text-end">' + r.threshold + '</td></tr>';
            }).join('') : '<tr><td colspan="5" class="text-center py-4 text-muted">All good — nothing is below its alert level.</td></tr>';
        }).catch(ERP.error);
    }

    function loadRules() {
        ERP.api('/api/admin/stock-alert-rules').then(function (j) {
            var rows = j.data || [];
            $('ruleRows').innerHTML = rows.length ? rows.map(function (r) {
                var who = r.scope === 'brand' ? 'Brand: <strong>' + ERP.esc(r.brand) + '</strong>' : 'Category: <strong>' + ERP.esc(r.category_name || ('#' + r.category_id)) + '</strong>';
                return '<tr><td>' + who + '</td><td class="text-end">' + r.threshold + '</td><td class="text-end"><button class="btn btn-sm btn-outline-danger" data-del="' + r.id + '">Remove</button></td></tr>';
            }).join('') : '<tr><td colspan="3" class="text-center py-3 text-muted">No alert levels set yet.</td></tr>';
        }).catch(ERP.error);
    }

    $('ruleRows').addEventListener('click', function (e) {
        var t = e.target.closest('[data-del]'); if (!t) return;
        if (!confirm('Remove this alert level?')) return;
        ERP.api('/api/admin/stock-alert-rules/' + t.dataset.del, { method: 'DELETE' }).then(function () { loadRules(); loadAlerts(); }).catch(ERP.error);
    });

    $('alForm').addEventListener('submit', function (e) {
        e.preventDefault();
        var body = { scope: scope, threshold: parseInt($('alThreshold').value, 10) || 0 };
        if (scope === 'brand') body.brand = $('alBrand').value.trim(); else body.category_id = +$('alCat').value;
        ERP.api('/api/admin/stock-alert-rules', { method: 'POST', body: body }).then(function () {
            ERP.notify('Alert level saved.');
            if (scope === 'brand') $('alBrand').value = '';
            loadRules(); loadAlerts();
        }).catch(ERP.error);
    });
    $('alBranch').addEventListener('change', loadAlerts);

    Promise.all([
        ERP.fillBranchSelect($('alBranch'), { all: 'All branches', remember: false, value: ERP.currentBranch() }),
        ERP.api('/api/admin/stock-items?per_page=500').catch(function () { return { data: [] }; }),
        ERP.api('/api/admin/brands').catch(function () { return { data: [] }; }),
        ERP.api('/api/admin/categories').catch(function () { return { data: [] }; })
    ]).then(function (r) {
        var seen = {};
        (r[1].data || []).forEach(function (i) { if (i.brand) seen[i.brand] = 1; });
        (r[2].data || []).forEach(function (b) { if (b.name) seen[b.name] = 1; });
        $('alBrandList').innerHTML = Object.keys(seen).sort().map(function (n) { return '<option value="' + ERP.esc(n) + '">'; }).join('');
        $('alCat').innerHTML = (r[3].data || []).map(function (c) { return '<option value="' + c.id + '">' + ERP.esc(c.name) + '</option>'; }).join('');
        loadAlerts(); loadRules();
    }).catch(ERP.error);
})();
</script>
@endverbatim
@endpush
