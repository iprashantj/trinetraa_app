@extends('layouts.admin')
@section('title', 'Categories')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Categories</h1>
</div>
<div id="catError" class="alert alert-danger py-2" style="display:none"></div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold" id="catFormTitle">New Category</div>
    <div class="card-body">
        <form id="catForm" class="row g-3">
            <div class="col-md-6"><label class="form-label fw-semibold">Name *</label><input class="form-control" name="name" required></div>
            <div class="col-md-6"><label class="form-label fw-semibold">Slug</label><input class="form-control" name="slug" placeholder="auto-generated"></div>
            <div class="col-12"><label class="form-label fw-semibold">Description</label><textarea class="form-control" rows="2" name="description"></textarea></div>
            <div class="col-md-8">
                <label class="form-label fw-semibold">Category Image</label>
                <div class="d-flex gap-3 align-items-start">
                    <div id="catImgPreview" style="width:72px;height:72px;border:2px dashed #dee2e6;border-radius:8px;overflow:hidden;flex-shrink:0;background:#f8f9fa;display:flex;align-items:center;justify-content:center;font-size:1.3rem">☰</div>
                    <div class="flex-grow-1">
                        <input class="form-control mb-2" name="image" id="catImageUrl" placeholder="Paste image URL — or upload a file">
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <label class="btn btn-sm btn-outline-secondary mb-0" for="catImageFile" style="cursor:pointer">📁 Upload</label>
                            <input type="file" id="catImageFile" accept="image/jpeg,image/jpg,image/png,image/webp,image/gif" style="display:none">
                            <span id="catImgStatus" class="small text-muted"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2"><label class="form-label fw-semibold">Sort</label><input type="number" class="form-control" name="sort_order" value="0"></div>
            <div class="col-md-2 d-flex align-items-end"><div class="form-check mb-2"><input type="checkbox" class="form-check-input" id="isActive" name="is_active" checked><label class="form-check-label fw-semibold" for="isActive">Active</label></div></div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary" id="catSubmitBtn">Create Category</button>
                <button type="button" class="btn btn-sm btn-secondary mt-2" id="catCancelBtn" style="display:none">Cancel Edit</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Slug</th><th>Sort</th><th>Active</th><th>Eyewears</th><th>Actions</th></tr></thead>
                <tbody id="catTableBody">
                    <tr><td colspan="7" class="text-center py-4 text-muted">No categories yet.</td></tr>
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
    var form = document.getElementById('catForm');
    var tbody = document.getElementById('catTableBody');
    var errBox = document.getElementById('catError');
    var formTitle = document.getElementById('catFormTitle');
    var submitBtn = document.getElementById('catSubmitBtn');
    var cancelBtn = document.getElementById('catCancelBtn');
    var editingId = null;
    var loading = false;
    var catWidget;

    function esc(s) {
        return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
    function showError(msg) { errBox.textContent = msg; errBox.style.display = 'block'; }
    function clearError() { errBox.textContent = ''; errBox.style.display = 'none'; }

    var rowsData = [];

    function load() {
        fetch('/api/admin/categories')
            .then(function (r) { return r.json(); })
            .then(function (d) { rowsData = d.data || []; render(); });
    }

    function render() {
        if (!rowsData.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No categories yet.</td></tr>';
            return;
        }
        tbody.innerHTML = rowsData.map(function (row) {
            var activeBadge = row.is_active
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-secondary">Inactive</span>';
            return '<tr>' +
                '<td>' + esc(row.id) + '</td>' +
                '<td>' + esc(row.name) + '</td>' +
                '<td><code>' + esc(row.slug) + '</code></td>' +
                '<td>' + esc(row.sort_order) + '</td>' +
                '<td>' + activeBadge + '</td>' +
                '<td>' + esc(row.eyewears_count) + '</td>' +
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
        form.name.value = row.name || '';
        form.slug.value = row.slug || '';
        form.description.value = row.description || '';
        form.image.value = row.image || '';
        if (catWidget) catWidget.updatePreview(row.image || '');
        form.sort_order.value = (row.sort_order != null ? row.sort_order : 0);
        form.is_active.checked = (row.is_active != null ? row.is_active : true);
        formTitle.textContent = 'Edit Category #' + row.id;
        submitBtn.textContent = 'Update Category';
        cancelBtn.style.display = '';
        clearError();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function resetForm() {
        editingId = null;
        form.reset();
        form.sort_order.value = 0;
        form.is_active.checked = true;
        if (catWidget) catWidget.updatePreview('');
        formTitle.textContent = 'New Category';
        submitBtn.textContent = 'Create Category';
        cancelBtn.style.display = 'none';
    }

    cancelBtn.addEventListener('click', function () { resetForm(); clearError(); });

    function payloadFromForm() {
        return {
            name: form.name.value,
            slug: form.slug.value,
            description: form.description.value,
            image: form.image.value,
            sort_order: Number(form.sort_order.value),
            is_active: form.is_active.checked
        };
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        if (loading) return;
        loading = true; clearError(); submitBtn.disabled = true;
        var prevText = submitBtn.textContent;
        submitBtn.textContent = 'Saving…';
        var payload = payloadFromForm();
        try {
            if (editingId) {
                var res = await window.apiFetch('/api/admin/categories/' + editingId, {
                    method: 'PUT', body: JSON.stringify(payload)
                });
                var d = await res.json();
                if (!res.ok) { showError(d.message || 'Failed.'); return; }
                resetForm();
            } else {
                await window.apiFetch('/api/admin/categories', {
                    method: 'POST', body: JSON.stringify(payload)
                });
                resetForm();
            }
            load();
        } catch (err) {
            showError(editingId ? 'Failed to update category.' : 'Failed to create category.');
        } finally {
            loading = false; submitBtn.disabled = false; submitBtn.textContent = prevText;
        }
    });

    async function remove(id) {
        if (!window.confirm('Delete this category?')) return;
        await fetch('/api/admin/categories/' + id, { method: 'DELETE' });
        load();
    }

    document.addEventListener('DOMContentLoaded', function () {
        catWidget = window.initImageWidget({
            urlInputId:  'catImageUrl',
            fileInputId: 'catImageFile',
            previewId:   'catImgPreview',
            statusId:    'catImgStatus',
            folder:      'misc',
            placeholder: '☰'
        });
        load();
    });
})();
</script>
@endverbatim
@endpush
