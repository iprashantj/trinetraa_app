@extends('layouts.admin')
@section('title', 'Eyewears')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Eyewears</h1>
    <div class="d-flex align-items-center gap-2">
        <label class="me-1 mb-0 small fw-semibold">Category:</label>
        <select class="form-select form-select-sm" style="width:auto" id="categoryFilter">
            <option value="">All</option>
        </select>
    </div>
</div>
<div id="ewError" class="alert alert-danger py-2" style="display:none"></div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold" id="ewFormTitle">New Eyewear</div>
    <div class="card-body">
        <form id="ewForm" class="row g-3">
            <div class="col-md-4"><label class="form-label fw-semibold">Category *</label>
                <select class="form-select" name="category_id" id="ewCategorySelect" required>
                    <option value="">Select category</option>
                </select>
            </div>
            <div class="col-md-4"><label class="form-label fw-semibold">Name *</label><input class="form-control" name="name" required></div>
            <div class="col-md-4"><label class="form-label fw-semibold">Slug</label><input class="form-control" name="slug" placeholder="auto-generated"></div>
            <div class="col-12"><label class="form-label fw-semibold">Description</label><textarea class="form-control" rows="2" name="description"></textarea></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Price (₹) *</label><input type="number" step="0.01" class="form-control" name="price" required></div>
            <div class="col-md-2"><label class="form-label fw-semibold">Discount %</label><input type="number" class="form-control" name="discount_percentage" value="0"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Brand</label><input class="form-control" name="brand"></div>
            <div class="col-md-2"><label class="form-label fw-semibold">Sort</label><input type="number" class="form-control" name="sort_order" value="0"></div>
            <div class="col-md-2 d-flex align-items-end"><div class="form-check mb-2"><input type="checkbox" class="form-check-input" id="ewActive" name="is_active" checked><label class="form-check-label fw-semibold" for="ewActive">Active</label></div></div>
            <div class="col-12">
                <label class="form-label fw-semibold">Product Image</label>
                <div class="d-flex gap-3 align-items-start">
                    <div id="ewImgPreview" style="width:80px;height:80px;border:2px dashed #dee2e6;border-radius:8px;overflow:hidden;flex-shrink:0;background:#f8f9fa;display:flex;align-items:center;justify-content:center;font-size:1.5rem">🕶️</div>
                    <div class="flex-grow-1">
                        <input class="form-control mb-2" name="image" id="ewImageUrl" placeholder="Paste image URL — or choose a file to upload">
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <label class="btn btn-sm btn-outline-secondary mb-0" for="ewImageFile" style="cursor:pointer">📁 Upload Image</label>
                            <input type="file" id="ewImageFile" accept="image/jpeg,image/jpg,image/png,image/webp,image/gif" style="display:none">
                            <span id="ewImgStatus" class="small text-muted"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12"><hr class="my-1"><div class="fw-semibold text-primary mb-2">🛒 Marketplace Integration</div></div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Availability</label>
                <select class="form-select" name="availability">
                    <option value="in_store">In Store Only</option>
                    <option value="amazon">Amazon Only</option>
                    <option value="flipkart">Flipkart Only</option>
                    <option value="both">Amazon &amp; Flipkart</option>
                    <option value="out_of_stock">Out of Stock</option>
                </select>
            </div>
            <div class="col-md-4"><label class="form-label fw-semibold">Marketplace SKU</label><input class="form-control" name="marketplace_sku" placeholder="e.g. RB3025-GLD-55"></div>
            <div class="col-12">
                <label class="form-label fw-semibold">Amazon Product URL</label>
                <div class="input-group">
                    <span class="input-group-text"><input type="checkbox" class="form-check-input mt-0 me-1" name="amazon_enabled" title="Enable Amazon button"> Enable</span>
                    <input class="form-control" name="amazon_url" placeholder="https://www.amazon.in/dp/...">
                </div>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Flipkart Product URL</label>
                <div class="input-group">
                    <span class="input-group-text"><input type="checkbox" class="form-check-input mt-0 me-1" name="flipkart_enabled" title="Enable Flipkart button"> Enable</span>
                    <input class="form-control" name="flipkart_url" placeholder="https://www.flipkart.com/...">
                </div>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary" id="ewSubmitBtn">Create Eyewear</button>
                <button type="button" class="btn btn-sm btn-secondary mt-2" id="ewCancelBtn" style="display:none">Cancel Edit</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Category</th><th class="text-end">Price</th><th>Availability</th><th>Marketplace</th><th>Active</th><th>Actions</th></tr></thead>
                <tbody id="ewTableBody">
                    <tr><td colspan="8" class="text-center py-4 text-muted">No eyewears yet.</td></tr>
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
    var form = document.getElementById('ewForm');
    var tbody = document.getElementById('ewTableBody');
    var errBox = document.getElementById('ewError');
    var formTitle = document.getElementById('ewFormTitle');
    var submitBtn = document.getElementById('ewSubmitBtn');
    var cancelBtn = document.getElementById('ewCancelBtn');
    var categoryFilter = document.getElementById('categoryFilter');
    var categorySelect = document.getElementById('ewCategorySelect');

    var editingId = null;
    var loading = false;
    var rowsData = [];
    var categories = [];
    var ewWidget;

    function esc(s) {
        return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
    function showError(msg) { errBox.textContent = msg; errBox.style.display = 'block'; }
    function clearError() { errBox.textContent = ''; errBox.style.display = 'none'; }

    function loadCategories() {
        return fetch('/api/admin/categories?per_page=200')
            .then(function (r) { return r.json(); })
            .then(function (d) {
                categories = d.data || [];
                var opts = categories.map(function (c) {
                    return '<option value="' + esc(c.id) + '">' + esc(c.name) + '</option>';
                }).join('');
                categoryFilter.innerHTML = '<option value="">All</option>' + opts;
                categorySelect.innerHTML = '<option value="">Select category</option>' + opts;
            });
    }

    function loadEyewears() {
        var q = categoryFilter.value ? '?category_id=' + encodeURIComponent(categoryFilter.value) : '';
        return fetch('/api/admin/eyewears' + q)
            .then(function (r) { return r.json(); })
            .then(function (d) { rowsData = d.data || []; render(); });
    }

    function render() {
        if (!rowsData.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-muted">No eyewears yet.</td></tr>';
            return;
        }
        tbody.innerHTML = rowsData.map(function (row) {
            var avail = row.availability || 'in_store';
            var availClass = avail === 'out_of_stock' ? 'bg-danger' : (avail === 'both' ? 'bg-success' : 'bg-info');
            var marketplace = '';
            if (row.amazon_enabled) marketplace += '<span class="badge bg-warning text-dark" style="font-size:0.65rem">AMZ</span>';
            if (row.flipkart_enabled) marketplace += '<span class="badge" style="background:#2874f0;font-size:0.65rem">FK</span>';
            var activeBadge = row.is_active
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-secondary">Inactive</span>';
            return '<tr>' +
                '<td>' + esc(row.id) + '</td>' +
                '<td>' + esc(row.name) + '</td>' +
                '<td>' + esc(row.category && row.category.name ? row.category.name : '—') + '</td>' +
                '<td class="text-end">₹' + Number(row.price).toFixed(2) + '</td>' +
                '<td><span class="badge ' + availClass + '" style="font-size:0.7rem">' + esc(avail) + '</span></td>' +
                '<td><div class="d-flex gap-1">' + marketplace + '</div></td>' +
                '<td>' + activeBadge + '</td>' +
                '<td><div class="d-flex gap-2">' +
                    '<button class="btn btn-sm btn-outline-primary" data-edit="' + esc(row.id) + '">Edit</button>' +
                    '<button class="btn btn-sm btn-outline-danger" data-del="' + esc(row.id) + '">Delete</button>' +
                '</div></td>' +
            '</tr>';
        }).join('');
    }

    tbody.addEventListener('click', function (e) {
        var editBtn = e.target.closest('[data-edit]');
        var delBtn = e.target.closest('[data-del]');
        if (editBtn) {
            var id = Number(editBtn.getAttribute('data-edit'));
            var row = rowsData.find(function (r) { return r.id === id; });
            if (row) startEdit(row);
        } else if (delBtn) {
            remove(Number(delBtn.getAttribute('data-del')));
        }
    });

    function startEdit(row) {
        editingId = row.id;
        form.category_id.value = (row.category_id != null ? row.category_id : '');
        form.name.value = row.name || '';
        form.slug.value = row.slug || '';
        form.description.value = row.description || '';
        form.price.value = (row.price != null ? Number(row.price) : '');
        form.discount_percentage.value = (row.discount_percentage != null ? row.discount_percentage : 0);
        form.image.value = row.image || '';
        if (ewWidget) ewWidget.updatePreview(row.image || '');
        form.brand.value = row.brand || '';
        form.sort_order.value = (row.sort_order != null ? row.sort_order : 0);
        form.is_active.checked = (row.is_active != null ? row.is_active : true);
        form.amazon_url.value = row.amazon_url || '';
        form.flipkart_url.value = row.flipkart_url || '';
        form.amazon_enabled.checked = (row.amazon_enabled != null ? row.amazon_enabled : false);
        form.flipkart_enabled.checked = (row.flipkart_enabled != null ? row.flipkart_enabled : false);
        form.availability.value = row.availability || 'in_store';
        form.marketplace_sku.value = row.marketplace_sku || '';
        formTitle.textContent = 'Edit Eyewear #' + row.id;
        submitBtn.textContent = 'Update Eyewear';
        cancelBtn.style.display = '';
        clearError();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function resetForm() {
        editingId = null;
        form.reset();
        form.discount_percentage.value = 0;
        form.sort_order.value = 0;
        form.is_active.checked = true;
        form.availability.value = 'in_store';
        if (ewWidget) ewWidget.updatePreview('');
        formTitle.textContent = 'New Eyewear';
        submitBtn.textContent = 'Create Eyewear';
        cancelBtn.style.display = 'none';
    }

    cancelBtn.addEventListener('click', function () { resetForm(); clearError(); });

    function payloadFromForm() {
        return {
            category_id: form.category_id.value,
            name: form.name.value,
            slug: form.slug.value,
            description: form.description.value,
            price: form.price.value,
            discount_percentage: Number(form.discount_percentage.value),
            image: form.image.value,
            brand: form.brand.value,
            sort_order: Number(form.sort_order.value),
            is_active: form.is_active.checked,
            amazon_url: form.amazon_url.value,
            flipkart_url: form.flipkart_url.value,
            amazon_enabled: form.amazon_enabled.checked,
            flipkart_enabled: form.flipkart_enabled.checked,
            availability: form.availability.value,
            marketplace_sku: form.marketplace_sku.value
        };
    }

    async function apiCall(url, method, body) {
        var r = await window.apiFetch(url, { method: method, body: JSON.stringify(body) });
        var d = await r.json();
        if (!r.ok) throw new Error(d.message || 'Request failed.');
        return d;
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (loading) return;
        loading = true; clearError(); submitBtn.disabled = true;
        submitBtn.textContent = 'Saving…';
        var payload = payloadFromForm();
        try {
            if (editingId) {
                await apiCall('/api/admin/eyewears/' + editingId, 'PUT', payload);
            } else {
                await apiCall('/api/admin/eyewears', 'POST', payload);
            }
            resetForm();
            await loadEyewears();
        } catch (err) {
            showError(err.message);
        } finally {
            loading = false; submitBtn.disabled = false;
            submitBtn.textContent = editingId ? 'Update Eyewear' : 'Create Eyewear';
        }
    });

    async function remove(id) {
        if (!window.confirm('Delete this eyewear?')) return;
        await fetch('/api/admin/eyewears/' + id, { method: 'DELETE' });
        await loadEyewears();
    }

    categoryFilter.addEventListener('change', loadEyewears);

    document.addEventListener('DOMContentLoaded', function () {
        ewWidget = window.initImageWidget({
            urlInputId:  'ewImageUrl',
            fileInputId: 'ewImageFile',
            previewId:   'ewImgPreview',
            statusId:    'ewImgStatus',
            folder:      'eyewears',
            placeholder: '🕶️'
        });
        loadCategories().then(loadEyewears);
    });
})();
</script>
@endverbatim
@endpush
