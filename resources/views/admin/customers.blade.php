@extends('layouts.admin')
@section('title', 'Customers')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3 mb-0">Customers</h1></div>
<div id="erpAlerts"></div>
<div id="cuError"></div>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold" id="cuFormTitle">New Customer</div>
    <div class="card-body">
        <form id="cuForm" class="row g-3">
            <input type="hidden" id="cu_id" value="">
            <div class="col-md-4"><label class="form-label fw-semibold">Name *</label><input class="form-control" id="cu_name" required></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Email</label><input type="email" class="form-control" id="cu_email"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Phone</label><input class="form-control" id="cu_phone"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Date of birth</label><input type="date" class="form-control" id="cu_dob"></div>
            <div class="col-md-8"><label class="form-label fw-semibold">Address</label><textarea class="form-control" rows="1" id="cu_address"></textarea></div>
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="cu_consent">
                    <label class="form-check-label" for="cu_consent">Customer agrees to receive offers and updates (marketing consent)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="cu_wa">
                    <label class="form-check-label" for="cu_wa">OK to send offers on WhatsApp</label>
                </div>
                <div class="form-text">Consent is recorded with the date it was given. Untick to withdraw it.</div>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary" id="cuSubmit">Create</button>
                <button type="button" class="btn btn-sm btn-secondary mt-2" id="cuCancel" style="display:none">Cancel</button>
            </div>
        </form>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <span>All customers <span class="text-muted small fw-normal" id="cuCount"></span></span>
        <input type="search" class="form-control form-control-sm" id="cuSearch" placeholder="Search name, phone or email…" style="max-width:280px">
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Consent</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody id="cuRows">
                    <tr><td colspan="7" class="text-center py-4 text-muted">Loading…</td></tr>
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
    var rows = [], editing = null;
    var form = document.getElementById('cuForm');
    var f = {
        id: document.getElementById('cu_id'), name: document.getElementById('cu_name'), email: document.getElementById('cu_email'),
        phone: document.getElementById('cu_phone'), address: document.getElementById('cu_address'), dob: document.getElementById('cu_dob'),
        consent: document.getElementById('cu_consent'), wa: document.getElementById('cu_wa'),
    };

    function setFormFromEditing() {
        var e = editing;
        document.getElementById('cuFormTitle').textContent = e ? 'Edit Customer #' + e.id : 'New Customer';
        document.getElementById('cuSubmit').textContent = e ? 'Update' : 'Create';
        document.getElementById('cuCancel').style.display = e ? '' : 'none';
        f.id.value = e ? e.id : '';
        f.name.value = e ? (e.name || '') : '';
        f.email.value = e ? (e.email || '') : '';
        f.phone.value = e ? (e.phone || '') : '';
        f.address.value = e ? (e.address || '') : '';
        f.dob.value = e && e.dob ? String(e.dob).slice(0, 10) : '';
        f.consent.checked = !!(e && e.marketing_consent);
        f.wa.checked = !!(e && e.whatsapp_opt_in);
    }

    function renderRows() {
        var tbody = document.getElementById('cuRows');
        document.getElementById('cuCount').textContent = rows.length ? '(' + rows.length + ')' : '';
        if (!rows.length) { tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No customers found.</td></tr>'; return; }
        tbody.innerHTML = rows.map(function (row) {
            var consent = row.marketing_consent
                ? '<span class="badge bg-success" title="' + ERP.esc(row.consent_at ? 'Given ' + ERP.fmtDate(row.consent_at) : '') + '">Yes</span>' + (row.whatsapp_opt_in ? ' <span class="badge bg-info">WhatsApp</span>' : '')
                : '<span class="badge bg-secondary">No</span>';
            return '<tr>' +
                '<td>' + row.id + '</td>' +
                '<td><a href="/admin/customers/' + row.id + '" class="fw-semibold text-decoration-none">' + ERP.esc(row.name) + '</a></td>' +
                '<td>' + (row.email ? ERP.esc(row.email) : '—') + '</td>' +
                '<td>' + (row.phone ? ERP.esc(row.phone) : '—') + '</td>' +
                '<td>' + consent + '</td>' +
                '<td>' + new Date(row.created_at).toLocaleDateString('en-IN') + '</td>' +
                '<td><div class="d-flex gap-2">' +
                '<a class="btn btn-sm btn-outline-secondary" href="/admin/customers/' + row.id + '">Profile</a>' +
                '<button class="btn btn-sm btn-outline-primary" data-edit="' + row.id + '">Edit</button>' +
                '<button class="btn btn-sm btn-outline-danger" data-del="' + row.id + '">Delete</button>' +
                '</div></td></tr>';
        }).join('');
        tbody.querySelectorAll('[data-edit]').forEach(function (b) {
            b.addEventListener('click', function () {
                var id = Number(b.getAttribute('data-edit'));
                editing = rows.find(function (r) { return r.id === id; });
                setFormFromEditing();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
        tbody.querySelectorAll('[data-del]').forEach(function (b) {
            b.addEventListener('click', function () { remove(Number(b.getAttribute('data-del'))); });
        });
    }

    function load() {
        var q = document.getElementById('cuSearch').value.trim();
        return ERP.api('/api/admin/customers?per_page=200' + (q ? '&search=' + encodeURIComponent(q) : '')).then(function (d) {
            rows = d.data || []; renderRows();
        }).catch(ERP.error);
    }

    function save(payload) {
        var btn = document.getElementById('cuSubmit');
        btn.disabled = true; btn.textContent = 'Saving…';
        var url = editing ? '/api/admin/customers/' + editing.id : '/api/admin/customers';
        return ERP.api(url, { method: editing ? 'PUT' : 'POST', body: payload }).then(function () {
            ERP.notify(editing ? 'Customer updated.' : 'Customer added.');
            editing = null; setFormFromEditing(); return load();
        }).catch(ERP.error).then(function () {
            btn.disabled = false; btn.textContent = editing ? 'Update' : 'Create';
        });
    }

    function remove(id) {
        if (!window.confirm('Delete this customer?')) return;
        ERP.api('/api/admin/customers/' + id, { method: 'DELETE' }).then(load).catch(ERP.error);
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        save({
            name: f.name.value, email: f.email.value, phone: f.phone.value, address: f.address.value, dob: f.dob.value || null,
            marketing_consent: f.consent.checked, whatsapp_opt_in: f.wa.checked,
        });
    });
    document.getElementById('cuCancel').addEventListener('click', function () { editing = null; setFormFromEditing(); });

    var t;
    document.getElementById('cuSearch').addEventListener('input', function () { clearTimeout(t); t = setTimeout(load, 300); });

    load();
})();
</script>
@endverbatim
@endpush
