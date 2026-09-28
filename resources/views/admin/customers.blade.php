@extends('layouts.admin')
@section('title', 'Customers')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3 mb-0">Customers</h1></div>
<div id="cuError"></div>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold" id="cuFormTitle">New Customer</div>
    <div class="card-body">
        <form id="cuForm" class="row g-3">
            <input type="hidden" id="cu_id" value="">
            <div class="col-md-4"><label class="form-label fw-semibold">Name *</label><input class="form-control" id="cu_name" required></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Email</label><input type="email" class="form-control" id="cu_email"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Phone</label><input class="form-control" id="cu_phone"></div>
            <div class="col-12"><label class="form-label fw-semibold">Address</label><textarea class="form-control" rows="2" id="cu_address"></textarea></div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary" id="cuSubmit">Create</button>
                <button type="button" class="btn btn-sm btn-secondary mt-2" id="cuCancel" style="display:none">Cancel</button>
            </div>
        </form>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Date</th><th>Actions</th></tr></thead>
                <tbody id="cuRows">
                    <tr><td colspan="6" class="text-center py-4 text-muted">Loading…</td></tr>
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
    var rows = [], editing = null, loading = false;
    var errorEl = document.getElementById('cuError');
    var form = document.getElementById('cuForm');
    var f = {
        id: document.getElementById('cu_id'),
        name: document.getElementById('cu_name'),
        email: document.getElementById('cu_email'),
        phone: document.getElementById('cu_phone'),
        address: document.getElementById('cu_address'),
    };

    function showError(msg) {
        errorEl.innerHTML = msg ? '<div class="alert alert-danger py-2">' + msg + '</div>' : '';
    }

    function escapeHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function setFormFromEditing() {
        if (editing) {
            document.getElementById('cuFormTitle').textContent = 'Edit Customer #' + editing.id;
            document.getElementById('cuSubmit').textContent = 'Update';
            document.getElementById('cuCancel').style.display = '';
            f.id.value = editing.id;
            f.name.value = editing.name || '';
            f.email.value = editing.email || '';
            f.phone.value = editing.phone || '';
            f.address.value = editing.address || '';
        } else {
            document.getElementById('cuFormTitle').textContent = 'New Customer';
            document.getElementById('cuSubmit').textContent = 'Create';
            document.getElementById('cuCancel').style.display = 'none';
            f.id.value = '';
            f.name.value = '';
            f.email.value = '';
            f.phone.value = '';
            f.address.value = '';
        }
    }

    function renderRows() {
        var tbody = document.getElementById('cuRows');
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No customers yet.</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map(function (row) {
            return '<tr>' +
                '<td>' + row.id + '</td>' +
                '<td>' + escapeHtml(row.name) + '</td>' +
                '<td>' + (row.email ? escapeHtml(row.email) : '—') + '</td>' +
                '<td>' + (row.phone ? escapeHtml(row.phone) : '—') + '</td>' +
                '<td>' + new Date(row.created_at).toLocaleDateString('en-IN') + '</td>' +
                '<td><div class="d-flex gap-2">' +
                '<button class="btn btn-sm btn-outline-primary" data-edit="' + row.id + '">Edit</button>' +
                '<button class="btn btn-sm btn-outline-danger" data-del="' + row.id + '">Delete</button>' +
                '</div></td>' +
                '</tr>';
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

    async function load() {
        var r = await fetch('/api/admin/customers');
        var d = await r.json();
        rows = d.data || [];
        renderRows();
    }

    async function save(payload) {
        try {
            loading = true; showError('');
            document.getElementById('cuSubmit').disabled = true;
            document.getElementById('cuSubmit').textContent = 'Saving…';
            var url = editing ? '/api/admin/customers/' + editing.id : '/api/admin/customers';
            var method = editing ? 'PUT' : 'POST';
            await fetch(url, { method: method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            editing = null; setFormFromEditing(); await load();
        } catch (e) { showError('Failed.'); } finally {
            loading = false;
            document.getElementById('cuSubmit').disabled = false;
            document.getElementById('cuSubmit').textContent = editing ? 'Update' : 'Create';
        }
    }

    async function remove(id) {
        if (!window.confirm('Delete?')) return;
        await fetch('/api/admin/customers/' + id, { method: 'DELETE' });
        await load();
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        save({
            name: f.name.value,
            email: f.email.value,
            phone: f.phone.value,
            address: f.address.value,
        });
    });

    document.getElementById('cuCancel').addEventListener('click', function () { editing = null; setFormFromEditing(); });

    load();
})();
</script>
@endverbatim
@endpush
