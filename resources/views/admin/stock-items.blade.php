@extends('layouts.admin')
@section('title', 'Stock Items')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3 mb-0">Stock Items</h1></div>
<div id="siError"></div>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold" id="siFormTitle">New Stock Item</div>
    <div class="card-body">
        <form id="siForm" class="row g-3">
            <input type="hidden" id="si_id" value="">
            <div class="col-md-6"><label class="form-label fw-semibold">Name *</label><input class="form-control" id="si_name" required></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Model No</label><input class="form-control" id="si_model_number"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">SKU</label><input class="form-control" id="si_sku"></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Category</label>
                <select class="form-select" id="si_category_id">
                    <option value="">None</option>
                </select>
            </div>
            <div class="col-md-2"><label class="form-label fw-semibold">Stock</label><input type="number" class="form-control" id="si_current_stock" value="0"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Sale Price (₹)</label><input type="number" step="0.01" class="form-control" id="si_sale_price"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Cost Price (₹)</label><input type="number" step="0.01" class="form-control" id="si_cost_price"></div>
            <div class="col-12"><label class="form-label fw-semibold">Description</label><textarea class="form-control" rows="2" id="si_description"></textarea></div>
            <div class="col-12"><label class="form-label fw-semibold">Image URL</label><input class="form-control" id="si_image"></div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary" id="siSubmit">Create</button>
                <button type="button" class="btn btn-sm btn-secondary mt-2" id="siCancel" style="display:none">Cancel</button>
            </div>
        </form>
    </div>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Model</th><th>SKU</th><th class="text-end">Stock</th><th class="text-end">Sale ₹</th><th>Actions</th></tr></thead>
                <tbody id="siRows">
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
    var rows = [], categories = [], editing = null, loading = false;
    var errorEl = document.getElementById('siError');
    var form = document.getElementById('siForm');
    var f = {
        id: document.getElementById('si_id'),
        name: document.getElementById('si_name'),
        model_number: document.getElementById('si_model_number'),
        sku: document.getElementById('si_sku'),
        category_id: document.getElementById('si_category_id'),
        current_stock: document.getElementById('si_current_stock'),
        sale_price: document.getElementById('si_sale_price'),
        cost_price: document.getElementById('si_cost_price'),
        description: document.getElementById('si_description'),
        image: document.getElementById('si_image'),
    };

    function showError(msg) {
        errorEl.innerHTML = msg ? '<div class="alert alert-danger py-2">' + msg + '</div>' : '';
    }

    function escapeHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function renderCategories() {
        f.category_id.innerHTML = '<option value="">None</option>' + categories.map(function (c) {
            return '<option value="' + c.id + '">' + escapeHtml(c.name) + '</option>';
        }).join('');
    }

    function setFormFromEditing() {
        if (editing) {
            document.getElementById('siFormTitle').textContent = 'Edit Item #' + editing.id;
            document.getElementById('siSubmit').textContent = 'Update';
            document.getElementById('siCancel').style.display = '';
            f.id.value = editing.id;
            f.name.value = editing.name || '';
            f.model_number.value = editing.model_number || '';
            f.sku.value = editing.sku || '';
            f.category_id.value = editing.category_id || '';
            f.current_stock.value = editing.current_stock != null ? editing.current_stock : 0;
            f.sale_price.value = Number(editing.sale_price) || '';
            f.cost_price.value = editing.cost_price ? Number(editing.cost_price) : '';
            f.description.value = editing.description || '';
            f.image.value = editing.image || '';
        } else {
            document.getElementById('siFormTitle').textContent = 'New Stock Item';
            document.getElementById('siSubmit').textContent = 'Create';
            document.getElementById('siCancel').style.display = 'none';
            f.id.value = '';
            f.name.value = '';
            f.model_number.value = '';
            f.sku.value = '';
            f.category_id.value = '';
            f.current_stock.value = 0;
            f.sale_price.value = '';
            f.cost_price.value = '';
            f.description.value = '';
            f.image.value = '';
        }
    }

    function renderRows() {
        var tbody = document.getElementById('siRows');
        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No items yet.</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map(function (row) {
            return '<tr>' +
                '<td>' + row.id + '</td>' +
                '<td>' + escapeHtml(row.name) + '</td>' +
                '<td>' + (row.model_number ? escapeHtml(row.model_number) : '—') + '</td>' +
                '<td>' + (row.sku ? escapeHtml(row.sku) : '—') + '</td>' +
                '<td class="text-end">' + row.current_stock + '</td>' +
                '<td class="text-end">₹' + Number(row.sale_price).toFixed(2) + '</td>' +
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
        var r = await fetch('/api/admin/stock-items');
        var d = await r.json();
        rows = d.data || [];
        renderRows();
    }

    async function save(payload) {
        try {
            loading = true; showError('');
            document.getElementById('siSubmit').disabled = true;
            document.getElementById('siSubmit').textContent = 'Saving…';
            var url = editing ? '/api/admin/stock-items/' + editing.id : '/api/admin/stock-items';
            var method = editing ? 'PUT' : 'POST';
            var r = await fetch(url, { method: method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            var d = await r.json();
            if (!r.ok) { showError(d.message || 'Failed.'); return; }
            editing = null; setFormFromEditing(); await load();
        } catch (e) { showError('Failed to save.'); } finally {
            loading = false;
            document.getElementById('siSubmit').disabled = false;
            document.getElementById('siSubmit').textContent = editing ? 'Update' : 'Create';
        }
    }

    async function remove(id) {
        if (!window.confirm('Delete?')) return;
        await fetch('/api/admin/stock-items/' + id, { method: 'DELETE' });
        await load();
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        save({
            name: f.name.value,
            model_number: f.model_number.value,
            sku: f.sku.value,
            category_id: f.category_id.value,
            current_stock: Number(f.current_stock.value),
            sale_price: f.sale_price.value,
            cost_price: f.cost_price.value,
            description: f.description.value,
            image: f.image.value,
        });
    });

    document.getElementById('siCancel').addEventListener('click', function () { editing = null; setFormFromEditing(); });

    load();
    fetch('/api/admin/categories?per_page=200').then(function (r) { return r.json(); }).then(function (d) { categories = d.data || []; renderCategories(); });
})();
</script>
@endverbatim
@endpush
