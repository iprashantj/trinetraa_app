@extends('layouts.admin')
@section('title', 'Suppliers')
@section('content')
<div id="erpAlerts"></div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Suppliers</h1>
    <button type="button" class="btn btn-primary btn-sm" id="spNew">+ New supplier</button>
</div>

<div class="card mb-3" id="spFormCard" style="display:none">
    <div class="card-header" id="spFormTitle">New Supplier</div>
    <div class="card-body">
        <form id="spForm" class="row g-3">
            <div class="col-md-5"><label class="form-label fw-semibold">Name *</label><input class="form-control" id="sp_name" required maxlength="255"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Phone</label><input class="form-control" id="sp_phone" maxlength="50"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Email</label><input type="email" class="form-control" id="sp_email"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">GSTIN</label><input class="form-control" id="sp_gstin" maxlength="20"></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Address</label><input class="form-control" id="sp_address"></div>
            <div class="col-md-2 d-flex align-items-end"><div class="form-check"><input type="checkbox" class="form-check-input" id="sp_active" checked><label class="form-check-label" for="sp_active">Active</label></div></div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary" id="spSave">Create</button>
                <button type="button" class="btn btn-outline-secondary" id="spCancel">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>GSTIN</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody id="spRows"><tr><td colspan="6" class="text-center py-4 text-muted">Loading…</td></tr></tbody>
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

    function load() {
        return ERP.api('/api/admin/suppliers').then(function (j) {
            rows = j.data || [];
            $('spRows').innerHTML = rows.length ? rows.map(function (s) {
                var on = s.is_active === 1 || s.is_active === true;
                return '<tr><td class="fw-semibold">' + ERP.esc(s.name) + '</td><td>' + ERP.esc(s.phone || '') + '</td><td>' + ERP.esc(s.email || '') + '</td><td>' + ERP.esc(s.gstin || '')
                    + '</td><td>' + (on ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>') + '</td>'
                    + '<td class="text-end text-nowrap"><button class="btn btn-sm btn-outline-primary" data-edit="' + s.id + '">Edit</button> <button class="btn btn-sm btn-outline-danger" data-del="' + s.id + '">Delete</button></td></tr>';
            }).join('') : '<tr><td colspan="6" class="text-center py-4 text-muted">No suppliers yet.</td></tr>';
        }).catch(ERP.error);
    }

    function openForm(s) {
        editing = s || null;
        $('spFormCard').style.display = '';
        $('spFormTitle').textContent = s ? 'Edit Supplier' : 'New Supplier';
        $('spSave').textContent = s ? 'Save changes' : 'Create';
        $('sp_name').value = s ? s.name : ''; $('sp_phone').value = s && s.phone || ''; $('sp_email').value = s && s.email || '';
        $('sp_gstin').value = s && s.gstin || ''; $('sp_address').value = s && s.address || '';
        $('sp_active').checked = s ? (s.is_active === 1 || s.is_active === true) : true;
        $('sp_name').focus();
    }
    function closeForm() { $('spFormCard').style.display = 'none'; editing = null; }

    $('spNew').onclick = function () { openForm(null); };
    $('spCancel').onclick = closeForm;
    $('spForm').addEventListener('submit', function (e) {
        e.preventDefault();
        var v = function (id) { var x = $(id).value.trim(); return x === '' ? null : x; };
        var body = { name: v('sp_name'), phone: v('sp_phone'), email: v('sp_email'), gstin: v('sp_gstin'), address: v('sp_address'), is_active: $('sp_active').checked };
        $('spSave').disabled = true;
        ERP.api('/api/admin/suppliers' + (editing ? '/' + editing.id : ''), { method: editing ? 'PUT' : 'POST', body: body })
            .then(function () { ERP.notify(editing ? 'Supplier updated.' : 'Supplier added.'); closeForm(); return load(); })
            .catch(ERP.error).then(function () { $('spSave').disabled = false; });
    });
    $('spRows').addEventListener('click', function (e) {
        var t = e.target.closest('button'); if (!t) return;
        if (t.dataset.edit) openForm(rows.filter(function (s) { return String(s.id) === t.dataset.edit; })[0]);
        else if (t.dataset.del) {
            if (!confirm('Delete this supplier? Past stock entries keep their records.')) return;
            ERP.api('/api/admin/suppliers/' + t.dataset.del, { method: 'DELETE' }).then(function () { ERP.notify('Supplier deleted.'); return load(); }).catch(ERP.error);
        }
    });
    load();
})();
</script>
@endverbatim
@endpush
