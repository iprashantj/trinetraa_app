@extends('layouts.admin')
@section('title', 'Branches')
@section('content')
<div id="erpAlerts"></div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Branches</h1>
        <p class="text-muted small mb-0">Each active branch with an address gets a public store page at <code>/stores/&lt;code&gt;</code> (for example <code>/stores/pun</code>). Fill in the Pune and Mumbai details so their pages can go live.</p>
    </div>
    <button class="btn btn-primary" id="brAdd">+ Add Branch</button>
</div>

<div class="card border-0 shadow-sm mb-4" id="brFormCard" style="display:none">
    <div class="card-header bg-white fw-semibold" id="brFormTitle">New Branch</div>
    <div class="card-body">
        <form id="brForm" class="row g-3">
            <input type="hidden" id="br_id">
            <div class="col-md-4"><label class="form-label fw-semibold">Name *</label><input class="form-control" id="br_name" required maxlength="255"></div>
            <div class="col-md-2"><label class="form-label fw-semibold">Code *</label><input class="form-control" id="br_code" required maxlength="20" pattern="[A-Za-z0-9_-]+" placeholder="PUN"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">City</label><input class="form-control" id="br_city" maxlength="255"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Phone</label><input class="form-control" id="br_phone" maxlength="50"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Address</label><textarea class="form-control" rows="2" id="br_address" maxlength="1000"></textarea></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Email</label><input type="email" class="form-control" id="br_email" maxlength="255">
                <label class="form-label fw-semibold mt-2">Opening hours</label><input class="form-control" id="br_hours" maxlength="255" placeholder="Mon–Sat: 10 AM – 8 PM, Sun: 11 AM – 6 PM"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">GSTIN</label><input class="form-control" id="br_gstin" maxlength="20" placeholder="27ABCDE1234F1Z5"><div class="form-text">Printed on this branch's invoices.</div></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Invoice prefix</label><input class="form-control" id="br_prefix" maxlength="10" placeholder="NSK"><div class="form-text">e.g. NSK/2026-27/0001</div></div>
            <div class="col-md-5"><label class="form-label fw-semibold">Google review link</label><input class="form-control" id="br_review" maxlength="500" placeholder="https://g.page/r/…/review"></div>
            <div class="col-md-9"><label class="form-label fw-semibold">Google Maps URL</label><input class="form-control" id="br_map" maxlength="500" placeholder="https://maps.google.com/?q=..."></div>
            <div class="col-md-3 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" id="br_active" checked><label class="form-check-label" for="br_active">Active</label></div></div>
            <div class="col-12">
                <button class="btn btn-primary" type="submit" id="brSave">Save</button>
                <button class="btn btn-outline-secondary" type="button" id="brCancel">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Branch</th><th>Code</th><th>Address</th><th>Phone</th><th>Status</th><th>Store page</th><th class="text-end">Actions</th></tr></thead>
                <tbody id="brRows"><tr><td colspan="7" class="text-center text-muted py-4">Loading…</td></tr></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@verbatim
<script>
(function () {
    var rows = [], card = document.getElementById('brFormCard');
    var ids = ['name', 'code', 'city', 'phone', 'address', 'email', 'hours', 'map', 'gstin', 'prefix', 'review'];
    function el(k) { return document.getElementById('br_' + k); }

    function open(b) {
        card.style.display = '';
        document.getElementById('brFormTitle').textContent = b ? 'Edit Branch — ' + b.name : 'New Branch';
        el('id').value = b ? b.id : '';
        el('name').value = b ? b.name || '' : ''; el('code').value = b ? b.code || '' : ''; el('city').value = b ? b.city || '' : '';
        el('phone').value = b ? b.phone || '' : ''; el('address').value = b ? b.address || '' : ''; el('email').value = b ? b.email || '' : '';
        el('hours').value = b ? b.hours || '' : ''; el('map').value = b ? b.map_url || '' : '';
        el('gstin').value = b ? b.gstin || '' : ''; el('prefix').value = b ? b.invoice_prefix || '' : ''; el('review').value = b ? b.review_url || '' : '';
        el('active').checked = b ? !!b.is_active : true;
        card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    function close() { card.style.display = 'none'; }

    function render() {
        var tb = document.getElementById('brRows');
        if (!rows.length) { tb.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No branches yet.</td></tr>'; return; }
        tb.innerHTML = rows.map(function (b) {
            var live = b.is_active && b.address;
            return '<tr><td class="fw-semibold">' + ERP.esc(b.name) + '<div class="small text-muted">' + ERP.esc(b.city || '') + '</div></td>' +
                '<td><code>' + ERP.esc(b.code) + '</code></td>' +
                '<td class="small">' + (b.address ? ERP.esc(b.address) : '<span class="text-warning">Address missing</span>') + '</td>' +
                '<td>' + ERP.esc(b.phone || '—') + '</td>' +
                '<td><span class="badge ' + (b.is_active ? 'bg-success' : 'bg-secondary') + '">' + (b.is_active ? 'Active' : 'Inactive') + '</span></td>' +
                '<td>' + (live ? '<a target="_blank" href="/stores/' + encodeURIComponent(String(b.code).toLowerCase()) + '">/stores/' + ERP.esc(String(b.code).toLowerCase()) + '</a>' : '<span class="text-muted small">Not published</span>') + '</td>' +
                '<td class="text-end text-nowrap"><button class="btn btn-sm btn-outline-secondary" data-edit="' + b.id + '">Edit</button> <button class="btn btn-sm btn-outline-danger" data-del="' + b.id + '">Delete</button></td></tr>';
        }).join('');
        tb.querySelectorAll('[data-edit]').forEach(function (x) { x.onclick = function () { open(rows.find(function (r) { return r.id == x.getAttribute('data-edit'); })); }; });
        tb.querySelectorAll('[data-del]').forEach(function (x) {
            x.onclick = function () {
                var b = rows.find(function (r) { return r.id == x.getAttribute('data-del'); });
                if (!confirm('Delete branch ' + b.name + '? Branches that still hold stock cannot be deleted.')) return;
                ERP.api('/api/admin/branches/' + b.id, { method: 'DELETE' }).then(function () { ERP.notify('Branch deleted.'); load(); }).catch(ERP.error);
            };
        });
    }

    function load() { return ERP.api('/api/admin/branches').then(function (j) { rows = j.data || []; render(); }).catch(ERP.error); }

    document.getElementById('brAdd').onclick = function () { open(null); };
    document.getElementById('brCancel').onclick = close;
    document.getElementById('brForm').addEventListener('submit', function (e) {
        e.preventDefault();
        var id = el('id').value;
        var body = {
            name: el('name').value, code: el('code').value, city: el('city').value || null, phone: el('phone').value || null,
            address: el('address').value || null, email: el('email').value || null, hours: el('hours').value || null,
            map_url: el('map').value || null, gstin: el('gstin').value.trim().toUpperCase() || null, invoice_prefix: el('prefix').value.trim() || null, review_url: el('review').value.trim() || null, is_active: el('active').checked,
        };
        ERP.api(id ? '/api/admin/branches/' + id : '/api/admin/branches', { method: id ? 'PUT' : 'POST', body: body })
            .then(function () { ERP.notify(id ? 'Branch updated.' : 'Branch added.'); close(); load(); }).catch(ERP.error);
    });
    load();
})();
</script>
@endverbatim
@endpush
