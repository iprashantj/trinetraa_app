@extends('layouts.admin')
@section('title', 'Coupons')
@section('content')
<div id="erpAlerts"></div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Coupons</h1>
    <button type="button" class="btn btn-primary btn-sm" id="cpNew">+ New coupon</button>
</div>
<p class="text-muted small">Staff type the code at the POS. The discount is checked again on the server when the bill is saved, so expired, used-up or wrong-brand codes cannot slip through.</p>

<div class="card mb-3" id="cpFormCard" style="display:none">
    <div class="card-header" id="cpFormTitle">New Coupon</div>
    <div class="card-body">
        <form id="cpForm" class="row g-3">
            <div class="col-md-3"><label class="form-label fw-semibold">Code *</label><input class="form-control text-uppercase" id="cp_code" required maxlength="40" pattern="[A-Za-z0-9_-]+" placeholder="DIWALI10"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Type *</label><select class="form-select" id="cp_type"><option value="percent">Percent off</option><option value="flat">Flat ₹ off</option></select></div>
            <div class="col-md-3"><label class="form-label fw-semibold" id="cpValueLbl">Percent *</label><input type="number" step="0.01" min="0.01" class="form-control" id="cp_value" required></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Max discount ₹</label><input type="number" step="0.01" min="0" class="form-control" id="cp_max_discount" placeholder="No cap"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Minimum bill ₹</label><input type="number" step="0.01" min="0" class="form-control" id="cp_min_amount" placeholder="0"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Only for brand</label><input class="form-control" id="cp_brand" maxlength="255" placeholder="Any brand"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Valid from</label><input type="date" class="form-control" id="cp_valid_from"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Valid to</label><input type="date" class="form-control" id="cp_valid_to"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Total uses allowed</label><input type="number" min="1" class="form-control" id="cp_usage_limit" placeholder="Unlimited"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Description</label><input class="form-control" id="cp_description" maxlength="255" placeholder="Shown to staff"></div>
            <div class="col-md-3 d-flex align-items-end"><div class="form-check"><input type="checkbox" class="form-check-input" id="cp_active" checked><label class="form-check-label" for="cp_active">Active</label></div></div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary" id="cpSave">Create</button>
                <button type="button" class="btn btn-outline-secondary" id="cpCancel">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Code</th><th>Discount</th><th>Rules</th><th>Valid</th><th>Used</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody id="cpRows"><tr><td colspan="7" class="text-center py-4 text-muted">Loading…</td></tr></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
@verbatim
<script>
(function () {
    var $ = function (id) { return document.getElementById(id); };
    var rows = [], editing = null;
    var F = ['code', 'type', 'value', 'max_discount', 'min_amount', 'brand', 'valid_from', 'valid_to', 'usage_limit', 'description'];

    function status(c) {
        var today = ERP.today();
        if (!c.is_active) return '<span class="badge bg-secondary">Off</span>';
        if (c.valid_to && String(c.valid_to).slice(0, 10) < today) return '<span class="badge bg-warning text-dark">Expired</span>';
        if (c.usage_limit && c.used_count >= c.usage_limit) return '<span class="badge bg-warning text-dark">Used up</span>';
        return '<span class="badge bg-success">Live</span>';
    }
    function load() {
        return ERP.api('/api/admin/coupons').then(function (j) {
            rows = j.data || [];
            $('cpRows').innerHTML = rows.length ? rows.map(function (c) {
                var rules = [];
                if (Number(c.min_amount) > 0) rules.push('min ' + ERP.inr(c.min_amount));
                if (c.max_discount != null) rules.push('cap ' + ERP.inr(c.max_discount));
                if (c.brand) rules.push(ERP.esc(c.brand) + ' only');
                var valid = (c.valid_from || c.valid_to) ? (ERP.fmtDate(c.valid_from) || '…') + ' → ' + (ERP.fmtDate(c.valid_to) || '…') : 'Always';
                return '<tr><td><code class="fw-bold">' + ERP.esc(c.code) + '</code><div class="small text-muted">' + ERP.esc(c.description || '') + '</div></td>'
                    + '<td>' + (c.type === 'percent' ? Number(c.value) + '%' : ERP.inr(c.value)) + '</td><td class="small">' + (rules.join(' · ') || '—') + '</td>'
                    + '<td class="small">' + valid + '</td><td>' + c.used_count + (c.usage_limit ? ' / ' + c.usage_limit : '') + '</td><td>' + status(c) + '</td>'
                    + '<td class="text-end text-nowrap"><button class="btn btn-sm btn-outline-primary" data-edit="' + c.id + '">Edit</button> <button class="btn btn-sm btn-outline-danger" data-del="' + c.id + '">Delete</button></td></tr>';
            }).join('') : '<tr><td colspan="7" class="text-center py-4 text-muted">No coupons yet.</td></tr>';
        }).catch(ERP.error);
    }
    function typeLabel() { $('cpValueLbl').textContent = $('cp_type').value === 'percent' ? 'Percent *' : 'Amount ₹ *'; }
    function openForm(c) {
        editing = c || null;
        $('cpFormCard').style.display = '';
        $('cpFormTitle').textContent = c ? 'Edit Coupon' : 'New Coupon';
        $('cpSave').textContent = c ? 'Save changes' : 'Create';
        F.forEach(function (f) {
            var v = c && c[f] != null ? c[f] : '';
            if (f === 'valid_from' || f === 'valid_to') v = v ? String(v).slice(0, 10) : '';
            $('cp_' + f).value = v;
        });
        if (!c) $('cp_type').value = 'percent';
        $('cp_active').checked = c ? !!c.is_active : true;
        typeLabel(); $('cp_code').focus();
    }
    function closeForm() { $('cpFormCard').style.display = 'none'; editing = null; }

    $('cp_type').addEventListener('change', typeLabel);
    $('cpNew').onclick = function () { openForm(null); };
    $('cpCancel').onclick = closeForm;
    $('cpForm').addEventListener('submit', function (e) {
        e.preventDefault();
        var v = function (f) { var x = $('cp_' + f).value.trim(); return x === '' ? null : x; };
        var num = function (f) { var x = v(f); return x === null ? null : +x; };
        var body = { code: v('code'), type: v('type'), value: num('value'), max_discount: num('max_discount'), min_amount: num('min_amount'), brand: v('brand'),
            valid_from: v('valid_from'), valid_to: v('valid_to'), usage_limit: num('usage_limit'), description: v('description'), is_active: $('cp_active').checked };
        $('cpSave').disabled = true;
        ERP.api('/api/admin/coupons' + (editing ? '/' + editing.id : ''), { method: editing ? 'PUT' : 'POST', body: body })
            .then(function () { ERP.notify(editing ? 'Coupon updated.' : 'Coupon created.'); closeForm(); return load(); })
            .catch(ERP.error).then(function () { $('cpSave').disabled = false; });
    });
    $('cpRows').addEventListener('click', function (e) {
        var t = e.target.closest('button'); if (!t) return;
        if (t.dataset.edit) openForm(rows.filter(function (c) { return String(c.id) === t.dataset.edit; })[0]);
        else if (t.dataset.del) {
            if (!confirm('Delete this coupon? Bills that already used it keep their discount.')) return;
            ERP.api('/api/admin/coupons/' + t.dataset.del, { method: 'DELETE' }).then(function () { ERP.notify('Coupon deleted.'); return load(); }).catch(ERP.error);
        }
    });
    load();
})();
</script>
@endverbatim
@endpush
