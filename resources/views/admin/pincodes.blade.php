@extends('layouts.admin')
@section('title', 'Pincodes')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Serviceable Pincodes</h4>
    <button class="btn btn-outline-primary btn-sm" id="seedBtn">Seed Nashik Pincodes</button>
</div>

<div class="card mb-4">
    <div class="card-header" id="formTitle">Add Pincode</div>
    <div class="card-body">
        <div id="formError"></div>
        <form id="pincodeForm" class="row g-3">
            <input type="hidden" id="editId" value="">
            <div class="col-md-3">
                <label class="form-label">Pincode *</label>
                <input class="form-control" id="fPincode" required placeholder="e.g. 422001">
            </div>
            <div class="col-md-5">
                <label class="form-label">Area Name</label>
                <input class="form-control" id="fArea" placeholder="e.g. Nashik City">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="fActive" checked>
                    <label class="form-check-label" for="fActive">Active</label>
                </div>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary" id="submitBtn">Add</button>
                <button type="button" class="btn btn-outline-secondary" id="cancelBtn" style="display:none">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0" id="listBody">
        <div class="text-center py-5"><div class="spinner-border" role="status"></div></div>
    </div>
</div>
@endsection
@push('scripts')
@verbatim
<script>
(function () {
    var NASHIK_PINCODES = [
        { pincode: '422001', area: 'Nashik City' },
        { pincode: '422002', area: 'Nashik Road' },
        { pincode: '422003', area: 'Deolali' },
        { pincode: '422004', area: 'Satpur' },
        { pincode: '422005', area: 'Panchavati' },
        { pincode: '422006', area: 'Sinhagad Road' },
        { pincode: '422007', area: 'Cidco' },
        { pincode: '422008', area: 'Gangapur Road' },
        { pincode: '422009', area: 'Ambad' },
        { pincode: '422010', area: 'Trimbak Road' },
        { pincode: '422011', area: 'Malegaon Camp' },
        { pincode: '422012', area: 'Dindori Road' },
        { pincode: '422013', area: 'Nashik East' }
    ];

    var listBody = document.getElementById('listBody');
    var formTitle = document.getElementById('formTitle');
    var formError = document.getElementById('formError');
    var form = document.getElementById('pincodeForm');
    var editIdEl = document.getElementById('editId');
    var fPincode = document.getElementById('fPincode');
    var fArea = document.getElementById('fArea');
    var fActive = document.getElementById('fActive');
    var submitBtn = document.getElementById('submitBtn');
    var cancelBtn = document.getElementById('cancelBtn');
    var seedBtn = document.getElementById('seedBtn');

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function resetForm() {
        editIdEl.value = '';
        fPincode.value = '';
        fArea.value = '';
        fActive.checked = true;
        formTitle.textContent = 'Add Pincode';
        submitBtn.textContent = 'Add';
        cancelBtn.style.display = 'none';
    }

    function render(items) {
        if (!items.length) {
            listBody.innerHTML = '<div class="text-center py-5 text-muted">No pincodes added yet. Use "Seed Nashik Pincodes" to add defaults.</div>';
            return;
        }
        var rows = items.map(function (item) {
            return '<tr>' +
                '<td class="fw-bold">' + esc(item.pincode) + '</td>' +
                '<td>' + esc(item.area || '—') + '</td>' +
                '<td><span class="badge ' + (item.is_active ? 'bg-success' : 'bg-secondary') + '">' + (item.is_active ? 'Active' : 'Inactive') + '</span></td>' +
                '<td><div class="d-flex gap-2">' +
                    '<button class="btn btn-sm btn-outline-primary" data-edit="' + esc(item.id) + '">Edit</button>' +
                    '<button class="btn btn-sm btn-outline-warning" data-toggle="' + esc(item.id) + '">' + (item.is_active ? 'Disable' : 'Enable') + '</button>' +
                    '<button class="btn btn-sm btn-outline-danger" data-delete="' + esc(item.id) + '">Delete</button>' +
                '</div></td>' +
                '</tr>';
        }).join('');

        listBody.innerHTML = '<div class="table-responsive"><table class="table table-hover mb-0">' +
            '<thead class="table-light"><tr><th>Pincode</th><th>Area</th><th>Status</th><th>Actions</th></tr></thead>' +
            '<tbody>' + rows + '</tbody></table></div>';

        listBody.querySelectorAll('[data-edit]').forEach(function (b) {
            b.addEventListener('click', function () { startEdit(findItem(b.getAttribute('data-edit'))); });
        });
        listBody.querySelectorAll('[data-toggle]').forEach(function (b) {
            b.addEventListener('click', function () { toggleActive(findItem(b.getAttribute('data-toggle'))); });
        });
        listBody.querySelectorAll('[data-delete]').forEach(function (b) {
            b.addEventListener('click', function () { deleteItem(b.getAttribute('data-delete')); });
        });
    }

    var currentItems = [];
    function findItem(id) {
        id = Number(id);
        return currentItems.filter(function (i) { return Number(i.id) === id; })[0];
    }

    function load() {
        fetch('/api/admin/pincodes').then(function (r) { return r.json(); }).then(function (d) {
            currentItems = d.data || [];
            render(currentItems);
        });
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        formError.innerHTML = '';
        submitBtn.disabled = true;
        var prevLabel = submitBtn.textContent;
        submitBtn.textContent = 'Saving…';
        try {
            var editId = editIdEl.value;
            var url = editId ? '/api/admin/pincodes/' + editId : '/api/admin/pincodes';
            var method = editId ? 'PUT' : 'POST';
            var payload = { pincode: fPincode.value, area: fArea.value, is_active: fActive.checked };
            var r = await fetch(url, { method: method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            var d = await r.json();
            if (!r.ok) {
                formError.innerHTML = '<div class="alert alert-danger py-2">' + esc(d.message || 'Error saving') + '</div>';
                return;
            }
            resetForm();
            load();
        } finally {
            submitBtn.disabled = false;
            if (submitBtn.textContent === 'Saving…') submitBtn.textContent = prevLabel;
        }
    });

    cancelBtn.addEventListener('click', resetForm);

    function startEdit(item) {
        if (!item) return;
        editIdEl.value = item.id;
        fPincode.value = item.pincode;
        fArea.value = item.area || '';
        fActive.checked = !!item.is_active;
        formTitle.textContent = 'Edit Pincode';
        submitBtn.textContent = 'Update';
        cancelBtn.style.display = '';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    async function toggleActive(item) {
        if (!item) return;
        await fetch('/api/admin/pincodes/' + item.id, {
            method: 'PUT', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ is_active: !item.is_active })
        });
        load();
    }

    async function deleteItem(id) {
        if (!confirm('Delete this pincode?')) return;
        await fetch('/api/admin/pincodes/' + id, { method: 'DELETE' });
        load();
    }

    seedBtn.addEventListener('click', async function () {
        seedBtn.disabled = true;
        seedBtn.textContent = 'Seeding…';
        for (var i = 0; i < NASHIK_PINCODES.length; i++) {
            var p = NASHIK_PINCODES[i];
            try {
                await fetch('/api/admin/pincodes', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ pincode: p.pincode, area: p.area, is_active: true })
                });
            } catch (e) {}
        }
        seedBtn.disabled = false;
        seedBtn.textContent = 'Seed Nashik Pincodes';
        load();
    });

    load();
})();
</script>
@endverbatim
@endpush
