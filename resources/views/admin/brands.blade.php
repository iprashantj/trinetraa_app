@extends('layouts.admin')
@section('title', 'Brands')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="h3 mb-0">Brands</h1></div>
<div id="brandError" class="alert alert-danger py-2" style="display:none"></div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold" id="brandFormTitle">New Brand</div>
    <div class="card-body">
        <form id="brandForm" class="row g-3">
            <div class="col-md-5">
                <label class="form-label fw-semibold">Brand Name *</label>
                <input class="form-control" name="name" required>
            </div>
            <div class="col-md-5">
                <label class="form-label fw-semibold">Slug *</label>
                <input class="form-control" name="slug" required>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Sort</label>
                <input type="number" class="form-control" name="sort_order" value="0">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Brand Logo</label>
                <div class="d-flex gap-3 align-items-start">
                    <div id="brandLogoPreview" style="width:80px;height:80px;border:2px dashed #dee2e6;border-radius:8px;overflow:hidden;flex-shrink:0;background:#f8f9fa;display:flex;align-items:center;justify-content:center;font-size:1.5rem">🏷️</div>
                    <div class="flex-grow-1">
                        <input class="form-control mb-2" name="logo" id="brandLogoUrl" placeholder="Paste logo URL — or choose a file to upload">
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            <label class="btn btn-sm btn-outline-secondary mb-0" for="brandLogoFile" style="cursor:pointer">📁 Upload Logo</label>
                            <input type="file" id="brandLogoFile" accept="image/jpeg,image/jpg,image/png,image/webp,image/gif" style="display:none">
                            <span id="brandLogoStatus" class="small text-muted"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Website URL</label>
                <input class="form-control" name="website" placeholder="https://...">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Description</label>
                <input class="form-control" name="description">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Brand Story</label>
                <textarea class="form-control" rows="4" name="story"></textarea>
            </div>
            <div class="col-12 d-flex align-items-center gap-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="bActive" name="is_active" checked>
                    <label class="form-check-label fw-semibold" for="bActive">Active</label>
                </div>
                <button type="submit" class="btn btn-primary" id="brandSubmitBtn">Create</button>
                <button type="button" class="btn btn-secondary" id="brandCancelBtn" style="display:none">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>ID</th><th>Logo</th><th>Name</th><th>Slug</th><th>Sort</th><th>Active</th><th>Actions</th></tr></thead>
                <tbody id="brandTableBody">
                    <tr><td colspan="7" class="text-center py-4 text-muted">No brands yet.</td></tr>
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
    var form = document.getElementById('brandForm');
    var tbody = document.getElementById('brandTableBody');
    var errBox = document.getElementById('brandError');
    var formTitle = document.getElementById('brandFormTitle');
    var submitBtn = document.getElementById('brandSubmitBtn');
    var cancelBtn = document.getElementById('brandCancelBtn');
    var nameInput = form.name;
    var slugInput = form.slug;
    var editingId = null;
    var loading = false;
    var rowsData = [];
    var brandWidget;

    function esc(s) {
        return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
    function slugify(str) { return String(str).toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, ''); }
    function showError(msg) { errBox.textContent = msg; errBox.style.display = 'block'; }
    function clearError() { errBox.textContent = ''; errBox.style.display = 'none'; }

    nameInput.addEventListener('input', function () {
        if (!editingId) slugInput.value = slugify(nameInput.value);
    });

    function load() {
        fetch('/api/admin/brands')
            .then(function (r) { return r.json(); })
            .then(function (d) { rowsData = d.data || []; render(); });
    }

    function render() {
        if (!rowsData.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">No brands yet.</td></tr>';
            return;
        }
        tbody.innerHTML = rowsData.map(function (row) {
            var logoCell = row.logo
                ? '<img src="' + esc(row.logo) + '" alt="' + esc(row.name) + '" style="width:48px;height:32px;object-fit:contain">'
                : '—';
            var activeBadge = row.is_active
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-secondary">Off</span>';
            return '<tr>' +
                '<td>' + esc(row.id) + '</td>' +
                '<td>' + logoCell + '</td>' +
                '<td class="fw-semibold">' + esc(row.name) + '</td>' +
                '<td><code>' + esc(row.slug) + '</code></td>' +
                '<td>' + esc(row.sort_order) + '</td>' +
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
        form.name.value = row.name || '';
        form.slug.value = row.slug || '';
        form.logo.value = row.logo || '';
        if (brandWidget) brandWidget.updatePreview(row.logo || '');
        form.story.value = row.story || '';
        form.description.value = row.description || '';
        form.website.value = row.website || '';
        form.sort_order.value = (row.sort_order != null ? row.sort_order : 0);
        form.is_active.checked = (row.is_active != null ? row.is_active : true);
        formTitle.textContent = 'Edit: ' + (row.name || '');
        submitBtn.textContent = 'Update';
        cancelBtn.style.display = '';
        clearError();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function resetForm() {
        editingId = null;
        form.reset();
        form.sort_order.value = 0;
        form.is_active.checked = true;
        if (brandWidget) brandWidget.updatePreview('');
        formTitle.textContent = 'New Brand';
        submitBtn.textContent = 'Create';
        cancelBtn.style.display = 'none';
    }

    cancelBtn.addEventListener('click', function () { resetForm(); clearError(); });

    function payloadFromForm() {
        return {
            name: form.name.value,
            slug: form.slug.value,
            logo: form.logo.value,
            story: form.story.value,
            description: form.description.value,
            website: form.website.value,
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
        var url = editingId ? '/api/admin/brands/' + editingId : '/api/admin/brands';
        var method = editingId ? 'PUT' : 'POST';
        try {
            var res = await window.apiFetch(url, {
                method: method, body: JSON.stringify(payload)
            });
            if (!res.ok) {
                var d = await res.json();
                throw new Error(d.error || 'Failed');
            }
            resetForm();
            load();
        } catch (err) {
            showError(err.message);
        } finally {
            loading = false; submitBtn.disabled = false;
            submitBtn.textContent = editingId ? 'Update' : 'Create';
        }
    });

    async function remove(id) {
        if (!window.confirm('Delete this brand?')) return;
        await fetch('/api/admin/brands/' + id, { method: 'DELETE' });
        load();
    }

    document.addEventListener('DOMContentLoaded', function () {
        brandWidget = window.initImageWidget({
            urlInputId:  'brandLogoUrl',
            fileInputId: 'brandLogoFile',
            previewId:   'brandLogoPreview',
            statusId:    'brandLogoStatus',
            folder:      'brands',
            placeholder: '🏷️'
        });
        load();
    });
})();
</script>
@endverbatim
@endpush
